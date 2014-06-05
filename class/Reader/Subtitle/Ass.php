<?php
namespace Reader\Subtitle;

/**
 * .ass file extension is used by Advanced SubStation Alpha (ASS) subtitles
 *
 * http://www.matroska.org/technical/specs/subtitles/ssa.html
 * http://en.wikipedia.org/wiki/SubStation_Alpha#Advanced_SubStation_Alpha
 */
class Ass extends \Reader\Subtitle
{
    var $mode = '';

    public static function isRecognized($data)
    {
        if (substr($data, 0, 13) == '[Script Info]')
            return true;

        return false;
    }

    /**
     * Removes ASS text formatting encoding, code contained within {\ and }
     */
    public static function stripTags($s)
    {
        $pattern = '/\{.*?\}/';
        $s = preg_replace($pattern, '', $s);
        return $s;
    }

    public function parse($data)
    {
        if (!$data)
            return false;

//        $data = utf8_strip_bom($data);

        if (!is_subtitle_ass($data)) {
            throw new \Exception('Not a ASS/SSA!');
        }

        $blocks = explode("\r\n", trim($data));

        $seq = 0;

        foreach ($blocks as $block) {
            if (!$block)
                continue;

            if ($block == '[Script Info]')  //XXX parse
                continue;

            if ($block == '[v4+ Styles]')  //XXX parse
                continue;

            if ($block == '[Events]') {     // Events hold the main dialogue
                $this->mode = 'events';
                continue;
            }

            switch ($this->mode) {
            case 'events':
                if (substr($block, 0, 7) == 'Format:')
                    continue;

                $piece = explode(',', trim($block), 10);

                if (!is_hms($piece[1]) || !is_hms($piece[2])) {
                    throw new \Exception('odd format: '.$piece[1].' ... '.$piece[2]);
                }

                $cap = new SubtitleCaption();
                $cap->seq      = ++$seq;
                $cap->time     = in_seconds($piece[1]);
                $cap->duration = in_seconds($piece[2]) - $cap->time;

                $txt = explode('\N', $piece[9]);  // split multi-line text

                // exclude caps without text
                if (empty($txt))
                    continue;

                $cap->text = self::stripTags($txt);

                $this->caps[] = $cap;
                break;

            default:
                continue;
            }

        }

        return true;
    }
}
