<?php
/**
 * SWFRender.class.php
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
 * @copyright (C) 2006-2007  LI Hui
 * @contact webmaster@easywms.com
 */

class SWFRender {
	private $minx;
	private $miny;
	private $maxx;
	private $maxy;
	private $width;
	private $height;
	private $enablestretchmap;
	public $swf;
	private $tem_tile;
	private $tem_image;
	
	public function setRender($minx, $miny, $maxx, $maxy, $width, $height, $swf, $font, $enablestretchmap)
		{
		$this->minx = $minx;
		$this->miny = $miny;
		$this->maxx = $maxx;
		$this->maxy = $maxy;
		$this->width = $width;
		$this->height = $height;
		$this->swf = $swf;
		$this->font = $font;
		$this->enablestretchmap = $enablestretchmap;
		}
	
	/**
	 *
	 * @DESCRIPTION :Class Constructor.																												 	**
	 */
	public function SWFRender()
		{
		}

	public function clearAllResource(){
		$this->tem_tile  = null;
		$this->tem_image = null;
	}
	
	public function createText($x, $y, $textstring, $fontsize, $color)
		{
		$data_x = $x;
		$data_y = $y;
		$coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x, $data_y, $this->enablestretchmap);
		$Pixel_x = $coord[0];
		$Pixel_y = $coord[1];
		
		$swftext = new SWFText();
		$swftext->setFont($this->font);
		$swftext->moveTo($Pixel_x, $Pixel_y);
		$swftext->setcolor($color->setRGB_R, $color->setRGB_G, $color->setRGB_B);
		$swftext->setHeight($fontsize);
		$swftext->addString($textstring);
		
