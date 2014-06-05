<?php
/**
 * takes input directory and convert all subtitles to .srt
 */

namespace cd;

require_once('config.php');

if (count($argv) < 2)
    die("Usage: ".$argv[0]." path or filename\n");

$inCmd = $argv[1];

$files = expand_arg_files($inCmd, array('*.srt', '*.ass'));
// d($files);

foreach ($files as $f) {
    echo "Parsing ".$f." ...\n";

    $cleaner = new SubtitleCleaner($f);

    if ($cleaner->cleanup()) {
        echo "Performed ".$cleaner->changes." modifications\n";
    //die('XXXX');
    }

    $outFile = no_suffix($f).'.srt';

    $cleaner->write($outFile, 'srt');
    echo "Wrote subtitle to ".$outFile."\n";
}
