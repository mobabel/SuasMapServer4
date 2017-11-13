<?php
/*
 * Created on 07.05.2009
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
 * import.inc
 * @version $Id$
 * @copyright (C) 2006-2009 LI Hui
 * @Description : 
 * @contact webmaster@easywms.com
 * 
 */
 
class Import{
	
	public static $TYPE_DEFAULT = "default";
	
	public static $TYPE_CSV = "csv";
	
	public static $TYPE_CSV_REMOTE = "csv_remote";
	
	public static $TYPE_SVG = "svg";
	
	public static $TYPE_SVG_REMOTE = "svg_remote";
	
	public static $TYPE_SHAPE = "shp";
	
	public static $TYPE_SHAPE_REMOTE = "shp_remote";
	
	public static $TYPE_DBF = "dbf";
	
	public static $TYPE_DBF_REMOTE = "dbf_remote";
	
	public static $TYPE_SHX = "shx";
	
	public static $TYPE_SHX_REMOTE = "shx_remote";
	
	public static $TYPE_MIF = "mif";
	
	public static $TYPE_MIF_REMOTE = "mif_remote";
	
	public static $TYPE_MID = "mid";
	
	public static $TYPE_MID_REMOTE = "mid_remote";
	
	public static $TYPE_E00 = "e00";
	
	public static $TYPE_E00_REMOTE = "e00_remote";
	
	public static $TYPE_OSM = "osm";
	
	public static $TYPE_OSM_REMOTE = "osm_remote";
	
	public static $TYPE_KML = "kml";
	
	public static $TYPE_KML_REMOTE = "kml_remote";
	
	public static $TYPE_GPX = "gpx";
	
	public static $TYPE_GPX_REMOTE = "gpx_remote";
	
	
	/**
	 *
	 * @description : print the EPSG select drop-down list
	 * @params : $type
	 */
	public static function printEPSGList($type){
		print '<div style="z-index:-1">
		<input name="srs_' . $type . '" id="srs_' . $type . '" type="text" size="15" value="SRS_not_defined"  class="smallInput"/>';
		if($type != self::$TYPE_DEFAULT){
			print '<select NAME="srs_TEMP" onchange="document.Form_' . $type . '.srs_' . $type . '.value =this.options[this.selectedIndex].value" class="button4"/>';
		}else{
			print '<select NAME="srs_TEMP" onchange="document.getElementById(\'srs_' . $type . '\').value = this.options[this.selectedIndex].value" class="button4"/>';
		}
		
		print '
		<option value="SRS_not_defined">SRS_not_defined</option>
		<option value="EPSG:NONE">EPSG:NONE</option>
		<option value="EPSG:2163">EPSG:2163</option>
		<option value="EPSG:2167">EPSG:2167</option>
		<option value="EPSG:2168">EPSG:2168</option>
		<option value="EPSG:2399">EPSG:2399</option>
		<option value="EPSG:21483">EPSG:21483</option>
		<option value="EPSG:23833">EPSG:23833</option>
		<option value="EPSG:25832">EPSG:25832</option>
		<option value="EPSG:27354">EPSG:27354</option>
		<option value="EPSG:31466">EPSG:31466</option>
		<option value="EPSG:31467">EPSG:31467</option>
		<option value="EPSG:31468">EPSG:31468</option>
		<option value="EPSG:31469">EPSG:31469</option>
		<option value="EPSG:31492">EPSG:31492</option>
		<option value="EPSG:4269">EPSG:4269</option>
		<option value="EPSG:4296">EPSG:4296</option>
		<option value="EPSG:4326">EPSG:4326 (WGS 84)</option>
		<option value="EPSG:42101">EPSG:42101</option>
		<option value="EPSG:42304">EPSG:42304</option>
		<option value="EPSG:6326">EPSG:6326</option>
		<option value="EPSG:7030">EPSG:7030</option>
		<option value="EPSG:8901">EPSG:8901</option>
		<option value="" title="User Defined"></option>
		</select>
		</div>
		';
	}
	
	
	public static function listDataInDirectory($type){
		global $user;
		switch ($type) {
			case self::$TYPE_SVG_REMOTE:
				echo '<select name="file_svg_remote" id="file_svg_remote" class="button4"/>';
			break;
			case self::$TYPE_CSV_REMOTE:
				echo '<select name="file_csv_remote" id="file_csv_remote" class="button4"/>';
			break;
			case self::$TYPE_SHAPE_REMOTE:
				echo '<select name="file_shp_remote" id="file_shp_remote" class="button4"/>';
			break;
			case self::$TYPE_DBF_REMOTE:
				echo '<select name="file_dbf_remote" id="file_dbf_remote" class="button4"/>';
			break;
			case self::$TYPE_SHX_REMOTE:
				echo '<select name="file_shx_remote" id="file_shx_remote" class="button4"/>';
			break;
			case self::$TYPE_MIF_REMOTE:
				echo '<select name="file_mif_remote" id="file_mif_remote" class="button4"/>';
			break;
			case self::$TYPE_MID_REMOTE:
				echo '<select name="file_mid_remote" id="file_mid_remote" class="button4"/>';
			break;
			case self::$TYPE_E00_REMOTE:
				echo '<select name="file_e00_remote" id="file_e00_remote" class="button4"/>';
			break;
			case self::$TYPE_OSM_REMOTE:
				echo '<select name="file_osm_remote" id="file_osm_remote" class="button4"/>';
			break;
			case self::$TYPE_KML_REMOTE:
				echo '<select name="file_kml_remote" id="file_kml_remote" class="button4"/>';
			break;
			case self::$TYPE_GPX_REMOTE:
				echo '<select name="file_gpx_remote" id="file_gpx_remote" class="button4"/>';
			break;
			default:
				echo '';
			break;
		}
		
		self::listDirectory($type, false, "", 0);
		
		/*if(self::$FoundFileCount == 0){
		 echo "<option value=\"\"></option>\n";
		 }*/
		
		echo "</select>";
	}
	
