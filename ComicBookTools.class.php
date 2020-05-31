<?php
// hide notices about RAR extention complaining about the arcive not
// being the
error_reporting(E_ERROR | E_PARSE);

// comic book images are large and high res
// this increase will allow you to embed the images into html
// with my script.
ini_set('memory_limit', '2048M');

/**
 * ComicBookTools
 * User: Abu Khadeejah Karl Holz
 * Date: 5/25/20
 * Time: 11:10 PM
 *
 * CBR files are RAR arcives with images for the comicbook
 * I didn't see anything else in the files i extracted like an
 * XML or toc file like other ebook formats that my eKatab class can read
 *
 * ***** Need RAR PECL EXTENTION ******************************
 *      ************************
 * you will need this pecl extention installed on your system.
 * i built it from source using the link bellow
 *
 * @link https://pecl.php.net/package/rar
 *
 * extract, then from the dir ->
 *
 * ./configure && make && make test
 * make install
 * ************************************************************
 *
 * Class comic_reader
 */
class comic_reader
{

    public $file  = FALSE;

    public $dir   = FALSE;

    public $comic = array();

    public $embed = FALSE;

    function __construct($file=FALSE) {
        if (is_file($file)) {
            $this->file = $file;
        } else {
            return FALSE;
        }

        return TRUE;
    }

    function __destruct() {
        return true;
    }


    function stream_prefix() {
        if (preg_match('/cbr$/', $this->file))
            return "rar://";
        if (preg_match('/cbz$/', $this->file))
            return "zip://";
        else
            return "";

    }

    function cbr_file() {
        if (!class_exists('RarArchive')) return FALSE;
        $r = RarArchive::open($this->file);
        if ($r === FALSE ) return FALSE;
        $rar = $r->getEntries();
        if ($rar === FALSE) return FALSE;
        if ($this->cover) return $_SERVER['PHP_SELF'] . "?" . base64_encode(str_replace($this->dir, '', $this->file) . "|" . $rar[0]->getName());
        foreach ($rar as $s) {
            if ($this->embed)
                $this->comic[] = $this->read_file($s->getName());
            else
                $this->comic[] = $_SERVER['PHP_SELF'] . "?" . base64_encode(str_replace($this->dir, '', $this->file) . "|" . $s->getName());

        }
        $r->close();

        return true;
    }

    function cbz_file() {
        $zip = new ZipArchive;
        $z = $zip->open($this->file);
        if ($z === TRUE) {
            if ($this->cover) return $_SERVER['PHP_SELF'] . "?" . base64_encode(str_replace($this->dir, '', $this->file) . "|" . $zip->getNameIndex(0));
            for ($i=0; $i < $zip->numFiles; $i++) {
                if ($this->embed)
                    $this->comic[] = $this->read_file($zip->getNameIndex($i));
                else
                    $this->comic[] = $_SERVER['PHP_SELF'] . "?" . base64_encode(str_replace($this->dir, '', $this->file) . "|" . $zip->getNameIndex($i));
            }
            $zip->close();
        } else {
            return FALSE;
        }

        return true;
    }

    /**
     * list_files
     *
     * checks the rar arcive
     * @return bool
     */
    function list_files() {
        $file = $this->cbr_file();
        if (! $file)
            return $this->cbz_file();
        return $file;

    }

    public $cover=FALSE;

    function get_cover( ) {
        $this->cover=TRUE;
        return $this->list_files();

    }

    function read_file($png=FALSE) {
        if (!$png) die("Error, no file name passed");

        $p=$this->stream_prefix();
        $st = $p . $this->file . "#" . urldecode($png);

        if ($this->embed)
            return 'data: Image/png;base64,' . base64_encode(file_get_contents($st));

        header("Content-Type: Image/png");
        imagepng(imagecreatefromstring(file_get_contents($st)));
        exit();

    }

}



/**
 * Class slide_show_html
 */
class slide_show_html {

    public $dir = FALSE;
    public $cbr_list = array();
    public $urls = array();

