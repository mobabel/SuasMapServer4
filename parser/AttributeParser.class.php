<?php
/**
 * Class to write or read attribute xml
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
 * @copyright (C) 2006-2009  LI Hui
 * @Description : This show the copyright .
 * @version $3.0$ 2006
 * @Author LI Hui
 * @version $4.0$ 2009.04
 * @Author LI Hui
 */
class AttributeParser {
	function writeTextAngleAttribute($text, $angle)
		{
		$attributes = "
			<attributes>
			<attribute name=\"suas_TextContent\" type=\"C\" length=\"".strlen($text)."\" dec=\"0\">$text</attribute>
			<attribute name=\"suas_TextAngle\" type=\"N\" length=\"".strlen($angle)."\" dec=\"1\">$angle</attribute>
			</attributes>";
		if($angle == 0)	 {
			$attributes = "
				<attributes>
				<attribute name=\"suas_TextContent\" type=\"C\" length=\"".strlen($text)."\" dec=\"0\">$text</attribute>
				<attribute name=\"suas_TextAngle\" type=\"N\" length=\"0\" dec=\"1\">0</attribute>
				</attributes>";
		}
		return $attributes;
		}
	
	function addTextAngleAttribute($attributes, $angle)
		{
		$angletext = "<attribute name=\"suas_TextAngle\" type=\"N\" length=\"".strlen($angle)."\" dec=\"1\">$angle</attribute>";
		
		$attributes = str_replace('<attribute name="suas_TextAngle" type="N" length="0" dec="1">0</attribute>', $angletext, $attributes);
		return $attributes;
		}
	
	function getTextAngel($attributes)
		{
		try{
			if(!@$xml = simplexml_load_string($attributes)){
				$txt[0] = "error in getTextAngel";
				$txt[1] = "0";
				return $txt;
			}	
			foreach ($xml->attribute as $attribute) {
				if($attribute['name']=="suas_TextContent") $txt[0] = $attribute;
				if($attribute['name']=="suas_TextAngle") $txt[1] = $attribute;
			}
			
			return $txt;
		}catch(Exception $e){
			$txt[0] = "error in getTextAngel";
			$txt[1] = "0";
			return $txt;
		}
		}
	
	/*
	 $attri = "<attributes><attribute name=\"Labeltext\" type=\"C\" length=\"64\" dec=\"0\">test</attribute>
	 <attribute name=\"angle\" type=\"N\" length=\"10\" dec=\"1\">0</attribute></attributes>";
	 getTextAngel($attri);
	 */
	
	function getAttributeFromKeyValueArray($keyarray, $valuearray){
		$size =  count($keyarray);
		$attributes = "<attributes>";
		for($i=0;$i<$size;$i++){
			if(!empty($valuearray) || !is_array($valuearray))
				$attributes .= "<attribute name=\"".$keyarray[$i]."\">".$valuearray[$i]."</attribute>";
		}
		$attributes .= "</attributes>";
		return $attributes;
	}
	
	/**
	 * array to <attributes>......</attributes>
	 */
	public function getAttributeStringFromArray($array){
		$attributes = "<attributes>";
		foreach($array as $key => $value){
			$attributes .= "<attribute name=\"".$key."\">".$value."</attribute>";
		}
		$attributes .= "</attributes>";
		return $attributes;
	}
	
	/**
	 * no a standard SUAS xml
	 * <attributes><id>xx</id>..........</attributes>
	 */
	function getArrayFromXml($xmlstring){
		$array = array();
		try{
			if(@!$xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?>'.$xmlstring)){
				return $array;
			}	
			//var_dump($xml);
			foreach ($xml as $key => $val) {
				$array["$key"] = $val."";//otherwise val will be the xml node
			}
			return $array;
		}catch(Exception $e){
			return $array;
		}
	}
	
	/**
	 * used for store configuration array for atlas
	 */
	public function getAttributeFromArray($array){
		return /*self::mb_*/serialize($array);
	}
	
	/**
	 * get configuration array from database
	 * 
	 */
	public function extractAttribute($attributes){
		$array = array();
		if ($attributes != "" AND strstr($attributes, '<attributes>')) {
			try{
				if(!@$xml = simplexml_load_string($attributes)){
					return $array;
				}	
				foreach ($xml->attribute as $attribute) {
					$key = $attribute['name'];
					$array["$key"] = $attribute[0];
				}
				//print_r($array);
				return $array;
			}catch(Exception $e){
				return $array;
			}
		}		
		try{
			$array = self::mb_unserialize($attributes);
		}catch(Exception $e){
			return $array;
		}
		return $array;
	}
	
	/**
	 * Serializes object with/without unicode fields
	 * Before serializing encodes unicode values
	 *
	 * http://de.php.net/serialize
	 * @return string
	 */
	public function mb_serialize($serial_str){
		$serialized = array();
		foreach(array_keys($serial_str) as $key){
			if(is_array($this->$key) ){
				eval('$serialized["'.$key.'"] = serialize($this->'.$key.');');
			}else{
				eval('$serialized["'.$key.'"] = utf8_encode($this->'.$key.');');
			}
		}
		$str = serialize($serialized);
		return $str;
	}
	/**
	 * Unserializes object with/without unicode fields
	 *
	 * @param string Serialized data
	 */
/*	public function unserialize($serialized){
		$data = unserialize($serialized);
		foreach($data as $prop => $val){
			if(is_array($this->$prop) ){
				$this->$prop = unserialize($val);
			}else{
				$this->$prop = utf8_decode($val);
			}
		}
	}*/
	
	/**
	 * Works great for serialized UTF-8 content that doesn't like to be unserialized
	 */
	function mb_unserialize($serial_str) {
		$out = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str );
		return unserialize($out);   
	}
}
?>