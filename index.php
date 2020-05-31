<?php
require_once("ComicBookTools.class.php");

$dir = "./comic" ;

if ( array_key_exists('QUERY_STRING', $_SERVER) && isset($_SERVER['QUERY_STRING'])) {
    $file=rawurldecode(base64_decode($_SERVER['QUERY_STRING']));
    $cb='';

    if (is_file($file)){
        $cb = $file;
    } elseif(is_file($dir . $file)) {
        $cb = $dir . $file;
    } else {
        list($arc, $img) = explode('|', $file);
        if (is_file($dir . $arc)) {
            $cb=$dir . $arc;
            $cbr = new comic_reader($cb);
            $cbr->dir=$dir;
            $cbr->read_file($img);
            exit();

        }
        header("Location: {$_SERVER['PHP_SELF']}}");
        die();

    }

    if ($cbr=new comic_reader($cb)) {
        $cbr->dir=$dir;
        $urls = $cbr->list_files();

        $slides_tmpl= new slide_show_html();
        $slides_tmpl->file=$cb;
        $slides_tmpl->dir=$dir;
        $slides_tmpl->urls=$cbr->comic;
        echo $slides_tmpl;
        exit();
    } else {

    }

} else {

    $ini=parse_ini_file('comic.ini', TRUE);
    $main="";
    foreach ($ini as $i) {
        $img=$i['img'][0];
        $url=$_SERVER['PHP_SELF'] . "?" . base64_encode(str_replace($i['dir'], '', $i['file']));
        $txt=$i['title'];

        $main .= <<<EOF
<li><a href="$url" target="_blank">$txt</a><br /><img src="$img" /></li>
EOF;

    }

}




?><!DOCTYPE html>
<html>
<head>
    <title>cbr/cbz Comicbook viewer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {box-sizing: border-box}

        body {
            font-family: "Lato", sans-serif;
            margin:0;
        }
        img {
            min-width: 100px;

            max-width: 300px;
            height: 500px;
            padding: 5px;
            margin: 5px;;
        }
        a {
            font-family: fantasy;
            font-size: larger;
            color: ghostwhite;
            background-color: darkred;
        }
        ul {
            list-style-type: none;

        }

        li {
            padding: 5px;
            margin: 5px;
            float: left;
            vertical-align: middle;
            text-align: center;
            border: solid;
            border-color: navy;
        }

    </style>
</head>
<body>
<ul>
<?php echo $main ?>
</ul>
<script>

</script>

</body>
</html>