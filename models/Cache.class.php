<?php
/*
 * Cache Class
 * Author :kingerq
 * Email: kingerq AT msn DOT com
 * Create date: 2006-05-05
 * Modified by LI Hui from 2007.04.06
 * Instance:
 include "Cache.class.php" ;
 
 $cache = new cache(30);
 $cache->cacheCheck();
 
 echo date("Y-m-d H:i:s");
 
 $cache->caching();
 */

class Cache {
	public static $TYPE_SVG = 0;
	
	public static $TYPE_PDF =  1;
	
	public static $TYPE_SWF =  2;
	
	public static $TYPE_PNG =  3;
	
	public static $TYPE_JPEG =  4;
	
	public static $TYPE_GIF =  5;
	
	public static $TYPE_WBMP =  6;
	
	public static $TYPE_BMP =  7;
	
	public static $TYPE_VML =  8;
	
	public static $TYPE_VRML =  9;
	
	public static $TYPE_VRMLZ =  10;
	
	public static $TYPE_X3D = 11;
	
	public static $TYPE_X3DZ =  12;
	
	public static $TYPE_XML =  13;
	
	public static $TYPE_HTML =  14;
	
	public static $TYPE_KML =  15;
	
	public static $TYPE_KMZ = 16;
	
	
	// Cache directoty SEE getCacheFileName
	private $cacheRoot = "../files/atlas/cache/"; 
	// Cache time
	private $cacheLimitTime = 0;
	// Cache name
	private $cacheFileName = "";
	// cache extension name
	private $cacheFileExt = "cache";
	
	private $error = "";
	
	private $aid ;
	
	/*default is 0: image/svg+xml
	 *0:image/svg+xml
	 *1:image/pdf
	 *2:image/swf
	 *3:image/png
	 *4:image/jpeg
	 *5:image/gif
	 *6:image/wbmp
	 *7:image/bmp
	 *8:image/vml
	 *9:image/vrml
	 *10:vrmlz
	 *11:x3d
	 *12:x3dz
	 *13: text/xml;charset=utf-8
	 *14: text/html;charset=utf-8
	 *15: kml
	 *16: kmz
	 */
	private $mode = 0;
	
	/*
	 * create the cache function
	 * int $cacheLimitTime
	 */
	function Cache($cacheLimitTime, $mode, $aid = "")
		{
		$this->mode = $mode;
		$this->aid = $aid;
		if (intval($cacheLimitTime))
			$this->cacheLimitTime = $cacheLimitTime;
		
		$this->cacheFileName = $this->getCacheFileName($aid);
		ob_start();
		}
	
