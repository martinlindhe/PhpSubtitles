<?php
namespace Client;

//TODO: SearchSubtitles api allows multiple video id:s!!  
//      but test at 2011-07-12 seems its not working (no result returned)
//    XXX see ticket: http://trac.opensubtitles.org/projects/opensubtitles/ticket/25

//TODO: DownloadSubtitles api allows multiple video id:s, so rework into
//      multi-requesting for several videos at once
//TODO: download sub for all wanted languages if they exists
//TODO: how long do session token last?

require_once('TempStore.php');
//require_once('input_gzip.php');
require_once('XmlRpcClient.php');
require_once('HashVideo.php');

class SubQueryItem
{
    var $filename;
    var $hash;
}

/**
 * Downloads subtitles from opensubtitles.org API
 *
 * Documentation:
 * http://trac.opensubtitles.org/projects/opensubtitles/wiki/XmlRpcIntro
 */
class OpenSubtitles extends XmlRpcClient
{
    protected $userAgent = 'core_dev 1.0';

    private   $token      = ''; //auth token, set after a successful login() call
    protected   $username, $password;
    private   $languages  = array('eng');

    function __construct($username = '', $password = '')
    {
        parent::__construct();

        $this->setUrl('http://api.opensubtitles.org/xml-rpc');
        $this->username = $username;
        $this->password = $password;

        $this->setConnectionTimeout(60*5); //5 min timeout, server is slow sometimes
    }

    /**
     * Logs in the user. Required before other API calls. anonymous logins are allowed
     */
    private function login()
    {
        if ($this->token)
            return true; // already logged in

        $temp = TempStore::getInstance();
        $key = 'opensubtitles/token';

        $token = $temp->get($key);
        if ($token) {
//            echo "CACHE: REUSING LOGIN TOKEN ".$token."\n";
            $this->token = $token;
            return true;
        }

        $res = $this->call(
            'LogIn',
            array($this->username, $this->password, $this->languages[0], $this->userAgent)
        );

        if ($res['status'] != '200 OK') {
            d($res);
            throw new \Exception('Login failed');
        }

        $this->token = $res['token'];
        $temp->set($key, $this->token, '20m');

        return true;
    }

    private function logout()
    {
        if (!$this->token)
            return false;

        $res = $this->call('LogOut', array($this->token));
        if ($res['status'] != '200 OK')
            throw new \Exception('Logout failed');

        return true;
    }


    function createQueryItem($file)
    {
        if (!file_exists($file))
            throw new \Exception('file not found '.$file);

        $i = new SubQueryItem();
        $i->hash = VideoHash::CalcFile($file);
        $i->filename = $file;

        return $i;
    }

    function fetchAll($items)
    {
//        throw new \Exception ('querying multiple files at once seems to be broken, 2011-07-12');

        if (empty($items))
            throw new \Exception('no input');

        if (!$this->token)
            $this->login();

        $query = $this->buildQuery($items);

d($query);
        $res = $this->call('SearchSubtitles', $query, true);
d($res);
    }

    private function buildQuery($items)
    {
        $query = array();

        foreach ($items as $item) {
            $size = filesize($item->filename);
            $query[] = array('sublanguageid' => $this->languages[0], 'moviehash' => $item->hash, 'moviebytesize' => $size); //, 'imdbid' => $imdbid );
        }

        return array($this->token, $query) ;
    }

    function fetch($vidFile)
    {
        $id = $this->SearchByFile($vidFile);
        if (!$id) {
            echo "ERROR: no match\n";
            return false;
        }

        $data = $this->DownloadSubtitles(array($id));

        if (is_subtitle_srt($data))
            $subFile = file_set_suffix($vidFile, '.srt');
        else if (is_subtitle_ass($data))
            $subFile = file_set_suffix($vidFile, '.ass');
        else {
            //throw new \Exception ('eeh what format');
            echo "XXX: unrecognized format: ".substr($data, 0, 30)." ...\n";
            $subFile = file_set_suffix($vidFile, '.sub'); //XXX: generic extension for unknown sub format
            file_put_contents($subFile, $data);

            echo "[saved] ".basename($subFile)."\n";
            return true;
        }

        $cleaner = new SubtitleCleaner($data);

        if ($cleaner->cleanup())
            echo "[cleaner] Performed ".$cleaner->changes." changes\n";
//        else
//            echo "[cleaner] No changes performed\n";

        $cleaner->write($subFile);

        echo "[saved] ".basename($subFile)."\n";

        return true;
    }

