<?php

/**
 *
 * @version 3.0
 * @copyright 2006
 * @author leelight
 * @contact webmaster@easywms.com
 */

/*
 *@Description: get the select real area coordinate(boundary xy) from pixel coordinate in GetFeatureInfo
 *@params:minx,miny,maxx,maxy: boundinbox of the map
 *@params:width,height: width and height of map in pixel
 *@params:Pixel_x,Pixel_y: the select point coordinate on the screen in pixel
 *@params:radius: the selected radius in pixel
 *@params enablestretchmap: flag to enable stretch map.
 *@return the select real area coordinate(boundary xy) in square and xy coordinate array. Later will change to circle(1st select from the rectange and then cal the distance within the circle)
 *
 */
function getSelectSquare($minx, $miny, $maxx, $maxy, $width, $height, $Pixel_x, $Pixel_y, $Pixelradius, $enablestretchmap)
	{
	$w = $maxx - $minx;
	$h = $maxy - $miny;
	$s = $w / $h;
	@$scale = $width / $height;
	$newquare = array();
	if ($enablestretchmap == 0) {
		// if w>h, the px will be kept, but py will be stretched
		if ($s >= 1) {
			$x_width = $Pixel_x * ($w / $width);
			$y_height = (($height - $Pixel_y) - ($height - $width / $s) / 2) * ($h * $s / $width);
			$radius = $Pixelradius * ($w / $width);
		} else if ($s < 1) {
			$x_width = ($Pixel_x - ($width - $height * $s) / 2) * ($w / ($s * $height));
			$y_height = ($height - $Pixel_y) * ($h / $height);
			@$radius = ($Pixelradius * $h) / $height;
		}
	} else if ($enablestretchmap == 1) {
		$x_width = $Pixel_x * ($w / $width);
		$y_height = ($height - $Pixel_y) * ($h / $height);
		// $y_height=($Pixel_y)*($h/$height);
		if ($s >= 1) {
			@$radius = ($Pixelradius * $h) / $height;
		} else
			@$radius = ($Pixelradius * $w) / $width;
	}
	$x_temp = $minx + $x_width;
	$y_temp = $miny + $y_height;
	
	$newquare[0] = $x_plus = $x_temp + $radius;
	$newquare[1] = $x_minus = $x_temp - $radius;
	$newquare[2] = $y_plus = $y_temp + $radius;
	$newquare[3] = $y_minus = $y_temp - $radius;
	$newquare[4] = $x_temp;
	$newquare[5] = $y_temp;
	return $newquare;
	}

/*
 *@Description: get the new width&height
 *@params:minx,miny,maxx,maxy: boundinbox of the map
 *@params:width,height: width and height of map in pixel
 *@params enablestretchmap: flag to enable stretch map.
 * false: The map will display in the middle of the map according the scale
 * true:  The map will stretch according the scale
 *@return get thenew width&height(if enablestretchmap is true, or the value not change) array
 * It will take longer side to calculate the new width and height
 */
function getStretchWidthHeight($minx, $miny, $maxx, $maxy, $width, $height, $enablestretchmap)
	{
	$size = array();
	if ($enablestretchmap == 0) {
		$size[0] = round($width);
		$size[1] = round($height);
	}
	if ($enablestretchmap == 1) {
		$w = $maxx - $minx;
		$h = $maxy - $miny;
		$s = $w / $h;
		$scale = $width / $height;
		// if w<=h, the height will be kept, but width will be stretched
		if ($s <= 1) {
			// $size[0]=round($height*$s);
			// $size[1]=round($height);
			$size[0] = $height * $s;
			$size[1] = $height;
			//$ratio = $h / $height;
			//$size[2] = $ratio;
		}
		// if w>h, the width will be kept, but height will be stretched
		else if ($s > 1) {
			// $size[0]=round($width);
			// $size[1]=round($width/$s);
			$size[0] = $width;
			$size[1] = $width / $s;
			//$ratio = $w / $width;
			//$size[2] = $ratio;
		}
	}
	return $size;
	}

/*
 * Same function as getStretchWidthHeight, but it will take shorter side to calculate the new width and height
 */
