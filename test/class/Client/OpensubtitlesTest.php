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
		$videoFile = '/Users/ml/Downloads/command_line_tools_for_osx_mavericks_april_2014.dmg';

		$client = new \Client\OpenSubtitles();
		
		$res = $client->SearchByFileHash($this->getConfigObject(), $videoFile);
		if (!$res) {
			echo "ERROR: no match\n";
			return false;
		}
		var_dump($res);
	}
}