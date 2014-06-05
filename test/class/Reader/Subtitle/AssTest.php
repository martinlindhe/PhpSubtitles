<?php
/**
 * @group Reader
 */
class AssTest extends \PHPUnit_Framework_TestCase
{
	function testParse()
	{
		$reader = new \Reader\Subtitle\Ass();
		
		$this->markTestIncomplete('TODO implement');
	}
	
    function testStripTags()
    {
        $this->assertEquals(
            'Sure, maybe.',
            \Reader\Subtitle\Ass::stripTags('{\i1}Sure, maybe.{\i0}')
        );
        
        $this->assertEquals(
            'THE DAY',
            \Reader\Subtitle\Ass::stripTags('{\fad(1000,1000)}THE DAY')
        );
                
        $this->assertEquals(
            '– Here?',
            \Reader\Subtitle\Ass::stripTags('{\an1\pos(134,273)}– Here?')
        );
    }
}
