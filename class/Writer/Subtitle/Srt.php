<?php
namespace \Writer\Subtitle;

class SubtitleWriterSrt extends \Writer\Subtitle
{
    public function render(\Writer\Subtitle $sub)
    {
        $res = '';
        $seq = 0;
        $lf = "\r\n";

        foreach ($sub->caps as $cap) {
            $res .=
            ++$seq.$lf.
            seconds_to_hms($cap->time, true, 3, ',', true).
            ' --> '.
            seconds_to_hms($cap->time + $cap->duration, true, 3, ',', true).$lf.
            implode($lf, $cap->text).$lf.
            $lf;
        }

        return $res;
    }
}