function getStretchWidthHeight_($minx, $miny, $maxx, $maxy, $width, $height, $enablestretchmap)
	{
	$size = array();
	if ($enablestretchmap == 0) {
		$size[0] = round($width);
		$size[1] = round($height);
	}
	if ($enablestretchmap == 1) {
		$w = $maxx - $minx;
		$h = $maxy - $miny;
		$s = $w / $h;
		$scale = $width / $height;
		// if w>=h, the width will be kept, but height will be stretched
		if ($s >= 1 AND $scale >= 1) {
			$size[0] = round($height * $s);
			$size[1] = round($height);
		} else if ($s >= 1 AND $scale < 1) {
			$size[0] = round($width);
			$size[1] = round($width / $s);
		}
		// if w<h, the height will be kept, but width will be stretched
		else if ($s < 1 AND $scale >= 1) {
			$size[0] = round($width);
			$size[1] = round($width / $s);
		} else if ($s < 1 AND $scale < 1) {
			$size[0] = round($height * $s);
			$size[1] = round($height);
		}
	}
	return $size;
	}

/*
 *@Description: get the pixel coordinate from real area coordinate
 *@params:minx,miny,maxx,maxy: boundinbox of the map
 *@params:width,height: width and height of map in pixel
 *@params:x,y: the select point coordinate in real world
 *@params enablestretchmap: flag to enable stretch map.
 * false: The map will display in the middle of the map according the scale
 * true:  The map will stretch according the scale
 *@return get the xy coordinate in pixel,scale(1 pixel to real distance) array
 *
 */
function getPixelXYFromReal($minx, $miny, $maxx, $maxy, $width, $height, $x, $y, $enablestretchmap){
	$w = $maxx - $minx;
	$h = $maxy - $miny; 
	$s = $w / $h; 
	$coord = array();
	if ($enablestretchmap == 0) {
		$Scale_x = ($x - $minx) / $w;
		$Scale_y = ($y - $miny) / $h;
		if ($s >= 1) {
			$Length_xpixel = $width * $Scale_x;
			$Length_ypixel = $width / $s * $Scale_y;
			// make the image display in the middle of picture
			$Pixel_x = $Length_xpixel;
			$Pixel_y = $height - $Length_ypixel - ($height - $width / $s) / 2;
			// $Pixel_y = $Length_ypixel+($height-$width/$s)/2;
			$scale = $w / $width;
		}
		if ($s < 1) {
			$Length_xpixel = $height * $s * $Scale_x;
			$Length_ypixel = $height * $Scale_y;
			// make the image display in the middle of picture
			$Pixel_x = $Length_xpixel + ($width - $height * $s) / 2;
			$Pixel_y = $height - $Length_ypixel;
			// $Pixel_y = $Length_ypixel;
			$scale = $h / $height;
		}
	}
	if ($enablestretchmap == 1) {
		$Pixel_x = $width * ($x - $minx) / $w;
		$Pixel_y = $height - $height * ($y - $miny) / $h;
		// $Pixel_y = $height*($y-$miny)/$h;
		$scale = $w / $width;
	}
	$coord[0] = $Pixel_x;//echo " ".$Pixel_x."/". $Pixel_y."<br>";
	$coord[1] = $Pixel_y;
	$coord[2] = $scale;
	return $coord;
}

/*
 *@Description: get the pixel coordinate from real area coordinate, use for raster image
 *@params:minx,miny,maxx,maxy: boundinbox of the map
 *@params:width,height: width and height of map in pixel
 *@params:x,y: the select point coordinate in real world
 *@params enablestretchmap: flag to enable stretch map.
 * false: The map will display in the middle of the map according the scale
 * true:  The map will stretch according the scale
 *@return get the xy coordinate in pixel,scale(1 pixel to real distance) array
 *
 */
