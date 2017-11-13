<?php

define("SUAS_NAME", "SUAS MapServer");
define("SUAS_NAME_PREFIX", "SUAS");
define("SUAS_NAME_POSTFIX", "MapServer");
define("SUAS_COPYRIGHT", " EasyWMS.com ");

//if here changes, should change the value in table variable
define("SITE_VERSION", "4.01");
define("SITE_VERSION_EDITION", "0422 Beta11");

define("SITE_DATATYPE_STR", "string");
define("SITE_DATATYPE_INT", "int");
define("SITE_DATATYPE_ARR", "array");
define("SITE_DATATYPE_BOL", "boolean");

/**
 *
 * @description :The maximum upload fize size, Don not change the value, unless you know what you are doing!
 * You can change it, but it is mainly controled by your php setting, in most case it is 2048 kb(2 Mbyte)
 * You could change it more than 2048, but you still have such limitation until you change the value in PHP.ini
 *
 * The php.ini file contains all the configuration settings for your installation.
 * Sometimes these setting might be overridden by directives in apache .htaccess files or even with in the scripts
 * themselves but for the moment let's just concentrate on the ini file.
 * upload_max_filesize and post_max_size
 *
 */
define("SITE_MAX_UPLOAD_SIZE", 20480); //kbytes

/**
 * @description :When you input very large data into database, please set the value a big number
 * It avoid abort the data processing when the execution time is out, unit is second.
 * But you need run PHP not in safe mode, otherwise this will not work!
 * Please open php.ini and set safe_mode to off.
 * safe_mode = Off
 */
define("SITE_MAX_TIMEOUT_LIMIT", 1800); //seconds

/**
 * @description :the directory name where daten can be uploaded to
 * It uses relative path, root is SUAS
 */
define("SITE_UPLOAD_DATA_DIRECTORY", "data");

define("mapTableFeaturegeometry", "featuregeometry");
define("mapTableFeatureclass", "featureclass");
define("mapTableFieldsFeaturegeometry", "id|aid|layer|geomtype|srs|xmin|ymin|xmax|ymax|geom|xlink|textcontent|attributes");

define("GeometryTypePoint", "Point");
define("GeometryTypeLineString", "LineString");
define("GeometryTypePolygon", "Polygon");
define("GeometryTypeText", "Text");
define("GeometryTypeImage", "Image");
define("GeometryTypeUnknown", "Unknown");
define("GeometryTypeCompond", "Compond");

//not use now
define("GeometryTypeMultiPoint", "MultiPoint");
define("GeometryTypeMultiLineString", "MultiLineString");
define("GeometryTypeMultiPolygon", "MultiPolygon");
define("GeometryTypeGeometryCollection", "GeometryCollection");
define("GeometryTypeMultiSurface", "MultiSurface");
define("GeometryTypeCurve", "Curve");
define("GeometryTypeMultiCurve", "MultiCurve");


define("SUAS_CFG_WMS_SERVICE", "WMS");
define("SUAS_CFG_WFS_SERVICE", "WFS");
define("SUAS_CFG_WMS_VERSION", "1.1.1");
define("SUAS_CFG_WFS_VERSION", "1.1.1");

define("StyleDefault", "Default");
define("SRSNotDefined", "SRS_not_defined");
define("LayerNotDefined", "LayerNotDefined");

$params['GetLegendGraphicWidth'] = 20;
$params['GetLegendGraphicHeight'] = 15;
$params['GetImageDefaultWidth'] = 400;
$params['GetImageDefaultHeight'] = 400;
$params['GetFeatureInfoRedius'] = 2;
global $params;

/*
*The array of supportted outputted map formats
*/
//vector
$MAP_RENDER_FORMAT['svg'] = 'image/svg+xml';
$MAP_RENDER_FORMAT['svgt'] = 'image/svgt+xml';
$MAP_RENDER_FORMAT['svgb'] = 'image/svgb+xml';
$MAP_RENDER_FORMAT['svgz'] = 'image/svgz';
$MAP_RENDER_FORMAT['svgtz'] = 'image/svgtz';
$MAP_RENDER_FORMAT['svgbz'] = 'image/svgbz';
$MAP_RENDER_FORMAT['pdf'] = 'application/pdf';
$MAP_RENDER_FORMAT['ezpdf'] = 'application/ezpdf';
$MAP_RENDER_FORMAT['swf'] = 'application/x-shockwave-flash';
$MAP_RENDER_FORMAT['vml'] = 'image/vml';
//3d
$MAP_RENDER_FORMAT['vrml'] = 'model/vrml';
$MAP_RENDER_FORMAT['vrmlz'] = 'model/vrmlz';
$MAP_RENDER_FORMAT['x3d'] = 'model/x3d+xml';
$MAP_RENDER_FORMAT['x3dz'] = 'model/x3dz';
$MAP_RENDER_FORMAT['kml'] = 'application/vnd.google-earth.kml+xml';
$MAP_RENDER_FORMAT['kmz'] = 'application/vnd.google-earth.kmz';
//raster
$MAP_RENDER_FORMAT['png'] = 'image/png';
$MAP_RENDER_FORMAT['jpeg'] = 'image/jpeg';
$MAP_RENDER_FORMAT['gif'] = 'image/gif';
$MAP_RENDER_FORMAT['wbmp'] = 'image/wbmp';
$MAP_RENDER_FORMAT['bmp'] = 'image/bmp';

