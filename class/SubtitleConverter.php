<?php
namespace cd;

// TODO move into a function of Writer\Subtitle

/**
 * Converts subtitles between different formats
 */
class SubtitleConverter
{
    /**
     * @param $in local file or data
     */
    static function convertTo($in, $format)
    {
        $cleaner = new SubtitleCleaner($in);

        if ($cleaner->cleanup())
            echo "[cleaner] Performed ".$cleaner->changes." changes\n";
        else
            echo "[cleaner] No changes performed\n";

        return $cleaner->render($format);
    }
}