function getPixelXYFromRealConverse($minx, $miny, $maxx, $maxy, $width, $height, $x, $y, $enablestretchmap)
	{
	$w = $maxx - $minx;
	$h = $maxy - $miny;
	$s = $w / $h;
	$coord = array();
	if ($enablestretchmap == 0) {
		$Scale_x = ($x - $minx) / $w;
		$Scale_y = ($y - $miny) / $h;
		if ($s >= 1) {
			$Length_xpixel = $width * $Scale_x;
			$Length_ypixel = $width / $s * $Scale_y;
			// make the image display in the middle of picture
			$Pixel_x = $Length_xpixel;
			// $Pixel_y = $height-$Length_ypixel-($height-$width/$s)/2;
			$Pixel_y = $Length_ypixel + ($height - $width / $s) / 2;
			$scale = $w / $width;
		}
		if ($s < 1) {
			$Length_xpixel = $height * $s * $Scale_x;
			$Length_ypixel = $height * $Scale_y;
			// make the image display in the middle of picture
			$Pixel_x = $Length_xpixel + ($width - $height * $s) / 2;
			// $Pixel_y = $height-$Length_ypixel;
			$Pixel_y = $Length_ypixel;
			$scale = $h / $height;
		}
	}
	if ($enablestretchmap == 1) {
		$Pixel_x = $width * ($x - $minx) / $w;
		// $Pixel_y = $height-$height*($y-$miny)/$h;
		$Pixel_y = $height * ($y - $miny) / $h;
		$scale = $w / $width;
	}
	$coord[0] = $Pixel_x;
	$coord[1] = $Pixel_y;
	$coord[2] = $scale;
	return $coord;
	}

/**
 *
 * @Description : sort the layer array according the priority
 */
function sortLayer($arrLayers,$arrPriority)
	{
	$numberoflayers = count($arrLayers);
	for ($i = 0; $i < $numberoflayers; $i++) {
		$min = $i;
		for($j = $i;$j < $numberoflayers;$j++) {
			if ($arrPriority[$j] < $arrPriority[$min]) {
				$min = $j;
			}
		}
		$tmpl = $arrPriority[$i];
		$arrPriority[$i] = $arrPriority[$min];
		$arrPriority[$min] = $tmpl;
		
		$tmp = $arrLayers[$i];
		$arrLayers[$i] = $arrLayers[$min];
		$arrLayers[$min] = $tmp;
	}
	
	return $arrLayers;
	}

/**
 * R*pi = 180 R = 6371km
 * @param meter
 * @return
 */
function getDegreeFromMeter($meter){
	$degree = 0;		
	$degree = 180 * ($meter / (Earth_Average_Radius * pi()));
	return $degree;
}

function getMeterFromDegree($degree){
	$meter = 0;		
	$meter = ($degree * (Earth_Average_Radius * pi())) / 180;
	return $meter;
} 

/**
 * get the current layer's scale
 */
function getCurrentScale($real_length, $pixel_length, $srs = ""){
	$scale = 1;
	switch(strtolower($srs)){
		case 'epsg:4326':{
			$real_length = getMeterFromDegree($real_length);
			
		}
		break;
	}
	
	$scale = round($real_length / ($pixel_length * PixelSize));
	return $scale;
}

/**
 * format the scale from string to number
 */
function formatScale($scale)
	{
	//$scale = str_replace(",", ".", $scale);
	return (int)$scale;
	}

/**
 * $coord: the array from getPixelXYFromReal or getPixelXYFromRealConverse
 * return the coordinates for 2.5D map
 */
function get25DXY($coord, $width, $height, $hangle, $vangle, $distance)
	{
	$X = $coord[0];
	$Y = $coord[1];
	
	$X0 = $X * cos($hangle) - $Y * sin($hangle);
	$Y0 = $height - ($X * sin($hangle) + $Y * cos($hangle));
	
	$Y1 = $coord_[1] = ($Y0 * $distance * cos($vangle) / ($distance + $Y0 * sin($vangle)));
	@$X1 = $coord_[0] = $X0 * $Y1 / ($Y0 * cos($vangle));
	
	$coord_[1] = $height - $Y1;
	// move down to draw the sky
	$coord_[1] = $coord_[1] - $height * (cos($vangle));
	// $coord_[1] = $coord_[1] - $height * 0.5;
	return $coord_;
	}

