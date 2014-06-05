<?php
namespace Client;

//TODO: SearchSubtitles api allows multiple video ids
//
//TODO: DownloadSubtitles api allows multiple video id:s, so rework into
//      multi-requesting for several videos at once
//TODO: download sub for all wanted languages if they exists
//TODO: how long do session token last?

//require_once('HashVideo.php');

class SubQueryItem
{
    var $fileName;
	var $fileSize;
	var $hash;
}

/**
 * Downloads subtitles from opensubtitles.org API
 *
 * http://trac.opensubtitles.org/projects/opensubtitles/wiki/XmlRpcIntro
 */
class OpenSubtitles
{
//    protected $userAgent = 'core_dev 1.0';

	protected $apiUrl = 'http://api.opensubtitles.org/xml-rpc';
    protected $apiToken = null;
    protected $languages = array('eng');

    function __construct()
    {		
		if (!extension_loaded('xmlrpc')) {
            throw new \Exception ('php5-xmlrpc not found');
		}
    }
	
	/**
	 * @param string $method
	 * @param array $params
	 */
    private function call($method, $params)
    {
        $opts = array(
        'output_type' => 'xml',
        'verbosity'   => 'no_white_space',
        'escaping'    => 'non-ascii',
        'version'     => 'xmlrpc',
        'encoding'    => 'UTF-8',
        );
        $request = xmlrpc_encode_request($method, $params, $opts);

		echo "ENCODED REQUEST: ".$request."\n";
		
		$header = array(
			'Content-Type: text/xml',
			'Content-Length: '.strlen($request)
		);

		$ch = curl_init();   
		curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

		$data = curl_exec($ch);       
		if (curl_errno($ch)) {
			throw new \Exception('curl error: '.curl_error($ch));
		}

		curl_close($ch);
		return xmlrpc_decode($data);
    }
	
    /**
     * Logs in the user. Required before other API calls. anonymous logins are allowed
     */
    private function login(\Client\OpenSubtitlesConfig $config)
    {
        if ($this->apiToken) {
            return true; // already logged in
		}

        $res = $this->call(
            'LogIn',
            array(
				$config->username,
				$config->password,
				$config->language,
				$config->userAgent,
			)
        );

        if ($res['status'] != '200 OK') {
            d($res);
            throw new \Exception('Login failed');
        }

        $this->apiToken = $res['token'];
        return true;
    }

    private function logout()
    {
        if (!$this->apiToken)
            return false;

        $res = $this->call('LogOut', array($this->apiToken));
        if ($res['status'] != '200 OK')
            throw new \Exception('Logout failed');
		
		$this->apiToken = null;

        return true;
    }

/*
    function fetchAll($items)
    {
//        throw new \Exception ('querying multiple files at once seems to be broken, 2011-07-12');

        if (empty($items))
            throw new \Exception('no input');

        if (!$this->apiToken)
            $this->login();

        $query = $this->buildQuery($items);

d($query);
        $res = $this->call('SearchSubtitles', $query, true);
d($res);
    }
*/
	
    private function createQueryItem($file)
    {
        if (!file_exists($file))
            throw new \Exception('file not found '.$file);

        $i = new SubQueryItem();
        $i->hash = VideoHash::CalcFile($file);
        $i->filename = $file;

        return $i;
    }

	/**
	 * Returns all matches to a file name
	 */
    public function findAllMatches($videoFile)
    {
        $id = $this->SearchByFile($videoFile);
        if (!$id) {
            echo "ERROR: no match\n";
            return false;
        }

        $data = $this->DownloadSubtitles(array($id));
		return $data;
/*
        if (is_subtitle_srt($data))
            $subFile = file_set_suffix($videoFile, '.srt');
        else if (is_subtitle_ass($data))
            $subFile = file_set_suffix($videoFile, '.ass');
        else {
            throw new \Exception('XXX: unrecognized format: '.substr($data, 0, 30).'...');
            $subFile = file_set_suffix($videoFile, '.sub'); //XXX: generic extension for unknown sub format
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
 */
    }

    //XXX extend function to return all subs that was queried
    private function DownloadSubtitles( $arr = array() )
    {		 
        $res = $this->call('DownloadSubtitles', array($this->apiToken, $arr));
        if ($res['status'] != '200 OK') {
            d($res);
            throw new \Exception('DownloadSubtitles fail 1');
        }

        if (count($res['data']) != 1)
            throw new \Exception('Unexpected number of results: '.count($res['data']));

        $data = gzdecode(base64_decode($res['data'][0]['data']));
        if (!$data)
            throw new \Exception('DownloadSubtitles fail 2');

        return $data;
    }

    /**
     * @return IDSubtitleFile of best match, or false
     */
    public function SearchByFileHash(\Client\OpenSubtitlesConfig $config, $fileName)
    {
		$this->login($config);
		
		$fileHash = \Reader\VideoHash::calculateFromFile($fileName);
		$fileSize = filesize($fileName);

        $query = array(
			$this->apiToken,
			array(
				'sublanguageid' => $config->language,
				'moviehash' => $fileHash,
				'moviebytesize' => $fileSize,
				//'imdbid' => $imdbid
			)
		);

        $res = $this->call('SearchSubtitles', $query);

        if (!$res) {
			throw new \Exception('data error - empty result 1');
        }

        if (!$res['data']) {
            throw new \Exception('data error - empty result 2');
		}
		
		return $res;
/*
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

        return $bestId;
 */
    }
}
