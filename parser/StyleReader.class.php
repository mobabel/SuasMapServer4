<?php
class StyleParser {
	public $xmlUserLayerName;
	public $xmlUserLayerTitle;
	public $xmlMinScaleDenominator;
	public $xmlMaxScaleDenominator;
	// For point,linestring,polygon,,text
	public $xmlSize;
	// For point,polygon,text
	public $xmlFillColor;
	// For linestring,polygon
	public $xmlStrokeColor ;
	// For text
	public $xmlFont;
	// For point
	public $xmlWellKnownName;
	// For linestring, point
	public $xmlStrokeOpacity;
	// For polygon, point, image
	public $xmlFillOpacity;
	// For text
	public $xmlFontStyle;
	public $xmlFontWeight;
	// For linestring
	public $xmlLineJoin;
	public $xmlLineCap;
	
	public $prefix;
	private $aid, $database;
	
	public function StyleParser($aid, $database)
		{
		$this->aid = $aid;
		$this->prefix = $aid;
		$this->database = $database;
		$this->xmlUserLayerName = "Default";
		$this->xmlUserLayerTitle = "Default";
		$this->xmlMinScaleDenominator = "";
		$this->xmlMaxScaleDenominator = "";
		$this->xmlSize = "1";
		$this->xmlFillColor = "-1";
		$this->xmlStrokeColor = "#000000";
		$this->xmlFont = "Arial";
		$this->xmlWellKnownName = "square";
		$this->xmlStrokeOpacity = "100";
		$this->xmlFillOpacity = "100";
		$this->xmlFontStyle = "normal";
		$this->xmlFontWeight = "normal";
		$this->xmlLineJoin = "round";
		$this->xmlLineCap = "butt";
		}
	
	/**
	 *
	 * @function createStyleNode4layer
	 * @description :read the style xml document and create style node for each layer
	 * @return one array which key is layername and value is style node in xml document
	 */
	public function createStyleNode4layer($isDefault = true, $stid = 0)
		{
		$doc = new DOMDocument('1.0', 'utf-8');
		
		/*
		 if (file_exists("../sld/styles/" . $this->prefix . "WmsStyles.xml")) {
		 @$doc->load("../sld/styles/" . $this->prefix . "WmsStyles.xml");
		 } else {
		 $error = 'The ' . $this->prefix . 'WmsStyles.xml does not exist!';
		 //exit($error);
		  //create a new empty one if not exist!
		   $stylefilename = tempnam("../sld/styles", "FOO");
		   copy($stylefilename,"../sld/styles/" . $this->prefix . "WmsStyles.xml");
		   @$doc->load("../sld/styles/" . $this->prefix . "WmsStyles.xml");
		   }
		   */
		if($isDefault){
			$style = $this->database->db_get_default_style($this->aid);
		}
		else{
			$style = $this->database->db_get_single_style($this->aid, $stid);
		}
		$doc->loadXML($style['style']);
		$aryXmlUserLayerNode['stylename'] = $style['stylename'];

		$xmlNameLayerNode = $doc->getElementsByTagName("NamedLayer");

		foreach ($xmlNameLayerNode as $xmlNameLayerNodes) {
			//print $xmlNameLayerNodes -> textContent."<br>";
			$xmlNameNode = $xmlNameLayerNodes->getElementsByTagName("Name");
			
			$sLayerName = $xmlNameNode->item(0)->textContent;
			
			$xmlUserLayerNode = $xmlNameLayerNodes->getElementsByTagName("UserStyle");
			// foreach ($xmlNameLayerNode as $xmlNameLayerNodes){
			// }
			$xmlUserLayerNode->item(0)->removeAttribute("xmlns");
			$aryXmlUserLayerNode[$sLayerName] = $xmlUserLayerNode->item(0);
		}
		//print_r($aryXmlUserLayerNode);
		return $aryXmlUserLayerNode;
		}
	