$MAP_WMS_FORMAT['getcapabilities']['xml'] = 'text/xml';
$MAP_WMS_FORMAT['getfeatureinfo']['xml'] = 'text/xml';
$MAP_WMS_FORMAT['getfeatureinfo']['html'] = 'text/html';
$MAP_WMS_FORMAT['describelayer']['xml'] = 'text/xml';

//not binded with encode!
$format['xml'] = 'text/xml;charset=utf-8';
$format['json'] = 'text/x-json; charset=utf-8';

$elementtype["C"] = "string";
$elementtype["N"] = "double";
//$elementtype = array('C'=>"string", 'N'=>"double");

define("SITE_MESSAGE_ERROR", "ERRORMESSAGE");
define("SITE_MESSAGE_INFO", "INFOMESSAGE");
define("SITE_MESSAGE_WARN", "WARNMESSAGE");

$siteinfo['role']['role_anonymous'] = "anonymous user";
$siteinfo['role']['role_authenticated'] = "authenticated user";
$siteinfo['role']['role_administrator'] = "administrator user";
global $siteinfo;

//define($siteinfo['role']['role_anonymous'] , "anonymous user");
//define($siteinfo['role']['role_authenticated'] , "authenticated user");
//define($siteinfo['role']['role_administrator'] , "administrator user");

define("MENU_ACCOUNT", "myaccount");
define("MENU_ATLAS", "atlas");
define("MENU_ATLAS_CONFIG", "atlasconfig");
define("MENU_ATLAS_DEMO", "atlasdemo");

/*
* Standardized rendering pixel size is defined to be 0.28mm x 0.28mm (millimeters).
* Frequently, the true pixel size of the final rendering device is unknown in the web environment,
* and 0.28mm is a common actual size for contemporary video displays.
*
*/
define("PixelSize", 0.00028);
define("Earth_Average_Radius", 6371000);

define("DEFAULT_VALUE_enablestretchmap", 1); //true
define("DEFAULT_VALUE_AtlasStatus", 0); //private
define("DEFAULT_VALUE_showCopyright", 1); //true
define("DEFAULT_VALUE_cacheExpiredTime", 86400); //one day
define("DEFAULT_VALUE_enableSVGPixelCoordinate", 0); //false
define("DEFAULT_VALUE_enableStreamSVG", 1); //true
define("DEFAULT_VALUE_outputEncodeCountry", "en");
define("DEFAULT_VALUE_OverlapRatio", 0.1);
define("DEFAULT_VALUE_GetMap25DOverlapRatio", 0.5);

define(debugMode, false);

//========================================
/**
 * @description :Record the log file for recording the errors and issues
 * 0: record log; 1: do not record log
 */
//$recordLog = 1;// if global is off, do not work
define(recordLog, 1);
/**
 * @description :log file's name
 * Please do not change it, unless you dont want others to check your log file
 * It uses relative path, root is SUAS
 */
//$recordFileName = "log.txt";// if global is off, do not work
define(recordFileName, "log.txt");

