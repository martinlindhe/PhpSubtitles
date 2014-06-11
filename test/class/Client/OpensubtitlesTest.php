<?php
/**
 * @group Client
 */
class OpenSubtitlesTest extends \PHPUnit_Framework_TestCase
{
    private function getConfigObject()
    {
        $conf = new \Client\OpenSubtitlesConfig();
        $conf->username = '';
        $conf->password = '';
        $conf->language = 'eng';

        return $conf;
    }

    function test1() 
    {
        $this->markTestSkipped('TODO client not working');
        
        $videoFile = '/Users/ml/Downloads/Misfits - Season 3/Misfits.S03E01.WS.PDTV.XviD-RiVER.avi';

        $client = new \Client\OpenSubtitles();

        $res = $client->SearchByFileHash($this->getConfigObject(), $videoFile);
        if (!$res) {
            echo "ERROR: no match\n";
            return false;
        }
        var_dump($res);
    }
}