    //XXX extend function to return all subs that was queried
    private function DownloadSubtitles( $arr = array() )
    {
        $temp = TempStore::getInstance();
        $key = 'SubtitleFetcher/subs/'.json_encode($arr);

        $data = $temp->get($key);
        if ($data)
            return $data;

        $res = $this->call('DownloadSubtitles', array($this->token, $arr));
        if ($res['status'] != '200 OK') {
            d($res);
            throw new \Exception('DownloadSubtitles fail 1');
        }

        if (count($res['data']) != 1)
            throw new \Exception('Unexpected number of results: '.count($res['data']));

        $data = gzdecode(base64_decode($res['data'][0]['data']));
        if (!$data)
            throw new \Exception('DownloadSubtitles fail 2');

        $temp->set($key, $data);

        return $data;
    }

    /**
     * @return IDSubtitleFile of best match, or false
     */
    private function SearchByFile($filename)
    {
        if (!$this->token)
            $this->login();

        $item = new SubQueryItem();
        $item->hash = HashVideo::CalcFile($filename);
        $item->filename = $filename;

        $query = $this->buildQuery(array($item));

        $temp = TempStore::getInstance();
        $key = 'SubtitleFetcher/'.json_encode($query);

        $bestId = $temp->get($key);
        if ($bestId)
            return $bestId;

        $res = $this->call('SearchSubtitles', $query);

        if (!$res) {
            // try again!!!
            $res = $this->call('SearchSubtitles', $query);

            if (!$res)
                throw new \Exception('data error!');
        }

        if (!$res['data'])
            return false;

        $base = file_set_suffix(basename($filename), '');

// d($res);

        //attempts to find a match - XXX improve matching
        //XXX respect   [UserRank] => trusted
        //XXX respect   [SubBad] => 0

        $score = array();
        for ($i=0; $i < count($res['data']); $i++) {

            //echo $m['SubAddDate'].' - '.$m['MovieReleaseName']. ' - '.$m['SubFileName']."\n";
            $id = $res['data'][$i]['IDSubtitleFile'];

            if (!isset($score[$id]))
                $score[$id] = 0;

            if ($res['data'][$i]['MatchedBy'] == 'moviehash')
                $score[$id]++;

            if (stripos($res['data'][$i]['SubAuthorComment'], 'resync') !== false) {
                echo "RESYNC bonus\n";
                $score[$id] +=2 ;
            }

            //exact match by video name
            if (stripos($res['data'][$i]['MovieReleaseName'], $base) !== false)
                $score[$id]++;

            //exact match by sub name
            if (stripos($res['data'][$i]['SubFileName'], $base) !== false)
                $score[$id]++;

            // if file name contains "dvd" then see if release nfo contains "dvd"
            if (stripos($filename, 'dvd') !== false)
                if (stripos($res['data'][$i]['SubFileName'], 'dvd') !== false || stripos($res['data'][$i]['SubAuthorComment'], 'dvd') !== false || stripos($res['data'][$i]['MovieReleaseName'], 'dvd') !== false )
                    $score[$id]++;

            // if SubFormat = "sub", downgrade since we dont have a parser for these
            if ($res['data'][$i]['SubFormat'] == 'sub')
                $score[$id] -= 2;
        }

        $bestScore = -10;
        $bestId    = 0;

        foreach ($score as $id => $val) {
            if ($val > $bestScore) {
                $bestId    = $id;
                $bestScore = $val;
            }
        }

        if (!$bestId) {
            if (count($res['data']) > 1)
                echo "WARNING: failed to select a sub, using first\n";
            $bestId = $res['data'][0]['IDSubtitleFile'];
            d($res['data']);
        }

        echo "[selected] http://www.opensubtitles.org/en/download/file/".$bestId.".gz (score ".$bestScore.", of ".count($res['data'])." matches)\n";

        $temp->set($key, $bestId);

        return $bestId;
    }
}
