<?php
/**
 * PDFRender.class.php
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

class PDFRender {
	private $minx;
	private $miny;
	private $maxx;
	private $maxy;
	private $width;
	private $height;
	private $enablestretchmap;
	public $pdf;
	private $font;
	private $image;
	private $tem_tile;
	private $tem_image;
	
	public function setRender($minx, $miny, $maxx, $maxy, $width, $height, $pdf, $font, $enablestretchmap)
		{
		$this->minx = $minx;
		$this->miny = $miny;
		$this->maxx = $maxx;
		$this->maxy = $maxy;
		$this->width = $width;
		$this->height = $height;
		$this->pdf = $pdf;
		$this->font = $font;
		$this->enablestretchmap = $enablestretchmap;
		}
	
	/**
	 *
	 * @DESCRIPTION :Class Constructor.
	 */
	public function PDFRender()
		{
		}
	
	public function clearAllResource(){
		@ImageDestroy($this->tem_tile);
		@ImageDestroy($this->tem_image);
		@ImageDestroy($this->image);
	}
	
	public function createText($x, $y, $textstring, $fontsize, $color)
		{
		$data_x = $x;
		$data_y = $y;
		$coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x, $data_y, $this->enablestretchmap);
		$Pixel_x = $coord[0];
		$Pixel_y = $coord[1];
		$this->pdf->setfont($this->font, $fontsize);
		$this->pdf->set_text_pos($Pixel_x, $Pixel_y);
		$this->pdf->show($textstring);
		}
	
	public function createTextWithScreenCoordinate($x, $y, $textstring, $fontsize, $color)
		{
		
		$this->pdf->setfont($this->font, $fontsize);
		$this->pdf->set_text_pos($x, $y);
		$this->pdf->show($textstring);
		
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
					$this->pdf->circle($Pixel_x, $Pixel_y, $radius);
					$this->pdf->fill();
					// for ellipse
					/*if ($radiusx >= $radiusy) {
					 $this->pdf->scale(1, $radiusy / $radiusx);
					 $this->pdf->circle($Pixel_x, $Pixel_y, $radiusy);
					 } elseif ($radiusx < $radiusy) {
					 $this->pdf->scale($radiusx / $radiusy, 1);
					 $this->pdf->circle($Pixel_x, $Pixel_y, $radiusx);
					 }
					 $this->pdf->fill();*/
				}
				break;
				case 'SQUARE': {
					$this->pdf->setlinewidth(1); //default
					$this->pdf->rect($Pixel_x - $radius, $Pixel_y - $radius, $radius * 2, $radius * 2);
					$this->pdf->stroke();
				}
				break;
				case "TRIANGLE": {
					$this->pdf->setlinewidth(1); //default
					$tem = getShapeTriangle($Pixel_x, $Pixel_y, $radius);
					for($j = 0;$j < 3;$j++) {
						if($j!=2){
							$this->pdf->moveto($tem[0][$j], $tem[1][$j]);
							$this->pdf->lineto($tem[0][$j+1], $tem[1][$j+1]);
						}
						else{
							$this->pdf->moveto($tem[0][$j], $tem[1][$j]);
							$this->pdf->lineto($tem[0][0], $tem[1][0]);
						}
					}
					$this->pdf->stroke();
				}
				break;
				case "STAR": {
					$this->pdf->setlinewidth(1); //default
					$tem = getShapeFiveCornerStar($Pixel_x, $Pixel_y, $radius);
					for($j = 0;$j < 10;$j++) {
						if($j!=9){
							$this->pdf->moveto($tem[0][$j], $tem[1][$j]);
							$this->pdf->lineto($tem[0][$j+1], $tem[1][$j+1]);
						}
						else{
							$this->pdf->moveto($tem[0][$j], $tem[1][$j]);
							$this->pdf->lineto($tem[0][0], $tem[1][0]);
						}
					}
					$this->pdf->stroke();
				}
				break;
				case "CROSS": {
					$this->pdf->setlinewidth(1); //default
					$tem = getShapeCross($Pixel_x, $Pixel_y, $radius);
					for($j = 0;$j < 12;$j++) {
						if($j!=11){
							$this->pdf->moveto($tem[0][$j], $tem[1][$j]);
							$this->pdf->lineto($tem[0][$j+1], $tem[1][$j+1]);
						}
						else{
							$this->pdf->moveto($tem[0][$j], $tem[1][$j]);
							$this->pdf->lineto($tem[0][0], $tem[1][0]);
						}
					}
					$this->pdf->stroke();
				}
				break;
				case "X": {
					$this->pdf->setlinewidth(1); //default
					$tem = getShapeX($Pixel_x, $Pixel_y, $radius);
					for($j = 0;$j < 12;$j++) {
						if($j!=11){
							$this->pdf->moveto($tem[0][$j], $tem[1][$j]);
							$this->pdf->lineto($tem[0][$j+1], $tem[1][$j+1]);
						}
						else{
							$this->pdf->moveto($tem[0][$j], $tem[1][$j]);
							$this->pdf->lineto($tem[0][0], $tem[1][0]);
						}
					}
					$this->pdf->stroke();
				}
				break;
				default: {
					$this->pdf->setlinewidth(1); //default
					$this->pdf->rect($Pixel_x - $radius, $Pixel_y - $radius, $radius * 2, $radius * 2);
					$this->pdf->stroke();
					// $pdf->closepath_fill_stroke();
					// $pdf->closepath_stroke();
					$this->pdf->closepath();
					$this->pdf->fill();
				}
			}
		}
		}
	
	public function createLinstring($x, $y, $pointNumber, $stroke , $strokewidth, $fillColor, $xmlLineJoin, $xmlLineCap, $blnFillLineString)
		{
		$data_x = $x;
		$data_y = $y;
		
		$this->pdf->setlinewidth($strokewidth);
		switch ($xmlLineJoin) {
			case 'miter':$this->pdf->setlinejoin(0);
			break;
			case 'round':$this->pdf->setlinejoin(1);
			break;
			case 'bevel':$this->pdf->setlinejoin(2);
			break;
		}
		switch ($xmlLineCap) {
			case 'butt':$this->pdf->setlinecap(0);
			break;
			case 'round':$this->pdf->setlinecap(1);
			break;
			case 'square':$this->pdf->setlinecap(2);
			break;
		}
		
		for($i = 0;$i < $pointNumber;$i++) {
			$coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
			$Pixel_x[$i] = $coord[0];
			$Pixel_y[$i] = $coord[1];
		}
		if ($blnFillLineString == 0) {
			for($i = 0;$i < $pointNumber;$i++) {
				if ($i < $pointNumber-1) {
					$this->pdf->moveto($Pixel_x[$i], $Pixel_y[$i]);
					$this->pdf->lineto($Pixel_x[$i + 1], $Pixel_y[$i + 1]);
				}
			}
			$this->pdf->stroke();
		} else { // if fill
			for($i = 0;$i < $pointNumber;$i++) {
				if ($i < $pointNumber-1) {
					$this->pdf->moveto($Pixel_x[$i], $Pixel_y[$i]);
					$this->pdf->lineto($Pixel_x[$i + 1], $Pixel_y[$i + 1]);
				}
				if ($i == $pointNumber-1) {
					$this->pdf->moveto($Pixel_x[$i], $Pixel_y[$i]);
					$this->pdf->lineto($Pixel_x[0], $Pixel_y[0]);
				}
			}
			$this->pdf->stroke();
			#$this->pdf->fill();
		}
		//$this->pdf->stroke();
		//$this->pdf->closepath_fill_stroke();
		//$this->pdf->closepath();
		//$this->pdf->fill();
		}
	
	function createMultiLinstring($x, $y, $lineNumber, $pointNumber, $stroke , $strokewidth, $fillColor ,$xmlLineJoin, $xmlLineCap, $blnFillLineString)
		{
		$data_x = $x;
		$data_y = $y;
		$this->pdf->setlinewidth($strokewidth);
		switch ($xmlLineJoin) {
			case 'miter':$this->pdf->setlinejoin(0);
			break;
			case 'round':$this->pdf->setlinejoin(1);
			break;
			case 'bevel':$this->pdf->setlinejoin(2);
			break;
		}
		switch ($xmlLineCap) {
			case 'butt':$this->pdf->setlinecap(0);
			break;
			case 'round':$this->pdf->setlinecap(1);
			break;
			case 'square':$this->pdf->setlinecap(2);
			break;
		}
		for($i = 0;$i < $lineNumber;$i++) {
			for($j = 0;$j < $pointNumber[$i];$j++) {
				$coord[$j] = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
				$Pixel_x[$j] = $coord[$j][0];
				$Pixel_y[$j] = $coord[$j][1];
			}
			if ($blnFillLineString == 0) {
				for($j = 0;$j < $pointNumber[$i];$j++) {
					if ($j < $pointNumber[$i]-1) {
						$this->pdf->moveto($Pixel_x[$j], $Pixel_y[$j]);
						$this->pdf->lineto($Pixel_x[$j + 1], $Pixel_y[$j + 1]);
					}
				}
				$this->pdf->stroke();
			} else { // if fill
				for($j = 0;$j < $pointNumber[$i];$j++) {
					if ($j < $pointNumber[$i]-1) {
						$this->pdf->moveto($Pixel_x[$j], $Pixel_y[$j]);
						$this->pdf->lineto($Pixel_x[$j + 1], $Pixel_y[$j + 1]);
					}
					if ($j == $pointNumber[$i]-1) {
						$this->pdf->moveto($Pixel_x[$j], $Pixel_y[$j]);
						$this->pdf->lineto($Pixel_x[0], $Pixel_y[0]);
					}
				}
				$this->pdf->stroke();
				#$this->pdf->fill();
			}
		}
		
		#       $this->pdf->closepath_fill_stroke();
		#       $this->pdf->closepath_stroke();
		#       $this->pdf->closepath();
		#       $this->pdf->fill();
		}
	
	public function createPolygon($x, $y, $pointNumber, $stroke , $strokewidth, $xmlLineJoin, $xmlLineCap)
		{
		$data_x = $x;
		$data_y = $y;
		$this->pdf->setlinewidth($strokewidth);
		switch ($xmlLineJoin) {
			case 'miter':$this->pdf->setlinejoin(0);
			break;
			case 'round':$this->pdf->setlinejoin(1);
			break;
			case 'bevel':$this->pdf->setlinejoin(2);
			break;
		}
		switch ($xmlLineCap) {
			case 'butt':$this->pdf->setlinecap(0);
			break;
			case 'round':$this->pdf->setlinecap(1);
			break;
			case 'square':$this->pdf->setlinecap(2);
			break;
		}
		for($i = 0;$i < $pointNumber;$i++) {
			$coord = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i], $data_y[$i], $this->enablestretchmap);
			$Pixel_x[$i] = $coord[0];
			$Pixel_y[$i] = $coord[1];
			// $valuep[$i][$i * 2] = $Pixel_x[$i];
			// $valuep[$i][$i * 2 + 1] = $Pixel_y[$i];
		}
		for($i = 0;$i < $pointNumber;$i++) {
			if ($i < $pointNumber-1) {
				$this->pdf->moveto($Pixel_x[$i], $Pixel_y[$i]);
				$this->pdf->lineto($Pixel_x[$i + 1], $Pixel_y[$i + 1]);
			}
			/*
			 elseif ($i == $pointNumber-1) {
			 // use later
			  $this->pdf->moveto($Pixel_x[$i], $Pixel_y[$i]);
			  $this->pdf->lineto($Pixel_x[0], $Pixel_y[0]);
			  }
			  */
			
		}
		$this->pdf->stroke();
		//$this->pdf->closepath_fill_stroke();
		//$this->pdff->closepath_stroke();
		//$this->pdf->closepath();
		//$this->pdf->fill();
		}
	
	function createMultiPolygon($x, $y, $lineNumber, $pointNumber, $stroke , $strokewidth, $xmlLineJoin, $xmlLineCap)
		{
		$data_x = $x;
		$data_y = $y;
		$this->pdf->setlinewidth($strokewidth);
		switch ($xmlLineJoin) {
			case 'miter':$this->pdf->setlinejoin(0);
			break;
			case 'round':$this->pdf->setlinejoin(1);
			break;
			case 'bevel':$this->pdf->setlinejoin(2);
			break;
		}
		switch ($xmlLineCap) {
			case 'butt':$this->pdf->setlinecap(0);
			break;
			case 'round':$this->pdf->setlinecap(1);
			break;
			case 'square':$this->pdf->setlinecap(2);
			break;
		}
		for($i = 0;$i < $lineNumber;$i++) {
			for($j = 0;$j < $pointNumber[$i];$j++) {
				$coord[$j] = getPixelXYFromRealConverse($this->minx, $this->miny, $this->maxx, $this->maxy, $this->width, $this->height, $data_x[$i][$j], $data_y[$i][$j], $this->enablestretchmap);
				$Pixel_x[$j] = $coord[$j][0];
				$Pixel_y[$j] = $coord[$j][1];
				// $valuep[$i][$j * 2] = $Pixel_x[$j];
				// $valuep[$i][$j * 2 + 1] = $Pixel_y[$j];
			}//print_r($Pixel_x);print_r($Pixel_y);
			for($j = 0;$j < $pointNumber[$i];$j++) {
				if ($j < $pointNumber[$i]-1) {
					$this->pdf->moveto($Pixel_x[$j], $Pixel_y[$j]);
					$this->pdf->lineto($Pixel_x[$j + 1], $Pixel_y[$j + 1]);
				}
			}
		}
		$this->pdf->stroke();
		//$this->pdf->closepath_fill_stroke();
		//$this->pdf->closepath_stroke();
		//$this->pdf->closepath();
		//$this->pdf->fill();
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
		
		$postfix = strtoupper(substr($imagelink, -3));
		$this->tem_tile = null;
		$this->tem_image = null;
		switch ($postfix) {
			case 'JPG': {
				$this->tem_image = $this->pdf->load_image("jpeg", $imagelink, "");
				if ($this->tem_image) {
					$this->tem_tile = imagecreatetruecolor($dst_w, $dst_h);
					$src_w = imagesx($this->tem_image);
					$src_h = imagesy($this->tem_image);
					imagecopyresampled($this->tem_tile, $this->tem_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
					
					$this->pdf->fit_image($this->tem_image, $dst_x, $dst_y, "");
				} 
				// if can not get image, return the rectange
				else{
					$this->pdf->rect($dst_x, $dst_y, $dst_w, $dst_h);
				}
			}
			break;
			case 'PNG': {
				$this->tem_image = $this->pdf->load_image("png", $imagelink, "");
				if ($this->tem_image) {
					$this->tem_tile = imagecreatetruecolor($dst_w, $dst_h);
					$src_w = imagesx($this->tem_image);
					$src_h = imagesy($this->tem_image);
					imagecopyresampled($this->tem_tile, $this->tem_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
					
					$this->pdf->fit_image($this->tem_image, $dst_x, $dst_y, "");
				} else{
					$this->pdf->rect($dst_x, $dst_y, $dst_w, $dst_h);
				}
			}
			break;
			case 'GIF': {
				$this->tem_image = $this->pdf->load_image("gif", $imagelink, "");
				if ($this->tem_image) {
					$this->tem_tile = imagecreatetruecolor($dst_w, $dst_h);
					$src_w = imagesx($this->tem_image);
					$src_h = imagesy($this->tem_image);
					imagecopyresampled($this->tem_tile, $this->tem_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
					
					$this->pdf->fit_image($this->tem_image, $dst_x, $dst_y, "");
				} else{
					$this->pdf->rect($dst_x, $dst_y, $dst_w, $dst_h);
				}
			}
		}
		}
}


?>