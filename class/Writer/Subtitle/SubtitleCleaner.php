<?php
namespace Writer\Subtitle;

//TODO: autodetect language (eng,swe) before correcting spelling etc
//TODO: autodetect input CP1252 (windows-1252) and convert to utf-8, see test-subs/win1252.*.srt

class SubtitleCleaner
{
    private $parser;                 ///< sub parser object
    var     $cleanedCaps = array();  ///< cleaned up caps
    var     $changes      = 0;       ///< number of changes performed

    /**
     * Removes crap from subs
     *
     * @param $caps array of \Reader\SubtitleCaption
     * @return true if sub was changed
     */
    public function cleanupCaptions(array $caps)
    {
        $strings = array(
        // eng subs:
        'subtitles:', 'subtitles by',
        'transcript :', 'transcript:', 'transcript by',
        'sync by n17t01',
        'sync,', 'synchro :', 'synchro:', 'synchronized by', 'synchronization by',
        'resync:', 'resynchro',
        'encoded by',
        'subscene',
        'seriessub',
        'addic7ed', 'addicted.com', 'allsubs.org', 'hdbits.org', 'bierdopje.com',
        'ragbear.com', 'ydy.com', 'yyets.net', 'indivx.net', 'sub-way.fr', 'forom.com',
        'napisy.org', '1000fr.com', 'opensubtitles.org', 'o p e n s u b t i t l e s',
        'sous-titres.eu', '300mbfilms.com',
        'thepiratebay',
        'MKV Player', // "Tip for download: Open Subtitles MKV Player"
        // swe subs:
        'swedish subtitles',
        'undertexter.se','undertexter. se', 'swesub.nu', 'divxsweden.net',
        'undertext av', 'översatt av', 'översättning av', 'rättad av', 'synkad av', 'synkat av', 
        'text av', 'text:', 'synk:', 'synkning:', 'transkribering:', 'korrektur:',
        'mediatextgruppen', 'texter på nätet',
        );

        $cleanedCaps = array();
        $changes = 0;
        foreach ($caps as $cap) {
            $skip = false;
            for ($i = 0; $i < count($cap->text); $i++) {
/*
                // Windows-1252 characters:  FIXME detect sub encoding, this will break utf8 files
                $tmp = $cap->text[$i];

                $cap->text[$i] = str_replace("“", '"', $cap->text[$i]);
                $cap->text[$i] = str_replace("”", '"', $cap->text[$i]);
                $cap->text[$i] = str_replace("…", '...', $cap->text[$i]);
                $cap->text[$i] = str_replace("⁈", '?!', $cap->text[$i]);
                $cap->text[$i] = str_replace("–", '-', $cap->text[$i]);
                $cap->text[$i] = str_replace("—", '-', $cap->text[$i]);
                $cap->text[$i] = str_replace("·", '.', $cap->text[$i]);

                if ($cap->text[$i] != $tmp)
                {
                    echo "Modified cap ".$cap->seq.":".$i.":\n";
                    echo "  From: ".$tmp."\n";
                    echo "  To  : ".$cap->text[$i]."\n";
                    $this->changes++;
                }
*/
                foreach ($strings as $s) {
                    if (mb_stripos(utf8_encode($cap->text[$i]), $s) !== false) {
                        echo 'Removed cap '.$cap->seq.": ";

                        foreach ($cap->text as $t) {
                            echo '"'.$t."\",\t";
                        }
                        echo "\n";

                        $skip = true;
                        $this->changes++;
                        break;
                    }
                }

                if (substr($cap->text[$i], -2) == '?.') {
                    $cap->text[$i] = substr($cap->text[$i], 0, -1);

                    echo 'Changed cap '.$cap->seq.': ?. -> ? in "'.$cap->text[$i]."\"\n";
                    $skip = true;
                    $changes++;
                }

                if ($skip)
                    break;
            }

            if ($skip)
                continue;

            $cleanedCaps[] = $cap;
        }

        $this->changes = $changes;
        return $cleanedCaps;
    }
}