    function __construct($dir=FALSE) {
        if(is_file('comic.ini')) {
            $this->ini = parse_ini_file('comic.ini', TRUE);
        }
        if (!is_dir($dir)) return FALSE;
        $this->dir=$dir;
        return TRUE;
    }

    function __destruct() { return TRUE; }

    public $ini=array();
    function get_cbr() {
        if (is_dir($this->dir)) {
            $html="";
            $nav="";
            $p=1;
            if (count($this->ini) == 0 ) {
                foreach (glob("$this->dir/*{,/*,/*/*}.cb[rz]",GLOB_BRACE) as $c) {
                    $cb = new comic_reader($c);
                    $cb->dir = $this->dir;
                    $png=$cb->get_cover();

                    $txt=basename($c);
                    $url=$this->comic_url($c);
                    $html .= $this->html_slide($png, $txt, $url);
                    $nav .= $this->html_nav($p);
                    $p++;
                }
            } else {
                foreach ($this->ini as $i => $ini) {
                    if ($ini['show']) {
                        $cb = new comic_reader($ini['file']);
                        $cb->dir = $ini['dir'];
                        $png=$ini['img'][0];

                        $txt=$ini['title'];
                        $url=$this->comic_url($ini['file']);
                        $html .= $this->html_slide($png, $txt, $url);
                        $nav .= $this->html_nav($p);
                        $p++;
                    }
                }
            }

//            $html .= $this->html_buttons();

            return array(
                'html' => $html,
                'nav'  => $nav
            );
        } else { return FALSE; }
    }

    function comic_url($c) {
        return $_SERVER['PHP_SELF']."?".base64_encode(str_replace($this->dir, '', $c));
    }

    function html_buttons() {
        return <<<EOF
        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
        <a class="next" onclick="plusSlides(1)">&#10095;</a>
EOF;
    }

    function html_nav($p){
        return <<<EOF
    <span class="dot" onclick="currentSlide($p)"></span>
EOF;
    }

    function html_slide($img, $p, $link=FALSE) {

        if (! $link) {
            $pages=count($this->urls);
            return <<<EOF
    <div class="mySlides fade">
        <div class="numbertext" >{$this->title} | $p / $pages </div>
        <div class="page"><img src="$img" /></div>
    </div>
EOF;
        } else {
            return <<<EOF
    <div class="mySlides fade">
        <div class="numbertext" ><a href="$link" >$p</a></div>
        <img src="$img" style="width:100%" alt="$p">
    </div>
EOF;
        }
    }

    function cbr_display() {
//        $pages=count($this->urls);
        $p=1;
        $html="";
        $nav="";

        foreach ($this->urls as $c) {
            $url=$c;
            $html .= $this->html_slide($url, $p);
            $nav .= $this->html_nav($p);
            $p++;
        }

        return array(
            'html' => $html,
            'nav'  => $nav
        );

    }
    public $title;

    function __toString() {

        $style=$this->style();
        $script=$this->script();
        $title="cbr/cbz Comicbook viewer";



        if (isset($_SERVER['QUERY_STRING'])) {
            $this->title=str_ireplace(array('.cbr', '.cbz', '.cb7'), array('', '', ''), basename($this->file));

            $cbr=$this->cbr_display();
            $cbr_view=$cbr['html'];
            $cbr_nav=$cbr['nav'];
            $title=str_ireplace(array('.cbr', '.cbz', '.cb7'), array('', '', ''), basename($this->file));
            $nav=$this->html_buttons();
            $main = <<<EOF
<div class="main">
    <div class="slideshow-container">
        $cbr_view
    </div>
    <br>
    <div class="slideshow-nav" style="text-align:center">
        $cbr_nav
    </div>
    $nav
</div>
EOF;



        } else {
            $main="<h1>No Comic has been requested</h1>";

        }

        return <<<EOF
<!DOCTYPE html>
<html>
<head>
    <title>$title</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
    $style
    </style>
</head>
<body>

$main


<script>
$script
</script>

</body>
</html>
EOF
;
    }

