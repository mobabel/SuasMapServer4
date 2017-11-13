<?php

/**
 * proxy.php
 * Copyright (C) 2006-2007  LI Hui
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @version $Id: proxy.php,v 1.2 2007/12/14 16:41:46 LI Hui Exp $
 * @copyright (C) 2006-2007  LI Hui
 * @Description: proxy for ajax cross domain request
 * @contact webmaster@easywms.com
 */

/*   : = %3a   / = %2f   @ = %40
 *   + = %2b   ( = %28   ) = %29
 *   ? = %3f   = = %3d   & = %26
*/
$assalowarray = array("%3a", "%2f", "%40", "%2b", "%28", "%29", "%3f", "%3d");
$assaupperarray = array("%3A", "%2F", "%40", "%2B", "%28", "%29", "%3F", "%3D");
$chararray = array(":", "/", "@", "+", "(", ")", "?", "=");

$QUERY_STRING = $_SERVER ['QUERY_STRING'];


//$QUERY_STRING = str_replace($assalowarray, $chararray, $QUERY_STRING);
//$QUERY_STRING = str_replace($assaupperarray, $chararray, $QUERY_STRING);
//this is for suas client, in Firefox the & will be turned to "&amp;" during the viriant transfering
$QUERY_STRING = str_replace('&amp;', '&', $QUERY_STRING);

//http://suasdemo.easywms.com/WMS/getmapcap.php?VERSION=1.1.1&SERVICE=WMS&REQUEST=GetCapabilities

/*
$a = explode('&', $QUERY_STRING);
$i = 0;
while ($i < count($a)) {
    $b = split('=', $a[$i]);
    $text_upper = strtoupper($b[0]);
    if ($text_upper == "URL") {
        $url = htmlspecialchars(urldecode($b[1]));
    }
    $i++;
}
*/
//only one URL parameter!!!
//url=http://suasdemo.easywms.com/WMS/getmapcap.php?VERSION=1.1.1&SERVICE=WMS&REQUEST=GetCapabilitiesha
$url = $QUERY_STRING;
$url =  substr($url, 4);
//$url = str_replace("URL=", "", $url);

header("Content-type: text/xml;charset=UTF-8;");
header("Cache-Control: no-cache, must-revalidate");

$source = @file_get_contents($url);
//can not have space before XML data, that will cause js cant recognize it is xmldom object.
echo trim($source);

?>