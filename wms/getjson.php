<?php
/**
* getjson.php
* Copyright (C) 2007-2008  LI Hui
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
* @version $Id: proxy.php,v 1.2 2007/12/18 23:12:09 LI Hui Exp $
* @copyright (C) 2006-2007  LI Hui
* @Description : call XML2JSON.class.php to transfer the xml content from URL to Json format
* @contact webmaster@easywms.com
*/

require_once '../Parser/XML2JSON.class.php';

/*   : = %3a   / = %2f   @ = %40
 *   + = %2b   ( = %28   ) = %29
 *   ? = %3f   = = %3d   & = %26
*/
$assalowarray = array("%3a", "%2f", "%40", "%2b", "%28", "%29", "%3f", "%3d", "%26");
$assaupperarray = array("%3A", "%2F", "%40", "%2B", "%28", "%29", "%3F", "%3D", "%26");
$chararray = array(":", "/", "@", "+", "(", ")", "?", "=", "&");

$QUERY_STRING = $_SERVER ['QUERY_STRING'];
// $QUERY_STRING = str_replace($assalowarray, $chararray, $QUERY_STRING);
// $QUERY_STRING = str_replace($assaupperarray, $chararray, $QUERY_STRING);
// this is for suas client, in Firefox the & will be turned to "&amp;" during the viriant transfering
$QUERY_STRING = str_replace('&amp;', '&', $QUERY_STRING);
// only one URL parameter!!!
// url=http://suasdemo.easywms.com/WMS/getmapcap.php?VERSION=1.1.1&SERVICE=WMS&REQUEST=GetCapabilitiesha
$url = $QUERY_STRING;
// if has no URL parameter

if (stripos($url, 'URL=') === false) {
        printErrorMessage("There is no parameter URL in the request.");
    }
    // delete 'url='
    $url = substr($url, 4);
    $jsonContents = "";
    try {
        $xmlStringContents = @file_get_contents($url);
    }
    catch(Exception $e) {
        $jsonContents = $e->getMessage();
        $jsonContents .= ' in ' . $e->getFile() . ', line: ' . $e->getLine() . '.';
        printErrorMessage($jsonContents);
    }

    $jsonContents = xml2json::transformXmlStringToJson($xmlStringContents);
    header("Content-type: text/x-json;charset=UTF-8;");
    header("Cache-Control: no-cache, must-revalidate");
    echo($jsonContents);


    function printErrorMessage($error)
    {
        header("Content-type: text/x-json;charset=UTF-8;");
        header("Cache-Control: no-cache, must-revalidate");
        $errorTempl = '{"ServiceExceptionReport":{"@attributes":{"version":"1.1.1"},"ServiceException":"%errorstring%"}}';
        $error = str_replace("%errorstring%", $error, $errorTempl);

        echo($error);
        exit();
    }

    ?>