    function style() {
        return <<<EOF
        * {box-sizing: border-box}

        .main {
            position: absolute;
            top: 0px;
            bottom: 0px;
            left: 0px;
            right: 0px;
            width: 100%;
            height: 100%;

        }
        .mySlides {display: none;}
        img {vertical-align: middle;}

        /* Slideshow container */
        .slideshow-container {
            position: absolute;
            top: 0;
            bottom: 5%;
            left: 0;
            right: 0;
            width: 100%;
            height: 95%
            text-align: center;


        }

        .slideshow-container img {
            width: 100%;

        }

        .mySlides {
            position: absolute;
            top: 0;
            bottom: 5%;
            left: 0;
            right: 0;
            width: 100%;
            height: 95%;
            text-align: center;
            margin: 0;

        }

        .page{
            position: absolute;
            top: 5%;

            left: 0;
            right: 0;
            width: 100%;
            height: 100%;
            text-align: center;
            margin: auto;
            overflow-y: scroll;
            z-index: 1;
        }
        .slideshow-nav {
            position: absolute;
            bottom: 0px;
            left: 0px;
            right: 0px;
            width: 100%;
            text-align: center
            z-index: 5;
        }
        /* Next & previous buttons */
        .prev, .next {
cursor: pointer;
    position: absolute;
    height: 91%;
    width: auto;
    padding-top: 34%;
    padding-right: 16px;
    padding-bottom: 16px;
    padding-left: 16px;
    bottom: 5%;
    color: white;
    font-weight: bold;
    font-size: 28px;
    transition: 0.6s ease;
    border-radius: 0 3px 3px 0;
    user-select: none;
    z-index: 1;
        }

        /* Position the "next button" to the right */
        .next {
            right: 0;
            border-radius: 3px 0 0 3px;
        }

        /* On hover, add a black background color with a little bit see-through */
        .prev:hover, .next:hover {

            background-color: rgba(255,255,255,0.8);
            color: red;
        }

        /* Caption text */
        .text {
            color: #f2f2f2;
            font-size: 15px;
            padding: 8px 12px;
            position: absolute;
            bottom: 8px;
            width: 100%;
            text-align: center;
        }

        /* Number text (1/3 etc) */
        .numbertext {
            color: #f2f2f2;
            background-color: rgba(5,25,55,0.8);
            font-size: 16px;
            padding: 8px 12px;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5%;
            width: 100%;
            z-index: 5;

            text-align: center;
        }

        .numbertext a {
            color: #f2f2f2;
            font-size: 16px;

            text-align: center;
        }

        /* The dots/bullets/indicators */
        .dot {
            cursor: pointer;
            height: 15px;
            width: 15px;
            margin: 0 2px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
            transition: background-color 0.6s ease;
        }

        .active, .dot:hover {
            background-color: #717171;
        }

        /* Fading animation */
        .fade {
            -webkit-animation-name: fade;
            -webkit-animation-duration: 1.5s;
            animation-name: fade;
            animation-duration: 1.5s;
        }

        @-webkit-keyframes fade {
            from {opacity: .4}
            to {opacity: 1}
        }

        @keyframes fade {
            from {opacity: .4}
            to {opacity: 1}
        }

        /* On smaller screens, decrease text size */
        @media only screen and (max-width: 300px) {
            .prev, .next,.text {font-size: 11px}
        }

        body {
            font-family: "Lato", sans-serif;
            margin:0;
            padding:0;
        }

EOF;

    }

    function script() {
        return <<<EOF
    var slideIndex = 1;
    showSlides(slideIndex);

    function plusSlides(n) {
        showSlides(slideIndex += n);
    }

    function currentSlide(n) {
        showSlides(slideIndex = n);
    }

    function showSlides(n) {
        var i;
        var slides = document.getElementsByClassName("mySlides");
        var dots = document.getElementsByClassName("dot");
        if (n > slides.length) {slideIndex = 1}
        if (n < 1) {slideIndex = slides.length}
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }
        slides[slideIndex-1].style.display = "block";
        dots[slideIndex-1].className += " active";
    }

    /** ******************************************************* */

EOF;


    }
}


