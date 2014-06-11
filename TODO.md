## TODO

fix tests, finish rewrite

. in bootstrap, register commands to be available from cli,
  like "php bootstrap.php download "Skins S02E02"





## OLD TODO:

HI PRIO:
    * update downloader (download.php to calculate all id:s first, then download them in one request)
        XXX dont work due to api problems?
        http://trac.opensubtitles.org/projects/opensubtitles/ticket/25

    * make uploader.php functional



MID PRIO:
    * Universal Subtitle Format (.usf) reader & writer (not common format)
        time based (GOOD!)
        vlc ska tydligen stödja formatet?
        http://www.titlevision.com/usf.htm
        http://en.wikipedia.org/wiki/Universal_Subtitle_Format



LOW PRIO:
    * MicroDVD (.sub) reader & writer (not very common format anymore)
        frame based (BAD!)
        http://en.wikipedia.org/wiki/MicroDVD

