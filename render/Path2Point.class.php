<?php

/**
 * Getmap Geometry Path, Polygon, Polyline 2 point Class
 *
 * @version $2.0$ 2006
 * @Author  leelight
 * @Contact leeglanz@hotmail.com
 *
 * @version $3.0$ 2006
 * @Author  leelight
 * @Contact leeglanz@hotmail.com
 */


/**
*This class can be used only for Polygon
* if the output graphic CLASS doesnt support polygon function, for example (gd?)
*/
class Polygon2Point {
private $pointsString;
public $Point_X_Po;
public $Point_Y_Po;
public $Number_Point_Po;


public function Polygon2Point($pointsString) {
$this->pointsString = $pointsString;
    //These are some spaces in the path, but the number is unclear, they must be delete
    $iSpace=3;
    #$iSpace=strlen($this->pointsString);
    for($iSpace;$iSpace>0;$iSpace--){
         $strChars = " ";
        for($iaddS=0;$iaddS<$iSpace;$iaddS++){
		     $strChars = $strChars." ";
		}
     $this->pointsString = str_replace($strChars,' ',$this->pointsString);
	}
	//echo $this->pointsString;

//Some SVG file uses ',' in polygon or polyline command or not, whatever delete it at first
//Replace ',' with space
$this->pointsString = strtr($this->pointsString,',',' ');
//Before explode, all empty in front and at end must be sure that is deleted!!!!!
$this->pointsString = trim($this->pointsString);

$Array_Point =explode(" ", $this->pointsString);
$this->Number_Point_Po = count($Array_Point)/2;
//echo $this->Number_Point."\n";
for ($j=0;$j<($this->Number_Point_Po)*2;$j++) {
    //All space must be deleted
    $Point_xy[$j]= trim($Array_Point[$j]);
	}
for($i=0;$i<=$this->Number_Point_Po;$i++){
//echo count($Point_xy);
$Point_x[$i] = $Point_xy[$i*2];
$Point_y[$i]= $Point_xy[$i*2+1];

$this->Point_X_Po[$i] = $Point_x[$i];
$this->Point_Y_Po[$i] = $Point_y[$i];

//Actually, the number of points is Number_Point_Po
//But we should add the last point, it is also the first point
//at last the number of points is (Number_Point_Po+1)
if ($i=$this->Number_Point_Po) {
    $this->Point_X_Po[$i] = $Point_x[0];
    $this->Point_Y_Po[$i] = $Point_y[0];
}

}
//$Point_XY_PoArray = array($this->pointsString);
//print_r($Point_XY_PoArray);
#for($i=0;$i<count($this->Point_X_Po);$i++){
#   echo $this->Point_X_Po[$i]."\n";
#   echo $this->Point_Y_Po[$i]."\n";
#}

}

}
//$pointsString='220,100 300,210 170    250          30,320';
//$Polygon2Point=new Polygon2Point($pointsString);

//====================================================================================
/**
*This class can be used for Polyline or Polygon
* if the output graphic CLASS support polygon function, for example pdflib
*/
class PolyGonLine2Point {
private $pointsString;
public $Point_X_Pol;
public $Point_Y_Pol;
public $Number_Point_Pol;


public function PolyGonLine2Point($pointsString) {
$this->pointsString = $pointsString;
    //These are some spaces in the path, but the number is unclear, they must be delete
    $iSpace=3;
    #$iSpace=strlen($this->pointsString);
    for($iSpace;$iSpace>0;$iSpace--){
         $strChars = " ";
        for($iaddS=0;$iaddS<$iSpace;$iaddS++){
		     $strChars = $strChars." ";
		}
     $this->pointsString = str_replace($strChars,' ',$this->pointsString);
	}
	//echo $this->pointsString;

//Some SVG file uses ',' in polygon or polyline command or not, whatever delete it at first
//Replace ',' with space
$this->pointsString = strtr($this->pointsString,',',' ');
//Before explode, all empty in front and at end must be sure that is deleted!!!!!
$this->pointsString = trim($this->pointsString);

$Array_Point =explode(" ", $this->pointsString);
$this->Number_Point_Pol = count($Array_Point)/2;
//echo $this->Number_Point."\n";
for ($j=0;$j<($this->Number_Point_Pol)*2;$j++) {
    //All space must be deleted
    $Point_xy[$j]= trim($Array_Point[$j]);
	}
for($i=0;$i<$this->Number_Point_Pol;$i++){
//echo count($Point_xy);
$Point_x[$i] = $Point_xy[$i*2];
$Point_y[$i]= $Point_xy[$i*2+1];

$this->Point_X_Pol[$i] = $Point_x[$i];
$this->Point_Y_Pol[$i] = $Point_y[$i];

}
//$Point_XY_PoArray = array($this->pointsString);
//print_r($Point_XY_PoArray);
#for($i=0;$i<count($this->Point_X_Po);$i++){
#   echo $this->Point_X_Po[$i]."\n";
#   echo $this->Point_Y_Po[$i]."\n";
#}

}

}
//$pointsString='220,100 300,210 170    250          30,320';
//$Polygon2Point=new Polygon2Point($pointsString);

