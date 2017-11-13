<?php
/**
 * RasterImageRender.class.php
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
 * @version $Id$
 * @copyright (C) 2006-2007  LI Hui
 * @contact webmaster@easywms.com
 */

class RasterImageRender {
	private $minx;
	private $miny;
	private $maxx;
	private $maxy;
	private $width;
	private $height;
	private $enablestretchmap;
	public $image;
	private $fontpath;
	private $fonttype;
	private $fontext;
	private $font;
	private $tem_tile;
	private $tem_image;
	
	private $hangle,$vangle,$distance;
	
	public function setRender($minx, $miny, $maxx, $maxy, $width, $height, $image, $enablestretchmap, $hangle=0,$vangle=0, $distance=0
		,$outputEncodeCountry = "en"){
		$this->minx = $minx;
		$this->miny = $miny;
		$this->maxx = $maxx;
		$this->maxy = $maxy;
		$this->width = $width;
		$this->height = $height;
		$this->image = $image;
		$this->enablestretchmap = $enablestretchmap;
		//TODO $this->fontpath = "../files/fonts/".$outputEncodeCountry."/";
		$this->fontpath = "../../fonts/".$outputEncodeCountry."/"; //for files/atlas/aid/wms.php
		$this->fonttype = "arial";
		$this->fontext = ".ttf";
		$this->font = $this->fontpath.$this->fonttype.$this->fontext;
		
		$this->hangle = $hangle;
		$this->vangle = $vangle;
		$this->distance = $distance;
		
	}
	
	public function clearAllResource(){
		@ImageDestroy($this->tem_tile);
		@ImageDestroy($this->tem_image);
		@ImageDestroy($this->image);
	}
	
	public function getFont($type){
		return $this->fontpath.$type.$this->fontext;
	}
	/**
	 *
	 * @DESCRIPTION :Class Constructor.
	 */
	public function RasterImageRender()
		{
		}
	
	public function createText($x, $y, $textstring, $fonttype_, $fontsize=5, $fontangle=0.0, $color)
		{
		if($fontsize=="")$fontsize =5;
		$data_x = $x;
		$data_y = $y;
		$coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x, $data_y, $this->enablestretchmap);
		if(blnGetMap25D)
			$coord = get25DXY($coord,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
		
		$Pixel_x = $coord[0];
		$Pixel_y = $coord[1];
		//imagestring ($this->image, $fontsize, $Pixel_x, $Pixel_y, $textstring, $color);
		//ttf file can not has space
		$fonttype_ = str_replace(" ", "", strtolower($fonttype_));
		if($fonttype_=="") $fonttype_ = $this->fontype;
		//check whether the ttf file existes
		if(file_exists($this->getFont($fonttype_))){
			$this->font = $this->getFont($fonttype_);
		}
		else{
			$this->font = $this->getFont($this->fonttype);
		}
		$fontangle = $fontangle+0.1;
		@imagettftext($this->image,$fontsize, $fontangle,$Pixel_x,$Pixel_y,$color,$this->font,$textstring);
		}
	
	public function createTextWithScreenCoordinate($x, $y, $textstring, $fontsize=5, $fontangle=0, $color)
		{
		if($fontsize=="")$fontsize =5;
		//imagestring ($this->image, $fontsize, $x, $y, $textstring, $color);
		@imagettftext($this->image,$fontsize, $fontangle,$x,$y,$color,$this->getFont($this->fonttype),$textstring);
		}
	
