<?php
/*
//1..      ends with "?." -> "?"

// 2. förbättra "diff" output av ändrad sub

namespace cd;

require_once('../config.php');

$file = '../test-subs/cleaner-test.srt';

$sub_file = '/tmp/tmp.srt'.mt_rand();

$cleaner = new SubtitleCleaner($file);

echo 'Wrote to '.$sub_file."\n";

if ($cleaner->cleanup())
    echo "Performed ".$cleaner->changes." changes\n";
else
    echo "No changes performed\n";

$cleaner->write($sub_file);
*/