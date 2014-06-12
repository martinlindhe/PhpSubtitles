<?php
namespace Writer\Subtitle;

class Ass extends \Writer\Subtitle implements \Writer\SubWriter
{
    var $lf = "\r\n";

    private function getHeader()
    {
        $res =
        '[Script Info]'.$this->lf.
        'Title: <untitled>'.$this->lf.
        'Original Script: <unknown>'.$this->lf.
        'ScriptType: v4.00+'.$this->lf.
        'PlayResX: 384'.$this->lf.
        'PlayResY: 288'.$this->lf.
        'PlayDepth: 0'.$this->lf.
        'Timer: 100.0'.$this->lf.
        'WrapStyle: 0'.$this->lf.
        $this->lf.
        '[v4+ Styles]'.$this->lf.
        'Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, '.
            'OutlineColour, BackColour, Bold, Italic,  Underline, StrikeOut, '.
            'ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, '.
            'Alignment, MarginL, MarginR, MarginV, Encoding'.$this->lf.
        'Style: Default,Arial,20,&H00FFFFFF,&H00000000,&H00000000,&H00000000,'.
            '0,0,0,0,100,100,0,0,1,2,0,2,15,15,15,0'.$this->lf.
        $this->lf.
        '[Events]'.$this->lf.
        'Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text'.$this->lf;
        return $res;
    }

    public function renderLocal(array $caps)
    {        
        $res = $this->getHeader();

        foreach ($caps as $cap) {
            $res .=
            'Dialogue: '.
            '0,'.
            $this->renderDuration($cap->startTime).','.
            $this->renderDuration($cap->startTime + $cap->duration).','.
            'Default,,0000,0000,0000,,'.
            implode('\N', $cap->text).
            $this->lf;
        }

        return $res;
    }

    /**
     * Renders a second representation as "18:40:22"
     */
    public function renderDuration($secs)
    {
        if (!is_numeric($secs)) {
            throw new \Exception ('bad input');
        }

        if (!$secs) {
            return '00:00:00';
        }

        $frac = $secs - (int) $secs;

        $secs = intval($secs);

        $m = (int) ($secs / 60);
        $s = $secs % 60;
        $h = (int) ($m / 60);
        $m = $m % 60;

        $s = $this->roundExact($s + $frac, 2);

        if ($m < 10) $m = '0'.$m;
        if ($s < 10) $s = '0'.$s;

        return $h.':'.$m.':'.$s;
    }

    /**
     * Rounds a number to exactly $precision number of decimals,
     * padding with zeros if nessecary
     */
    private function roundExact($val, $precision)
    {
        $ex = explode('.', round($val, $precision));

        if (empty($ex[1]) || strlen($ex[1]) < $precision) {
            $ex[1] = str_pad( !empty($ex[1]) ? $ex[1] : 0, $precision, '0');
        }

        return implode('.', $ex);
    }
}
