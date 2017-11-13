<?php
     
  /* This is the WMS Server address */
  $wmsURL = $_REQUEST["mapserver"];

  /* Replace "\\" by "\".
     Typically introduced by map specification in UNM server
     in Windows environment. */
  if(get_magic_quotes_gpc())
    $wmsURL = stripslashes($wmsURL);

  //print_r($_REQUEST);
  
  $request = "";
  $sep = "";
  foreach ( $_REQUEST as $key => $value ) {
    //echo "Key: ".$key.", Value: ".$value."<br>";
    /* Remove the "mapserver" key from the request */
    if ( $key != "mapserver") {
      $request .= $sep.$key."=".$value;
      $sep = "&";
    }
  }
   

  $format = "text/xml";
  if (array_key_exists('format', $_REQUEST))
  	$format = $_REQUEST["format"];
  else if (array_key_exists('FORMAT', $_REQUEST))
  	$format = $_REQUEST["FORMAT"];
  else
   $format = "text/xml";
      
  $sep = "?";
  if ( strrchr($wmsURL, "?") != false ) $sep = "&";
  if ( $wmsURL{strlen($wmsURL)-1} == "?" ) $sep = "";

  /* Optionally write all requests in a log file */
  /*
  $fh = fopen("proxy.log", "ab+");
  $timestamp = strftime("[%Y-%m-%d %H:%M:%S]");  
  foreach ( $_REQUEST as $key => $value ) {
    fwrite($fh, "Key: ".$key.", Value: ".$value."\n");
  }
  fwrite($fh, $timestamp.": ".$wmsURL.$sep.$request."\n");
  fclose($fh);
  */

  header("Content-type: $format");
	$url = subStr($wmsURL.$sep.$request, 1);
  readfile($url);

?>
