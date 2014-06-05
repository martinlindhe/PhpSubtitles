<?php
/**
 * @group Reader
 */
class SrtTest extends \PHPUnit_Framework_TestCase
{
    function testParse()
    {
        $reader = new \Reader\Subtitle\Srt();

        $text =
            "1\n".
            "00:00:04,630 --> 00:00:06,018\n".
            "<i>♪ Go ninja! ♪\n".
            "[title music]</i>\n".
            "\n".
            "2\n".
            "00:00:06,053 --> 00:00:08,138\n".
            "<i>[Randy] I was chosen\n".
            "to protect my school\n".
            "from all evil</i>\n";

        $caps = $reader->parse($text);

        $expectedOne = new \Reader\SubtitleCaption();
        $expectedOne->seq = 1;
        $expectedOne->startTime = 4.63;
        $expectedOne->duration = 1.388;
        $expectedOne->text = array('<i>♪ Go ninja! ♪', '[title music]</i>');

        $this->assertEquals(
            $expectedOne,
            $caps[0]
        );

        $expectedTwo = new \Reader\SubtitleCaption();
        $expectedTwo->seq = 2;
        $expectedTwo->startTime = 6.053;
        $expectedTwo->duration = 2.085;
        $expectedTwo->text = array('<i>[Randy] I was chosen', 'to protect my school', 'from all evil</i>');

        $this->assertEquals(
            $expectedTwo,
            $caps[1]
        );
    }

    function testParseWrongSequence()
    {
        // NOTE sequence numbering starts at 1,
        // make sure we generate proper sequence on broken input
        $reader = new \Reader\Subtitle\Srt();

        $text =
            "0\n".
            "00:00:04,630 --> 00:00:06,018\n".
            "<i>♪ Go ninja! ♪</i>\n";

        $caps = $reader->parse($text);

        $expectedOne = new \Reader\SubtitleCaption();
        $expectedOne->seq = 1;
        $expectedOne->startTime = 4.63;
        $expectedOne->duration = 1.388;
        $expectedOne->text = array('<i>♪ Go ninja! ♪</i>');

        $this->assertEquals(
            $expectedOne,
            $caps[0]
        );
    }
}
