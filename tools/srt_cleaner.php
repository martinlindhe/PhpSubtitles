<?php
/**
 * Parses a .srt (SubRip text) subtitle, cleans it up and writes it to disk
 */

namespace cd;

require_once('config.php');

if (count($argv) < 2)
    die("Usage: ".$argv[0]." path or filename\n");

$in_cmd = $argv[1];

$backup = false; // perform backup?


$files = expand_arg_files($in_cmd, array('*.srt', '*.ass'));
// d($files);

foreach ($files as $f) {
    echo "Parsing ".$f."...\n";

    $cleaner = new SubtitleCleaner($f);

    if ($cleaner->cleanup()) {
        echo "Performed ".$cleaner->changes." modifications\n";

        if ($backup) {
            //backup orginal file
            $backup_file = file_set_suffix($f, '.srt.org');
            echo "Backed up orginal file as ".$backup_file."\n";
            rename($f, $backup_file);
        }

        $cleaner->write($f);
        echo "Wrote clean subtitle to ".$f."\n";
    } else {
        echo "No changes performed\n";
    }
}