	public static $FoundFileCount = 0;
	
	/**
	 *
	 * @description : list the source files in directory 'data'
	 * @params : $type
	 */
	public static function listDirectory($type, $godeep = false, $dict = "", $deep = 0){
		global $user;
		$uid = $user['uid'];
		if (!$godeep) {
			$directory = "../files/user/".$uid."/data"; 
		} else {
			$directory = $dict;
		}
		@$mydir = dir($directory);
		if(!$mydir && !$godeep){
			//create user/${uid}/data
			createFile($directory."/", 0777, true);
			echo "<option value=\"\"></option>\n";
			return;
		}
		while ($file = $mydir->read()) {
			if ((is_dir("$directory/$file")) AND ($file != ".") AND ($file != "..")) {
				// echo "<optgroup label=\"$file\">";
				// $directory = $directory."/".$file;
				// listDataInDirectory(-1);
				// echo "</optgroup>";
				$strflag = "";
				for($i=0;$i<$deep;$i++){
					$strflag .= "-" ;
				}
				echo "<option disabled=\"true\" class=\"directory\">".$strflag.$file."</option>";
				$deep++;
				$dict = $directory . "/" . $file;
				self::listDirectory($type, true, $dict, $deep);
			} else {
				if (($file != ".") AND ($file != "..")) {
					$filePostfix = strtoupper(substr($file, strlen($file)-3, strlen($file)));
					switch ($type) {
						case self::$TYPE_SVG_REMOTE:
							if ($filePostfix == "SVG" /*|| $filePostfix == "TXT"*/)
								echo "<option value=\"$directory" . "/" . "$file\">$file</option>\n";
						break;
						case self::$TYPE_CSV_REMOTE:
							if ($filePostfix == "CSV" /*|| $filePostfix == "TXT"*/)
								echo "<option value=\"$directory" . "/" . "$file\">$file</option>\n";
						break;
						case self::$TYPE_SHAPE_REMOTE:
							if ($filePostfix == "SHP")
								echo "<option value=\"$directory" . "/" . "$file\">$file</option>\n";
						break;
						case self::$TYPE_DBF_REMOTE:
							if ($filePostfix == "DBF")
								echo "<option value=\"$directory" . "/" . "$file\">$file</option>\n";
						break;
						case self::$TYPE_SHX_REMOTE:
							if ($filePostfix == "SHX")
								echo "<option value=\"$directory" . "/" . "$file\">$file</option>\n";
						break;
						case self::$TYPE_MIF_REMOTE:
							if ($filePostfix == "MIF")
								echo "<option value=\"$directory" . "/" . "$file\">$file</option>\n";
						break;
						case self::$TYPE_MID_REMOTE:
							if ($filePostfix == "MID")
								echo "<option value=\"$directory" . "/" . "$file\">$file</option>\n";
						break;
						case self::$TYPE_E00_REMOTE:
							if ($filePostfix == "E00")
								echo "<option value=\"$directory" . "/" . "$file\">$file</option>\n";
						break;
						case self::$TYPE_OSM_REMOTE:
							if ($filePostfix == "OSM")
								echo "<option value=\"$directory" . "/" . "$file\">$file</option>\n";
						break;
						case self::$TYPE_KML_REMOTE:
							if ($filePostfix == "KML")
								echo "<option value=\"$directory" . "/" . "$file\">$file</option>\n";
						break;
						case self::$TYPE_GPX_REMOTE:
							if ($filePostfix == "GPX")
								echo "<option value=\"$directory" . "/" . "$file\">$file</option>\n";
						break;
					}
				}
				self::$FoundFileCount++;
			}
		}
		
		$mydir->close();
	}
	//listDataInDirectory(3);
	
