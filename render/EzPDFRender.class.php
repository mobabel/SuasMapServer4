<?php
/**
 * EzPDFRender.class.php
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
 * @contact webmaster@easywms.com
 */

class EzPDFRender {
	private $minx;
	private $miny;
	private $maxx;
	private $maxy;
	private $width;
	private $height;
	private $enablestretchmap;
	private $pdf;
	private $tem_tile;
	private $tem_image;
	
	public function setRender($minx, $miny, $maxx, $maxy, $width, $height, $pdf, $enablestretchmap)
		{
		$this->minx = $minx;
		$this->miny = $miny;
		$this->maxx = $maxx;
		$this->maxy = $maxy;
		$this->width = $width;
		$this->height = $height;
		$this->pdf = $pdf;
		// $this->font = $font;
		$this->enablestretchmap = $enablestretchmap;
		}
	
	/**
	 *
	 * @DESCRIPTION :Class Constructor.																												 	**
	 */
	public function EzPDFRender()
		{
		}
	
	public function clearAllResource(){
		@ImageDestroy($this->tem_tile);
		@ImageDestroy($this->tem_image);
	}
	
	public function createText($x, $y, $textstring, $fontsize, $color)
		{
		$data_x = $x;
		$data_y = $y;
		$coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x, $data_y, $this->enablestretchmap);
		$Pixel_x = $coord[0];
		$Pixel_y = $coord[1];
		$this->pdf->addText($Pixel_x, $Pixel_y, $fontsize, $textstring, 0);
		}
	
	public function createTextWithScreenCoordinate($x, $y, $textstring, $fontsize, $color)
		{
		$this->pdf->addText($x, $y, $fontsize, $textstring, 0);
		}
	/*
	 * @DESCRIPTION createPoints: used to create points and multipoints
	 */
	public function createPoints($x, $y, $pointNumber, $pointstyle="SQUARE", $color, $radius)
		{
		$data_x = $x;
		$data_y = $y;
		for($i = 0;$i < $pointNumber;$i++) {
			$coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
			$Pixel_x = $coord[0];
			$Pixel_y = $coord[1];
			switch ($pointstyle) {
				case 'CIRCLE': {
					$this->pdf->ellipse($Pixel_x, $Pixel_y, $radius);
					// for ellipse
					// $this->pdf->ellipse($Pixel_x, $Pixel_y, $radiusx,$radiusx);
				}
				break;
				case 'SQUARE': {
					$this->pdf->filledRectangle($Pixel_x - $radius, $Pixel_y - $radius, $radius * 2, $radius * 2);
				}
				break;
				case "TRIANGLE": {
					$tem = getShapeTriangle($Pixel_x, $Pixel_y, $radius);
					for($j = 0;$j < 3;$j++) {
						$valuep[$j * 2] = $tem[0][$j];
						$valuep[$j * 2 + 1] = $tem[1][$j];
					}
					$this->pdf->polygon($valuep, 3, 1);
				}
				break;
				case "STAR": {
					$tem = getShapeFiveCornerStar($Pixel_x, $Pixel_y, $radius);
					for($j = 0;$j < 10;$j++) {
						$valuep[$j * 2] = $tem[0][$j];
						$valuep[$j * 2 + 1] = $tem[1][$j];
					}
					$this->pdf->polygon($valuep, 10, 1);
				}
				break;
				case "CROSS": {
					$tem = getShapeCross($Pixel_x, $Pixel_y, $radius);
					for($j = 0;$j < 12;$j++) {
						$valuep[$j * 2] = $tem[0][$j];
						$valuep[$j * 2 + 1] = $tem[1][$j];
					}
					$this->pdf->polygon($valuep, 12, 1);
				}
				break;
				case "X": {
					$tem = getShapeX($Pixel_x, $Pixel_y, $radius);
					for($j = 0;$j < 12;$j++) {
						$valuep[$j * 2] = $tem[0][$j];
						$valuep[$j * 2 + 1] = $tem[1][$j];
					}
					$this->pdf->polygon($valuep, 12, 1);
				}
				break;
				default: {
					$this->pdf->ellipse($Pixel_x, $Pixel_y, $radius);
				}
			}
		}
		}
	
	public function createLinstring($x, $y, $pointNumber, $stroke , $strokewidth, $fillColor, $xmlLineJoin, $xmlLineCap, $blnFillLineString)
		{
		$data_x = $x;
		$data_y = $y;
		// setLineStyle([width],[cap],[join],[dash],[phase])
		$this->pdf->setLineStyle($strokewidth, $xmlLineCap, $xmlLineJoin);
		for($i = 0;$i < $pointNumber;$i++) {
			$coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
			$Pixel_x[$i] = $coord[0];
			$Pixel_y[$i] = $coord[1];
			// if $blnFillLineString is ture then use
			$valuep[$i * 2] = $Pixel_x[$i];
			$valuep[$i * 2 + 1] = $Pixel_y[$i];
			$valuep[$i * 2 + 2] = $Pixel_x[0];
			$valuep[$i * 2 + 3] = $Pixel_y[0];
		}
		if ($blnFillLineString == 0) {
			for($i = 0;$i < $pointNumber;$i++) {
				if ($i < $pointNumber-1) {
					$this->pdf->line($Pixel_x[$i], $Pixel_y[$i], $Pixel_x[$i + 1], $Pixel_y[$i + 1]);
				}
			}
		} else { // if fill
			$this->pdf->polygon($valuep, $pointNumber, 1);
		}
		}
	
	function createMultiLinstring($x, $y, $lineNumber, $pointNumber, $stroke , $strokewidth, $fillColor , $xmlLineJoin, $xmlLineCap, $blnFillLineString)
		{
		$data_x = $x;
		$data_y = $y;
		// setLineStyle([width],[cap],[join],[dash],[phase])
		$this->pdf->setLineStyle($strokewidth, $xmlLineCap, $xmlLineJoin);
		for($i = 0;$i < $lineNumber;$i++) {
			for($j = 0;$j < $pointNumber[$i];$j++) {
				$coord[$j] = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
				$Pixel_x[$j] = $coord[$j][0];
				$Pixel_y[$j] = $coord[$j][1];
				// if $blnFillLineString is ture then use
				$valuep[$i][$j * 2] = $Pixel_x[$j];
				$valuep[$i][$j * 2 + 1] = $Pixel_y[$j];
				$valuep[$i][$j * 2 + 2] = $Pixel_x[0];
				$valuep[$i][$j * 2 + 3] = $Pixel_y[0];
			}
			if ($blnFillLineString == 0) {
				for($j = 0;$j < $pointNumber[$i];$j++) {
					if ($j < $pointNumber[$i]-1) {
						$this->pdf->line($Pixel_x[$j], $Pixel_y[$j], $Pixel_x[$j + 1], $Pixel_y[$j + 1]);
					}
				}
			} else { // if fill
				$this->pdf->polygon($valuep[$i], $pointNumber[$i], 1);
			}
		}
		}
	
	public function createPolygon($x, $y, $pointNumber, $stroke , $strokewidth, $xmlLineJoin, $xmlLineCap)
		{
		$data_x = $x;
		$data_y = $y;
		$this->pdf->setLineStyle($strokewidth, $xmlLineCap, $xmlLineJoin);
		for($i = 0;$i < $pointNumber;$i++) {
			$coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
			$Pixel_x[$i] = $coord[0];
			$Pixel_y[$i] = $coord[1];
			$valuep[$i * 2] = $Pixel_x[$i];
			$valuep[$i * 2 + 1] = $Pixel_y[$i];
		}
		$this->pdf->polygon($valuep, $pointNumber, 1);
		}
	
	function createMultiPolygon($x, $y, $lineNumber, $pointNumber, $stroke , $strokewidth, $xmlLineJoin, $xmlLineCap)
		{
		$data_x = $x;
		$data_y = $y;
		$this->pdf->setLineStyle($strokewidth, $xmlLineCap, $xmlLineJoin);
		for($i = 0;$i < $lineNumber;$i++) {
			for($j = 0;$j < $pointNumber[$i];$j++) {
				$coord[$j] = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
				$Pixel_x[$j] = $coord[$j][0];
				$Pixel_y[$j] = $coord[$j][1];
				$valuep[$i][$j * 2] = $Pixel_x[$j];
				$valuep[$i][$j * 2 + 1] = $Pixel_y[$j];
			}
			$this->pdf->polygon($valuep[$i], $pointNumber[$i], 1);
		}
		}
	
	public function createImage($x, $y, $pointNumber, $imagelink, $alpha, $color) // $alpha 0-100, 0 does nothing
		{
		// $pointNumber==5
		$data_x = $x;
		$data_y = $y;
		for($i = 0;$i < $pointNumber;$i++) {
			$coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
			$Pixel_x[$i] = $coord[0];
			$Pixel_y[$i] = $coord[1];
		}
		$dst_x = $Pixel_x[0];
		$dst_y = $Pixel_y[0];
		$dst_w = $Pixel_x[2] - $Pixel_x[0];
		$dst_h = abs($Pixel_y[2] - $Pixel_y[0]);
		
		$this->tem_image = null;
		$postfix = strtoupper(substr($imagelink, -3));
		switch ($postfix) {
			case 'JPG': {
				if ($this->pdf->addJpegFromFile($imagelink, $dst_x, $dst_y, $dst_w, $dst_h)) {
					
				} else{
					$this->pdf->rectangle($dst_x, $dst_y, $dst_w, $dst_h);
				}
			}
			break;
			case 'PNG': {
				if ($this->pdf->addPngFromFile($imagelink, $dst_x, $dst_y, $dst_w, $dst_h)) {
					
				} else{
					$this->pdf->rectangle($dst_x, $dst_y, $dst_w, $dst_h);
				}
			}
			break;
			case 'GIF': {
				 $this->tem_image = @imagecreatefromgif($imagelink);
				 if ($this->tem_image) {
					 $this->tem_tile = imagecreatetruecolor($dst_w, $dst_h);
					 $src_w = imagesx($this->tem_image);
					 $src_h = imagesy($this->tem_image);
					 imagecopyresampled($this->tem_tile, $this->tem_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);

					 //$this->pdf->fit_image($this->tem_image, $dst_x, $dst_y, "");
					 $this->pdf->addImage($this->tem_tile, $dst_x, $dst_y, $dst_w, $dst_h, 100);
				 } else{
				 	$this->pdf->rectangle($dst_x, $dst_y, $dst_w, $dst_h);
				 }
				 
				 
			}
		}
	}
}

?>