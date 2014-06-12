<?php
namespace Writer\Subtitle;

class Srt extends \Writer\Subtitle implements \Writer\SubWriter
{
    var $lf = "\r\n";

    public function renderLocal(array $caps)
    {
        $res = '';
        $seq = 0;

        foreach ($caps as $cap) {
            $seq++;
            $res .=
            $seq.$this->lf.
            $this->renderDuration($cap->startTime).
            ' --> '.
            $this->renderDuration($cap->startTime + $cap->duration).$this->lf.
            implode($this->lf, $cap->text).$this->lf.
            $this->lf;
        }

        return $res;
    }

    /**
     * Renders a second representation as "00:00:06,018"
     */
    public function renderDuration($secs)
    {        
        if (!is_numeric($secs)) {
            throw new \Exception ('bad input');
        }

        if (!$secs) {
            return '00:00:00,000';
        }

        $frac = $secs - (int) $secs;

        $secs = intval($secs);

        $m = (int) ($secs / 60);
        $s = $secs % 60;
        $h = (int) ($m / 60);
        $m = $m % 60;

        $s = $this->roundExact($s + $frac, 3);

        if ($h < 10) $h = '0'.$h;
        if ($m < 10) $m = '0'.$m;
        if ($s < 10) $s = '0'.$s;

        $s = str_replace('.', ',', $s);

        return $h.':'.$m.':'.$s;
    }

    /**
     * Rounds a number to exactly $precision number of decimals,
     * padding with zeros if nessecary
     */
    private function roundExact($val, $precision)
    {
        if (!is_numeric($val)) {
            throw new \Exception('not numeric');
        }
            
        $ex = explode('.', round($val, $precision));

        if (empty($ex[1]) || strlen($ex[1]) < $precision) {
            $ex[1] = str_pad( !empty($ex[1]) ? $ex[1] : 0, $precision, '0');
        }

        return implode('.', $ex);
    }

}
