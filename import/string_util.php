<?php


  function str_insert($insertstring, $intostring, $offset) {
    $part1 = substr($intostring, 0, $offset);
    $part2 = substr($intostring, $offset);

    $part1 = $part1 . $insertstring;
    $whole = $part1 . $part2;
    return $whole;
  }

  function str_insertFromLast($search, $insert, $subject)
  {

    $offset = strripos($subject, $search);

    $part1 = substr($subject, 0, $offset);
    $part2 = substr($subject, $offset);

    $part1 = $part1 . $insert;
    $whole = $part1 . $part2;
    return $whole;
  }


function str_replace_count($search,$replace,$subject,$times) {

    $subject_original=$subject;

    $len=strlen($search);
    $pos=0;
    for ($i=1;$i<=$times;$i++) {
        $pos=strpos($subject,$search,$pos);

        if($pos!==false) {
            $subject=substr($subject_original,0,$pos);
            $subject.=$replace;
            $subject.=substr($subject_original,$pos+$len);
            $subject_original=$subject;
        } else {
            break;
        }
    }

    return($subject);

}

  function getReferrer() {

    $refDomain = $_REQUEST['URL'] == "" ? $_SERVER['HTTP_REFERER'] : (string) $_REQUEST['URL'];

    $refDomain = strtolower($refDomain);

    if (stristr($refDomain, 'http://')) {
            $refDomain = substr($refDomain, 7);

    }

    if (stristr($refDomain, 'https://')) {
            $refDomain = substr($refDomain, 8);
    }

    if (stristr($refDomain, '/')) {
            $refDomain = substr($refDomain, 0, strpos($refDomain, "/"));

    }

   $refDomain = str_replace ('www.', '', $refDomain);

   /*
    if (!stristr($refDomain, 'www.')) {
            $refDomain = "www.".$refDomain;
    }
    */

    //error_log("\n refDomain: ".$refDomain."\n", 3, "error.log");

    return $refDomain;
  }

?>
