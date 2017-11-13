<?php
/**
 * Send Exception Class
 * Copyright (C) 2006-2007  leelight
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
 * @version $Id$
 * @copyright (C) 2006-2007  leelight
 * @Description : This show the copyright .
 * @contact webmaster@easywms.com
 * @version $1.0$ 2005
 * @Author Filmon Mehari and Professor Dr. Franz Josef Behr
 * @Contact filmon44@yahoo.com and franz-josef.behr@hft-stuttgart.de
 * @version $2.0$ 2006.05
 * @Author Chen Hang and leelight
 * @Contact unitony1980@hotmail.com
 * @version $3.0$ 2006
 * @Author leelight
 * @Contact webmaster@easywms.com
 */
class SendExceptionClass {
    private $serverhost;
    private $wfsservice;
    private $wfsversion;

    public function SendExceptionClass($serverhost, $wfsservice, $wfsversion)
    {
        $this->serverhost = $serverhost;
        $this->wfsservice = $wfsservice;
        $this->wfsversion = $wfsversion;
    }

    function sendexception ($errornumber, $errorexceptionstring)
    {
        $this->printXMLException($errornumber, $errorexceptionstring);
        exit();
    }
    public function printXMLException($errornumber, $errorexceptionstring)
    {
        header("Content-type: text/xml;charset=utf-8");
        echo('<?xml version="1.0" encoding="UTF-8" ?>' . "\n");
        echo('<ExceptionReport version="' . $this->wfsversion . '" xmlns="http://www.opengis.net/ogc"
			xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
			xsi:schemaLocation="owsExceptionReport.xsd">' . "\n");
        echo('<Exception code="' . $errornumber . '" locator="INSERT STMT 01">' . "\n");
        echo('<ExceptionText>' . "\n");
        echo ($errorexceptionstring . "\n");
        echo('</ExceptionText>' . "\n");
        echo('</Exception>' . "\n");
        echo('</ExceptionReport>');
    }
}

?>