	/*
	 * @DESCRIPTION createPoints: used to create points and multipoints
	 */
	public function createPoints($x, $y, $pointNumber, $pointstyle="SQUARE", $color, $radius=5)
		{
		$data_x = $x;
		$data_y = $y;
		for($i = 0;$i < $pointNumber;$i++) {
			$coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
			if(blnGetMap25D)
				$coord = get25DXY($coord,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
			
			$Pixel_x = $coord[0];
			$Pixel_y = $coord[1];
			
			switch ($pointstyle) {
				case 'CIRCLE': {
					//unfill
					// imageellipse($this->image, $Pixel_x, $Pixel_y, $radius, $radius, $color);
					//fill
					imagefilledellipse($this->image, $Pixel_x, $Pixel_y, $radius, $radius, $color);
				}
				break;
				case 'SQUARE': {
					//unfill
					// imagerectangle($this->image, $Pixel_x-$radius, $Pixel_y-$radius, $Pixel_x+$radius, $Pixel_y+$radius, $color);
					//fill
					imagefilledrectangle($this->image, $Pixel_x - $radius, $Pixel_y - $radius, $Pixel_x + $radius, $Pixel_y + $radius, $color);
				}
				break;
				case 'TRIANGLE': {
					$tem = getShapeTriangle($Pixel_x, $Pixel_y, $radius);
					for($j=0;$j<3;$j++){
						$valuep[$j * 2] = $tem[0][$j];
						$valuep[$j * 2 + 1] = $tem[1][$j];
					}
					imagefilledpolygon($this->image, $valuep, 3, $color);
				}
				break;
				case 'STAR': {
					$tem = getShapeFiveCornerStar($Pixel_x, $Pixel_y, $radius);
					//unfill
					/*
					 for($j=0;$j<10;$j++){
					 if ($j < 9)
					 imageline($this->image, $tem[0][$j], $tem[1][$j], $tem[0][$j+1], $tem[1][$j + 1], $color);
					 else
					 imageline($this->image, $tem[0][$j], $tem[1][$j], $tem[0][0], $tem[1][0], $color);
					 }
					 */
					//fill
					for($j=0;$j<10;$j++){
						$valuep[$j * 2] = $tem[0][$j];
						$valuep[$j * 2 + 1] = $tem[1][$j];
					}
					imagefilledpolygon($this->image, $valuep, 10, $color);
				}
				break;
				case 'CROSS': {
					$tem = getShapeCross($Pixel_x, $Pixel_y, $radius);
					for($j=0;$j<12;$j++){
						$valuep[$j * 2] = $tem[0][$j];
						$valuep[$j * 2 + 1] = $tem[1][$j];
					}
					imagefilledpolygon($this->image, $valuep, 12, $color);
				}
				break;
				case 'X': {
					$tem = getShapeX($Pixel_x, $Pixel_y, $radius);
					for($j=0;$j<12;$j++){
						$valuep[$j * 2] = $tem[0][$j];
						$valuep[$j * 2 + 1] = $tem[1][$j];
					}
					imagefilledpolygon($this->image, $valuep, 12, $color);
				}
				break;
				default: {
					imagefilledrectangle($this->image, $Pixel_x - $radius, $Pixel_y - $radius, $Pixel_x + $radius, $Pixel_y + $radius, $color);
				}
			}
		}
		}
	
	public function createLinstring($x, $y, $pointNumber, $stroke , $strokewidth, $fillColor, $blnFillColor)
		{
		$data_x = $x;
		$data_y = $y;
		for($i = 0;$i < $pointNumber;$i++) {
			$coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
			if(blnGetMap25D)
				$coord = get25DXY($coord,$this->width, $this->height, $this->hangle,$this->vangle, $this->distance);
			
			$Pixel_x[$i] = $coord[0];
			$Pixel_y[$i] = $coord[1];
			// if $blnFillColor is ture then use
			$valuep[$i * 2] = $Pixel_x[$i];
			$valuep[$i * 2 + 1] = $Pixel_y[$i];
			$valuep[$i * 2 + 2] = $Pixel_x[0];
			$valuep[$i * 2 + 3] = $Pixel_y[0];
		}
		// if fill
		if ($blnFillColor == 0) {
			for($i = 0;$i < $pointNumber;$i++) {
				if ($i < $pointNumber-1) {
					//$this->createDickline($this->image, $Pixel_x[$i], $Pixel_y[$i], $Pixel_x[$i + 1], $Pixel_y[$i + 1], $stroke,$strokewidth);
					imageline($this->image, $Pixel_x[$i], $Pixel_y[$i], $Pixel_x[$i + 1], $Pixel_y[$i + 1], $stroke);
				}
				/*
				 if ($i == $pointNumber-1) {
				 if ($Pixel_x[$i] == $Pixel_x[0] AND $Pixel_y[$i] == $Pixel_y[0]) {
				 imageline($this->image, $Pixel_x[$i], $Pixel_y[$i], $Pixel_x[0], $Pixel_y[0], $stroke);
				 }
				 }
				 */
			}
		} else {
			imagefilledpolygon($this->image, $valuep, $pointNumber, $fillColor);
		}
		if ($strokewidth != null) {
			if(function_exists(imagesetthickness))
				imagesetthickness($this->image, $strokewidth);
		}
		}
	
	function createMultiLinstring($x, $y, $lineNumber, $pointNumber, $stroke , $strokewidth, $fillColor, $blnFillColor)
		{
		$data_x = $x;
		$data_y = $y;
		for($i = 0;$i < $lineNumber;$i++) {
			for($j = 0;$j < $pointNumber[$i];$j++) {
				$coord[$j] = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
				if(blnGetMap25D){
					//$coord[$j] = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
					$coord[$j] = get25DXY($coord[$j],$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
				}
				
				$Pixel_x[$j] = $coord[$j][0];
				$Pixel_y[$j] = $coord[$j][1];
				// if $blnFillColor is ture then use
				$valuep[$i][$j * 2] = $Pixel_x[$j];
				$valuep[$i][$j * 2 + 1] = $Pixel_y[$j];
				$valuep[$i][$j * 2 + 2] = $Pixel_x[0];
				$valuep[$i][$j * 2 + 3] = $Pixel_y[0];
			}
			// if fill
			if ($blnFillColor == 0) {
				for($j = 0;$j < $pointNumber[$i];$j++) {
					if ($j < $pointNumber[$i]-1) {
						//$this->createDickline($this->image, $Pixel_x[$j], $Pixel_y[$j], $Pixel_x[$j + 1], $Pixel_y[$j + 1], $stroke,$strokewidth);
						imageline($this->image, $Pixel_x[$j], $Pixel_y[$j], $Pixel_x[$j + 1], $Pixel_y[$j + 1], $stroke);
						// $this->createDickline($this->image, $Pixel_x[$j], $Pixel_y[$j], $Pixel_x[$j + 1], $Pixel_y[$j + 1], $stroke,$strokewidth);
					}
					/*
					 if ($j == $pointNumber[$i]-1) {
					 if ($Pixel_x[$j] == $Pixel_x[0] AND $Pixel_y[$j] == $Pixel_y[0]) {
					 imageline($this->image, $Pixel_x[$j], $Pixel_y[$j], $Pixel_x[0], $Pixel_y[0], $stroke);
					 }
					 }
					 */
				}
			} else {
				imagefilledpolygon($this->image, $valuep[$i], $pointNumber[$i], $fillColor);
			}
		}
		// 9 is #ffffff
		if ($fillColor != 9) {
		}
		if ($strokewidth != null) {
			if(function_exists(imagesetthickness))
				imagesetthickness($this->image, $strokewidth);
		}
		}
	
	
	// This function not use now, has been replaced by createMultiPolygon!
	public function createPolygon($x, $y, $pointNumber, $stroke, $fillColor, $strokewidth, $blnFillColor)
		{
		$data_x = $x;
		$data_y = $y;
		for($i = 0;$i < $pointNumber;$i++) {
			$coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
			if(blnGetMap25D){
				//$coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
				$coord = get25DXY($coord,$this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
			}
			
			$Pixel_x[$i] = $coord[0];
			$Pixel_y[$i] = $coord[1];
			$valuep[$i * 2] = $Pixel_x[$i];
			$valuep[$i * 2 + 1] = $Pixel_y[$i];
		}
		//if not fill
		if(!$blnFillColor){
			imagepolygon($this->image, $valuep, $pointNumber, $stroke);
		}else{
			imagefilledpolygon($this->image, $valuep, $pointNumber, $fillColor);
			imagepolygon($this->image, $valuep, $pointNumber, $stroke);
		}
		if ($strokewidth != null) {
			if(function_exists(imagesetthickness))
				imagesetthickness($this->image, $strokewidth);
		}
		// Activate the fast drawing antialiased methods for lines and wired polygons.
		if(function_exists(imageantialias))
			imageantialias($this->image, true);
		}
	
	
	// This function is used for create polygon also!
	function createMultiPolygon($x, $y, $lineNumber, $pointNumber, $stroke, $fillColor, $strokewidth, $blnFillColor)
		{
		$data_x = $x;
		$data_y = $y;
		for($i = 0;$i < $lineNumber;$i++) {
			for($j = 0;$j < $pointNumber[$i];$j++) {
				$coord[$j] = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
				if(blnGetMap25D){
					//$coord[$j] = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
					$coord[$j] = get25DXY($coord[$j], $this->width, $this->height,$this->hangle,$this->vangle, $this->distance);
				}
				
				$Pixel_x[$j] = $coord[$j][0];
				$Pixel_y[$j] = $coord[$j][1];
				$valuep[$i][$j * 2] = $Pixel_x[$j];
				$valuep[$i][$j * 2 + 1] = $Pixel_y[$j];
			} 
			//print_r($valuep);
			//if not fill
			if(!$blnFillColor){
				imagepolygon($this->image, $valuep[$i], $pointNumber[$i], $stroke);
			}else{
				@imagefilledpolygon($this->image, $valuep[$i], $pointNumber[$i], $fillColor);
				imagepolygon($this->image, $valuep[$i], $pointNumber[$i], $stroke);
			}
			
			if ($strokewidth != null) {
				if(function_exists(imagesetthickness))
					imagesetthickness($this->image, $strokewidth);
			}
		}
		// Activate the fast drawing antialiased methods for lines and wired polygons.
		if(function_exists(imageantialias))
			imageantialias($this->image, true);
		}
	
	function createDickline($img, $start_x, $start_y, $end_x, $end_y, $color, $thickness){
		$angle = (atan2(($start_y - $end_y), ($end_x - $start_x)));
		
		$dist_x = $thickness * (sin($angle));
		$dist_y = $thickness * (cos($angle));
		
		$p1x = ceil(($start_x + $dist_x));
		$p1y = ceil(($start_y + $dist_y));
		$p2x = ceil(($end_x + $dist_x));
		$p2y = ceil(($end_y + $dist_y));
		$p3x = ceil(($end_x - $dist_x));
		$p3y = ceil(($end_y - $dist_y));
		$p4x = ceil(($start_x - $dist_x));
		$p4y = ceil(($start_y - $dist_y));
		
		$array = array(0 => $p1x, $p1y, $p2x, $p2y, $p3x, $p3y, $p4x, $p4y);
		imagefilledpolygon ($img, $array, (count($array) / 2), $color);
	}
	
	/**
	 * $alpha 0-100, 0 does nothing
	 */
	public function createImage($x, $y, $pointNumber, $imagelink, $alpha, $color) 
		{
		
		// $pointNumber==5
		$data_x = $x;
		$data_y = $y;
		$fillColor = imagecolorallocate ($this->image, 255, 0, 0);//white
		$color = $fillColor;
		for($i = 0;$i < $pointNumber;$i++) {
			$coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
			$Pixel_x[$i] = $coord[0];
			$Pixel_y[$i] = $coord[1];
		}
		$dst_x = $Pixel_x[0];
		$dst_y = $Pixel_y[0];
		$dst_w = abs($Pixel_x[2] - $Pixel_x[0]);
		//because image need absolute value
		$dst_h = abs($Pixel_y[2] - $Pixel_y[0]);
		
		// if too small in big scale, doesnt display it
		if ($dst_w > 1 and $dst_h > 1){
			if ($imagelink != "") {
				$this->tem_tile = null;
				$this->tem_image = null;
				$postfix = strtoupper(substr($imagelink, -3));
				switch ($postfix) {
					case 'JPG': {
						$this->tem_image = @imagecreatefromjpeg($imagelink);
						if ($this->tem_image) {
							$this->tem_tile = imagecreatetruecolor($dst_w, $dst_h);
							$src_w = imagesx($this->tem_image);
							$src_h = imagesy($this->tem_image);
							//imagecopyresampled($this->tem_tile, $this->tem_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
							
							imagecopyresized($this->tem_tile, $this->tem_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);		
							imagecopymerge($this->image, $this->tem_tile, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $alpha);
						} 
						// if can not get image, return the rectange
						else{
							$this->tem_tile = imagecreatetruecolor($dst_w, $dst_h);
							imagerectangle($this->tem_tile, $Pixel_x[0], $Pixel_x[0], $Pixel_x[2], $Pixel_y[2], $color);
							imagecopymerge($this->image, $this->tem_tile, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $alpha);
						}
					}
					break;
					case 'PNG': {
						$this->tem_image = @imagecreatefrompng($imagelink);
						if ($this->tem_image) {
							$this->tem_tile = imagecreatetruecolor($dst_w, $dst_h);
							$src_w = imagesx($this->tem_image);
							$src_h = imagesy($this->tem_image);
							//imagecopyresampled($this->tem_tile, $this->tem_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
							
							imagecopyresized($this->tem_tile, $this->tem_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
							imagecopymerge($this->image, $this->tem_tile, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $alpha);
							//imagecopy($this->image, $this->tem_tile, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h);
						} else{
							$this->tem_tile = imagecreatetruecolor($dst_w, $dst_h);
							imagerectangle($this->tem_tile, $Pixel_x[0], $Pixel_x[0], $Pixel_x[2], $Pixel_y[2], $color);				
							imagecopymerge($this->image, $this->tem_tile, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $alpha);
						}
					}
					break;
					case 'GIF': {
						$this->tem_image = @imagecreatefromgif($imagelink);
						if ($this->tem_image) {
							$this->tem_tile = imagecreatetruecolor($dst_w, $dst_h);
							$src_w = imagesx($this->tem_image);
							$src_h = imagesy($this->tem_image);
							//imagecopyresampled($this->tem_tile, $this->tem_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
							
							imagecopyresized($this->tem_tile, $this->tem_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
							imagecopymerge($this->image, $this->tem_tile, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $alpha);
						} else{
							$this->tem_tile = imagecreatetruecolor($dst_w, $dst_h);
							imagerectangle($this->tem_tile, $Pixel_x[0], $Pixel_x[0], $Pixel_x[2], $Pixel_y[2], $color);
							imagecopymerge($this->image, $this->tem_tile, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $alpha);
						}							
					}
				}
			}
		}
		}
	
	/*
	 * @DESCRIPTION createLegendGraphicPoint: used to create point legend graphic, input pixel value
	 */
	public function createLegendGraphicPoint($width, $height, $pointstyle, $color, $radius)
		{
		//the boder will not be empty
		$width = $width-1;
		$height = $height-1;
		imagerectangle($this->image, 0, 0, $width, $height, $color);
		//imagefilledrectangle($this->image, 0, $height, $width, 0, $color);
		
		$Pixel_x = $width/2;
		$Pixel_y = $height/2;
		switch ($pointstyle) {
			case 'CIRCLE': {
				//unfill
				// imageellipse($this->image, $Pixel_x, $Pixel_y, $radius, $radius, $color);
				//fill
				imagefilledellipse($this->image, $Pixel_x, $Pixel_y, $radius, $radius, $color);
			}
			break;
			case 'SQUARE': {
				//unfill
				// imagerectangle($this->image, $Pixel_x-$radius, $Pixel_y-$radius, $Pixel_x+$radius, $Pixel_y+$radius, $color);
				//fill
				imagefilledrectangle($this->image, $Pixel_x - $radius, $Pixel_y - $radius, $Pixel_x + $radius, $Pixel_y + $radius, $color);
			}
			break;
			case 'TRIANGLE': {
				$tem = getShapeTriangle($Pixel_x, $Pixel_y, $radius);
				for($j=0;$j<3;$j++){
					$valuep[$j * 2] = $tem[0][$j];
					$valuep[$j * 2 + 1] = $tem[1][$j];
				}
				imagefilledpolygon($this->image, $valuep, 3, $color);
			}
			break;
			case 'STAR': {
				$tem = getShapeFiveCornerStar($Pixel_x, $Pixel_y, $radius);
				//unfill
				/*
				 for($j=0;$j<10;$j++){
				 if ($j < 9)
				 imageline($this->image, $tem[0][$j], $tem[1][$j], $tem[0][$j+1], $tem[1][$j + 1], $color);
				 else
				 imageline($this->image, $tem[0][$j], $tem[1][$j], $tem[0][0], $tem[1][0], $color);
				 }
				 */
				//fill
				for($j=0;$j<10;$j++){
					$valuep[$j * 2] = $tem[0][$j];
					$valuep[$j * 2 + 1] = $tem[1][$j];
				}
				imagefilledpolygon($this->image, $valuep, 10, $color);
			}
			break;
			case 'CROSS': {
				$tem = getShapeCross($Pixel_x, $Pixel_y, $radius);
				for($j=0;$j<12;$j++){
					$valuep[$j * 2] = $tem[0][$j];
					$valuep[$j * 2 + 1] = $tem[1][$j];
				}
				imagefilledpolygon($this->image, $valuep, 12, $color);
			}
			break;
			case 'X': {
				$tem = getShapeX($Pixel_x, $Pixel_y, $radius);
				for($j=0;$j<12;$j++){
					$valuep[$j * 2] = $tem[0][$j];
					$valuep[$j * 2 + 1] = $tem[1][$j];
				}
				imagefilledpolygon($this->image, $valuep, 12, $color);
			}
			break;
		}
		
		}
	
	/*
	 * @DESCRIPTION createLegendGraphicText: used to create text legend graphic, input pixel value
	 */
	public function createLegendGraphicText($width, $height, $textstring, $fontsize, $color){
		//the boder will not be empty
		$width = $width-1;
		$height = $height-1;
		imagerectangle($this->image, 0, 0, $width, $height, $color);
		if($fontsize>$height)
			$fontsize = $height;
		
		imagestring ($this->image, $fontsize, $width/2-2, -1, $textstring, $color);
	}
	
	public function createLegendGraphicLineString($width, $height, $stroke , $strokewidth, $fillColor, $blnFillColor){
		if ($blnFillColor == 0) {
			$width = $width-1;
			$height = $height-1;
			imagerectangle($this->image, 0, 0, $width, $height, $stroke);
			imageline($this->image, 0, $height+1, $width+1, 0, $stroke);
		}
		else {
			imagerectangle($this->image, 0, 0, $width, $height, $stroke);
			imagefilledrectangle($this->image, 1, 1, $width-1, $height-1, $fillColor);
		}
		
	}
	
	public function createLegendGraphicPolygon($width, $height, $stroke , $strokewidth, $fillColor, $blnFillColor){
		imagerectangle($this->image, 0, 0, $width, $height, $stroke);
		imagefilledrectangle($this->image, 1, 1, $width-1, $height-1, $fillColor);
	}
	
	public function createLegendGraphicImage($width, $height){
		$border = imagecolorallocate ($this->image, 190, 54, 54);
		$leftblock = imagecolorallocate ($this->image, 100, 47, 131);
		$rightblock = imagecolorallocate ($this->image, 100, 154, 64);
		$centercolor = imagecolorallocate ($this->image, 254, 240, 47);
		imagefilledrectangle($this->image, 0, 0, $width-1, $height-1, $border);
		
		imagefilledrectangle($this->image, 0, 0, ($width-2)*3/4, ($height-2)/2+1, $leftblock);
		imagefilledrectangle($this->image, 0, 0, ($width-2)/4, $height, $leftblock);
		
		imagefilledrectangle($this->image, ($width-2)*3/4+1, 0, $width, $height, $rightblock);
		imagefilledrectangle($this->image, ($width-2)/4+1, ($height-2)/2+1, $width, $height, $rightblock);
		$radius = $height/8;
		imagefilledrectangle($this->image, $width/2 - $radius, $height/2 - $radius, $width/2 + $radius, $height/2 + $radius, $centercolor);
	}
	public function createLegendGraphicUnknown($width, $height, $stroke){
		$width = $width-1;
		$height = $height-1;
		imagerectangle($this->image, 0, 0, $width, $height, $stroke);
	}
	
}
?>