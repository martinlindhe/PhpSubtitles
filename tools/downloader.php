<?php
/**
 * Fetches subtitles for all video files in specified directory & cleans them up
 */

namespace cd;

require_once('config.php');

if ($argc == 1) {
    echo "Syntax: ".$argv[0]." [videos|directories]\n";
    die;
}

echo "[".now()."] downloader started\n";

$files = expand_arg_files($argv[1], array('*.avi', '*.mkv', '*.mp4'));

print_r($files);

$fetcher = new SubtitleFetcher();


$items = array();

foreach ($files as $vid_file) {
    echo "[search] ".basename($vid_file)."\n";

    if ($fetcher->fetch($vid_file) && count($files) > 1) {
        $delay = 100000; //0.1 sec
        echo "[sleep] ".($delay / 1000000)." s\n\n";
        usleep($delay);
    }


//    $items[] = $fetcher->createQueryItem($vid_file);
}

//$fetcher->fetchAll($items);
