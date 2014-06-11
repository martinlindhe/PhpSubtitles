<?php
namespace Writer\Subtitle;

class Srt extends \Writer\Subtitle implements \Writer\SubWriter
{
    public function renderLocal(array $caps)
    {
        $res = '';
        $seq = 0;
        $lf = "\r\n";

        foreach ($caps as $cap) {
            $res .=
            ++$seq.$lf.
            $this->seconds_to_hms($cap->startTime, true, 3, ',', true).
            ' --> '.
            $this->seconds_to_hms($cap->startTime + $cap->duration, true, 3, ',', true).$lf.
            implode($lf, $cap->text).$lf.
            $lf;
        }

        return $res;
    }
    

    /**
     * Renders a second representation as "18:40:22"
     * @param $milli force millisecond rendering?
     * @param $precision decimal precision of milliseconds
     * @param $separator decimal separator for millisecond value
     * @param $pad_hours set to true to always pad hours to 2 digits
     */
    function seconds_to_hms($secs, $show_milli = false, $precision = 2, $separator = '.', $pad_hours = false)
    {
        if (!is_numeric($secs))
            throw new \Exception ('bad input');

        if (!$secs)
            return '00:00:00';

        $frac = $secs - (int) $secs;

        $secs = intval($secs);

        $m = (int) ($secs / 60);
        $s = $secs % 60;
        $h = (int) ($m / 60);
        $m = $m % 60;

        if ($frac || $show_milli)
            $s = $this->round_decimals($s + $frac, $precision);

        if ($pad_hours && $h < 10) $h = '0'.$h;
        if ($m < 10) $m = '0'.$m;
        if ($s < 10) $s = '0'.$s;

        if ($separator != '.')
            $s = str_replace('.', $separator, $s);

        return $h.':'.$m.':'.$s;
    }

    /**
     * Rounds a number to exactly $precision number of decimals, padding with zeros if nessecary
     */
    function round_decimals($val, $precision = 0, $separator = '.', $combinator = '.')  // XXX FIXME move to math.php
    {
        $ex = explode($separator, round($val, $precision));

        if (empty($ex[1]) || strlen($ex[1]) < $precision)
            $ex[1] = str_pad( !empty($ex[1]) ? $ex[1] : 0, $precision, '0');

        if (!$precision)
            return $ex[0];

        return implode($combinator, $ex);
    }

}