	/*
	 * Check whether the static cache file has the date within the limited date
	 * If within the data, return cache content, if not, return false
	 */
	function cacheCheck()
		{
		
		$contentType['svg'] = 'image/svg+xml';
		$contentType['pdf'] = 'image/pdf';
		$contentType['swf'] = 'application/x-shockwave-flash';
		$contentType['vml'] = 'image/vml';
		$contentType['vrml'] = 'model/vrml';
		$contentType['vrmlz'] = 'model/vrmlz';
		$contentType['x3d'] = 'model/x3d+xml';
		$contentType['x3dz'] = 'model/x3dz';
		
		$contentType['kml'] = 'application/vnd.google-earth.kml+xml';
		$contentType['kmz'] = 'application/vnd.google-earth.kmz';
		
		$contentType['png'] = 'image/png';
		$contentType['jpeg'] = 'image/jpeg';
		$contentType['gif'] = 'image/gif';
		$contentType['wbmp'] = 'image/wbmp';
		$contentType['bmp'] = 'image/bmp';
		$contentType['xml'] = 'text/xml;charset=utf-8';
		$contentType['html'] = 'text/html;charset=utf-8';
		
		if (file_exists($this->cacheFileName)) {
			$cTime = $this->getFileCreateTime($this->cacheFileName);
			if ($cTime + $this->cacheLimitTime > time()) {
				switch ($this->mode) {
					case 0;
					header("Content-Type: " . $contentType['svg']);
					break;
					case 1;
					header("Content-Type: " . $contentType['pdf']);
					break;
					case 2;
					header("Content-Type: " . $contentType['swf']);
					break;
					case 3;
					header("Content-Type: " . $contentType['png']);
					break;
					case 4;
					header("Content-Type: " . $contentType['jpeg']);
					break;
					case 5;
					header("Content-Type: " . $contentType['gif']);
					break;
					case 6;
					header("Content-Type: " . $contentType['wbmp']);
					break;
					case 7;
					header("Content-Type: " . $contentType['bmp']);
					break;
					case 8;
					header("Content-Type: " . $contentType['vml']);
					break;
					case 9;
					header("Content-Type: " . $contentType['vrml']);
					break;
					case 10;
					header("Content-Type: " . $contentType['vrmlz']);
					break;
					case 11;
					header("Content-Type: " . $contentType['x3d']);
					break;
					case 12;
					header("Content-Type: " . $contentType['x3dz']);
					break;
					case 13;
					header("Content-Type: " . $contentType['xml']);
					break;
					case 14;
					header("Content-Type: " . $contentType['html']);
					break;
					case 15;
					header("Content-Type: " . $contentType['kml']);
					break;
					case 16;
					header("Content-Type: " . $contentType['kmz']);
					break;
					default: header("Content-Type: " . $contentType['svg']);
				}
				
				echo file_get_contents($this->cacheFileName);
				ob_end_flush();
				exit;
			}
		}
		return false;
		}
	
	/*
	 * Cache content in file or output the static cached file
	 * string $staticFileName
	 */
	function caching($staticFileName = "")
		{
		if ($this->cacheFileName) {
			$cacheContent = ob_get_contents();
			// echo $cacheContent;
			ob_end_flush();
			
			if ($staticFileName) {
				$this->saveFile($staticFileName, $cacheContent);
			}
			
			if ($this->cacheLimitTime)
				$this->saveFile($this->cacheFileName, $cacheContent);
		}
		}
	
	/*
	 * Clear the cache files
	 * string $fileName filename or all the files
	 * return true or false
	 */
	function clearCache($fileName = "all")
		{
		if ($fileName != "all") {
			$fileName = $this->cacheRoot . strtoupper(md5($fileName)) . "." . $this->cacheFileExt;
			if (file_exists($fileName)) {
				return @unlink($fileName);
			} else {
				$this->error = "The cache file you want to delete does not exist!";
				recordlog($this->error);
				return false;
			}
		}
		if (is_dir($this->cacheRoot)) {
			if ($dir = @opendir($this->cacheRoot)) {
				while ($file = @readdir($dir)) {
					$check = is_dir($file);
					if (!$check)
						@unlink($this->cacheRoot . $file);
				}
				@closedir($dir);
				return true;
			} else {
				$this->error = "Could not open cache directory, please check it!";
				recordlog($this->error);
				return false;
			}
		} else {
			$this->error = "The cache directory does not exist, please create it in SUAS root manully and chmod this directory to 777 if use Unix.";
			recordlog($this->error);
			return false;
		}
		}
	
