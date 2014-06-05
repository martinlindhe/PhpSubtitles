<?php
namespace Writer\Subtitle;

class Ass extends \Writer\Subtitle
{
    public function render(\Writer\Subtitle $sub)
    {
        $lf = "\r\n";

        $res =
        '[Script Info]'.$lf.
        'Title: <untitled>'.$lf.
        'Original Script: <unknown>'.$lf.
        'ScriptType: v4.00+'.$lf.
        'PlayResX: 384'.$lf.
        'PlayResY: 288'.$lf.
        'PlayDepth: 0'.$lf.
        'Timer: 100.0'.$lf.
        'WrapStyle: 0'.$lf.
        $lf.
        '[v4+ Styles]'.$lf.
        'Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, '.
            'OutlineColour, BackColour, Bold, Italic,  Underline, StrikeOut, '.
            'ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, '.
            'Alignment, MarginL, MarginR, MarginV, Encoding'.$lf.
        'Style: Default,Arial,20,&H00FFFFFF,&H00000000,&H00000000,&H00000000,'.
            '0,0,0,0,100,100,0,0,1,2,0,2,15,15,15,0'.$lf.
        $lf.
        '[Events]'.$lf.
        'Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text'.$lf;

        foreach ($sub->caps as $cap) {
            $res .=
            'Dialogue: 0,'.
            seconds_to_hms($cap->time, true).
            ','.
            seconds_to_hms($cap->time + $cap->duration, true).
            ',Default,,0000,0000,0000,,'.
            implode('\N', $cap->text).$lf;
        }

        return $res;
    }
}
