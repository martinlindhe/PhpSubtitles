<?php
namespace Reader;

/**
 * Hash calculation code based on snippet from
 * http://trac.opensubtitles.org/projects/opensubtitles/wiki/HashSourceCodes
 */
class VideoHash
{
    public static function calculateFromFile($file)
    {
        if (!file_exists($file)) {
            throw new \FileNotFoundException();
		}

        $fsize = filesize($file);
        if ($fsize < 65536) {
            throw new \Exception('file too small');
		}

        $handle = fopen($file, 'rb');

        $hash = array(
			3 => 0,
			2 => 0,
			1 => ($fsize >> 16) & 0xFFFF,
			0 => $fsize & 0xFFFF
		);

        for ($i = 0; $i < 8192; $i++) {
            $hash = self::addUint64($hash, $handle);
		}

        $offset = $fsize - 65536;
        fseek($handle, $offset > 0 ? $offset : 0, SEEK_SET);

        for ($i = 0; $i < 8192; $i++) {
            $hash = self::addUint64($hash, $handle);
		}

        fclose($handle);

        $res = sprintf("%04x%04x%04x%04x", $hash[3], $hash[2], $hash[1], $hash[0]);
        return $res;
    }

    private static function addUint64($a, $handle)
    {
        $u = unpack("va/vb/vc/vd", fread($handle, 8));
        $b = array(0 => $u["a"], 1 => $u["b"], 2 => $u["c"], 3 => $u["d"]);

        $o = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);

        $carry = 0;
        for ($i = 0; $i < 4; $i++) {
            if (($a[$i] + $b[$i] + $carry) > 0xffff) {
                $o[$i] += ($a[$i] + $b[$i] + $carry) & 0xffff;
                $carry = 1;
            } else {
                $o[$i] += ($a[$i] + $b[$i] + $carry);
                $carry = 0;
            }
        }

        return $o;
    }
}