function get25DXYConverse($coord, $width, $height, $hangle, $vangle, $distance)
	{
	$X = $coord[0];
	$Y = $coord[1];
	
	$X0 = $X * cos($hangle) - $Y * sin($hangle);
	$Y0 = $height - ($X * sin($hangle) + $Y * cos($hangle));
	
	$Y1 = $coord_[1] = ($Y0 * $distance * cos($vangle) / ($distance + $Y0 * sin($vangle)));
	@$X1 = $coord_[0] = $X0 * $Y1 / ($Y0 * cos($vangle));
	// move down to draw the sky
	// $coord_[1] = $coord_[1] + $height * (1 - cos($vangle));
	// $coord_[1] = $coord_[1] + $height * 0.3;
	return $coord_;
	}

function getOrgXYFrom25DXY($coord25d, $width, $height, $hangle, $vangle, $distance)
	{
	$X1 = $coord25d[0];
	$Y1 = $coord25d[1];
	
	$Y0 = $Y1 * $distance / ($distance * cos($vangle) - $Y1 * sin($vangle));
	$X0 = $X1 * $Y0 * cos($vangle) / $Y1;
	
	$Y = ($Y0 - $X0 * tan($hangle)) / (sin($hangle) * tan($hangle) + cos($hangle));
	$X = ($X0 + $Y * sin($hangle)) / cos($hangle);
	
	$orgcoord[1] = $Y;
	$orgcoord[0] = $X;
	
	return $orgcoord;
	}
	/*
	 * this will extend the boundary to fit the right bbox of 2.5D map
	 */
function getExtendBbox($minx, $miny, $maxx, $maxy, $vangle)
	{
	@$dis = ($maxx - $maxy) * (1 - cos($vangle)) / (2 * cos($vangle));
	
	$newbbox[0] = $minx - $dis;
	$newbbox[1] = $miny ;
	$newbbox[2] = $maxx + $dis;
	$newbbox[3] = $maxy + ($maxy - $miny) * ($dis * 2) / ($maxx - $minx); //print_r($newbbox);
	
	return $newbbox;
	}

/**
 * Get the contour line of one linestring
 *
 * @params : $x $y linestring coordiante
 * @params : $pointNumber
 * @params : $width
 * @return the contourline coordinate array
 */
