<?php
namespace Reader\Subtitle;

/**
 * .srt SubRip subtitle reader
 *
 * Unofficial documentation: http://forum.doom9.org/showthread.php?p=470941#post470941
 * Player support: http://ale5000.altervista.org/subtitles.htm
 *
 *
 * http://en.wikipedia.org/wiki/SubRip
 *
 * The time format used is hours:minutes:seconds,milliseconds. The decimal separator
 * used is the comma, since the program was written in France, and the line break
 * used is the CR+LF pair. Subtitles are indexed numerically, starting at 1.
 *
 *   Subtitle number
 *   Start time --> End time
 *   Text of subtitle (one or more lines)
 *   Blank line
 */
class Srt extends \Reader\Subtitle
{
    function isRecognized($data)
    {
        $data = utf8_strip_bom($data);

        if (
            substr($data, 0, 2) == "1\n"   || substr($data, 0, 2) == "0\n"   || substr($data, 0, 3) == "-1\n" || //Unix encoding
            substr($data, 0, 3) == "1\r\n" || substr($data, 0, 3) == "0\r\n" || substr($data, 0, 4) == "-1\r\n"  //Windows encoding
        )
            return true;

        return false;
    }

    protected function parse($data)
    {
        if (!$data)
            return false;

        // file_put_contents('xxx.raw', $data);
        
        // UTF-8 BOM marker
        if (substr($data, 0, 3) == "\xEF\xBB\xBF") {
            $data = mb_convert_encoding(substr($data, 3), 'ISO-8859-1', 'UTF-8');
        }        
            
        /*
        // UTF-16-LE BOM marker
        if (substr($data, 0, 2) == "\xFF\xFE")
        {
            $data = mb_convert_encoding(substr($data, 2), 'ISO-8859-1', 'UTF-16LE');
        }
        */

        if (!is_subtitle_srt($data))
            throw new \Exception('Not a srt!');

        // NOTE: some subs incorrectly use unix linefeeds, so we convert
        // all windows CRLF pairs to LF to simplify parsing
        $data = str_replace("\r\n", "\n", trim($data));

        $rows = explode("\n", $data);

        for ($i = 0; $i <= count($rows); $i++) {
            $rows[$i] = trim($rows[$i]);
            
            if ($rows[$i] === "")
                continue;

            if (!is_numeric($rows[$i]))
                throw new \Exception('expected sequence number (integer), found odd data at line '.($i+1).': "'.$rows[$i]."\"\n".$rows[$i+1]);

            $cap = new SubtitleCaption();
            $cap->seq = $rows[$i];

            $aa = explode(' --> ', trim($rows[$i+1]));  // 00:26:36,595 --> 00:26:40,656

            if (!is_hms($aa[0]) || !is_hms($aa[1]))
                throw new \Exception('expected time period, found odd data at row '.($i+2).': '.$rows[$i+1]);

            $cap->time     = in_seconds($aa[0]);    // sub start time
            $cap->duration = in_seconds($aa[1]) - $cap->time;

            // find multi-line sub, allow all text until new numeric is found (next chunk)
            for ($j=2; $j <= 5; $j++) {
                if (!isset($rows[$i+$j]))
                    break;
                    
                $rows[$i+$j] = trim($rows[$i+$j]);
                if (!$rows[$i+$j])
                    break;

                if (numbers_only($rows[$i+$j]) && $j >= 3 && ($rows[$i+$j] <= 10000)) {
                    throw new \Exception("XXX: breaking at row ".($i+$j)." on data ". ($rows[$i+$j]));
                    $i--;
                    break;
                }

                // allow first line to be empty (found in some crappy files)
                if ($j>2 && !$rows[$i+$j])
                    break;

                if ($rows[$i+$j])
                    $cap->text[] = $rows[$i+$j];
            }

            $i += $j;

            // HACK to show 0-duration text for 1 second
            if ($cap->duration <= 0 && !empty($cap->text))
                $cap->duration = 1;

            // exclude caps without text
            if (empty($cap->text))
                continue;

            $this->caps[] = $cap;
        }

        return true;
    }
}
