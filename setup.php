<?php
/**
 * Created by PhpStorm.
 * User: kholz
 * Date: 5/29/20
 * Time: 10:05 AM
 */

require_once("ComicBookTools.class.php");

$dir = "./comic" ;

$compatable=array();
$bad=array();
$ini=array();
$_SERVER['PHP_SELF']="index.php";
foreach (glob("$dir/*{,/*,/*/*}.cb[zr7]",GLOB_BRACE) as $c) {
    $ini[$c]['title']=str_replace(array('.cbr', '.cbz', '.cb7'), array('','',''), basename($c));

    $ini[$c]['file']=$c;
    $ini[$c]['dir']=$dir;
    if ($cbr=new comic_reader($c)) {
        $cbr->dir=$dir;
        $cbr->list_files();
        foreach ($cbr->comic as $i)
            $ini[$c]['img'][]=$i;
        if (count($cbr->comic) > 0) {
            $compatable[$c]=$cbr->comic;
            $ini[$c]['show']=1;
        } else {
            $bad[]=$c;
            $ini[$c]['show']=0;
        }
    }

}

echo "Good Comics: " . count($compatable) . "\n";
echo "Bad format: " . count($bad) . "\n";

$inf="";
foreach ($ini as $t => $i) {
    $inf .= "[$t]\n";
    foreach ($i as $tt => $ii) {
        if (!is_array($ii)) {
            $inf .= "$tt=\"$ii\"\n";
        } else {
            foreach ($ii as $iii) $inf .= $tt."[]=\"$iii\"\n";
        }

    }
}
file_put_contents('comic.ini',$inf);