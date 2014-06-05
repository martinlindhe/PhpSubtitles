<?php
/**
 * takes input directory and convert all subtitles to .srt
 */

namespace cd;

require_once('config.php');

if (count($argv) < 2)
    die("Usage: ".$argv[0]." path or filename\n");

$in_cmd = $argv[1];

$files = expand_arg_files($in_cmd, array('*.srt', '*.ass'));
// d($files);

foreach ($files as $f) {
    echo "Parsing ".$f." ...\n";

    $cleaner = new SubtitleCleaner($f);

    if ($cleaner->cleanup()) {
        echo "Performed ".$cleaner->changes." modifications\n";
    //die('XXXX');
    }

    $out_file = no_suffix($f).'.srt';

    $cleaner->write($out_file, 'srt');
    echo "Wrote subtitle to ".$out_file."\n";
}