		$this->swf->add($swftext);
		}
	
	public function createTextWithScreenCoordinate($x, $y, $textstring, $fontsize, $color)
		{
		$swftext = new SWFText();
		$swftext->setFont($this->font);
		$swftext->moveTo($x, $y);
		$swftext->setcolor($color->setRGB_R, $color->setRGB_G, $color->setRGB_B);
		$swftext->setHeight($fontsize);
		$swftext->addString($textstring);
		
		$this->swf->add($swftext);
		}
	/*
	 * @DESCRIPTION createPoints: used to create points and multipoints
	 */
	public function createPoints($x, $y, $pointNumber, $pointstyle="SQUARE", $color, $radius)
		{
		$data_x = $x;
		$data_y = $y;
		$shape = new SWFShape();
		
		$shape->setLine(1, $color->setRGB_R, $color->setRGB_G, $color->setRGB_B);
		//$shape->addFill($color->setRGB_R, $color->setRGB_G, $color->setRGB_B);
		$shape->setRightFill($color->setRGB_R, $color->setRGB_G, $color->setRGB_B);
		for($i = 0;$i < $pointNumber;$i++) {
			$coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
			$Pixel_x = $coord[0];
			$Pixel_y = $coord[1];
			
			switch ($pointstyle) {
				case 'CIRCLE': {
					$shape->movepento($Pixel_x, $Pixel_y);
					$shape->drawCircle($radius);
					// for ellipse
					//$this->swf->ellipse($Pixel_x, $Pixel_y, $radiusx,$radiusx);
					//$shape->drawCircle($radius);
					#                        if ($radiusx >= $radiusy)
					#                            $movie_shape->scaleTo(1, $radiusy / $radiusx);
					#                        else
					#						    $movie_shape->scaleTo($radiusx / $radiusy, 1);
					
				}
				break;
				case 'SQUARE': {
					$shape->movepento($Pixel_x - $radius, $Pixel_y - $radius);
					$shape->drawlineto($Pixel_x - $radius, $Pixel_y + $radius);
					
					$shape->movepento($Pixel_x - $radius, $Pixel_y + $radius);
					$shape->drawlineto($Pixel_x + $radius, $Pixel_y + $radius);
					
					$shape->movepento($Pixel_x + $radius, $Pixel_y + $radius);
					$shape->drawlineto($Pixel_x + $radius, $Pixel_y - $radius);
					
					$shape->movepento($Pixel_x + $radius, $Pixel_y - $radius);
					$shape->drawlineto($Pixel_x - $radius, $Pixel_y - $radius);
					
				}
				break;
				case "TRIANGLE": {
					$tem = getShapeTriangle($Pixel_x, $Pixel_y, $radius);
					for($j = 0;$j < 3;$j++) {
						if($j!=2){
							$shape->movepento($tem[0][$j], $tem[1][$j]);
							$shape->drawlineto($tem[0][$j+1], $tem[1][$j+1]);
						}
						else{
							$shape->movepento($tem[0][$j], $tem[1][$j]);
							$shape->drawlineto($tem[0][0], $tem[1][0]);
						}
					}
				}
				break;
				case "STAR": {
					$tem = getShapeFiveCornerStar($Pixel_x, $Pixel_y, $radius);
					for($j = 0;$j < 10;$j++) {
						if($j!=9){
							$shape->movepento($tem[0][$j], $tem[1][$j]);
							$shape->drawlineto($tem[0][$j+1], $tem[1][$j+1]);
						}
						else{
							$shape->movepento($tem[0][$j], $tem[1][$j]);
							$shape->drawlineto($tem[0][0], $tem[1][0]);
						}
					}
				}
				break;
				case "CROSS": {
					$tem = getShapeCross($Pixel_x, $Pixel_y, $radius);
					for($j = 0;$j < 12;$j++) {
						if($j!=11){
							$shape->movepento($tem[0][$j], $tem[1][$j]);
							$shape->drawlineto($tem[0][$j+1], $tem[1][$j+1]);
						}
						else{
							$shape->movepento($tem[0][$j], $tem[1][$j]);
							$shape->drawlineto($tem[0][0], $tem[1][0]);
						}
					}
				}
				break;
				case "X": {
					$tem = getShapeX($Pixel_x, $Pixel_y, $radius);
					for($j = 0;$j < 12;$j++) {
						if($j!=11){
							$shape->movepento($tem[0][$j], $tem[1][$j]);
							$shape->drawlineto($tem[0][$j+1], $tem[1][$j+1]);
						}
						else{
							$shape->movepento($tem[0][$j], $tem[1][$j]);
							$shape->drawlineto($tem[0][0], $tem[1][0]);
						}
					}
				}
				break;
				default: {
					$shape->movepento($Pixel_x - $radius, $Pixel_y - $radius);
					$shape->drawlineto($Pixel_x - $radius, $Pixel_y + $radius);
					
					$shape->movepento($Pixel_x - $radius, $Pixel_y + $radius);
					$shape->drawlineto($Pixel_x + $radius, $Pixel_y + $radius);
					
					$shape->movepento($Pixel_x + $radius, $Pixel_y + $radius);
					$shape->drawlineto($Pixel_x + $radius, $Pixel_y - $radius);
					
					$shape->movepento($Pixel_x + $radius, $Pixel_y - $radius);
					$shape->drawlineto($Pixel_x - $radius, $Pixel_y - $radius);
				}
			}
		}
		$this->swf->add($shape);
		}
	
	public function createLinstring($x, $y, $pointNumber, $stroke , $strokewidth, $fillColor, $xmlLineJoin, $xmlLineCap, $blnFillLineString)
		{
		$data_x = $x;
		$data_y = $y;
		$shape = new SWFShape();
		$shape->setLine($strokewidth, $stroke->setRGB_R, $stroke->setRGB_G, $stroke->setRGB_B);
		for($i = 0;$i < $pointNumber;$i++) {
			$coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
			$Pixel_x[$i] = $coord[0];
			$Pixel_y[$i] = $coord[1];
			// if $blnFillLineString is ture then use
			//$valuep[$i * 2] = $Pixel_x[$i];
			//$valuep[$i * 2 + 1] = $Pixel_y[$i];
			//$valuep[$i * 2 + 2] = $Pixel_x[0];
			//$valuep[$i * 2 + 3] = $Pixel_y[0];
		}
		if ($blnFillLineString == 0) {
			for($i = 0;$i < $pointNumber;$i++) {
				if ($i < $pointNumber-1) {
					$shape->movepento($Pixel_x[$i], $Pixel_y[$i]);
					$shape->drawlineto($Pixel_x[$i + 1], $Pixel_y[$i + 1]);
				}
			}
		} else { // if fill
			//$shape->addFill($fillColor->setRGB_R, $fillColor->setRGB_G, $fillColor->setRGB_B);
			$shape->setRightFill($fillColor->setRGB_R, $fillColor->setRGB_G, $fillColor->setRGB_B);
			for($i = 0;$i < $pointNumber;$i++) {
				if ($i < $pointNumber-1) {
					$shape->movepento($Pixel_x[$i], $Pixel_y[$i]);
					$shape->drawlineto($Pixel_x[$i + 1], $Pixel_y[$i + 1]);
				}
				if ($i == $pointNumber-1) {
					$shape->movepento($Pixel_x[$i], $Pixel_y[$i]);
					$shape->drawlineto($Pixel_x[0], $Pixel_y[0]);
				}
			}
		}
		$this->swf->add($shape);
		}
	
	function createMultiLinstring($x, $y, $lineNumber, $pointNumber, $stroke , $strokewidth, $fillColor ,$xmlLineJoin, $xmlLineCap, $blnFillLineString)
		{
		$data_x = $x;
		$data_y = $y;
		$shape = new SWFShape();
		$shape->setLine($strokewidth, $stroke->setRGB_R, $stroke->setRGB_G, $stroke->setRGB_B);
		for($i = 0;$i < $lineNumber;$i++) {
			for($j = 0;$j < $pointNumber[$i];$j++) {
				$coord[$j] = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
				$Pixel_x[$j] = $coord[$j][0];
				$Pixel_y[$j] = $coord[$j][1];
				// if $blnFillLineString is ture then use
				//$valuep[$i][$j * 2] = $Pixel_x[$j];
				//$valuep[$i][$j * 2 + 1] = $Pixel_y[$j];
				//$valuep[$i][$j * 2 + 2] = $Pixel_x[0];
				//$valuep[$i][$j * 2 + 3] = $Pixel_y[0];
			}
			if ($blnFillLineString == 0) {
				for($j = 0;$j < $pointNumber[$i];$j++) {
					if ($j < $pointNumber[$i]-1) {
						$shape->movepento($Pixel_x[$j], $Pixel_y[$j]);
						$shape->drawlineto($Pixel_x[$j + 1], $Pixel_y[$j + 1]);
					}
				}
			} else { // if fill
				$shape->setRightFill($fillColor->setRGB_R, $fillColor->setRGB_G, $fillColor->setRGB_B);
				for($j = 0;$j < $pointNumber[$i];$j++) {
					if ($j < $pointNumber[$i]-1) {
						$shape->movepento($Pixel_x[$j], $Pixel_y[$j]);
						$shape->drawlineto($Pixel_x[$j + 1], $Pixel_y[$j + 1]);
					}
					if ($j == $pointNumber[$i]-1) {
						$shape->movepento($Pixel_x[$j], $Pixel_y[$j]);
						$shape->drawlineto($Pixel_x[0], $Pixel_y[0]);
					}
				}
			}
		}
		$this->swf->add($shape);
		}
	
	public function createPolygon($x, $y, $pointNumber, $stroke , $strokewidth, $xmlLineJoin, $xmlLineCap)
		{
		$data_x = $x;
		$data_y = $y;
		$shape = new SWFShape();
		$shape->setLine($strokewidth, $stroke->setRGB_R, $stroke->setRGB_G, $stroke->setRGB_B);
		$shape->setRightFill($stroke->setRGB_R, $stroke->setRGB_G, $stroke->setRGB_B);
		for($i = 0;$i < $pointNumber;$i++) {
			$coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
			$Pixel_x[$i] = $coord[0];
			$Pixel_y[$i] = $coord[1];
			//$valuep[$i * 2] = $Pixel_x[$i];
			//$valuep[$i * 2 + 1] = $Pixel_y[$i];
		}
		for($i = 0;$i < $pointNumber;$i++) {
			if ($i < $pointNumber-1) {
				$shape->movepento($Pixel_x[$i], $Pixel_y[$i]);
				$shape->drawlineto($Pixel_x[$i + 1], $Pixel_y[$i + 1]);
			}
		}
		$this->swf->add($shape);
		}
	
	function createMultiPolygon($x, $y, $lineNumber, $pointNumber, $stroke , $strokewidth, $xmlLineJoin, $xmlLineCap)
		{
		$data_x = $x;
		$data_y = $y;
		$shape = new SWFShape();
		$shape->setLine($strokewidth, $stroke->setRGB_R, $stroke->setRGB_G, $stroke->setRGB_B);
		$shape->setRightFill($stroke->setRGB_R, $stroke->setRGB_G, $stroke->setRGB_B);
		for($i = 0;$i < $lineNumber;$i++) {
			for($j = 0;$j < $pointNumber[$i];$j++) {
				$coord[$j] = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
				$Pixel_x[$j] = $coord[$j][0];
				$Pixel_y[$j] = $coord[$j][1];
				//$valuep[$i][$j * 2] = $Pixel_x[$j];
				//$valuep[$i][$j * 2 + 1] = $Pixel_y[$j];
			}
			for($j = 0;$j < $pointNumber[$i];$j++) {
				if ($j < $pointNumber[$i]-1) {
					$shape->movepento($Pixel_x[$j], $Pixel_y[$j]);
					$shape->drawlineto($Pixel_x[$j + 1], $Pixel_y[$j + 1]);
				}
			}
		}
		$this->swf->add($shape);
		}
	
	//not yet finish!!!!
	public function createImage($x, $y, $pointNumber, $imagelink, $alpha, $color) // $alpha 0-100, 0 does nothing
		{
		// $pointNumber==5
		$data_x = $x;
		$data_y = $y;
		for($i = 0;$i < $pointNumber;$i++) {
			$coord = getPixelXYFromReal($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
			$Pixel_x[$i] = $coord[0];
			$Pixel_y[$i] = $coord[1];
		}
		$dst_x = $Pixel_x[0];
		$dst_y = $Pixel_y[0];
		$dst_w = $Pixel_x[2] - $Pixel_x[0];
		$dst_h = abs($Pixel_y[2] - $Pixel_y[0]);
		
		$this->tem_tile  = null;
		$this->tem_image = null;
		$postfix = strtoupper(substr($imagelink, -3));
		switch ($postfix) {
			case 'JPG': {
				//TODO how to add image in right position?
				@$handle = fopen($imagelink, "r"); 
  				if($handle){
					while (!feof($handle)){
						$this->tem_image .= fread($handle, 1024);
					} 
					$img = new SWFBitmap($this->tem_image); 
					fclose($handle); 
					
					$this->swf->add($img);
  				}
			}
			break;
			case 'PNG': {
				/*$shape = new SWFShape();
  				$f = $shape->addFill(new SWFBitmap(file_get_contents($imagelink)));
  				if($f){
  					$shape->setRightFill($f);
  					$shape->drawLine($dst_w, 0);
  				}else{
  					
  				}
  				$this->swf->add($shape);	*/
  				
  				@$handle = fopen($imagelink, "r"); 
  				if($handle){
					while (!feof($handle)){
						$this->tem_image .= fread($handle, 1024);
					} 
					$img = new SWFBitmap($this->tem_image); 
					fclose($handle); 
					
					$this->swf->add($img);
  				}
			}
			break;
			case 'GIF': {
				@$handle = fopen($imagelink, "r"); 
  				if($handle){
					while (!feof($handle)){
						$this->tem_image .= fread($handle, 1024);
					} 
					$img = new SWFBitmap($this->tem_image); 
					fclose($handle); 
					
					$this->swf->add($img);
  				}	
			}
		}
	}
}


?>