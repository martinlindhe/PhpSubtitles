<?php
/**
 * @group Writer
 */

//TODO förbättra "diff" output av ändrad sub

class SubtitleCleanerTest extends \PHPUnit_Framework_TestCase
{
    function test1()
    {   
        $reader = new \Reader\Subtitle\Srt();

        $text =
            "0\n". // wrong start index
            "00:00:04,630 --> 00:00:06,018\n".
            "<i>Go ninja!</i>\n";
        
        $cleaner = new \Writer\Subtitle\SubtitleCleaner();
        $cleanCaps = $cleaner->cleanupCaptions(
            $reader->parse($text)
        );
        
        $this->assertEquals(0, $cleaner->changes);
        
            
        $cleanText = \Writer\Subtitle::render($cleanCaps, new \Writer\Subtitle\Srt());
        
        $this->assertEquals(
            "1\r\n". // fixed start index & line feeds
            "00:00:04,630 --> 00:00:06,018\r\n".
            "<i>Go ninja!</i>\r\n".
            "\r\n",
            $cleanText
        );
    }
}