	/**
	 *
	 * @function getLayerStyleFromStyleNode
	 * @description :read the style node of one layer and get the style
	 * @return one array which key is layername and value is style node in xml document
	 */
	public function getLayerStyleFromStyleNode($layername, $layertype, $aryXmlUserLayerNode)
		{
		$doc = new DOMDocument('1.0', 'utf-8');
		$xpath = new domxpath($doc);
		
		//print out the error message, dont add @ here
		if(!empty($aryXmlUserLayerNode[$layername]))
			$fragment = $blnfragment = $doc->importNode($aryXmlUserLayerNode[$layername], true);
		
		if (!empty($blnfragment)) {
			$doc->appendChild($fragment);
			// delete the attribute xmlns="http://www.opengis.net/sld" in UserStyle in $aryXmlUserLayerNode
			// $UserStyle = $doc->getElementsByTagName("UserStyle")-> item(0);
			// $xmlns = $UserStyle->getAttributeNode("xmlns") ;
			// $doc->removeAttribute("xmlns");
			// $doc->saveXML();
			$xmlUserLayerNameNode = $xpath->query("/UserStyle/Name");
			$this->xmlUserLayerName = $xmlUserLayerNameNode->item(0)->textContent;
			
			$xmlUserLayerTitleNode = $xpath->query("/UserStyle/Title");
			$this->xmlUserLayerTitle = $xmlUserLayerTitleNode->item(0)->textContent;
			
			$xmlMinScaleDenominatorNode = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/MinScaleDenominator");
			$this->xmlMinScaleDenominator = $xmlMinScaleDenominatorNode->item(0)->textContent;
			
			$xmlMaxScaleDenominatorNode = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/MaxScaleDenominator");
			$this->xmlMaxScaleDenominator = $xmlMaxScaleDenominatorNode->item(0)->textContent;
			
			switch (strtoupper($layertype)) {
				case 'POINT': {
					$xmlSymbolizerNode = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/PointSymbolizer/Graphic/*");
					foreach ($xmlSymbolizerNode as $nodes) {
						if ($nodes->nodeName == 'Size') {
							$this->xmlSize = $nodes->textContent;
							// echo $xmlSize;
						}
					}
					$this->xmlWellKnownName = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/PointSymbolizer/Graphic/Mark/WellKnownName")->item(0)->textContent;
					// echo $this->xmlWellKnownName;
					$xmlStrokeNode = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/PointSymbolizer/Graphic/Mark/Stroke/*");
					foreach ($xmlStrokeNode as $nodes) {
						if ($nodes->nodeName == 'CssParameter') {
							foreach ($nodes->attributes as $attribute) {
								if ($attribute->name == 'name' && $attribute->value == 'stroke-opacity') {
									$this->xmlStrokeOpacity = $nodes->textContent;
									// echo $xmlFillColor;
								}
							}
						}
					}
					
					$xmlFillNode = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/PointSymbolizer/Graphic/Mark/Fill/*");
					foreach ($xmlFillNode as $nodes) {
						if ($nodes->nodeName == 'CssParameter') {
							foreach ($nodes->attributes as $attribute) {
								if ($attribute->name == 'name' && $attribute->value == 'fill') {
									$this->xmlFillColor = $nodes->textContent;
									//echo $xmlFillColor;
								}
								if ($attribute->name == 'name' && $attribute->value == 'fill-opacity') {
									$this->xmlFillOpacity = $nodes->textContent;
									// echo $xmlFillColor;
								}
							}
						}
					}
				}
				break;
				case 'LINESTRING': {
					$xmlSymbolizerNodeFill = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/LineSymbolizer/Fill/*");
					foreach ($xmlSymbolizerNodeFill as $nodes) {
						if ($nodes->nodeName == 'CssParameter') {
							foreach ($nodes->attributes as $attribute) {
								if ($attribute->name == 'name' && $attribute->value == 'fill') {
									$this->xmlFillColor = $nodes->textContent;
									// echo $this->xmlFillColor;
								}
								if ($attribute->name == 'name' && $attribute->value == 'fill-opacity') {
									$this->xmlFillOpacity = $nodes->textContent;
									// echo $this->xmlFillOpacity;
								}
							}
						}
					}
					
					$xmlSymbolizerNode = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/LineSymbolizer/Stroke/*"); // /CssParameter[.name='stroke']
					foreach ($xmlSymbolizerNode as $nodes) {
						if ($nodes->nodeName == 'CssParameter') {
							foreach ($nodes->attributes as $attribute) {
								if ($attribute->name == 'name' && $attribute->value == 'stroke') {
									$this->xmlStrokeColor = $nodes->textContent;
									//$xmlStrokeColor;
								}
								if ($attribute->name == 'name' && $attribute->value == 'stroke-width') {
									$this->xmlSize = $nodes->textContent;
									// echo $xmlSize;
								}
								if ($attribute->name == 'name' && $attribute->value == 'stroke-opacity') {
									$this->xmlStrokeOpacity = $nodes->textContent;
									//echo $this->xmlStrokeOpacity;
								}
								if ($attribute->name == 'name' && $attribute->value == 'stroke-linejoin') {
									$this->xmlLineJoin = $nodes->textContent;
									// echo $this->xmlLineJoin;
								}
								if ($attribute->name == 'name' && $attribute->value == 'stroke-linecap') {
									$this->xmlLineCap = $nodes->textContent;
									// echo $this->xmlLineCap;
								}
							}
						}
					}
				}
				break;
				case 'POLYGON': {
					$xmlSymbolizerNodeFill = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/PolygonSymbolizer/Fill/*");
					foreach ($xmlSymbolizerNodeFill as $nodes) {
						if ($nodes->nodeName == 'CssParameter') {
							foreach ($nodes->attributes as $attribute) {
								if ($attribute->name == 'name' && $attribute->value == 'fill') {
									$this->xmlFillColor = $nodes->textContent;
									// echo $xmlFillColor;
								}
								if ($attribute->name == 'name' && $attribute->value == 'fill-opacity') {
									$this->xmlFillOpacity = $nodes->textContent;
									// echo xmlFillOpacity;
								}
							}
						}
					}
					
					$xmlSymbolizerNodeStroke = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/PolygonSymbolizer/Stroke/*");
					foreach ($xmlSymbolizerNodeStroke as $nodes) {
						if ($nodes->nodeName == 'CssParameter') {
							foreach ($nodes->attributes as $attribute) {
								if ($attribute->name == 'name' && $attribute->value == 'stroke') {
									$this->xmlStrokeColor = $nodes->textContent;
									// echo $xmlStrokeColor;
								}
								if ($attribute->name == 'name' && $attribute->value == 'stroke-width') {
									$this->xmlSize = $nodes->textContent;
									// echo $xmlSize;
								}
							}
						}
					}
				}
				break;
				case 'TEXT': {
					$xmlSymbolizerNodeFill = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/TextSymbolizer/Fill/*");
					foreach ($xmlSymbolizerNodeFill as $nodes) {
						if ($nodes->nodeName == 'CssParameter') {
							foreach ($nodes->attributes as $attribute) {
								if ($attribute->name == 'name' && $attribute->value == 'fill') {
									$this->xmlFillColor = $nodes->textContent;
									// echo $xmlFillColor;
								}
							}
						}
					}
					
					$xmlSymbolizerNodeStroke = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/TextSymbolizer/Font/*");
					foreach ($xmlSymbolizerNodeStroke as $nodes) {
						if ($nodes->nodeName == 'CssParameter') {
							foreach ($nodes->attributes as $attribute) {
								if ($attribute->name == 'name' && $attribute->value == 'font-family') {
									$this->xmlFont = $nodes->textContent;
									// echo $xmlFont;
								}
								if ($attribute->name == 'name' && $attribute->value == 'font-size') {
									$this->xmlSize = $nodes->textContent;
									// echo $xmlSize;
								}
								if ($attribute->name == 'name' && $attribute->value == 'font-style') {
									$this->xmlFontStyle = $nodes->textContent;
									// echo $xmlSize;
								}
								if ($attribute->name == 'name' && $attribute->value == 'font-weight') {
									$this->xmlFontWeight = $nodes->textContent;
									// echo $xmlSize;
								}
							}
						}
					}
				}
				break;
				case 'IMAGE': {
					$xmlSymbolizerNodeOpacity = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/RasterSymbolizer/*");
					foreach ($xmlSymbolizerNodeOpacity as $nodes) {
						if ($nodes->nodeName == 'Opacity') {
							$this->xmlFillOpacity = $nodes->textContent;
							// echo $this->xmlFillOpacity;
						}
					}
				}
				break;
				case 'COMPOND': {
					$xmlSymbolizerNodeFill = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/LineSymbolizer/Fill/*");
					foreach ($xmlSymbolizerNodeFill as $nodes) {
						if ($nodes->nodeName == 'CssParameter') {
							foreach ($nodes->attributes as $attribute) {
								if ($attribute->name == 'name' && $attribute->value == 'fill') {
									$this->xmlFillColor = $nodes->textContent;
									// echo $this->xmlFillColor;
								}
								if ($attribute->name == 'name' && $attribute->value == 'fill-opacity') {
									$this->xmlFillOpacity = $nodes->textContent;
									// echo $this->xmlFillOpacity;
								}
							}
						}
					}
					
					$xmlSymbolizerNode = $xpath->query("/UserStyle/FeatureTypeStyle/Rule/LineSymbolizer/Stroke/*"); // /CssParameter[.name='stroke']
					foreach ($xmlSymbolizerNode as $nodes) {
						if ($nodes->nodeName == 'CssParameter') {
							foreach ($nodes->attributes as $attribute) {
								if ($attribute->name == 'name' && $attribute->value == 'stroke') {
									$this->xmlStrokeColor = $nodes->textContent;
									//$xmlStrokeColor;
								}
								if ($attribute->name == 'name' && $attribute->value == 'stroke-width') {
									$this->xmlSize = $nodes->textContent;
									// echo $xmlSize;
								}
								if ($attribute->name == 'name' && $attribute->value == 'stroke-opacity') {
									$this->xmlStrokeOpacity = $nodes->textContent;
									//echo $this->xmlStrokeOpacity;
								}
								if ($attribute->name == 'name' && $attribute->value == 'stroke-linejoin') {
									$this->xmlLineJoin = $nodes->textContent;
									// echo $this->xmlLineJoin;
								}
								if ($attribute->name == 'name' && $attribute->value == 'stroke-linecap') {
									$this->xmlLineCap = $nodes->textContent;
									// echo $this->xmlLineCap;
								}
							}
						}
					}
				}
				break;
				case 'UNKNOWN': {
				}
				break;
				default: {
				}
			}
		}

		}
	
	
	/**
	 *
	 * @function : getStyleInfoContainer
	 * @description : get the style information
	 * @param  $ :$strLayerName: layer name
	 * @param  $ :$strLayerType: layer type
	 * @param  $ :$strLayerLabel: text layer label
	 */
	public static function getStyleInfoContainer($strLayerName, $strLayerType, $strLayerLabel)
		{
		// call function getLayerType to get Layer's Type
		$strStyleInfoContainer = "";
		switch (strtoupper($strLayerType)) {
			case "POLYGON": {
				$strStyleInfoContainer = " <input type=hidden name=\"layerName\" value=\"$strLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"Default\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"Default\">\n"
					. "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"Polygon\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFill\" value=\"-1\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFillOpacity\" value=\"100\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStroke\" value=\"#000000\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeWidth\" value=\"1\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"\">\n" ;
				return $strStyleInfoContainer;
			}
			break;
			case "LINESTRING": {
				$strStyleInfoContainer = "<input type=hidden name=\"layerName\" value=\"$strLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"Default\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"Default\">\n"
					. "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"LineString\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFill\" value=\"-1\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFillOpacity\" value=\"100\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStroke\" value=\"#000000\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeWidth\" value=\"1\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeOpacity\" value=\"100\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeLinejoin\" value=\"round\">\n" // miter, round, bevel
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeLinecap\" value=\"butt\">\n" // butt, round, square
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeDasharray\" value=\"\">\n" // use later
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeDashoffset\" value=\"\">\n" // use later
					. "<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"\">\n" ;
				return $strStyleInfoContainer;
			}
			break;
			case "POINT": {
				$strStyleInfoContainer = "<input type=hidden name=\"layerName\" value=\"$strLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"Default\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"Default\">\n"
					. "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"Point\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFill\" value=\"#808080\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFillOpacity\" value=\"100\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStroke\" value=\"#808080\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeOpacity\" value=\"100\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtWellknownName\" value=\"square\">\n" // square, circle, triangle, star, cross , x
					. "<input type=hidden name=\"$strLayerName" . "txtSize\" value=\"6\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"\">\n" ;
				return $strStyleInfoContainer;
			}
			break;
			case "TEXT": {
				$strStyleInfoContainer = "<input type=hidden name=\"layerName\" value=\"$strLayerName\">\n" . "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"Default\">\n" . "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"Default\">\n" . "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"Text\">\n" . "<input type=hidden name=\"$strLayerName" . "txtLabel\" value=\"$strLayerLabel\">\n" . "<input type=hidden name=\"$strLayerName" . "sltFontFamily\" value=\"Arial\">\n" . "<input type=hidden name=\"$strLayerName" . "txtFontSize\" value=\"10\">\n" . "<input type=hidden name=\"$strLayerName" . "txtFontStyle\" value=\"normal\">\n" . // normal, italic, oblique
					"<input type=hidden name=\"$strLayerName" . "txtFontWeight\" value=\"normal\">\n" . // normal, bold
					"<input type=hidden name=\"$strLayerName" . "txtFontColor\" value=\"#000000\">\n" . "<input type=hidden name=\"$strLayerName" . "txtFontHalo\" value=\"-1\">\n" . // #ffffff use later, in Fill
					"<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"\">\n" . "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"\">\n" ;
				return $strStyleInfoContainer;
			}
			break;
			case "IMAGE": {
				$strStyleInfoContainer = "<input type=hidden name=\"layerName\" value=\"$strLayerName\">\n" . "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"Default\">\n" . "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"Default\">\n" . "<input type=hidden name=\"$strLayerName" . "Title\" value=\"$strLayerName\">\n" . "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"Image\">\n" . "<input type=hidden name=\"$strLayerName" . "txtOpacity\" value=\"100\">\n" . "<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"\">\n" . "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"\">\n" ;
				return $strStyleInfoContainer;
			}
			break;
			case "COMPOND": {
				$strStyleInfoContainer = "<input type=hidden name=\"layerName\" value=\"$strLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"Default\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"Default\">\n"
					. "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"LineString\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFill\" value=\"-1\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFillOpacity\" value=\"100\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStroke\" value=\"#000000\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeWidth\" value=\"1\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeOpacity\" value=\"100\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeLinejoin\" value=\"round\">\n" // miter, round, bevel
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeLinecap\" value=\"butt\">\n" // butt, round, square
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeDasharray\" value=\"\">\n" // use later
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeDashoffset\" value=\"\">\n" // use later
					. "<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"\">\n" ;
				return $strStyleInfoContainer;
			}
			break;
			case "UNKNOWN": {
				$strStyleInfoContainer = "<input type=hidden name=\"layerName\" value=\"$strLayerName\">\n" . "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"Default\">\n" . "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"Default\">\n" . "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"Unknown\">\n" . "<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"\">\n" . "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"\">\n" ;
				return $strStyleInfoContainer;
			}
			break;
			
			default: {
				$strStyleInfoContainer = "<input type=hidden name=\"layerName\" value=\"$strLayerName\">\n" . "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"Default\">\n" . "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"Default\">\n" . "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"Unknown\">\n" . "<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"\">\n" . "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"\">\n" ;
				return $strStyleInfoContainer;
			}
		}
		}
	
	/**
	 *
	 * @function : getStyleInfoContainerFromSLD
	 * @description : get the style information
	 * @param  $ :$strLayerName: layer name
	 * @param  $ :$strLayerType: layer type
	 * @params :$xmlUserLayerName,$xmlUserLayerTitle,$xmlMinScaleDenominator, $xmlMaxScaleDenominator, $xmlSize, $xmlFillColor, $xmlStrokeColor,
	 *           $xmlWellKnownName, $xmlStrokeOpacity,
	 *           $xmlFillOpacity, $xmlFont, $xmlFontStyle, $xmlFontWeight, $xmlLineJoin, $xmlLineCap
	 */
	public static function getStyleInfoContainerFromSLD($strLayerName, $strLayerType, $xmlUserLayerName, $xmlUserLayerTitle, $xmlMinScaleDenominator, $xmlMaxScaleDenominator,
														$xmlSize, $xmlFillColor, $xmlStrokeColor, $xmlFont, $xmlWellKnownName, $xmlStrokeOpacity,
														$xmlFillOpacity, $xmlFont, $xmlFontStyle, $xmlFontWeight, $xmlLineJoin, $xmlLineCap)
		{
		// call function getLayerType to get Layer's Type
		$strStyleInfoContainer = "";
		
		if ($xmlUserLayerName == "")$xmlUserLayerName = "Default";
		if ($xmlUserLayerTitle == "")$xmlUserLayerTitle = "Default";
		if ($xmlMinScaleDenominator == "")$xmlMinScaleDenominator = "";
		if ($xmlMaxScaleDenominator == "")$xmlMaxScaleDenominator = "";
		if ($xmlSize == "")$xmlSize = "1";
		if ($xmlFillColor == "")$xmlFillColor = "-1";
		if ($xmlStrokeColor == "")$xmlStrokeColor = "#000000";
		if ($xmlFont == "")$xmlFont = "Arial";
		if ($xmlWellKnownName == "")$xmlWellKnownName = "square";
		if ($xmlStrokeOpacity == "")$xmlStrokeOpacity = "100";
		if ($xmlFillOpacity == "")$xmlFillOpacity = "100";
		if ($xmlFontStyle == "")$xmlFontStyle = "normal";
		if ($xmlFontWeight == "")$xmlFontWeight = "normal";
		if ($xmlLineJoin == "")$xmlLineJoin = "round";
		if ($xmlLineCap == "")$xmlLineCap = "butt";
		
		switch (strtoupper($strLayerType)) {
			case "POLYGON": {
				$strStyleInfoContainer = " <input type=hidden name=\"layerName\" value=\"$strLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"$xmlUserLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"$xmlUserLayerTitle\">\n"
					. "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"Polygon\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFill\" value=\"$xmlFillColor\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFillOpacity\" value=\"$xmlFillOpacity\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStroke\" value=\"$xmlStrokeColor\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeWidth\" value=\"$xmlSize\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"$xmlMinScaleDenominator\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"$xmlMaxScaleDenominator\">\n" ;
				return $strStyleInfoContainer;
			}
			break;
			case "LINESTRING": {
				$strStyleInfoContainer = "<input type=hidden name=\"layerName\" value=\"$strLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"$xmlUserLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"$xmlUserLayerTitle\">\n"
					. "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"LineString\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFill\" value=\"$xmlFillColor\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFillOpacity\" value=\"$xmlFillOpacity\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStroke\" value=\"$xmlStrokeColor\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeWidth\" value=\"$xmlSize\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeOpacity\" value=\"$xmlStrokeOpacity\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeLinejoin\" value=\"$xmlLineJoin\">\n" . // miter, round, bevel
					"<input type=hidden name=\"$strLayerName" . "txtStrokeLinecap\" value=\"$xmlLineCap\">\n" . // butt, round, square
					"<input type=hidden name=\"$strLayerName" . "txtStrokeDasharray\" value=\"\">\n" . // use later
					"<input type=hidden name=\"$strLayerName" . "txtStrokeDashoffset\" value=\"\">\n" . // use later
					"<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"$xmlMinScaleDenominator\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"$xmlMaxScaleDenominator\">\n" ;
				return $strStyleInfoContainer;
			}
			break;
			case "POINT": {
				$strStyleInfoContainer = "<input type=hidden name=\"layerName\" value=\"$strLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"$xmlUserLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"$xmlUserLayerTitle\">\n"
					. "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"Point\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFill\" value=\"$xmlFillColor\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFillOpacity\" value=\"$xmlFillOpacity\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStroke\" value=\"$xmlStrokeColor\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeOpacity\" value=\"$xmlStrokeOpacity\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtWellknownName\" value=\"$xmlWellKnownName\">\n" // square, circle, triangle, star, cross , x
					. "<input type=hidden name=\"$strLayerName" . "txtSize\" value=\"$xmlSize\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"$xmlMinScaleDenominator\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"$xmlMaxScaleDenominator\">\n" ;
				return $strStyleInfoContainer;
			}
			break;
			case "TEXT": {
				$strStyleInfoContainer = "<input type=hidden name=\"layerName\" value=\"$strLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"$xmlUserLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"$xmlUserLayerTitle\">\n"
					. "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"Text\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtLabel\" value=\"$strLayerLabel\">\n"
					. "<input type=hidden name=\"$strLayerName" . "sltFontFamily\" value=\"$xmlFont\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFontSize\" value=\"$xmlSize\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFontStyle\" value=\"$xmlFontStyle\">\n" // normal, italic, oblique
					. "<input type=hidden name=\"$strLayerName" . "txtFontWeight\" value=\"$xmlFontWeight\">\n" // normal, bold
					. "<input type=hidden name=\"$strLayerName" . "txtFontColor\" value=\"$xmlFillColor\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFontHalo\" value=\"-1\">\n" // #ffffff use later, in Fill
					. "<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"$xmlMinScaleDenominator\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"$xmlMaxScaleDenominator\">\n" ;
				return $strStyleInfoContainer;
			}
			break;
			case "IMAGE": {
				$strStyleInfoContainer = "<input type=hidden name=\"layerName\" value=\"$strLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"$xmlUserLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"$xmlUserLayerTitle\">\n"
					. "<input type=hidden name=\"$strLayerName" . "Title\" value=\"$strLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"Image\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtOpacity\" value=\"$xmlFillOpacity\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"$xmlMinScaleDenominator\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"$xmlMaxScaleDenominator\">\n" ;
				return $strStyleInfoContainer;
			}
			break;
			case "COMPOND": {
				$strStyleInfoContainer = "<input type=hidden name=\"layerName\" value=\"$strLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"$xmlUserLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"$xmlUserLayerTitle\">\n"
					. "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"Compond\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFill\" value=\"$xmlFillColor\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtFillOpacity\" value=\"$xmlFillOpacity\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStroke\" value=\"$xmlStrokeColor\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeWidth\" value=\"$xmlSize\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeOpacity\" value=\"$xmlStrokeOpacity\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStrokeLinejoin\" value=\"$xmlLineJoin\">\n" . // miter, round, bevel
					"<input type=hidden name=\"$strLayerName" . "txtStrokeLinecap\" value=\"$xmlLineCap\">\n" . // butt, round, square
					"<input type=hidden name=\"$strLayerName" . "txtStrokeDasharray\" value=\"\">\n" . // use later
					"<input type=hidden name=\"$strLayerName" . "txtStrokeDashoffset\" value=\"\">\n" . // use later
					"<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"$xmlMinScaleDenominator\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"$xmlMaxScaleDenominator\">\n" ;
				return $strStyleInfoContainer;
			}
			break;
			case "UNKNOWN": {
				$strStyleInfoContainer = "<input type=hidden name=\"layerName\" value=\"$strLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"$xmlUserLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"$xmlUserLayerTitle\">\n"
					. "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"Unknown\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"$xmlMinScaleDenominator\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"$xmlMaxScaleDenominator\">\n" ;
				return $strStyleInfoContainer;
			}
			break;
			
			default: {
				$strStyleInfoContainer = "<input type=hidden name=\"layerName\" value=\"$strLayerName\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleName\" value=\"Default\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtStyleTitle\" value=\"Default\">\n"
					. "<input type=hidden name=\"$strLayerName" . "layerType\" value=\"Unknown\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMinRange\" value=\"$xmlMinScaleDenominator\">\n"
					. "<input type=hidden name=\"$strLayerName" . "txtMaxRange\" value=\"$xmlMaxScaleDenominator\">\n" ;
				return $strStyleInfoContainer;
			}
		}
		// initialize the values
		$xmlUserLayerName = "";
		$xmlUserLayerTitle = "";
		$xmlMinScaleDenominator = "";
		$xmlMaxScaleDenominator = "";
		$xmlSize = "";
		$xmlFillColor = "";
		$xmlStrokeColor = "";
		$xmlFont = "";
		$xmlWellKnownName = "";
		$xmlStrokeOpacity = "";
		$xmlFillOpacity = "";
		$xmlFontStyle = "";
		$xmlFontWeight = "";
		$xmlLineJoin = "";
		$xmlLineCap = "";
		}
	
	/**
	 *
	 * @function : createWmsStyles
	 * @description : wirte the style information into the hidden input field for each layer
	 * @param  $ :$arrLayerName: layer name array
	 * @param  $ :$arraySrsNamePre: srsname + "_"
	 * @param  $ :$arrLayerType: layer type array
	 * @param  $ :$prefix: $dbname + $tableprefix
	 */
	public static function createWmsStyles($arrLayerName, $arraySrsNamePre, $arrLayerType, $aid, $database, $sld_style_name, $stid = 0, $saveas = false)
		{
		$doc = new DOMDocument('1.0', 'utf-8');
		//print_r($_POST);
		
		/*
		 $doc->loadXML('<StyledLayerDescriptor version="1.0.0"
		 xmlns="http://www.opengis.net/sld"
		 xmlns:ogc="http://www.opengis.net/ogc"
		 xmlns:xlink="http://www.w3.org/1999/xlink"
		 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
		 </StyledLayerDescriptor>');
		 */
		// we want a nice output
		$doc->formatOutput = true;
		// create a root node and set an attribute
		$root = $doc->createElement("StyledLayerDescriptor");
		$doc->appendChild($root);
		$root->setAttribute("version", "1.0.0");
		// If this attribute is added, in StyleReader will have problem to parse the xml fragment!
		// $root->setAttribute("xmlns", "http://www.opengis.net/sld");
		$root->setAttribute("xmlns:ogc", "http://www.opengis.net/ogc");
		$root->setAttribute("xmlns:xlink", "http://www.w3.org/1999/xlink");
		$root->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
		// create style for each layer
		for($i = 0;$i < count($arrLayerName);$i++) {
			// create element NamedLayer for each layer
			$objNamedLayer = $doc->createElement("NamedLayer");
			$root->appendChild($objNamedLayer);
			// create element Name for each NamedLayer
			$objName = $doc->createElement("Name", $arrLayerName[$i]);
			$objNamedLayer->appendChild($objName);
			// create element UserStyle for each NamedLayer element
			$objUserStyle = $doc->createElement("UserStyle");
			$objNamedLayer->appendChild($objUserStyle);
			// create element Name  for element UserStyle
			$objUserStyleName = $doc->createElement("Name", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStyleName']);
			$objUserStyle->appendChild($objUserStyleName);
			// create element Name  for element UserStyle
			$objUserStyleTitle = $doc->createElement("Title", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStyleTitle']);
			$objUserStyle->appendChild($objUserStyleTitle);
			// create element IsDefault for UserStyle
			$objUserStyleIsDefault = $doc->createElement("IsDefault", "1");
			$objUserStyle->appendChild($objUserStyleIsDefault);
			// create element FeatureTypeStyle for UserStyle
			$objFeatureTypeStyle = $doc->createElement("FeatureTypeStyle");
			$objUserStyle->appendChild($objFeatureTypeStyle);
			// create element Rule for element FeatureTypeStyle
			$objRule = $doc->createElement("Rule");
			$objFeatureTypeStyle->appendChild($objRule);
			// create element name for element Rule
			$objRuleName = $doc->createElement("Name", $arrLayerName[$i] . "_Style_Rule");
			$objRule->appendChild($objRuleName);
			// create element title for element Rule
			$objRuleTitle = $doc->createElement("Title", $arrLayerName[$i] . " Style Rule");
			$objRule->appendChild($objRuleTitle);
			
			//echo $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtMinRange']."<br>" ;
			//echo $arraySrsNamePre[$i].$arrLayerName[$i] . 'txtMinRange'."<br>";
			// create element MinScaleDenominator for element Rule
			if ($_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtMinRange'] != "") {
				$objMinScaleDenominator = $doc->createElement("MinScaleDenominator", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtMinRange']);
				$objRule->appendChild($objMinScaleDenominator);
				$objMinScaleDenominator = null;
			}
			// create element MaxScaleDenominator for element Rule
			if ($_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtMaxRange'] != "") {
				$objMaxScaleDenominator = $doc->createElement("MaxScaleDenominator", $_POST[$arraySrsNamePre[$i] .  $arrLayerName[$i] . 'txtMaxRange']);
				$objRule->appendChild($objMaxScaleDenominator);
				$objMaxScaleDenominator = null;
			}
			
			switch (strtoupper($arrLayerType[$i])) {
				case "POLYGON": {
					// create node PolygonSymbolizer for element Rule
					$objPolygonSymbolizer = $doc->createElement("PolygonSymbolizer");
					$objRule->appendChild($objPolygonSymbolizer);
					// create Fill node for element PolygonSymbolizer
					$objFill = $doc->createElement("Fill");
					$objPolygonSymbolizer->appendChild($objFill);
					// create CssParameter for element Fill
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtFill']);
					$objCssParameter->setAttribute("name", "fill");
					$objFill->appendChild($objCssParameter);
					// create CssParameter for element fill-opacity
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtFillOpacity']);
					$objCssParameter->setAttribute("name", "fill-opacity");
					$objFill->appendChild($objCssParameter);
					// create Stroke for element PolygonSymbolizer
					$objStroke = $doc->createElement("Stroke");
					$objPolygonSymbolizer->appendChild($objStroke);
					// create CssParameter for element stroke
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStroke']);
					$objCssParameter->setAttribute("name", "stroke");
					$objStroke->appendChild($objCssParameter);
					// create CssParameter for element stroke
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStrokeWidth']);
					$objCssParameter->setAttribute("name", "stroke-width");
					$objStroke->appendChild($objCssParameter);
					
					$objCssParameter = null;
					$objStroke = null;
					$objFill = null;
					$objPolygonSymbolizer = null;
				}
				break;
				case "LINESTRING": {
					// create LinesringSymbolizer for element Rule
					$objLineSymbolizer = $doc->createElement("LineSymbolizer");
					$objRule->appendChild($objLineSymbolizer);
					// create Fill node for element PolygonSymbolizer
					$objFill = $doc->createElement("Fill");
					$objLineSymbolizer->appendChild($objFill);
					// create CssParameter for element Fill
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtFill']);
					$objCssParameter->setAttribute("name", "fill");
					$objFill->appendChild($objCssParameter);
					// create CssParameter for element fill-opacity
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtFillOpacity']);
					$objCssParameter->setAttribute("name", "fill-opacity");
					$objFill->appendChild($objCssParameter);
					// create Stroke node for element PolygonSymbolizer
					$objStroke = $doc->createElement("Stroke");
					$objLineSymbolizer->appendChild($objStroke);
					// create CssParameter node for element stroke
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStroke']);
					$objCssParameter->setAttribute("name", "stroke");
					$objStroke->appendChild($objCssParameter);
					// create CssParameter node for element stroke
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStrokeWidth']);
					$objCssParameter->setAttribute("name", "stroke-width");
					$objStroke->appendChild($objCssParameter);
					// create CssParameter node for element stroke-opacity
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStrokeOpacity']);
					$objCssParameter->setAttribute("name", "stroke-opacity");
					$objStroke->appendChild($objCssParameter);
					// create CssParameter node for element stroke-linejoin
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStrokeLinejoin']);
					$objCssParameter->setAttribute("name", "stroke-linejoin");
					$objStroke->appendChild($objCssParameter);
					// create CssParameter node for element stroke-linecap
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStrokeLinecap']);
					$objCssParameter->setAttribute("name", "stroke-linecap");
					$objStroke->appendChild($objCssParameter);
					
					$objCssParameter = null;
					$objFill = null;
					$objStroke = null;
					$objLineSymbolizer = null;
				}
				break;
				case "POINT": {
					// create PointSymbolizer node for element Rule
					$objPointSymbolizer = $doc->createElement("PointSymbolizer");
					$objRule->appendChild($objPointSymbolizer);
					// create Graphic node for element PointSymbolizer
					$objGraphic = $doc->createElement("Graphic");
					$objPointSymbolizer->appendChild($objGraphic);
					// create Mark node for element Graphic element
					$objMark = $doc->createElement("Mark");
					$objGraphic->appendChild($objMark);
					// create WellKnownName node for Mark node
					$objWellKnownName = $doc->createElement("WellKnownName", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtWellknownName']);
					$objMark->appendChild($objWellKnownName);
					// create Stroke node for element PolygonSymbolizer
					$objStroke = $doc->createElement("Stroke");
					$objMark->appendChild($objStroke);
					// create CssParameter for element stroke-opacity
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStrokeOpacity']);
					$objCssParameter->setAttribute("name", "stroke-opacity");
					$objStroke->appendChild($objCssParameter);
					// create Fill for element PolygonSymbolizer
					$objFill = $doc->createElement("Fill");
					$objMark->appendChild($objFill);
					// create CssParameter for element Fill
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtFill']);
					$objCssParameter->setAttribute("name", "fill");
					$objFill->appendChild($objCssParameter);
					// create CssParameter for element txtFillOpacity
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtFillOpacity']);
					$objCssParameter->setAttribute("name", "fill-opacity");
					$objFill->appendChild($objCssParameter);
					// create Size node for element Graphic element
					$objSize = $doc->createElement("Size", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtSize']);
					$objGraphic->appendChild($objSize);
					
					$objCssParameter = null;
					$objStroke = null;
					$objFill = null;
					$objSize = null;
					$objWellKnownName = null;
					$objMark = null;
					$objGraphic = null;
					$objPointSymbolizer = null;
				}
				break;
				
				case "TEXT": {
					// create TextSymbolizer node for element Rule
					$objTextSymbolizer = $doc->createElement("TextSymbolizer");
					$objRule->appendChild($objTextSymbolizer);
					// create Label node for element textSymbolizer
					$objLabel = $doc->createElement("Label");
					$objTextSymbolizer->appendChild($objLabel);
					// create PropertyName node for element Label
					$objPropertyName = $doc->createElement("ogc:PropertyName", $_POST[$arraySrsNamePre[$i] .  $arrLayerName[$i] . 'txtLabel']);
					$objLabel->appendChild($objPropertyName);
					// create Font node for element TextSymbolizer
					$objFont = $doc->createElement("Font");
					$objTextSymbolizer->appendChild($objFont);
					// create CssParameter for element Font
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'sltFontFamily']);
					$objCssParameter->setAttribute("name", "font-family");
					$objFont->appendChild($objCssParameter);
					// create CssParameter for element Font
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtFontSize']);
					$objCssParameter->setAttribute("name", "font-size");
					$objFont->appendChild($objCssParameter);
					// create CssParameter for element font-style
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtFontStyle']);
					$objCssParameter->setAttribute("name", "font-style");
					$objFont->appendChild($objCssParameter);
					// create CssParameter for element font-weight
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtFontWeight']);
					$objCssParameter->setAttribute("name", "font-weight");
					$objFont->appendChild($objCssParameter);
					// create Fill node for element TextSymbolizer
					$objFill = $doc->createElement("Fill");
					$objTextSymbolizer->appendChild($objFill);
					// create CssParameter for element Fill
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtFontColor']);
					$objCssParameter->setAttribute("name", "fill");
					$objFill->appendChild($objCssParameter);
					
					$objCssParameter = null;
					$objPropertyName = null;
					$objFill = null;
					$objLabel = null;
					$objTextSymbolizer = null;
				}
				break;
				case "IMAGE": {
					// create RasterSymbolizer node for element Rule
					// webMap don't support raster sybmolization
					$objRasterSymbolizer = $doc->createElement("RasterSymbolizer");
					$objRule->appendChild($objRasterSymbolizer);
					// create Opacity node for RasterSymbolizer node
					$objOpacity = $doc->createElement("Opacity", $_POST[$arraySrsNamePre[$i] .  $arrLayerName[$i] . 'txtOpacity']);
					$objRasterSymbolizer->appendChild($objOpacity);
					
					$objRasterSymbolizer = null;
					$objOpacity = null;
				}
				break;

				case "COMPOND": {
					// create LinesringSymbolizer for element Rule
					$objLineSymbolizer = $doc->createElement("LineSymbolizer");
					$objRule->appendChild($objLineSymbolizer);
					// create Fill node for element PolygonSymbolizer
					$objFill = $doc->createElement("Fill");
					$objLineSymbolizer->appendChild($objFill);
					// create CssParameter for element Fill
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtFill']);
					$objCssParameter->setAttribute("name", "fill");
					$objFill->appendChild($objCssParameter);
					// create CssParameter for element fill-opacity
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtFillOpacity']);
					$objCssParameter->setAttribute("name", "fill-opacity");
					$objFill->appendChild($objCssParameter);
					// create Stroke node for element PolygonSymbolizer
					$objStroke = $doc->createElement("Stroke");
					$objLineSymbolizer->appendChild($objStroke);
					// create CssParameter node for element stroke
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStroke']);
					$objCssParameter->setAttribute("name", "stroke");
					$objStroke->appendChild($objCssParameter);
					// create CssParameter node for element stroke
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStrokeWidth']);
					$objCssParameter->setAttribute("name", "stroke-width");
					$objStroke->appendChild($objCssParameter);
					// create CssParameter node for element stroke-opacity
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStrokeOpacity']);
					$objCssParameter->setAttribute("name", "stroke-opacity");
					$objStroke->appendChild($objCssParameter);
					// create CssParameter node for element stroke-linejoin
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStrokeLinejoin']);
					$objCssParameter->setAttribute("name", "stroke-linejoin");
					$objStroke->appendChild($objCssParameter);
					// create CssParameter node for element stroke-linecap
					$objCssParameter = $doc->createElement("CssParameter", $_POST[$arraySrsNamePre[$i] . $arrLayerName[$i] . 'txtStrokeLinecap']);
					$objCssParameter->setAttribute("name", "stroke-linecap");
					$objStroke->appendChild($objCssParameter);
					
					$objCssParameter = null;
					$objFill = null;
					$objStroke = null;
					$objLineSymbolizer = null;
				}
				break;

				case "UNKNOWN": {
					// do nothing
				}
			}
		}
		
		if($database->db_save_style($aid, $sld_style_name, $stylecontent = $doc->saveXML(), $stid, $saveas)){
			return true;
		}else{
			return false;
		}
		//$doc->save("../sld/styles/" . $aid . "WmsStyles.xml");
		}
	
}
// $styleparser = new StyleParser();
// $aryXmlUserLayerNode = $styleparser->createStyleNode4layer();
// $styleparser->getLayerStyleFromStyleNode('wald','POLYGON',$aryXmlUserLayerNode);
// $styleparser->getLayerStyleFromStyleNode('LayerNotDefined','LINESTRING',$aryXmlUserLayerNode);
// $styleparser->getLayerStyleFromStyleNode('animationsubahn','POINT',$aryXmlUserLayerNode);
// $styleparser->getLayerStyleFromStyleNode('loading','TEXT',$aryXmlUserLayerNode);
// $styleparser->getLayerStyleFromStyleNode('StadtKarte200','IMAGE',$aryXmlUserLayerNode);

?>