	/*
	 * Clear the cache files from date to one date
	 * string $fileName filename or all the files
	 * return true or false
	 */
	function clearCacheFromDateTo($fm,$fd,$fy,$tm,$td,$ty)
		{
		if($fm=="" && $fd=="" && $fy==""){
			$fm = 1;
			$fd = 1;
			$fy = 1970;
		}
		if($tm=="" && $td=="" && $ty==""){
			$today = getdate();
			$fm = $today['mon'];
			$fd = $today['mday'];;
			$fy = $today['year'];;
		}
		if($fm=="" && $fd=="" && $fy=="" && $tm=="" && $td=="" && $ty==""){
			$this->clearCache("all");
			return true;
		}
		$ftime = mktime(0, 0, 0, $fm, $fd, $fy);
		$ttime = mktime(0, 0, 0, $tm, $td, $ty);
		if (is_dir($this->cacheRoot)) {
			if ($dir = @opendir($this->cacheRoot)) {
				while ($file = @readdir($dir)) {
					$check = is_dir($file);
					if (!$check){
						$cTime = $this->getFileCreateTime($file);
						if ($cTime < $ttime && $cTime >$ftime) {
							@unlink($this->cacheRoot . $file);
						}
					}
				}
				@closedir($dir);
				return true;
			} else {
				$this->error = "Could not open cache directory, please check it!";
				recordlog($this->error);
				return false;
			}
		} else {
			$this->error = "The cache directory does not exist, please create it in SUAS root manully and chmod this directory to 777 if use Unix.";
			recordlog($this->error);
			return false;
		}
		}
	
	/*
	 *get the current URL query string
	 */
	function get_url()
		{
		if (!isset($_SERVER['REQUEST_URI'])) {
			$url = $_SERVER['REQUEST_URI'];
		} else {
			$url = $_SERVER['script_NAME'];
			$url .= (!empty($_SERVER['QUERY_STRING'])) ? '?' . $_SERVER['QUERY_STRING'] : '';
		}
		//filter the aid string &aid=x (or aid=x, maybe the first parameter)
		$url = str_replace("&aid=".$this->aid, "", $url);
		$url = str_replace("aid=".$this->aid, "", $url);
		return $url;
		}
	
	/*
	 * create the cache name depending on the URL request
	 */
	function getCacheFileName($aid){
		if(!empty($aid)){
			//this is for wms.php in files/atlas/aid/cache
			$this->cacheRoot = "cache/";
		}
		return $this->cacheRoot . strtoupper(md5($this->get_url())) . "." . $this->cacheFileExt;
	}
	
	/*
	 * Get static cahce file date
	 * string $fileName
	 * return the date in seconds, else return 0;
	 */
	function getFileCreateTime($fileName)
		{
		if (! trim($fileName)) return 0;
		
		if (file_exists($fileName)) {
			return intval(filemtime($fileName));
		} else return 0;
		}
	
	/*
	 * Save static cache file
	 * string $fileName
	 * string $text      file content
	 * return true or false
	 */
	function saveFile($fileName, $text)
		{
		if (! $fileName || ! $text) return false;
		
		if ($this->makeDir(dirname($fileName))) {
			if ($fp = fopen($fileName, "w")) {
				if (!@flock($fp, LOCK_EX)) { // LOCK_NB
					$this->error = "Could not lock the cache file.";; //trigger_error
					recordlog("Could not lock the cache file.");
					return false;
				}
				if (@fwrite($fp, $text)) {
					@flock($fp, LOCK_UN); //release Lockdown
					fclose($fp);
					return true;
				} else {
					@flock($fp, LOCK_UN); //release Lockdown
					fclose($fp);
					$this->error = "Could not write the cache file.";
					recordlog($this->error);
					return false;
				}
			}
		}
		return false;
		}
	
	/*
	 * continue to create dir
	 * string $dir directory
	 * int $mode   privilege
	 * return  true or false
	 */
	function makeDir($dir, $mode = "0777")
		{
		if (! $dir) return 0;
		$dir = str_replace("\\", "/", $dir);
		
		$mdir = "";
		foreach(explode("/", $dir) as $val) {
			$mdir .= $val . "/";
			if ($val == ".." || $val == "." || trim($val) == "") continue;
			
			if (! file_exists($mdir)) {
				if (!@mkdir($mdir, $mode)) {
					$this->error = "The cache directory does not exist and can not be createn, please create cache folder in SUAS root manully and chmod this directory to 777 if use Unix.";
					recordlog($this->error);
					return false;
				}
			}
		}
		return true;
		}
	
	function getError()
		{
		return $this->error;
		}
}

?>