//====================================================================================
class Path2Point {
private $pathString;
private $Point_X;
private $Point_Y;
private $Number_Point;
//$Number_path_M_devided is number of array
public $Number_path_M_devided;
//$Number_Point_M is array storing the points number of each new path from new start point M
public $Number_Point_M;
public $Point_X_M ;
public $Point_Y_M ;

public function Path2Point($pathString) {
$this->pathString = $pathString;
//Replace ',' with space
$this->pathString = str_replace(',',' ',$this->pathString);
//Replace 'Z' with space
$pathStringTemp = strtr($this->pathString,'Z',' ');
//Delete 'M' and space in the front and at end
//$pathString = trim(substr($pathStringTemp, strpos($pathStringTemp, 'M')+1));


//----------------------------------------------------------
/**
*If These more than one start point M
*/
   $path_M_devided =explode("M", trim($pathStringTemp));
   //Because even nothing before the first M, it is also devided from the first M
   $this->Number_path_M_devided = count($path_M_devided)-1;
   //$i1 must be set from 1, because at 0 is nothing
   for($i1=1;$i1<=$this->Number_path_M_devided;$i1++){


//Point to relative(l) method
if (strchr($path_M_devided[$i1],'l')){
//Replace 'l' with space
$pathString = strtr($path_M_devided[$i1],'l',' ');
//echo $pathString."|"."\n";

    //These are some spaces in the path, but the number is unclear, they must be delete
    $iSpace= 3;
    #$iSpace=strlen($pathString);
    for($iSpace;$iSpace>0;$iSpace--){
         $strChars = " ";
        for($iaddS=0;$iaddS<$iSpace;$iaddS++){
		     $strChars = $strChars." ";
		}
     $pathString = str_replace($strChars,' ',$pathString);
	}

	//echo $pathString."|"."\n";
//Before explode, all empty in front and at end must be sure that is deleted!!!!!
$Array_Point =explode(" ", trim($pathString));
$this->Number_Point_M[$i1] = count($Array_Point)/2;
//echo $this->Number_Point_M[$i1]."\n";
for ($j=0;$j<($this->Number_Point_M[$i1])*2;$j++) {
    //All space must be deleted
    $Point_DxDy[$j]= trim($Array_Point[$j]);
	}

for($i=0;$i<$this->Number_Point_M[$i1];$i++){
$Point_Dx[$i] = $Point_DxDy[$i*2];
$Point_Dy[$i]= $Point_DxDy[$i*2+1];
if($i==0){
$this->Point_X[0] = $Point_DxDy[0];
$this->Point_Y[0] = $Point_DxDy[1];
}
if ($i>0) {
$this->Point_X[$i] = $this->Point_X[$i-1] + $Point_Dx[$i];
$this->Point_Y[$i] = $this->Point_Y[$i-1] + $Point_Dy[$i];
}
//echo $this->Point_Y[$i]."\n";
//echo $Point_DxDy[0]."\n".$Point_DxDy[1]."\n".$Point_DxDy[2]."\n";
}

$this->Point_X_M[$i1] = $this->Point_X;
$this->Point_Y_M[$i1] = $this->Point_Y;
#print_r($this->Point_X);
#print_r($this->Point_Y);
}

//Point to absolute(L) method
if (strchr($path_M_devided[$i1],'L')){
//Replace 'L' with space
$pathString = strtr($path_M_devided[$i1],'L',' ');
//echo $pathString."|"."\n";

    //These are some spaces in the path, but the number is unclear, they must be delete
    $iSpace= 3;
    #$iSpace=strlen($pathString);
    for($iSpace;$iSpace>0;$iSpace--){
         $strChars = " ";
        for($iaddS=0;$iaddS<$iSpace;$iaddS++){
		     $strChars = $strChars." ";
		}
     $pathString = str_replace($strChars,' ',$pathString);
	}

	//echo $pathString."|"."\n";
//Before explode, all empty in front and at end must be sure that is deleted!!!!!
$Array_Point =explode(" ", trim($pathString));
$this->Number_Point_M[$i1] = count($Array_Point)/2;

for ($j=0;$j<($this->Number_Point_M[$i1])*2;$j++) {
    //All space must be deleted
    $PointXY[$j]= trim($Array_Point[$j]);
	}
//echo $pathString;

for($i=0;$i<$this->Number_Point_M[$i1];$i++){
$this->Point_X[$i] = $PointXY[$i*2];
$this->Point_Y[$i] = $PointXY[$i*2+1];
$this->Point_XY_MArray[$i1]=array_fill($i*2,1,$this->Point_X[$i]);
#$this->Point_XY_MArray[$i1]=array_fill($i*2+1,1,$this->Point_Y[$i]);
}
$this->Point_X_M[$i1] = $this->Point_X;
$this->Point_Y_M[$i1] = $this->Point_Y;

}
   }//$i1 for
#print_r($this->Point_XY_MArray);
//print_r($this->Point_X_M);
//$im must be set from 1, because at 0 is nothing
#for($im=1;$im<=$this->Number_path_M_devided;$im++){
#   $Point_X = array_slice($this->Point_X_M[$im],0,$this->Number_Point_M[$im]);
#   $Point_Y = array_slice($this->Point_Y_M[$im],0,$this->Number_Point_M[$im]);
##   for($i=0;$i<$this->Number_Point_M[$im];$i++){
##   echo $Point_X[$i]."\n";
##   echo $Point_Y[$i]."\n";
##}
#   echo implode("\n",$Point_X),"\n";
#   echo implode("\n",$Point_Y),"\n";
#}

}



}

#$pathString='M250  ,            150 L150    350 L50 , 250 Z';
#$pathString='M25, 15   l15   35 l5 ,  25 Z  ';
#$pathString='M-3142,3461l51 13 89 25 47 14 66 19 229 67 3667,-14M250 150 l50          50 50        50';
//$pathString='M-3142 ,3461L51 13 89 25 47 14    66 19 229 67 3667 ,-14M250      150 L50 50 50 -50';
#$pathString='M3315 3270l-59 7 -115 8 -73 8 -85 12 -41 5 -31 5 -29 4 -33 5 -179 24 -20 4 -228 37 -81 15 -43 8';
#$Path2Point=new Path2Point($pathString);
?>