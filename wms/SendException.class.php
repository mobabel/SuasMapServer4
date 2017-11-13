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
    private $wmsservice;
    private $wmsversion;
    private $exceptions;
    private $format;
    private $minx, $miny, $maxx, $maxy, $width, $height, $enablestretchmap;

    public function SendExceptionClass($serverhost, $wmsservice, $wmsversion , $exceptions, $format,
        $minx, $miny, $maxx, $maxy, $width, $height, $enablestretchmap, $defaultwidth, $defaultheight)
    {
        $this->serverhost = $serverhost;
        $this->wmsservice = $wmsservice;
        $this->wmsversion = $wmsversion;
        $this->exceptions = $exceptions;
        $this->format = $format;
        $this->minx = $minx;
        $this->miny = $miny;
        $this->maxx = $maxx;
        $this->maxy = $maxy;
        $this->width = $width;
        $this->height = $height;
        $this->enablestretchmap = $enablestretchmap;

        if ($this->width == "") {
            $this->width = $defaultwidth;
            $this->height = $defaultheight;
        }
        if ($this->height == "") {
            $this->width = $defaultwidth;
            $this->height = $defaultheight;
        }
        if ($this->minx==0 && $this->miny==0 && $this->maxx==0 && $this->maxy==0){
            $this->minx = 0;
            $this->miny = 0;
            $this->maxx = $this->width;
            $this->maxy = $this->height;
        }

        if (equalIgnoreCase($this->format, "image/pdf") OR equalIgnoreCase($this->format, "image/ezpdf")
                OR equalIgnoreCase($this->format, "image/swf"))
            $this->exceptions = "application/vnd.ogc.se_xml";
    }

    public function sendexception ($errornumber, $errorexceptionstring)
    {
        if (equalIgnoreCase($this->exceptions, "application/vnd.ogc.se_xml")) {
            $this->printXMLException($errornumber, $errorexceptionstring);
        }

        if (equalIgnoreCase($this->exceptions, "application/vnd.ogc.se_inimage")) {

            $size = getStretchWidthHeight($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $this->enablestretchmap);
            $this->width = $size[0];
            $this->height = $size[1];
            // the format is wrong then send xml
            if (!equalIgnoreCase($this->format, "image/svg+xml") AND !equalIgnoreCase($this->format, "image/svgt+xml") AND !equalIgnoreCase($this->format, "image/svgb+xml")
            AND !equalIgnoreCase($this->format, "image/svgz+xml") AND !equalIgnoreCase($this->format, "image/svgtz+xml") AND !equalIgnoreCase($this->format, "image/svgbz+xml")
			AND !equalIgnoreCase($this->format, "image/png") AND !equalIgnoreCase($this->format, "image/jpeg")
                    AND !equalIgnoreCase($this->format, "image/gif")AND !equalIgnoreCase($this->format, "image/pdf") AND !equalIgnoreCase($this->format, "image/ezpdf") AND !equalIgnoreCase($this->format, "image/wbmp") AND !equalIgnoreCase($this->format, "image/swf")
                    AND !equalIgnoreCase($this->format, "image/bmp") AND $this->format != "")
                $this->printXMLException($errornumber, $errorexceptionstring);

            if (equalIgnoreCase($this->format, "image/png") OR equalIgnoreCase($this->format, "image/gif") OR equalIgnoreCase($this->format, "image/jpeg")
                    OR equalIgnoreCase($this->format, "image/wbmp") OR equalIgnoreCase($this->format, "image/bmp"))
                $this->printRasterImageException($errornumber, $errorexceptionstring);

            if (equalIgnoreCase($this->format, "image/svg+xml") OR equalIgnoreCase($this->format, "image/svgz+xml")) {
                $this->printSVGException($errornumber, $errorexceptionstring);
            }
            if (equalIgnoreCase($this->format, "image/svgt+xml") OR equalIgnoreCase($this->format, "image/svgb+xml")
			OR equalIgnoreCase($this->format, "image/svgtz+xml") OR equalIgnoreCase($this->format, "image/svgbz+xml")) {
                $this->printSVGTException($errornumber, $errorexceptionstring);
            }
            if (equalIgnoreCase($this->format, "image/pdf")) {
                $this->printPDFException($errornumber, $errorexceptionstring);
            }
            if (equalIgnoreCase($this->format, "image/ezpdf")) {
                $this->printezPDFException($errornumber, $errorexceptionstring);
            }
            if (equalIgnoreCase($this->format, "image/swf")) {
                $this->printSWFException($errornumber, $errorexceptionstring);
            }
        }
        exit();
    }

    public function printXMLException($errornumber, $errorexceptionstring)
    {
    	$errorexceptionstring = str_replace("<br>", "", $errorexceptionstring);
        header("Content-type: text/xml;charset=utf-8");
        print('<?xml version="1.0" encoding="UTF-8" ?>' . "\n");
        print('<!DOCTYPE ServiceExceptionReport SYSTEM ');
        print('"' . $this->serverhost .'wms/exception_1_1_1.dtd">' . "\n");
        print('<ServiceExceptionReport version="' . $this->wmsversion . '">' . "\n");
        print('<ServiceException code="InvalidUpdateSequence">' . "\n");
        echo ($errorexceptionstring . "\n");
        print('</ServiceException>' . "\n");
        print('</ServiceExceptionReport>');
    }

    public function printRasterImageException($errornumber, $errorexceptionstring)
    {
        $rasterimagerender = new RasterImageRender();

        $newImg = imagecreate($this->width, $this->height);
        $bg = imagecolorallocate ($newImg, 0xFF, 0xFF, 0xFF); //blank but white
        // ImageColorTransparent($newImg, $bg);
        $color_copyrightinfo = imagecolorallocate ($newImg, 255, 0, 0);
        $rasterimagerender->setRender($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $newImg, $this->enablestretchmap,$hangle, $vangle, $distance);

        $rasterimagerender->createTextWithScreenCoordinate(10, $this->height / 2, $errorexceptionstring, 10, 0 ,$color_copyrightinfo);

        if (equalIgnoreCase($this->format, "image/png")) {
            header("Content-Type:image/png");
            ImagePNG($newImg);
            ImageDestroy($newImg);
        } elseif (equalIgnoreCase($this->format, "image/gif")) {
            header("Content-Type:image/gif");
            Imagegif($newImg);
            ImageDestroy($newImg);
        } elseif (equalIgnoreCase($this->format, "image/jpeg")) {
            header("Content-Type:image/jpeg");
            Imagejpeg($newImg);
            ImageDestroy($newImg);
        } elseif (equalIgnoreCase($this->format, "image/wbmp")) {
            header("Content-Type:image/wbmp");
            imagewbmp($newImg);
            ImageDestroy($newImg);
        } elseif (equalIgnoreCase($this->format, "image/bmp")) {
            header("Content-Type:image/bmp");
            imagebmp($newImg, '' , 8, 0);
            // imagebmp_($newImg, '' , 0);
            ImageDestroy($newImg);
        }
    }

    public function printSVGException($errornumber, $errorexceptionstring)
    {
        header("Content-Type: image/svg+xml");
        print_r('<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"
         "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="' . $this->width . ' px" height="' . $this->height . ' px" viewBox="0 0 ' . $this->width . ' ' . $this->height . '" preserveAspectRatio="xMidYMid meet" xml:space="preserve" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd" xmlns:suassvg="http://suas.easywms.com" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" >
<text id="" x="0" y="' . ($this->height / 2) . '" style="fill:red;font-size:10px;text-anchor:begin;font-rendering:optimizeLegibility;pointer-events:none" >
' . $errorexceptionstring . '
</text>
</svg>
');
    }

    public function printSVGTException($errornumber, $errorexceptionstring)
    {
        header("Content-Type: image/svg+xml");
        print_r('<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Tiny//EN"
         "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11-tiny.dtd">
<svg version="1.1" width="' . $this->width . ' px" height="' . $this->height . ' px" viewBox="0 0 ' . $this->width . ' ' . $this->height . '" preserveAspectRatio="xMidYMid meet" xml:space="preserve" xmlns:suassvg="http://suas.easywms.com" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" >
<text id="" x="0" y="' . ($this->height / 2) . '"  font-size="10px" font-rendering="optimizeLegibility" fill="red" text-anchor="begin" pointer-events="none">
' . $errorexceptionstring . '
</text>
</svg>
');
    }

    public function printPDFException($errornumber, $errorexceptionstring){
        $this->printXMLException($errornumber, $errorexceptionstring);
	}

	public function printezPDFException($errornumber, $errorexceptionstring){
        $this->printXMLException($errornumber, $errorexceptionstring);
	}
	public function printSWFException($errornumber, $errorexceptionstring){
        $this->printXMLException($errornumber, $errorexceptionstring);
	}
}

?>