function getContourLine($x, $y, $pointNumber, $width)
	{
	$dis = $width / 2;
	$num = 2 * $pointNumber;
	if ($pointNumber == 2) {
		$angle = (atan2(($y[0] - $y[1]), ($x[1] - $x[0])));
		
		$dist_x = $dis * (sin($angle));
		$dist_y = $dis * (cos($angle));
		
		$p1x = $x[0] + $dist_x;
		$p1y = $y[0] + $dist_y;
		$p2x = $x[1] + $dist_x;
		$p2y = $y[1] + $dist_y;
		
		$p3x = $x[1] - $dist_x;
		$p3y = $y[1] - $dist_y;
		$p4x = $x[0] - $dist_x;
		$p4y = $y[0] - $dist_y;
		
		$clx[0] = $p1x;
		$clx[1] = $p2x;
		$cly[0] = $p1y;
		$cly[1] = $p2y;
		
		$clx[2] = $p3x;
		$clx[3] = $p4x;
		$cly[2] = $p3y;
		$cly[3] = $p4y;
	} else if ($pointNumber > 2) {
		for($i = 0, $j = 0;$i < $pointNumber-2;$i++, $j = $j + 1) {
			$middlepointa = array();
			$middlepointb = array();
			
			$anglea = (atan2(($y[$i] - $y[$i + 1]), ($x[$i + 1] - $x[$i])));
			$angleb = (atan2(($y[$i + 1] - $y[$i + 2]), ($x[$i + 2] - $x[$i + 1])));
			
			$dist_xa = $dis * (sin($anglea));
			$dist_ya = $dis * (cos($anglea));
			
			$dist_xb = $dis * (sin($angleb));
			$dist_yb = $dis * (cos($angleb));
			// left countour line a
			$p1xa = $x[$i] + $dist_xa;
			$p1ya = $y[$i] + $dist_ya;
			$p2xa = $x[$i + 1] + $dist_xa;
			$p2ya = $y[$i + 1] + $dist_ya;
			// right countour line
			$p3xa = $x[$i + 1] - $dist_xa;
			$p3ya = $y[$i + 1] - $dist_ya;
			$p4xa = $x[$i] - $dist_xa;
			$p4ya = $y[$i] - $dist_ya;
			
			// left countour line b
			$p1xb = $x[$i + 1] + $dist_xb;
			$p1yb = $y[$i + 1] + $dist_yb;
			$p2xb = $x[$i + 2] + $dist_xb;
			$p2yb = $y[$i + 2] + $dist_yb;
			// right countour line
			$p3xb = $x[$i + 2] - $dist_xb;
			$p3yb = $y[$i + 2] - $dist_yb;
			$p4xb = $x[$i + 1] - $dist_xb;
			$p4yb = $y[$i + 1] - $dist_yb;
			// $array = array(0 => $p1x, $p1y, $p2x, $p2y, $p3x, $p3y, $p4x, $p4y);
			$middlepointa = getIntersectPointFrom2Lines($p1xa, $p1ya, $p2xa, $p2ya, $p1xb, $p1yb, $p2xb, $p2yb);
			$middlepointb = getIntersectPointFrom2Lines($p3xb, $p3yb, $p4xb, $p4yb, $p3xa, $p3ya, $p4xa, $p4ya);
			
			if($j==0){
				$clx[$j] = $p1xa;
				$clx[$j + 1] = $middlepointa[0];
				$clx[$j + 2] = $p2xb;
				$cly[$j] = $p1ya;
				$cly[$j + 1] = $middlepointa[1];
				$cly[$j + 2] = $p2yb;
				
				$clx[$num - $j-3] = $p3xb;
				$clx[$num - $j-2] = $middlepointb[0];
				$clx[$num - $j-1] = $p4xa;
				$cly[$num - $j-3] = $p3yb;
				$cly[$num - $j-2] = $middlepointb[1];
				$cly[$num - $j-1] = $p4ya;
			}
			else{
				//$clx[$j] = $p1xa;
				$clx[$j + 1] = $middlepointa[0];
				$clx[$j + 2] = $p2xb;
				//$cly[$j] = $p1ya;
				$cly[$j + 1] = $middlepointa[1];
				$cly[$j + 2] = $p2yb;
				
				$clx[$num - $j-3] = $p3xb;
				$clx[$num - $j-2] = $middlepointb[0];
				//$clx[$num - $j-1] = $p4xa;
				$cly[$num - $j-3] = $p3yb;
				$cly[$num - $j-2] = $middlepointb[1];
				//$cly[$num - $j-1] = $p4ya;
			}
		}
	}
	$array[0] = $clx; //print_r($clx);
	$array[1] = $cly;
	
	return $array;
	}

/**
 * from x1 to x2 line1
 * from x3 to x4 line2
 * return the intersect point
 */
function getIntersectPointFrom2Lines($x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4)
	{
	$N = $x2 - $x1;
	$P = $x4 - $x3;
	$O = $y2 - $y1;
	$Q = $y4 - $y3;
	
	$K = $Q * $N - $O * $P;
	if ($K == 0) {
		// the 2 lines could be in the same line
		$tempX = $re[0] = ($x2 + $x3) / 2;
		$tempY = $re[1] = ($y2 + $y3) / 2;
		return $re;
		// the 2 liens could be parallel lines, this will not occur in our case to get the countourline
	} else {
		$U = $N * $y1 - $O * $x1;
		$V = $P * $y3 - $Q * $x3;
		// $tempX = $re[0] = Round(($P * $U - $N * $V) / $K, 4);
		// $tempY = $re[1] = Round(($Q * $U - $O * $V) / $K, 4);
		$tempX = $re[0] = ($P * $U - $N * $V) / $K;
		$tempY = $re[1] = ($Q * $U - $O * $V) / $K;
		return $re;
	}
	}
//print_r(getIntersectPointFrom2Lines(1, 1, 2, 2, 4, 4, 5, 5));


/**
 * Get the shape coordinate array of one Triangle
 */