$cty['UN']='World';
$cty['AF']='Afghanistan';
$cty['AX']='Aland Islands';
$cty['AL']='Albania';
$cty['DZ']='Algeria';
$cty['AS']='American Samoa';
$cty['AD']='Andorra';
$cty['AO']='Angola';
$cty['AI']='Anguilla';
$cty['AQ']='Antarctica';
$cty['AG']='Antigua And Barbuda';
$cty['AR']='Argentina';
$cty['AM']='Armenia';
$cty['AW']='Aruba';
$cty['AU']='Australia';
$cty['AT']='Austria';
$cty['AZ']='Azerbaijan';
$cty['BS']='Bahamas';
$cty['BH']='Bahrain';
$cty['BD']='Bangladesh';
$cty['BB']='Barbados';
$cty['BY']='Belarus';
$cty['BE']='Belgium';
$cty['BZ']='Belize';
$cty['BJ']='Benin';
$cty['BM']='Bermuda';
$cty['BT']='Bhutan';
$cty['BO']='Bolivia';
$cty['BA']='Bosnia And Herzegovina';
$cty['BW']='Botswana';
$cty['BV']='Bouvet Island';
$cty['BR']='Brazil';
$cty['IO']='British Indian Ocean Territory';
$cty['BN']='Brunei Darussalam';
$cty['BG']='Bulgaria';
$cty['BF']='Burkina Faso';
$cty['BI']='Burundi';
$cty['KH']='Cambodia';
$cty['CM']='Cameroon';
$cty['CA']='Canada';
$cty['CV']='Cape Verde';
$cty['KY']='Cayman Islands';
$cty['CF']='Central African Republic';
$cty['TD']='Chad';
$cty['CL']='Chile';
$cty['CN']='China';
$cty['CX']='Christmas Island';
$cty['CC']='Cocos (Keeling) Islands';
$cty['CO']='Colombia';
$cty['KM']='Comoros';
$cty['CG']='Congo';
$cty['CD']='Congo, Democratic Republic';
$cty['CK']='Cook Islands';
$cty['CR']='Costa Rica';
$cty['CI']='Cote D\'Ivoire';
$cty['HR']='Croatia';
$cty['CU']='Cuba';
$cty['CY']='Cyprus';
$cty['CZ']='Czech Republic';
$cty['DK']='Denmark';
$cty['DJ']='Djibouti';
$cty['DM']='Dominica';
$cty['DO']='Dominican Republic';
$cty['EC']='Ecuador';
$cty['EG']='Egypt';
$cty['SV']='El Salvador';
$cty['GQ']='Equatorial Guinea';
$cty['ER']='Eritrea';
$cty['EE']='Estonia';
$cty['ET']='Ethiopia';
$cty['FK']='Falkland Islands (Malvinas)';
$cty['FO']='Faroe Islands';
$cty['FJ']='Fiji';
$cty['FI']='Finland';
$cty['FR']='France';
$cty['GF']='French Guiana';
$cty['PF']='French Polynesia';
$cty['TF']='French Southern Territories';
$cty['GA']='Gabon';
$cty['GM']='Gambia';
$cty['GE']='Georgia';
$cty['DE']='Germany';
$cty['GH']='Ghana';
$cty['GI']='Gibraltar';
$cty['GR']='Greece';
$cty['GL']='Greenland';
$cty['GD']='Grenada';
$cty['GP']='Guadeloupe';
$cty['GU']='Guam';
$cty['GT']='Guatemala';
$cty['GG']='Guernsey';
$cty['GN']='Guinea';
$cty['GW']='Guinea-Bissau';
$cty['GY']='Guyana';
$cty['HT']='Haiti';
$cty['HM']='Heard Island &amp; Mcdonald Islands';
$cty['VA']='Holy See (Vatican City State)';
$cty['HN']='Honduras';
$cty['HK']='Hong Kong';
$cty['HU']='Hungary';
$cty['IS']='Iceland';
$cty['IN']='India';
$cty['ID']='Indonesia';
$cty['IR']='Iran, Islamic Republic Of';
$cty['IQ']='Iraq';
$cty['IE']='Ireland';
$cty['IM']='Isle Of Man';
$cty['IL']='Israel';
$cty['IT']='Italy';
$cty['JM']='Jamaica';
$cty['JP']='Japan';
$cty['JE']='Jersey';
$cty['JO']='Jordan';
$cty['KZ']='Kazakhstan';
$cty['KE']='Kenya';
$cty['KI']='Kiribati';
$cty['KR']='Korea';
$cty['KW']='Kuwait';
$cty['KG']='Kyrgyzstan';
$cty['LA']='Lao People\'s Democratic Republic';
$cty['LV']='Latvia';
$cty['LB']='Lebanon';
$cty['LS']='Lesotho';
$cty['LR']='Liberia';
$cty['LY']='Libyan Arab Jamahiriya';
$cty['LI']='Liechtenstein';
$cty['LT']='Lithuania';
$cty['LU']='Luxembourg';
$cty['MO']='Macao';
$cty['MK']='Macedonia';
$cty['MG']='Madagascar';
$cty['MW']='Malawi';
$cty['MY']='Malaysia';
$cty['MV']='Maldives';
$cty['ML']='Mali';
$cty['MT']='Malta';
$cty['MH']='Marshall Islands';
$cty['MQ']='Martinique';
$cty['MR']='Mauritania';
$cty['MU']='Mauritius';
$cty['YT']='Mayotte';
$cty['MX']='Mexico';
$cty['FM']='Micronesia, Federated States Of';
$cty['MD']='Moldova';
$cty['MC']='Monaco';
$cty['MN']='Mongolia';
$cty['ME']='Montenegro';
$cty['MS']='Montserrat';
$cty['MA']='Morocco';
$cty['MZ']='Mozambique';
$cty['MM']='Myanmar';
$cty['NA']='Namibia';
$cty['NR']='Nauru';
$cty['NP']='Nepal';
$cty['NL']='Netherlands';
$cty['AN']='Netherlands Antilles';
$cty['NC']='New Caledonia';
$cty['NZ']='New Zealand';
$cty['NI']='Nicaragua';
$cty['NE']='Niger';
$cty['NG']='Nigeria';
$cty['NU']='Niue';
$cty['NF']='Norfolk Island';
$cty['MP']='Northern Mariana Islands';
$cty['NO']='Norway';
$cty['OM']='Oman';
$cty['PK']='Pakistan';
$cty['PW']='Palau';
$cty['PS']='Palestinian Territory, Occupied';
$cty['PA']='Panama';
$cty['PG']='Papua New Guinea';
$cty['PY']='Paraguay';
$cty['PE']='Peru';
$cty['PH']='Philippines';
$cty['PN']='Pitcairn';
$cty['PL']='Poland';
$cty['PT']='Portugal';
$cty['PR']='Puerto Rico';
$cty['QA']='Qatar';
$cty['RE']='Reunion';
$cty['RO']='Romania';
$cty['RU']='Russian Federation';
$cty['RW']='Rwanda';
$cty['BL']='Saint Barthelemy';
$cty['SH']='Saint Helena';
$cty['KN']='Saint Kitts And Nevis';
$cty['LC']='Saint Lucia';
$cty['MF']='Saint Martin';
$cty['PM']='Saint Pierre And Miquelon';
$cty['VC']='Saint Vincent And Grenadines';
$cty['WS']='Samoa';
$cty['SM']='San Marino';
$cty['ST']='Sao Tome And Principe';
$cty['SA']='Saudi Arabia';
$cty['SN']='Senegal';
$cty['RS']='Serbia';
$cty['SC']='Seychelles';
$cty['SL']='Sierra Leone';
$cty['SG']='Singapore';
$cty['SK']='Slovakia';
$cty['SI']='Slovenia';
$cty['SB']='Solomon Islands';
$cty['SO']='Somalia';
$cty['ZA']='South Africa';
$cty['GS']='South Georgia And Sandwich Isl.';
$cty['ES']='Spain';
$cty['LK']='Sri Lanka';
$cty['SD']='Sudan';
$cty['SR']='Suriname';
$cty['SJ']='Svalbard And Jan Mayen';
$cty['SZ']='Swaziland';
$cty['SE']='Sweden';
$cty['CH']='Switzerland';
$cty['SY']='Syrian Arab Republic';
$cty['TW']='Taiwan';
$cty['TJ']='Tajikistan';
$cty['TZ']='Tanzania';
$cty['TH']='Thailand';
$cty['TL']='Timor-Leste';
$cty['TG']='Togo';
$cty['TK']='Tokelau';
$cty['TO']='Tonga';
$cty['TT']='Trinidad And Tobago';
$cty['TN']='Tunisia';
$cty['TR']='Turkey';
$cty['TM']='Turkmenistan';
$cty['TC']='Turks And Caicos Islands';
$cty['TV']='Tuvalu';
$cty['UG']='Uganda';
$cty['UA']='Ukraine';
$cty['AE']='United Arab Emirates';
$cty['GB']='United Kingdom';
$cty['US']='United States';
$cty['UM']='United States Outlying Islands';
$cty['UY']='Uruguay';
$cty['UZ']='Uzbekistan';
$cty['VU']='Vanuatu';
$cty['VE']='Venezuela';
$cty['VN']='Viet Nam';
$cty['VG']='Virgin Islands, British';
$cty['VI']='Virgin Islands, U.S.';
$cty['WF']='Wallis And Futuna';
$cty['EH']='Western Sahara';
$cty['YE']='Yemen';
$cty['ZM']='Zambia';
$cty['ZW']='Zimbabwe';
global $cty;
?>