	static $ENCODE_LIST = array();
	
	public static function get_encode_list($default = "", $name ="select_encode"){
		$ENCODE_LIST['User Defined'][''] = '';
		
		$ENCODE_LIST['Unicode']['Unicode (UTF-8)'] = 'UTF-8';
		$ENCODE_LIST['Unicode']['Unicode (UTF-16 Little Endian)'] = 'UTF-16LE';
		$ENCODE_LIST['Unicode']['Unicode (UTF-16 Big Endian)'] = 'UTF-16BE';
		$ENCODE_LIST['Unicode']['Unicode (UTF-32 Little Endian)'] = 'UTF-32LE';
		$ENCODE_LIST['Unicode']['Unicode (UTF-32 Big Endian)'] = 'UTF-32BE';
		
		$ENCODE_LIST['West European']['Western (ISO-8859-1)'] = 'ISO-8859-1';
		$ENCODE_LIST['West European']['Western (ISO-8859-15)'] = 'ISO-8859-15';
		$ENCODE_LIST['West European']['Western (IBM-850)'] = 'IBM-850';
		$ENCODE_LIST['West European']['Western (MacRoman)'] = 'MacRoman';
		$ENCODE_LIST['West European']['Western (Windows-1252)'] = 'Windows-1252';
		$ENCODE_LIST['West European']['Celtic (ISO-8859-14)'] = 'ISO-8859-14';
		$ENCODE_LIST['West European']['Greek (ISO-8859-7)'] = 'ISO-8859-7';
		$ENCODE_LIST['West European']['Greek (MacGreek)'] = 'MacGreek';
		$ENCODE_LIST['West European']['Greek (Windows-1253)'] = 'Windows-1253';
		$ENCODE_LIST['West European']['Icelandic (MacIcelandic)'] = 'MacIcelandic';
		$ENCODE_LIST['West European']['Nordic (ISO-8859-10)'] = 'ISO-8859-10';
		$ENCODE_LIST['West European']['South European (ISO-8859-3)'] = 'ISO-8859-3';
		
		$ENCODE_LIST['East European']['Baltic (ISO-8859-4)'] = 'ISO-8859-4';
		$ENCODE_LIST['East European']['Baltic (ISO-8859-13)'] = 'ISO-8859-13';
		$ENCODE_LIST['East European']['Baltic (Windows-1257)'] = 'Windows-1257';
		$ENCODE_LIST['East European']['Central European (IBM-852)'] = 'IBM-852';
		$ENCODE_LIST['East European']['Central European (ISO-8859-2)'] = 'ISO-8859-2';
		$ENCODE_LIST['East European']['Central European (MacCE)'] = 'MacCE';
		$ENCODE_LIST['East European']['Central European (Windows-1250)'] = 'Windows-1250';
		$ENCODE_LIST['East European']['Croatian (MacCroatian)'] = 'MacCroatian';
		$ENCODE_LIST['East European']['Cyrillic (IBM-855)'] = 'IBM-855';
		$ENCODE_LIST['East European']['Cyrillic (ISO-8859-5)'] = 'ISO-8859-5';
		$ENCODE_LIST['East European']['Cyrillic (ISO-IR-111)'] = 'ISO-IR-111';
		$ENCODE_LIST['East European']['Cyrillic (KOI8-R)'] = 'KOI8-R';
		$ENCODE_LIST['East European']['Cyrillic (MacCyrillic)'] = 'MacCyrillic';
		$ENCODE_LIST['East European']['Cyrillic (Windows-1251)'] = 'Windows-1251';
		$ENCODE_LIST['East European']['Cyrillic/Russian (CP-866)'] = 'CP-866';
		$ENCODE_LIST['East European']['Cyrillic/Ukrainian (KOI8-U)'] = 'KOI8-U';
		$ENCODE_LIST['East European']['Cyrillic/Ukrainian (MacUkrainian)'] = 'MacUkrainian';
		$ENCODE_LIST['East European']['Romanian (ISO-8859-16)'] = 'ISO-8859-16';
		$ENCODE_LIST['East European']['Romanian (MacRomanian)'] = 'MacRomanian';
		
		$ENCODE_LIST['East Asian']['Chinese Simplified (GB2312)'] = 'GB2312';
		$ENCODE_LIST['East Asian']['Chinese Simplified (GBK)'] = 'GBK';
		$ENCODE_LIST['East Asian']['Chinese Simplified (GB18030)'] = 'GB18030';
		$ENCODE_LIST['East Asian']['Chinese Simplified (HZ)'] = 'HZ';
		$ENCODE_LIST['East Asian']['Chinese Simplified (ISO-2022-CN)'] = 'ISO-2022-CN';
		$ENCODE_LIST['East Asian']['Chinese Traditional (Big5)'] = 'Big5';
		$ENCODE_LIST['East Asian']['Chinese Traditional (Big5-HKSCS)'] = 'Big5-HKSCS';
		$ENCODE_LIST['East Asian']['Chinese Traditional (EUC-TW)'] = 'EUC-TW';
		$ENCODE_LIST['East Asian']['Japanese (EUC-JP)'] = 'EUC-JP';
		$ENCODE_LIST['East Asian']['Japanese (ISO-2022-JP)'] = 'ISO-2022-JP';
		$ENCODE_LIST['East Asian']['Japanese (Shift_JIS)'] = 'Shift_JIS';
		$ENCODE_LIST['East Asian']['Korean (EUC-KR)'] = 'EUC-KR';
		$ENCODE_LIST['East Asian']['Korean (UHC)'] = 'UHC';
		$ENCODE_LIST['East Asian']['Korean (JOHAB)'] = 'JOHAB';
		$ENCODE_LIST['East Asian']['Korean (ISO-2022-KR)'] = 'ISO-2022-KR';
		
		$ENCODE_LIST['SE & SW Asian']['Armenian (ARMSCII-8)'] = 'ARMSCII-8';
		$ENCODE_LIST['SE & SW Asian']['Georgian (GEOSTD8)'] = 'GEOSTD8';
		$ENCODE_LIST['SE & SW Asian']['Thai (TIS-620)'] = 'TIS-620';
		$ENCODE_LIST['SE & SW Asian']['Thai (ISO-8859-11)'] = 'ISO-8859-11';
		$ENCODE_LIST['SE & SW Asian']['Thai (Windows-874)'] = 'Windows-874';
		$ENCODE_LIST['SE & SW Asian']['Turkish (IBM-857)'] = 'IBM-857';
		$ENCODE_LIST['SE & SW Asian']['Turkish (ISO-8859-9)'] = 'ISO-8859-9';
		$ENCODE_LIST['SE & SW Asian']['Turkish (MacTurkish)'] = 'MacTurkish';
		$ENCODE_LIST['SE & SW Asian']['Turkish (Windows-1254)'] = 'Windows-1254';
		$ENCODE_LIST['SE & SW Asian']['Vietnamese (TCVN)'] = 'TCVN';
		$ENCODE_LIST['SE & SW Asian']['Vietnamese (VISCII)'] = 'VISCII';
		$ENCODE_LIST['SE & SW Asian']['Vietnamese (VPS)'] = 'VPS';
		$ENCODE_LIST['SE & SW Asian']['Vietnamese (Windows-1258)'] = 'Windows-1258';
		$ENCODE_LIST['SE & SW Asian']['Hindi (MacDevanagari)'] = 'MacDevanagari';
		$ENCODE_LIST['SE & SW Asian']['Gujarati (MacGujarati)'] = 'MacGujarati';
		$ENCODE_LIST['SE & SW Asian']['Gurmukhi (MacGurmukhi)'] = 'MacGurmukhi';
		
		$ENCODE_LIST['Middle Eastern']['Arabic (ISO-8859-6)'] = 'ISO-8859-6';
		$ENCODE_LIST['Middle Eastern']['Arabic (Windows-1256)'] = 'Windows-1256';
		$ENCODE_LIST['Middle Eastern']['Arabic (IBM-864)'] = 'IBM-864';
		$ENCODE_LIST['Middle Eastern']['Arabic (MacArabic)'] = 'MacArabic';
		$ENCODE_LIST['Middle Eastern']['Farsi (MacFarsi)'] = 'MacFarsi';
		$ENCODE_LIST['Middle Eastern']['Hebrew (ISO-8859-8-I)'] = 'ISO-8859-8-I';
		$ENCODE_LIST['Middle Eastern']['Hebrew (Windows-1255)'] = 'Windows-1255';
		$ENCODE_LIST['Middle Eastern']['Hebrew Visual(ISO-8859-8)'] = 'ISO-8859-8';
		$ENCODE_LIST['Middle Eastern']['Hebrew (IBM-862)'] = 'IBM-862';
		$ENCODE_LIST['Middle Eastern']['Hebrew (MacHebrew)'] = 'MacHebrew';
		
		
		$last .= '<input name="' . $name . '" id="' . $name . '" type="text" size="15" value=""  class="smallInput"/>';
		
		$last .= '<select NAME="'.$name.'TEMP" ID="'.$name.'TEMP" onchange="document.getElementById(\'' . $name . '\').value = this.options[this.selectedIndex].value" class="button4"/>';		
		
		foreach($ENCODE_LIST as $key => $val){
			$last .= '<optgroup label="'.$key.'" title="'.$key.'">';
			
			foreach($val as $k => $v){
				if($v == $default){
					$last .= '<option value="'.$v.'" title="'.$v.'" selected>'.$k.'</option>';
				}else{
					$last .= '<option value="'.$v.'" title="'.$v.'">'.$k.'</option>';
				}
			}
			
			$last .= '</optgroup>';
		}
		$last .= '</select>';
		echo  $last;
	}
	
	
	
} 



?>