function getShapeTriangle($x, $y, $radius){
	$temx = array();
	$temy = array();
	$temx[0]= $x+ $radius*cos(deg2rad(330));
	$temy[0]= $y+ $radius*sin(deg2rad(330));
	
	$temx[1]= $x;
	$temy[1]= $y - $radius*2;
	
	$temx[2]= $x+ $radius*cos(deg2rad(210));
	$temy[2]= $y+ $radius*sin(deg2rad(210));
	
	return array(0=>$temx, $temy);
}
/**
 * Get the shape coordinate array of one cross
 */
function getShapeCross($x, $y, $radius){
	$temx = array();
	$temy = array();
	$step = $radius/2;
	$xmin = $x - $radius;
	$ymin = $y - $radius;
	$xmax = $x + $radius;
	$ymax = $y + $radius;
	
	$temx = array(0=>$xmin, $xmin, $xmin+$step, $xmin+$step,$xmin+$step*3,$xmin+$step*3,$xmax,$xmax,$xmin+$step*3,$xmin+$step*3,$xmin+$step,$xmin+$step);
	$temy = array(0=>$ymin+$step, $ymin+$step*3, $ymin+$step*3, $ymax,$ymax,$ymin+$step*3,$ymin+$step*3,$ymin+$step,$ymin+$step,$ymin,$ymin,$ymin+$step);
	return array(0=>$temx, $temy);
}
/**
 * Get the shape coordinate array of one FiveCornerStar
 */
function getShapeFiveCornerStar($x, $y, $radius){
	$step = $radius/2;
	$ax = array();
	$ay = array();
	$bx = array();
	$by = array();
	for($i=0;$i<=5;$i++)
	{
		$ax[$i] = $x + $step * sin(3.1415927*0.8*$i);
		$ay[$i] = $y - $step * cos(3.1415927*0.8*$i);
	}
	
	for ($i=0;$i<=5;$i++)
	{
		$bx[$i] = $x + (-3*$step/8) * sin(3.1415927*0.8*$i);
		$by[$i] = $y - (-3*$step/8) * cos(3.1415927*0.8*$i);
	}
	
	for($i=0;$i<5;$i++)
	{
		//pDC->MoveTo(a[i]);
		//pDC->LineTo(b[i+1]);
		//pDC->MoveTo(b[i]);
		//pDC->LineTo(a[i+1]);
		
		$arrayx[$i*2] = $ax[$i];
		$arrayy[$i*2] = $ay[$i];
		
		$arrayx[$i*2+1] = $bx[$i+1];
		$arrayy[$i*2+1] = $by[$i+1];
	}
	$arrayx = array(0=>$arrayx[0],$arrayx[1],$arrayx[4],$arrayx[5],$arrayx[8],$arrayx[9],$arrayx[2],$arrayx[3],$arrayx[6],$arrayx[7]);
	$arrayy = array(0=>$arrayy[0],$arrayy[1],$arrayy[4],$arrayy[5],$arrayy[8],$arrayy[9],$arrayy[2],$arrayy[3],$arrayy[6],$arrayy[7]);
	return array(0=>$arrayx, $arrayy);
}
//print_r(getShapeFiveCornerStar(0,0,400));
 /**
  * Get the shape coordinate array of one X
  */
function getShapeX($x, $y, $radius){
	$temx = array();
	$temy = array();
	$step = $radius/2;
	$xmin = $x - $radius;
	$ymin = $y - $radius;
	$xmax = $x + $radius;
	$ymax = $y + $radius;
	
	//from middle point in the left side
	$temx = array(0=>$x-$step,$xmin,$x-$step,$x,$x+$step,$xmax,$x+$step,$xmax,$x+$step,$x,$x-$step,$xmin);
	$temy = array(0=>$y,$y+$step,$ymax,$y+$step,$ymax,$y+$step,$y,$y-$step,$ymin,$y-$step,$ymin,$y-$step);
	return array(0=>$temx, $temy);
}
function getTransX($x,$radius){
	return  $x+ $radius*cos(deg2rad(30));
}
function getTransY($y,$radius){
	return  $y+ $radius*sin(deg2rad(30));
}

?>