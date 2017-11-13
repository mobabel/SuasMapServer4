<?php
/**
 * mysql.class.php
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
 * @copyright (C) 2006-2007  leelight
 * @Description : This class performes several operations in MySQL .
 * @contact webmaster@easywms.com
 */
include_once 'common.inc';

class Database {
	/**
	 * PRIVATE PROPERTIES
	 */
	public $databaseConnection; // Conection to the database.
	public $databaseHost; // IP of the MySQL host.
	// public $databasePort;			// MySQL Port.
	public $databaseUser; // MySQL Login.
	public $databasePass; // MySQL Password.
	public $databaseDb; // Database name.
	public $databasePrefix; // database prefix name
	public $databaseError; // Error name.
	public $databaseErrNo; // Error number.
	public $databaseErrorMessage = ""; // Error message.
	public $databaseLocked; // Lock flag.
	public $databaseFeedback; // Text of answer.
	public $databaseFile; // Name of the file for the backup and restore. (Ex. /tmp/backup/backup.sql)
	public $log; //for using getLog4Databae
	public $recordgood = 0; //record number that inserted successfully
	public $recordbad = 0; //record number that hasnt inserted
	
	public $siteInfo;
	
	function setSiteInfo($siteInfo)
		{
		$this->siteInfo = $siteInfo;
		}
	
	function getDatabaseError()
		{
		return $this->databaseError;
		}
	
	function setDatabaseError($value)
		{
		$this->databaseError = $value;
		}
	
	/**
	 *
	 * @Description :	Handle errors.																												 	**
	 * @params :error: Error occurs.
	 * @notice : use special PHP function																																					**
	 */
	function databaseErrorHandle($error)
		{
		$this->databaseError = @mysql_error($this->databaseConnection);
		$this->databaseErrNo = @mysql_errno($this->databaseConnection);
		// if ($this->mysql_locked)
		// $this->mysql_unlock();
		return $this->databaseErrorFormat($error);
		}
	
	/**
	 *
	 * @Description :	Handle infos.																												 	**
	 * @params :error: Error occurs.																																				**
	 */
	function databaseInfoHandle($info)
		{
		return $this->databaseErrorMessage = $info;
		}
	
	/**
	 *
	 * @Description :	Format the error and output																																**
	 * @params :error: Error occurs.
	 */
	/*
	 function databaseErrorFormat($error) {
	 $errormessage = "<h1>Failure</h1>"."<p id=\"intro\">You must correct the error below before process can continue:</br></br>";
	 $errormessage .= "<span style=\"color:#000000\"><b>Error: ".$error."</b></span></br></br>";
	 $errormessage .= "<b>MySQL Error</b>: ".$this->databaseError." (".$this->databaseErrNo.")</br>";
	 $errormessage .= "<a href=\"javascript: history.go(-1)\">Click here to go back</a>.</p>";
	 return $errormessage;
	 }
	 */
	
	/**
	 *
	 * @Description :	Format the error and output	for xml																																	**
	 * @params :error: Error occurs.
	 */
	function databaseErrorFormat($error)
		{
		$this->databaseErrorMessage = "" . $error . "</br>";
		if($this->databaseErrNo != 0){
			$this->databaseErrorMessage .= "MySQL Error: " . $this->databaseError . " (" . $this->databaseErrNo . ")</br>";
		}
		// http://dev.mysql.com/doc/refman/5.0/en/error-messages-server.html
		// $this->databaseErrorMessage .="Refer to <a href=\"http://dev.mysql.com/doc/refman/5.0/en/error-messages-server.html\" target=\"_blank\">MySQL Error Messages</a>";
		return $this->databaseErrorMessage;
		}
	
	function databaseGetErrorMessage()
		{
		return trim($this->databaseErrorMessage);
		}
	
	function dbEmptyErrorMessage()
		{
		$this->databaseErrorMessage = "";
		}
	
	/**
	 *
	 * @DESCRIPTION :Class Constructor.
	 */
	function database()
		{
		}
	
	/**
	 *
	 * @Description :Set the host, port, login, password	and database.																													 																																					**
	 * @params :host: Host IP where MySQL is.
	 * @params :user: MySQL Login.
	 * @params :pass: MySQL Password.
	 * @params :db: Database name.
	 */
	function databaseConfig($host, $user, $pass, $db, $databasePrefix = "")
		{
		$this->databaseHost = $host;
		$this->databasePass = $pass;
		$this->databaseUser = $user;
		$this->databaseDb = $db;
		$this->databasePrefix = $databasePrefix;
		$this->log = "";
		$this->recordgood = 0;
		$this->recordbad = 0;
		}
	
	/**
	 *
	 * @Description :Create the connection to the database.																													 	**
	 * @notice use special php function
	 */
	function databaseConnect()
		{
		$this->databaseConnection = @mysql_connect($this->databaseHost, $this->databaseUser, $this->databasePass);
		if (!$this->databaseConnection) {
			$this->databaseErrorHandle("Can not connect to Database! Please check your connection, username and password.");
		}
		$success = @mysql_select_db($this->databaseDb);
		//  @mysql _query("SET NAMES 'utf8'");
		if (!$success)
			$this->databaseErrorHandle("Database $this->databaseDb could not be opened!");
		}
	
	/**
	 *
	 * @Description :Create the connection without database.
	 * @notice use special php function
	 */
	function databaseConnectNoDatabase()
		{
		$this->databaseConnection = @mysql_connect($this->databaseHost, $this->databaseUser, $this->databasePass);
		if (!$this->databaseConnection) {
			$this->databaseErrorHandle("Can not connect to Database! Please check your connection, username and password.");
		}
		}
	
	function databaseSetPrefix($databasePrefix = "")
		{
		$this->databasePrefix = $databasePrefix;
		}
	
	/**
	 *
	 * @TODO should be renamed as getFetchArray()
	 * @Description :Fetch a result row as an associative array, a numeric array, or both
	 * By using MYSQL_BOTH (default), you'll get an array with both associative and number indices.
	 * Using MYSQL_ASSOC, you only get associative indices (as mysql_fetch_assoc() works),
	 * using MYSQL_NUM, you only get number indices (as mysql_fetch_row() works).
	 * @params : record: record array
	 * @return : columns in array
	 * @notice use special php function
	 */
	function getColumns($result, $type = MYSQL_BOTH)
		{
		$columns = @mysql_fetch_array($result, $type);
		if ($error = mysql_error()) {
			// it is strange, the last element in while($row=$database->getColumns($result)) always is wrong!
			$this->databaseErrorHandle("mysql_fetch_array error in getColumns");
			return false;
		}
		
		return $columns;
		}
	
	/**
	 *
	 * @Description :Get a result row as an enumerated array
	 * @params : record: columns
	 * @return : rows in array
	 * @notice use special php function
	 */
	function getRows($result)
		{
		@$rows = mysql_fetch_row($result);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle("mysql_fetch_row error in getRows");
			return false;
		}
		
		return $rows;
		}
	
	/**
	 *
	 * @Description :Get number of fields in result
	 * @params : rows: rows array
	 * @return : fields number as integer
	 * @notice use special php function
	 */
	function getFieldsNumber($result)
		{
		@$num = mysql_num_fields($result);
		if ($error = mysql_error())
			$this->databaseErrorHandle("mysql_num_fields error in getFieldsNumber");
		
		return $num;
		}
	
	/**
	 *
	 * @Description :Get the rows number
	 * @params : rows: rows array
	 * @return : columns number
	 * @notice use special php function
	 */
	function getRowsNumber($field)
		{
		@$num = mysql_num_rows($field);
		if ($error = mysql_error())
			$this->databaseErrorHandle("mysql_num_rows error in getRowsNumber");
		
		return $num;
		}
	
	/**
	 *
	 * @Description :Get the database version as array
	 * @return :	version string
	 * @notice use special php function
	 *
	 * or 	$result = mysql_get_server_info();																																**
	 */
	function getDatabaseVersion()
		{
		@$version = mysql_query('SELECT VERSION() AS version', $this->databaseConnection);
		$result = mysql_fetch_array($version);
		if ($error = mysql_error())
			$this->databaseErrorHandle("Can not get database version!");
		
		return $result;
		}
	
	/**
	 *
	 * @Description :Get the database names list object
	 * @return :	database name list object
	 * @notice use special php function																																			**
	 */
	function getDatabaseName()
		{
		$databasename = @mysql_list_dbs($this->databaseConnection);
		if (!$databasename)
			$this->databaseErrorHandle("Can not get database name!");
		
		return $databasename;
		}
	
	/**
	 *
	 * @Description :delete one table with name
	 * @params : tablename																																**
	 */
	function databaseDeleteTable($tablename)
		{
		$result = @mysql_query("DROP TABLE $tablename", $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle("Can not delete this table $tablename!");
			return false;
		}
		return true;
		}
	
	/**
	 *
	 * @Description :Get the table names list object
	 * @params : databasename
	 * @return :	table name list
	 * @notice use special php function																																**
	 */
	function getTableName($databasename)
		{
		$tablename = @mysql_list_tables($databasename);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle("Can not get tables from $databasename!");
			return false;
		}
		
		return $tablename;
		}
	
	/**
	 *
	 * @Description :Get the columns object from table
	 * @params : tablename
	 * @return :	columns list																																			**
	 */
	function getColumnsFromTable($tablename)
		{
		$columns = @mysql_query("SHOW COLUMNS FROM $tablename", $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle("Can not get columns from $tablename!");
			return false;
		}
		
		return $columns;
		}
	
	/**
	 *
	 * @Description :Excute any SQL query																																	**
	 */
	function databaseAnyQuery($sqlstring)
		{
		$result = @mysql_query($sqlstring, $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle("Can not excute this SQL: ");
			return false;
		} else
			return true;
		}
	
	/**
	 *
	 * @Description :Create a database																																	**
	 */
	function databaseCreateDatabase($databasename)
		{
		$result = @mysql_query("CREATE DATABASE $databasename DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci", $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle("Can not create this database $databasename!");
			return false;
		}
		return true;
		}
	
	/**
	 *
	 * @Description :delete a database																																	**
	 */
	function databaseDeleteDatabase($databasename)
		{
		$result = @mysql_query("DROP DATABASE $databasename", $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle("Can not delete this database $databasename!");
			return false;
		}
		return true;
		}
	
	/**
	 *
	 * @Description :Test the privilege to create and drop database																																**
	 */
	function databaseCheckCreateAndDropDatabasePrivelege()
		{
		$testname = "test" . date("Ymd");
		// later could rewrite code here, to avoid create error log
		$result = $this->databaseCreateDatabase($testname);
		if ($result) {
			$result = $this->databaseDeleteDatabase($testname);
		}
		
		return $result;
		}
	
	function databaseCheckTablePrivelege()
		{
		$testname = "test" . date("Ymd");
		$success['SELECT'] = false;
		$success['CREATE'] = false;
		$success['INSERT'] = false;
		$success['UPDATE'] = false;
		$success['DELETE'] = false;
		$success['DROP'] = false;
		// Test CREATE.
		$query = 'CREATE TABLE ' . $testname . ' (id int NULL)';
		@$result = mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to create a test table on your database server with the command %query. <ul><li>Are you sure the configured username has the necessary permissions to create tables in the database?</li></ul>If you are unsure what these terms mean you should probably contact your hosting provider.', array('%query' => $query)));
			return $success;
		} else {
			$success['SELECT'] = true;
			$success['CREATE'] = true;
		}
		// Test INSERT.
		$query = 'INSERT INTO ' . $testname . ' (id) VALUES (1)';
		@$result = mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to insert a value into a test table on your database server. We tried inserting a value with the command %query.', array('%query' => $query)));
			return $success;
		} else {
			$success['INSERT'] = true;
		}
		// Test UPDATE.
		$query = 'UPDATE ' . $testname . ' SET id = 2';
		@$result = mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to update a value in a test table on your database server. We tried updating a value with the command %query.', array('%query' => $query)));
			return $success;
		} else {
			$success['UPDATE'] = true;
		}
		// Test DELETE.
		$query = 'DELETE FROM ' . $testname;
		@$result = mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to delete a value from a test table on your database server. We tried deleting a value with the command %query.', array('%query' => $query)));
			return $success;
		} else {
			$success['DELETE'] = true;
		}
		// Test DROP.
		$query = 'DROP TABLE ' . $testname;
		@$result = mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to drop a test table from your database server. We tried dropping a table with the command %query.', array('%query' => $query)));
		} else {
			$success['DROP'] = true;
		}
		
		return $success;
		}
	
	/**
	 *
	 * @Description :Close the connection of the database.
	 * @notice use special php function
	 */
	function databaseClose()
		{
		$result = @mysql_close($this->databaseConnection);
		if ($error = mysql_error())
			$this->databaseErrorHandle("Can not close the connection of database!");
		}
	
	/**
	 * if not exist, return false, if yes return all columns
	 */
	function login($username, $password, $validate_ready)
		{
		$mpassword = md5($password);
		if ($validate_ready)
			$query = "SELECT * FROM " . $this->databasePrefix . "users WHERE `name`='$username' AND `pass`='$mpassword' AND `status`='1'";
		else
			$query = "SELECT * FROM " . $this->databasePrefix . "users WHERE `name`='$username' AND `pass`='$mpassword'";
		
		@$result = mysql_query($query, $this->databaseConnection);
		if ($error = mysql_error()){
			$this->databaseErrorHandle(t("Failed to login, error: %error ", array('%error' => $error)));
			return false;
		}
		while ($login = $this->getColumns($result)) {
			if (!$login) {
				$this->databaseInfoHandle("Sorry, unrecognized username or password.");
				return false;
			}{
				//update the login and access timestamp
				$condition = "`access` = '" . time() . "',
					`login` = '" . time() . "'";
				$query = "UPDATE  " . $this->databasePrefix . "users SET $condition WHERE `name`='$username' AND `pass`='$mpassword' LIMIT 1";
				$result = mysql_query($query, $this->databaseConnection);
				
				return $login;
			}
		}
		}
	
	/**
	 * get the role as string from uid
	 */
	function get_role($uid)
	{
		if ($uid == 1)
			return $this->siteInfo['role']['role_administrator'];
		
		$sql = "SELECT status FROM " . $this->databasePrefix . "users WHERE uid='$uid' ";
		@$result = mysql_query($sql, $this->databaseConnection);
		$record = $this->getColumns($result);
		if ($record['status'] == 0)
			return $this->siteInfo['role']['role_anonymous'];
		else {
			$sql = "SELECT rid FROM " . $this->databasePrefix . "users_roles WHERE uid='$uid' ";
			@$result = mysql_query($sql, $this->databaseConnection);
			$record = $this->getColumns($result);
			if (!$record)
				return $this->siteInfo['role']['role_authenticated'];
			else {
				$sql = "SELECT name FROM " . $this->databasePrefix . "role WHERE rid='" . $record['rid'] . "' ";
				@$result = mysql_query($sql, $this->databaseConnection);
				$record = $this->getColumns($result);
				return $record['name'];
			}
		}
	}
	
	function get_user($uid){
		$sql = "SELECT * FROM " . $this->databasePrefix . "users WHERE uid='$uid' LIMIT 1";
		@$result = mysql_query($sql, $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to get user in get_user. We tried querying the command %query.'));
			return false;
		}
		$record = $this->getColumns($result);
		return $record;
	}
	
	/**
	 * register one new user, could only check existed user if set $checkExist true
	 */
	function db_register_user($username, $password, $email, $needValidate = false, $checkExist = false)
		{
		$sql = "SELECT uid FROM " . $this->databasePrefix . "users WHERE name='$username' ";
		@$result = mysql_query($sql, $this->databaseConnection);
		$record = $this->getColumns($result);
		if ($record) {
			$this->databaseInfoHandle("$username already exists, please choose other user name.");
			return false;
		}
		if (!$checkExist) {
			if (!$needValidate) {
				$fields = 'name,pass,mail,created,status';
				$values = "'" . $username . "','" . md5($password) . "','" . $email . "','" . time() . "','1'";
			} else {
				$fields = 'name,pass,mail,created';
				$values = "'" . $username . "','" . md5($password) . "','" . $email . "','" . time() . "'";
			}
			$sql = "INSERT INTO " . $this->databasePrefix . "users (" . $fields . ") VALUES (" . $values . ")";
			@$result = mysql_query($sql, $this->databaseConnection);
			if ($error = mysql_error()) {
				$this->databaseErrorHandle("Failed to register user name $username.");
				return false;
			}
			//return new uid
			$query = "SELECT uid FROM " . $this->databasePrefix . "users WHERE  `name` = '$username'";
			@$result = @mysql_query($query);
			while ($row = $this->getColumns($result)) {
				return $row['uid'];
			}
			return false;
		}else{
			return true;
		}
		}
	
	function db_update_user($uid, $data){
		$condition = //"`name` = '" . $data['name'] . "',".
			"`mail` = '" . $data['mail'] . "'"
			.(empty($data['status'])?"":", `status` = '" . $data['status'] . "'")
			. ", `init` = '" . $data['mail'] . "'"
			. (empty($data['pass'])?"":", `pass` = '" . md5($data['pass']) . "'");
		
		$query = "UPDATE  " . $this->databasePrefix . "users SET $condition WHERE `uid` ='$uid' ";
		@mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to update user <b>%name</b>. We tried querying with the command %query.', array('%name' => $data['name'], '%query' => $query)));
			return false;
		}
		$query = "SELECT * FROM " . $this->databasePrefix . "users WHERE `uid`='$uid'";
		$result = @mysql_query($query);
		while ($login = $this->getColumns($result)) {
			return $login;
		}
		return false;
	}
	
	function db_get_latestuser(){
		$sql = "SELECT name, uid FROM " . $this->databasePrefix . "users ORDER BY created desc LIMIT 5";
		@$result = mysql_query($sql, $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to db_get_latestuser.'));
			return false;
		}
		return $result;
	}
	
	/**
	 * 
	 * $orderby: a.modified, a.name, a.status,
	 * $sort: asc, desc
	 */
	function db_get_atlas_list($listmax = 5, $orderby = "modified", $sort="asc", $page = 1, $total = 0, $uid = 0)
		{
		//TODO if if for my atlas, total will change?
		if($total == 0 || $total == ""){
			if(empty($uid) || $uid == "0"){
			//filter those which has no geometry
				$query = "SELECT a.aid, count(f.fcid) AS layercount FROM " . $this->databasePrefix . "atlas AS a, "
					.$this->databasePrefix."featureclass AS f WHERE f.aid=a.aid GROUP by a.aid";
			}else{
				//list all, not filter
				$query = "SELECT aid FROM " . $this->databasePrefix . "atlas WHERE `uid` = '$uid'";
			}

			$result= @mysql_query($query);
			$total= $this->getRowsNumber($result);
		}   	
		if($page < 1  || empty($page)) $page  = 1;
		if(empty($orderby))$orderby = "modified";
		if(empty($sort))$sort = "asc";
		//TODO
		if(empty($listmax) || $listmax <= 0 )$listmax = 5;
		
		$pagenum=ceil($total/$listmax); 
		//if $pagenum is 0, will cause $offset get minus value
		$pagenum = $pagenum == 0?1:$pagenum;
		$page=min($pagenum,$page); //first page
		$prepg=$page-1; //previous page
		$nextpg=($page==$pagenum ? 0 : $page+1);//next page
		$offset=($page-1) * $listmax;
		$boundaryleft = ($total?($offset+1):0);
		$boundaryright = min($offset+$listmax,$total);
		
		if(empty($uid) || $uid == "0"){
			/*$query = "SELECT a.name as aname, a.aid, a.status, a.title, a.abstract, a.modified, u.name as uname, f.fcid as layercount FROM " . $this->databasePrefix . "atlas a, "
			 .$this->databasePrefix."users u , ".$this->databasePrefix."featureclass f WHERE a.uid= u.uid AND f.`aid`=a.`aid`"//       		
			 ." ORDER BY UPPER(a.".$orderby.") ".$sort." LIMIT ".$offset.", ".$listmax." "; */
			$query = "SELECT a.name as aname, a.aid, a.status, a.title, a.abstract, a.modified, a.created, u.name as uname, ac.totalcount, count(f.fcid) as layercount FROM " 
				. $this->databasePrefix . "atlas AS a INNER JOIN "
				.$this->databasePrefix."users AS u ON a.`uid`= u.`uid` INNER JOIN ".$this->databasePrefix."featureclass AS f ON f.aid=a.aid "
				." LEFT JOIN ".$this->databasePrefix."atlas_counter AS ac ON ac.aid=a.aid"       		
				." GROUP BY a.aid ORDER BY UPPER(a.".$orderby.") ".$sort." LIMIT ".$offset.", ".$listmax." ";  
				//echo   $query;      	
		}else{
			/*$query = "SELECT a.*, count(f.fcid) as layercount FROM " . $this->databasePrefix . "atlas AS a INNER JOIN "
			 .$this->databasePrefix."featureclass AS f ON a.uid='$uid' AND f.aid=a.aid "
			 ."  GROUP BY a.aid LIMIT ".$offset.", ".$listmax;*/
			/*$query = "SELECT a.*, count(f.fcid) as layercount FROM " . $this->databasePrefix . "atlas AS a, "
			 .$this->databasePrefix."featureclass AS f WHERE a.uid='$uid' AND f.aid=a.aid "
			 ."  GROUP BY a.aid LIMIT ".$offset.", ".$listmax;*/
			$query = "SELECT a.*, ac.totalcount FROM " . $this->databasePrefix . "atlas AS a "
				." left JOIN ".$this->databasePrefix."atlas_counter AS ac"
				." ON ac.aid=a.aid WHERE a.`uid`='$uid' " 
				." ORDER BY a.created desc LIMIT ".$offset.", ".$listmax;
		}
		//echo $query;
		@$result = mysql_query($query, $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to get atlas list. We tried querying the command %query.', array('%query' => $query)));
			return false;
		}
		$last['total'] = $total;
		$last['result'] = $result;
		return $last;
	}
	
	function db_get_hotestatlas()
	{
		//should have layers, so count the featureclass >0
		$yesterday = time()-86400;
		$query = "SELECT a.name as aname, a.aid, a.status, a.title, a.abstract, a.modified, a.created, u.name as uname , count(f.fcid) as layercount FROM " 
				. $this->databasePrefix . "atlas AS a  INNER JOIN ".$this->databasePrefix."users AS u ON a.uid= u.uid AND a.status=1 " 
				." INNER JOIN ".$this->databasePrefix."featureclass AS f ON f.aid=a.aid JOIN ".$this->databasePrefix."atlas_counter AS ac ON ac.aid=a.aid AND ac.timestamp >$yesterday"    		
				." GROUP BY a.aid ORDER BY ac.daycount desc LIMIT 1";      //  	
		//echo $query;
		@$result = mysql_query($query, $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to get atlas list. We tried querying the command %query.', array('%query' => $query)));
			return false;
		}

		return $result;
	}
	
	function db_get_randomatlas($listmax)
	{		
		//can be used for selecting from category
/*			$query = "SELECT word, trans
FROM `recitewords` AS t1 JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM `recitewords` WHERE `recatid` in ('$recatid'))-(SELECT MIN(id) FROM `recitewords` WHERE `recatid` in ('$recatid')))+(SELECT MIN(id) FROM `recitewords` WHERE `recatid` in ('$recatid'))) AS id) AS t2 WHERE t1.id >= t2.id
ORDER BY t1.id LIMIT $resultcount";*/	
		
		//should have layers, so count the featureclass >0
		$atlastable = $this->databasePrefix . "atlas";
		$query = "SELECT a.name as aname, a.aid, a.status, a.title, a.abstract, a.modified, a.created, u.name as uname , count(f.fcid) as layercount FROM " 
				. "$atlastable AS a  INNER JOIN ".$this->databasePrefix."users AS u ON a.uid= u.uid AND a.status=1 " 
				." INNER JOIN ".$this->databasePrefix."featureclass AS f ON f.aid=a.aid  " 
				." JOIN (SELECT ROUND(RAND() * ((SELECT MAX(aid) FROM `$atlastable`)-(SELECT MIN(aid) FROM `$atlastable`))+(SELECT MIN(aid) FROM `$atlastable` )) AS id) AS t2 WHERE a.aid >= t2.id"    		
				." GROUP BY a.aid  LIMIT $listmax";     	
		//echo $query;
		@$result = mysql_query($query, $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to get atlas list. We tried querying the command %query.', array('%query' => $query)));
			return false;
		}

		return $result;
	}
	
	/**
	 * 
	 */
	function db_get_atlas($aid, $uid = "")
		{//$this->databasePrefix."atlas_counter AS ac 
		//if both is empty, selec the default
		if((empty($aid) || $aid == "0")&&(empty($uid) || $uid == "0")){
			$query = "SELECT a.*, u.name as username, ac.totalcount FROM " . $this->databasePrefix . "atlas AS a "
			. " INNER JOIN " . $this->databasePrefix . "users AS u ON a.uid = u.uid "
			." LEFT JOIN ".$this->databasePrefix."atlas_counter AS ac ON ac.aid=a.aid LIMIT 1";
		}
		//if has aid, dont care the uid
		else if((!empty($aid) && $aid != "0")){
			$query = "SELECT a.*, u.name as username, ac.totalcount FROM " . $this->databasePrefix . "atlas AS a "
			." INNER JOIN " . $this->databasePrefix . "users AS u ON a.aid='$aid' AND a.uid = u.uid "
			." LEFT JOIN ".$this->databasePrefix."atlas_counter AS ac ON ac.aid=a.aid LIMIT 1";
		}
		//echo $query;
		@$result = mysql_query($query, $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to get atlas information data in db_get_atlas. We tried querying the command %query.', array('%query' => $query)));
			return false;
		} 
		while ($info = $this->getColumns($result, MYSQL_ASSOC)) {
			if (!$info) {
				$this->databaseInfoHandle("Wrong atlas id or uid?");
				return false;
			}
			return $info;
		}
	}
	
	/**
	 * if $uid is empty, and aid is given, get the aid row number(how many users have added it into favo)
	 * if uid is given, and aid is given, check if this atlas has been added by this uid, and the row number
	 *
	 */
	function db_get_atlas_favoinfo($aid, $uid = ""){
		if(empty($aid)){
			$this->databaseErrorHandle(t('Failed to get atlas information data in db_get_atlas_favoinfo. ids are empty.'));
			return false;	
		}
		if( !empty($aid) && empty($uid) ){
			$query = "SELECT count(af.afid) as favocount FROM " . $this->databasePrefix . "atlas_favorite AS af WHERE `aid`= '$aid' LIMIT 1";
			@$result = mysql_query($query, $this->databaseConnection);
			if ($error = mysql_error()) {
				$this->databaseErrorHandle(t('Failed to get atlas information data in db_get_atlas_favoinfo. We tried querying the command %query.', array('%query' => $query)));
				return false;
			}
			while ($info = $this->getColumns($result)) {
				$favoinfo['favocount'] = $info['favocount'];
			}
			return $favoinfo; 
		}
		else if( !empty($aid) && !empty($uid) ){    	
			$query = "SELECT count(afid) as favocucount FROM " . $this->databasePrefix . "atlas_favorite " 
				. " WHERE `aid`= '$aid' AND `uid` = '$uid'";
			@$result = mysql_query($query, $this->databaseConnection);
			if ($error = mysql_error()) {
				$this->databaseErrorHandle(t('Failed to get atlas information data in db_get_atlas_favoinfo. We tried querying the command %query.', array('%query' => $query)));
				return false;
			}
			while ($info = $this->getColumns($result)) {
				$favoinfo['favocucount'] = $info['favocucount'];
			}
			$query = "SELECT count(afid) as favocount FROM " . $this->databasePrefix . "atlas_favorite " 
				. " WHERE `aid`= '$aid'";
			@$result = mysql_query($query, $this->databaseConnection);
			if ($error = mysql_error()) {
				$this->databaseErrorHandle(t('Failed to get atlas information data in db_get_atlas_favoinfo. We tried querying the command %query.', array('%query' => $query)));
				return false;
			}
			while ($info = $this->getColumns($result)) {
				$favoinfo['favocount'] = $info['favocount'];
			}
			return $favoinfo;
		}
		
		return $favoinfo;
	}
	
	function db_atlas_counter($aid){
		$query = "SELECT totalcount, daycount, timestamp FROM " . $this->databasePrefix . "atlas_counter " 
				. " WHERE `aid`= '$aid' LIMIT 1";
		@$result = mysql_query($query, $this->databaseConnection);
		$row = $this->getColumns($result);

		if($row){
			$currenttime = time();
			//echo  "has row ".($currenttime - $row['timestamp']);
			$totalcount = $row['totalcount']+1;
			//more than 1 day
			if($currenttime - $row['timestamp'] > 86400){
				$daycount = 1;
				$timestamp = $currenttime;
			}else{
				$daycount = $row['daycount']+1;
				$timestamp = $row['timestamp'];
			}
			
			$condition = "`totalcount` = '" . $totalcount . "', `daycount` = '". $daycount ."'" .
			", `timestamp` = '".$timestamp."'";
			$query = "UPDATE  " . $this->databasePrefix . "atlas_counter SET $condition WHERE `aid` ='$aid' LIMIT 1";

			@mysql_query($query, $this->databaseConnection);
			if ($error = mysql_error()) {
				$this->databaseErrorHandle(t('Failed to update in db_atlas_counter. We tried querying the command %query.', array('%query' => $query)));
				return false;
			}
		}else{
			$timestamp = time();
			$daycount = 1;
			$totalcount = 1;
			
			$fields = "`aid` ,`totalcount`, `daycount` , `timestamp`";
			$values = "'" . $aid . "' , '" . $totalcount . "', '".$daycount."', '".$timestamp."'";
			$query = "INSERT INTO " . $this->databasePrefix . "atlas_counter (" . $fields . ") VALUES (" . $values . ")";
			@mysql_query($query, $this->databaseConnection);
			if ($error = mysql_error()) {
				$this->databaseErrorHandle(t('Failed to insert in db_atlas_counter. We tried querying the command %query.', array('%query' => $query)));
				return false;
			}
		}
		return true;
	}

	/**
	 * 
	 */
	function db_get_atlas_favorite_list($listmax = 5, $orderby = "created", $sort="desc", $page = 1, $total = 0, $uid = 0){
		if($total == 0 || $total == ""){
			$result= @mysql_query("select aid FROM " . $this->databasePrefix . "atlas_favorite WHERE `uid` = '$uid' ");
			$total= $this->getRowsNumber($result);
		}   	
		if($page < 1  || empty($page)) $page  = 1;
		if(empty($orderby))$orderby = "created";
		if(empty($sort))$sort = "desc";
		if(empty($listmax) || $listmax <= 0 )$listmax = 5;
		
		$pagenum=ceil($total/$listmax); 
		//if $pagenum is 0, will cause $offset get minus value
		$pagenum = $pagenum == 0?1:$pagenum;
		$page=min($pagenum,$page); //first page
		$prepg=$page-1; //previous page
		$nextpg=($page==$pagenum ? 0 : $page+1);//next page
		$offset=($page-1) * $listmax;
		$boundaryleft = ($total?($offset+1):0);
		$boundaryright = min($offset+$listmax,$total);
		
		if($orderby = "created"){
			$orderby_sql = "a.".$orderby;
		}else if($orderby = "created"){
			$orderby_sql = "af.".$orderby;
		}

		//layercount will be couat n times, which n is the number of returned column
		$query = "SELECT a.name as aname, a.aid, a.status, a.title, a.abstract, a.modified, a.created, u.name as uname, count(f.fcid) as layercount, af.created as favocreated, af.desc FROM " 
			. $this->databasePrefix . "atlas  AS a INNER JOIN "
			.$this->databasePrefix."users AS u ON a.uid= u.uid AND a.aid in (select `aid` from " . $this->databasePrefix . "atlas_favorite where `uid`='$uid') INNER JOIN ".$this->databasePrefix."featureclass AS f ON f.aid=a.aid "       		
			." INNER JOIN " . $this->databasePrefix . "atlas_favorite AS af ON af.aid = a.aid GROUP BY a.aid ORDER BY UPPER($orderby_sql) ".$sort." LIMIT ".$offset.", ".$listmax;
		
		@$result = mysql_query($query, $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to get favorite list in db_get_atlas_favorite_list. We tried querying the command %query.', array('%query' => $query)));
			return false;
		}
		$last['total'] = $total;
		$last['result'] = $result;
		return $last;
	}
	
	function db_atlas_add_favorite($aid, $uid, $desc){
		//check if atlas has been added by user
		$query = "SELECT aid FROM " . $this->databasePrefix . "atlas_favorite AS af WHERE `uid`= '$uid' AND `aid`= '$aid'";
		@$result = mysql_query($query, $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to check favorite list in db_atlas_add_favorite. We tried querying the command %query.', array('%query' => $query)));
			return false;
		}		
		//has added before
		if($this->getRowsNumber($result) > 0){
			$this->databaseErrorHandle(t('Atlas has been added into favorite before by this user.'));
			return false;
		}
		//TODO favo count has exceed the limit 20?
		$favomaxlimit = 20;
		$query = "SELECT aid FROM " . $this->databasePrefix . "atlas_favorite WHERE `uid`= '$uid'";
		@$result = mysql_query($query, $this->databaseConnection);
		if($this->getRowsNumber($result) > $favomaxlimit){
			$this->databaseErrorHandle(t('Your favorite atlas have exceed the maximum limit %favomaxlimit.',  array('%favomaxlimit' => $favomaxlimit)));
			return false;
		}
		
		$fields = "`aid` ,`uid`, `desc` , `created`";
		$values = "'" . $aid . "' , '" . $uid . "', '".$desc."', '".time()."'";
		$query = "INSERT INTO " . $this->databasePrefix . "atlas_favorite (" . $fields . ") VALUES (" . $values . ")";
		@mysql_query($query, $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to add atlas into favirate in db_atlas_add_favorite. We tried querying the command %query.', array('%query' => $query)));
			return false;
		}
		return true;
	}
	
	function db_atlas_remove_favorite($aid, $uid){
		$query = "DELETE FROM `" . $this->databasePrefix . "atlas_favorite` WHERE `aid` ='$aid' AND `uid` ='$uid'";
		$result = @mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to remove atlas from favorite. We tried querying with the command %query.', array('%query' => $query)));
			return false;
		}
		return true;
	}
	/**
	 * 
	 */
	function db_get_atlas_cfg($uid = "", $aid){
		$query = "SELECT `variable`, `name`, `status` FROM " . $this->databasePrefix . "atlas WHERE "
			." `aid`='$aid' ".(empty($uid) || $uid == "0"?"":"AND `uid` = '$uid'")." LIMIT 1";
		@$result = mysql_query($query, $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to get atlas information data in db_get_atlas_cfg. We tried querying the command %query.', array('%query' => $query)));
			return false;
		} while ($info = $this->getColumns($result)) {
			if (!$info) {
				$this->databaseInfoHandle("Wrong atlas id or uid?");
				return false;
			}
			return $info;
		}
	}
	
	/**
	 * not used now
	 * @Description :Get the rows object group by one column
	 * @params : tablename: table name
	 * @params : column: column name for group
	 * @return : columns
	 */
	function getRowsGroupBy($tablename, $column)
		{
		@$rows = mysql_query("SELECT * FROM " . $this->databasePrefix . $tablename . " GROUP BY " . $column, $this->databaseConnection);
		if ($error = mysql_error())
			$this->databaseErrorHandle("Query error in getRowsGroupBy()");
		
		return $rows;
		}
	
	/**
	 *
	 * @Description :Get the rows from Featureclass by given layer
	 * @params : aid																										 	**
	 * @params : layer: where = '' in sql
	 * @return : rows																																				**
	 */
	function getRowsByLayer($aid, $currentlayer)
		{
		$query = "SELECT * FROM " . $this->databasePrefix . "featureclass  WHERE `layer` = '" . $currentlayer . "' "
			.(empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'");
		@$rows = mysql_query($query, $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Query error in getRowsByLayer(). We tried querying with the command %query.', array('%query' => $query)));
			return false;
		}
		return $rows;
		}
	
	/**
	 *
	 * @Description :Get the rows from Featuregeometry by given layer
	 * @params : aid
	 * @params : layer: where = '' in sql
	 * @return : rows
	 */
	function getRows4GeomByLayer($aid, $currentlayer)
		{
		$query = "SELECT * FROM " . $this->databasePrefix . "featuregeometry  WHERE `layer` = '" . $currentlayer . "' "
			.(empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'");
		@$rows = mysql_query($query, $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Query error in getRows4GeomByLayer(). We tried querying with the command %query.', array('%query' => $query)));
			return false;
		}
		return $rows;
		}
	
	/**
	 *
	 * @Description : Create all tables for suas
	 * @params : $tables_sql: sql from tables.php in Models
	 * @params : $tablenamePrefix: table name prefix
	 * @return true if successful
	 * @usage : install/3.php
	 */
	function createTablesForSUAS($tables_sql, $tablenamePrefix)
		{
		// foreach ($tables_sql as $sql) {
		for($i = 0; $i < 30/*count($tables_sql)*/; $i++) {
			if (!empty($tables_sql[$i])) {
				// Replace with the user defined tablename
				$query = str_replace(array("%SUAS%"), array($tablenamePrefix), $tables_sql[$i]);
				@$result = mysql_query($query);
				if ($error = mysql_error()) {
					$this->databaseErrorHandle(t('Failed to create all tables for SUAS. We tried querying with the command %query.', array('%query' => $query)));
					return false;
				}
			}
		}
		return true;
		}
	
	/**
	 *
	 * @Description : drop all tables for suas
	 * @params : $tablenamePrefix: table name prefix
	 * @return true if successful
	 * @usage : install/2b.php
	 */
	function dropTablesForSUAS($drop_tables_sql, $tablenamePrefix)
		{
		$query = str_replace(array("%SUAS%"), array($tablenamePrefix), $drop_tables_sql);
		@$result = mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to drop all tables for SUAS. We tried querying with the command %query.', array('%query' => $query)));
			return false;
		} else {
			return true;
		}
		}
	
	/**
	 *
	 * @Description : empty table for suas table (featuregeometry, featureclass)
	 * @params : $tablenamePrefix: table name prefix
	 * @return true if successful
	 * @usage : setting/s2aempty.php
	 */
	function emptyTableForSUAS($tablenamePrefix)
		{
		if (!$this->makeTableEmpty($tablenamePrefix . mapTableFeaturegeometry))
			return false;
		if (!$this->makeTableEmpty($tablenamePrefix . mapTableFeatureclass))
			return false;
		
		return true;
		}
	
	function getSUASVersion()
		{
		$sql = "SELECT value FROM " . $this->databasePrefix . "variable WHERE `name` = 'site_version' LIMIT 1";
		@$result = mysql_query($sql, $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle("Query error in getSUASVersion");
			return false;
		} else {
			while ($row = $this->getColumns($result)) {
				$version = gsd($row['value']);
				return $version;
			}
		}
		}
	
	/**
	 *
	 * @Description : get the status of all the tables
	 * @return recordset when successful, false if failed
	 * @output structure: 18 rows
	 * Name  Engine  Version  Row_format  Rows  Avg_row_length  Data_length  Max_data_length
	 * Index_length  Data_free  Auto_increment  Create_time  Update_time  Check_time
	 * Collation  Checksum  Create_options  Comment
	 */
	function showTableStatus()
		{
		$result = @mysql_query("SHOW TABLE STATUS;", $this->databaseConnection);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle("Query error in ShowTableStatus");
			return false;
		}
		
		return $result;
		}
	
	/**
	 *
	 * @Description : get the datail inforamtion of table
	 * @params : $table: table name
	 * @return array for Rows, Data_length,Create_time, Update_time, or array with "unknown" if failed
	 * @usage :
	 */
	function getTableDetailInformation($tablename)
		{
		$result = $this->showTableStatus();
		$row = $this->getColumns($result);
		$array = array();
		
		$array[0] = "unknown";
		$array[1] = "unknown";
		$array[2] = "unknown";
		$array[3] = "unknown";
		// the while here is not loop with the count of row(here is 18x2=36), but the tables' number in the database
		// why 18x2? because the id and field name are both stored, ig. 0, Nmae, 1 , Engine, 2, Version............
		while ($row = $this->getColumns($result)) {
			$tem = $row["Name"];
			if ($tem == $tablename) {
				$array[0] = $row["Rows"];
				$array[1] = round($row["Data_length"] / 1024 + $row["Index_length"] / 1024);
				$array[2] = $row["Create_time"];
				$array[3] = $row["Update_time"];
				return $array;
			}
		}
		return $array;
		}
	
	/*
	 function db_size_info($dbsize) {
	 $bytes = array('KB', 'KB', 'MB', 'GB', 'TB');
	 if ($dbsize < 1024) $dbsize = 1;
	 for ($i = 0; $dbsize > 1024; $i++) $dbsize /= 1024;
	 $db_size_info['size'] = ceil($dbsize);
	 $db_size_info['type'] = $bytes[$i];
	 return $db_size_info;
	 }
	 // Database size = table size + index size:
	  $rows = db_query("SHOW TABLE STATUS");
	  $dbssize = 0;
	  while ($row = mysql_fetch_array($rows)) {
	  $dbssize += $row['Data_length'] + $row['Index_length'];
	  }
	  print "$dbssize bytes<br />";
	  $dbssize = db_size_info($dbssize);
	  print "or<br />";
	  print "{$dbssize['size']} {$dbssize['type']}";
	  */
	
	/**
	 *
	 * @Description : check if the table has the standard fields inside or not
	 * @params : $table: table name
	 * @return true if successful
	 * @usage : install/2b.php
	 */
	function TableHasSameFields($tablename)
		{
		try {
			$arrayfields = explode("|", mapTableFieldsFeaturegeometry);
			$length = count($arrayfields);
			$number = 0;
			$columns = $this->getColumnsFromTable($tablename);
			// $row is every column in the table, and $rows has 12 description for it
			while ($row = $this->getColumns($columns)) {
				$tem = $row["Field"];
				// echo $row["Type"];
				for($i = 0;$i < $length;$i++) {
					if ($tem != $arrayfields[$i])
						$number++;
				}
				if ($number == $length)
					return false;
				$number = 0;
				// if ($tem != "id" && $tem != "layer" && $tem != "recid" && $tem != "geomtype" && $tem != "xmin" && $tem != "ymin" && $tem != "xmax" && $tem != "ymax" && $tem != "geom" && $tem != "svgxlink" && $tem != "srs" && $tem != "attributes" && $tem != "style") {
				// return false;
				// }
			}
		}
		catch(Exception $e) {
			$this->databaseErrorHandle("Error in TableHasSameFields.");
			return false;
		}
		return true;
		}
	
	/**
	 *
	 * @Description : crate one new atlas record
	 * @params : $uid, $key, $info
	 * @return aid if succussful, 0 if failed
	 * @usage :
	 */
	function db_create_atlas($uid, $key, $info)
		{
		$fields = "`uid` ,`key` ,`variable`,`status` ,`name` ,`title` ,`abstract` ,`layertitle` ,
			`keyword1` ,`keyword2` ,`keyword3` ,`keyword4` ,`person` ,`organization` ,`position` ,`contactaddress`,
			`addresstype` ,`address` ,`city` ,`stateorprovince` ,`postcode` ,`country` ,`phone` ,`mail` ,
			`fees` ,`accessconstraints` ,`type`, `created`, `modified`";
		
		$cfg['enablestretchmap'] = DEFAULT_VALUE_enablestretchmap;
		$cfg['showCopyright'] = DEFAULT_VALUE_showCopyright;
		$cfg['cacheExpiredTime'] = DEFAULT_VALUE_cacheExpiredTime;
		$cfg['enableSVGPixelCoordinate'] = DEFAULT_VALUE_enableSVGPixelCoordinate;
		$cfg['enableStreamSVG'] = DEFAULT_VALUE_enableStreamSVG;
		$cfg['outputEncodeCountry'] = DEFAULT_VALUE_outputEncodeCountry;
		$cfg['OverlapRatio'] = DEFAULT_VALUE_OverlapRatio;
		$cfg['GetMap25DOverlapRatio'] = DEFAULT_VALUE_GetMap25DOverlapRatio;
		$cfg['GoogleMapKey'] = "";
		
		include_once '../parser/AttributeParser.class.php';
		$variable = AttributeParser::getAttributeFromArray($info);
		
		$values = "'" . $uid . "' ,'" . $key . "' ,'".$variable."' ,'".DEFAULT_VALUE_AtlasStatus."' ,'" . $info['AtlasName'] . "' ,'" . $info['ServerTitle'] . "' ,'" . $info['ServerAbstract'] . "' ,'" . $info['LayerTitle'] . "' ,
			'" . $info['Keyword1'] . "' ,'" . $info['Keyword2'] . "' ,'" . $info['Keyword3'] . "' ,'" . $info['Keyword4'] . "' ,'" . $info['ContactPerson'] . "' ,'" . $info['ContactOrganization'] . "' ,'" . $info['ContactPosition'] . "' ,'" . $info['ContactAddress'] . "' ,
			'" . $info['AddressType'] . "' ,'" . $info['Address'] . "' ,'" . $info['City'] . "' ,'" . $info['StateOrProvince'] . "' ,'" . $info['PostCode'] . "' ,'" . $info['Country'] . "' ,'" . $info['ContactVoiceTelephone'] . "' ,'" . $info['ContactElectronicMailAddress'] . "' ,
			'" . $info['ServerFee'] . "' ,'" . $info['ServerAccessconstraints'] . "' ,'" . $info['ServerType'] . "'" .
			", '".time()."', '".time()."'";
		
		$query = "INSERT INTO " . $this->databasePrefix . "atlas (" . $fields . ") VALUES (" . $values . ")";
		$result = @mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to create new Atlas <b>%name</b>. We tried querying with the command %query.', array('%name' => $info['AtlasName'], '%query' => $query)));
			return false;
		} else {
			$query = "SELECT aid FROM " . $this->databasePrefix . "atlas WHERE  `key` = '$key'";
			$result = @mysql_query($query);
			while ($row = $this->getColumns($result)) {
				return $row['aid'];
			}
			return 0;
		}
		}
	
	function db_drop_atlas($uid, $aid)
		{
		//delete layers geometry firstly
		$query = "DELETE FROM `" . $this->databasePrefix . "featuregeometry` WHERE `aid` ='$aid' ";
		$result = @mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to delete geometry for atlas. We tried querying with the command %query.', array('%query' => $query)));
			return false;
		}
		
		//continue to layers featureclass
		$query = "DELETE FROM `" . $this->databasePrefix . "featureclass` WHERE `aid` ='$aid' ";
		$result = @mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to delete featureclass for atlas. We tried querying with the command %query.', array('%query' => $query)));
			return false;
		}
		
		//continue to delete style
		$query = "DELETE FROM `" . $this->databasePrefix . "style` WHERE `aid` ='$aid' ";
		$result = @mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to delete style for atlas. We tried querying with the command %query.', array('%query' => $query)));
			return false;
		}
		
		//continue to delete atlas record
		$query = "DELETE FROM `" . $this->databasePrefix . "atlas` WHERE `aid` ='$aid' AND `uid` ='$uid'";
		$result = @mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to delete atlas. We tried querying with the command %query.', array('%query' => $query)));
			return false;
		}
		return true;
		}
	
	function db_update_atlas($uid = "", $aid, $info)
		{
		$condition = "`name` = '" . $info['AtlasName'] . "',
			`title` = '" . $info['ServerTitle'] . "',
			`abstract` = '" . $info['ServerAbstract'] . "',
			`layertitle` = '" . $info['LayerTitle'] . "',
			`keyword1` = '" . $info['Keyword1'] . "',
			`keyword2` = '" . $info['Keyword2'] . "',
			`keyword3` = '" . $info['Keyword3'] . "',
			`keyword4` = '" . $info['Keyword4'] . "',
			`person` = '" . $info['ContactPerson'] . "',
			`organization` = '" . $info['ContactOrganization'] . "',
			`position` = '" . $info['ContactPosition'] . "',
			`contactaddress` = '" . $info['ContactAddress'] . "',
			`addresstype` = '" . $info['AddressType'] . "',
			`address` = '" . $info['Address'] . "',
			`city` = '" . $info['City'] . "',
			`stateorprovince` = '" . $info['StateOrProvince'] . "',
			`postcode` = '" . $info['PostCode'] . "',
			`country` = '" . $info['Country'] . "',
			`phone` = '" . $info['ContactVoiceTelephone'] . "',
			`mail` = '" . $info['ContactElectronicMailAddress'] . "',
			`fees` = '" . $info['ServerFee'] . "',
			`accessconstraints` = '" . $info['ServerAccessconstraints'] . "',
			`modified` = '" . time() . "',
			`type` = '" . $info['ServerType'] . "'";
		
		$query = "UPDATE  " . $this->databasePrefix . "atlas SET $condition WHERE `aid` ='$aid' " .
			(empty($uid) || $uid == "0"?"":"AND `uid` = '$uid'"). " LIMIT 1";
		@mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to update Atlas <b>%name</b>. We tried querying with the command %query.', array('%name' => $info['AtlasName'], '%query' => $query)));
			return false;
		}
		return true;
		}
	
	/**
	 * update the cfg for atlas
	 */
	function db_update_atlas_cfg($uid = "", $aid, $info)
		{
		$condition = "`variable` = '" . $info['variable'] . "', `status` = '". $info['status'] ."'" .
			", `modified` = '".time()."'";
		
		$query = "UPDATE  " . $this->databasePrefix . "atlas SET $condition WHERE `aid` ='$aid' " .
			(empty($uid) || $uid == "0"?"":"AND `uid` = '$uid'"). " LIMIT 1";
		@mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to update Atlas <b>%name</b> Configuration. We tried querying with the command %query.', array('%name' => $info['AtlasName'], '%query' => $query)));
			return false;
		}
		return true;
		}
	
	/**
	 * update only the modified time column
	 */
	function db_update_atlas_modified_time($aid){
		$condition = "`modified` = '".time()."'";
		
		$query = "UPDATE  " . $this->databasePrefix . "atlas SET $condition WHERE `aid` ='$aid' " .
			(empty($uid) || $uid == "0"?"":"AND `uid` = '$uid'"). " LIMIT 1";
		@mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Error  in db_update_atlas_modified_time. We tried querying with the command %query.', array( '%query' => $query)));
			return false;
		}
		return true;
	}
	
	/**
	 * if stid is null value, save new style and set the style as default style for atlas
	 * if stid is given, overwrite the old style with new style
	 * if use save as option, save the style as new style
	 */
	function db_save_style($aid, $name, $style, $stid = 0, $saveas = false)
		{
		if (!$saveas) {
			// if exists not, insert new record, and set the style id for atlas
			if ($stid == 0) {
				$fields = "`aid` ,`name` , `style`";
				$values = "'" . $aid . "' ,'" . $name . "','" . $style . "'";
				$query = "INSERT INTO " . $this->databasePrefix . "style (" . $fields . ") VALUES (" . $values . ")";
				@mysql_query($query);
				if ($error = mysql_error()) {
					$this->databaseErrorHandle(t('Failed to create new style <b>%name</b>. We tried querying with the command %query.', array('%name' => $name, '%query' => $query)));
					return false;
				} else {
					$query = "SELECT MAX(stid) FROM " . $this->databasePrefix . "style WHERE `aid`= '$aid' LIMIT 1";
					$result = @mysql_query($query);
					// normally, it will have no error
					while ($rec = $this->getColumns($result)) {
						$stid_ = $rec[0];
					}
					// then change stid in atlas table
					$query = "UPDATE " . $this->databasePrefix . "atlas SET `stid`='" . $stid_ . "' WHERE `aid`='$aid' LIMIT 1";
					@mysql_query($query);
					if ($error = mysql_error()) {
						$this->databaseErrorHandle(t('Failed to set style id for Atlas. We tried querying with the command %query.', array('%query' => $query)));
						return false;
					}
					return true;
				}
			}
			// save the existed style
			else {
				// use the default style name, dont change name
				if ($name == "use_exist_style_name") {
					$condition = "`style` = '" . $style . "'";
				} else {
					$condition = "`name` = '" . $name . "',`style` = '" . $style . "'";
				}
				$query = "UPDATE  " . $this->databasePrefix . "style SET $condition WHERE `stid` ='$stid'  LIMIT 1";
				@mysql_query($query);
				if ($error = mysql_error()) {
					$this->databaseErrorHandle(t('Failed to save style <b>%name</b>. We tried querying with the command %query.', array('%name' => $name, '%query' => $query)));
					return false;
				}
				return true;
			}
		} else {
			$fields = "`aid` ,`name` , `style`";
			$values = "'" . $aid . "' ,'" . $name . "','" . $style . "'";
			$query = "INSERT INTO " . $this->databasePrefix . "style (" . $fields . ") VALUES (" . $values . ")";
			@mysql_query($query);
			if ($error = mysql_error()) {
				$this->databaseErrorHandle(t('Failed to save style as <b>%name</b>. We tried querying with the command %query.', array('%name' => $name, '%query' => $query)));
				return false;
			}
			return true;
		}
		}
	
	function db_change_style_name($aid, $stid, $newname)
		{
		$condition = "`name` = '" . $newname . "'";
		$query = "UPDATE  " . $this->databasePrefix . "style SET $condition WHERE `aid` = '$aid' AND `stid` ='$stid'  LIMIT 1";
		@mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to change style name to <b>%newname</b>. We tried querying with the command %query.', array('%name' => $newname, '%query' => $query)));
			return false;
		}
		return true;
		}
	
	/**
	 * Set this style as default style for Atlas
	 */
	function db_set_default_style($aid, $stid)
		{
		$query = "UPDATE " . $this->databasePrefix . "atlas SET `stid`='" . $stid . "' WHERE `aid`='$aid' LIMIT 1";
		@mysql_query($query);
		if ($error = mysql_error()) {
			$this->databaseErrorHandle(t('Failed to set style %name as default style for Atlas. We tried querying with the command %query.', array('%name' => $name, '%query' => $query)));
			return false;
		}
		return true;
		}
	
	function db_delete_style($aid, $stid)
		{
		$query = "SELECT stid FROM " . $this->databasePrefix . "atlas WHERE `aid`= '$aid' LIMIT 1";
		$result = @mysql_query($query);
		// normally, it will have no error
		while ($rec = $this->getColumns($result)) {
			$stid_ = $rec[0];
		}
		if ($stid == $stid_) {
			$this->databaseErrorHandle(t('Can not delete the default style for Atlas. We tried querying with the command %query.', array('%query' => $query)));
			return false;
		} else {
			$query = "DELETE FROM " . $this->databasePrefix . "style WHERE `aid` ='$aid' and `stid` = $stid";
			$result = @mysql_query($query);
			if ($error = mysql_error()) {
				$this->databaseErrorHandle(t('Failed to delete this style for Atlas. We tried querying with the command %query.', array('%query' => $query)));
				return false;
			}
			return true;
		}
		}
	
	private static $default_sld_xml = '<?xml version="1.0" encoding="utf-8"?>
<StyledLayerDescriptor version="1.0.0" xmlns:ogc="http://www.opengis.net/ogc" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <NamedLayer>
    <Name></Name>
    <UserStyle>
      <Name></Name>
      <Title></Title>
      <IsDefault>1</IsDefault>
      <FeatureTypeStyle>
        <Rule>
          <Name></Name>
          <Title></Title>
        </Rule>
      </FeatureTypeStyle>
    </UserStyle>
  </NamedLayer>
</StyledLayerDescriptor>';

/**
 * get the default style for one atlas, if not exist, return empty xml
 */
function db_get_default_style($aid)
	{
	$query = "SELECT a.stid, s.style, s.name AS name FROM " . $this->databasePrefix . "style AS s, " . $this->databasePrefix . "atlas AS a WHERE a.aid = '$aid' AND s.stid = a.stid LIMIT 1";
	$result = @mysql_query($query);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Failed to get default style for Atlas. We tried querying with the command %query.', array('%query' => $query)));
		$style['stylename'] = '';
		$style['style'] = self::$default_sld_xml;
		return $style;
	} 
	while ($rec = $this->getColumns($result)) {
		$style['stylename'] = $rec['name'];
		$style['style'] = $rec['style'];
	}
	if (empty($style)) {
		$style['stylename'] = '';
		$style['style'] = self::$default_sld_xml;
		return $style;
	}
	return $style;
	}

function db_get_single_style($aid, $stid)
	{
	$query = "SELECT name, style FROM " . $this->databasePrefix . "style WHERE `aid`= '$aid' AND `stid`= '$stid' LIMIT 1";
	$result = @mysql_query($query);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Failed to get style for Atlas. We tried querying with the command %query.', array('%query' => $query)));
		$style['stylename'] = '';
		$style['style'] = self::$default_sld_xml;
		return $style;
	} 
	while ($rec = $this->getColumns($result)) {
		$style['stylename'] = $rec['name'];
		$style['style'] = $rec['style'];
	}
	//if stid = 0
	if (empty($style)) {
		$style['stylename'] = '';
		$style['style'] = self::$default_sld_xml;
		return $style;
	}
	return $style;
	}

/**
 * return the default stid and the stid records list
 */
function db_get_stylelist($aid)
	{
	// get the default stid also
	$query = "SELECT stid FROM " . $this->databasePrefix . "atlas WHERE `aid`= '$aid' LIMIT 1";
	$result = @mysql_query($query);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Failed to get default style id. We tried querying with the command %query.', array('%query' => $query)));
		return false;
	} else {
		while ($rec = $this->getColumns($result)) {
			$stids['stid_default'] = $rec['stid'];
		}
	}
	$query = "SELECT * FROM " . $this->databasePrefix . "style WHERE `aid`= '$aid' ORDER BY name";
	$result = @mysql_query($query);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Failed to get style list. We tried querying with the command %query.', array('%query' => $query)));
		return false;
	}
	$stids['stid_records'] = $result;
	return $stids;
	}
// ======================================For Metadata==========================================================================
/**
 *
 * @Description :Get the rows group by one column
 * @params : aid:
 * @params : column: column name for group
 * @return : columns
 */
function getRows4MetaGroupBy($aid = "", $column)
	{
	$query = "SELECT * FROM " . $this->databasePrefix . "featureclass WHERE `visiable` = '1' "
		.(empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'"). " GROUP BY " . $column;
	@$rows = mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle("Query error in getRows4MetaGroupBy");
		return false;
	}
	
	return $rows;
	}

/**
 *
 * @Description :Get the rows by srs
 * @params : $currentsrs: where = '' in sql
 * @params : $columnname: sql will group by one column name, for example 'layer', then select the rows that has no repeat layername
 * @return : rows
 */
function getRowsBySrsGroupBy($aid = "", $currentsrs, $columnname)
	{
	@$rows = mysql_query("SELECT * FROM " . $this->databasePrefix . "featureclass WHERE `srs` = '" . $currentsrs
		. "' AND `visiable` = '1' " . (empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'") . " GROUP BY " . $columnname, $this->databaseConnection);
	if ($error = mysql_error())
		$this->databaseErrorHandle("Query error in getRowsBySrsGroupBy");
	
	return $rows;
	}

/**
 *
 * @Description :Get the rows by srs
 * @params : tablename: table name(for featureclass  and featuregeometry table)
 * @params : $currentsrs: where = '' in sql
 * @params : $column: one column, for example layer, then select the rows that has no repeat layername
 * @return : rows																			     *
 */
function getLayersBySrsGroupByLayer($aid = "", $currentsrs, $isFeaturegeometry)
	{
	if ($isFeaturegeometry) {
		$query = "SELECT DISTINCT layer FROM " . $this->databasePrefix . "featuregeometry WHERE srs = '" . $currentsrs . "'"
			.(empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'")." GROUP BY layer";
	} else {
		$query = "SELECT DISTINCT layer FROM " . $this->databasePrefix . "featureclass WHERE srs = '" . $currentsrs . "'"
			.(empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'")." GROUP BY layer";
	}
	@$rows = mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error())
		$this->databaseErrorHandle("Query error in getLayersBySrsGroupByLayer");
	
	return $rows;
	}

/**
 *
 * @Description :Get the rows of minx miny maxx maxy for all layers and srss
 * @params : tablename: table name(for featureclass table)
 * @return : rows
 */
function getRowsMinMaxXY($aid = "")
	{
	@$rows = mysql_query("SELECT MIN(xmin), MIN(ymin), MAX(xmax), MAX(ymax) FROM "
		. $this->databasePrefix . "featureclass WHERE visiable = '1' ".(empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'") , $this->databaseConnection);
	if ($error = mysql_error())
		$this->databaseErrorHandle("Query error in getRowsMinMaxXY");
	
	return $rows;
	}

/**
 *
 * @Description :Get the rows of minx miny maxx maxy which srs is given
 * @params : tablename: table name(for featureclass table)
 * @params : currentsrs: current srs name
 * @return : rows
 */
function getRowsMinMaxXYBySrs($aid = "", $currentsrs)
	{
	@$rows = mysql_query("SELECT MIN(`xmin`), MIN(`ymin`), MAX(`xmax`), MAX(`ymax`) FROM " . $this->databasePrefix
		. "featureclass WHERE `visiable` = '1' AND `srs` = '$currentsrs' " . (empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'"), $this->databaseConnection);
	if ($error = mysql_error())
		$this->databaseErrorHandle("Query error in getRowsMinMaxXYBySrs");
	
	return $rows;
	}

/**
 *
 * @Description :Get the rows of minx miny maxx maxy which layer is given, Only used for GetFeatureInfo
 * @params : tablename: table name(for featureclass table)
 * @params : currentsrs: current layer name
 * @return : rows
 */
function getRowsMinMaxXYByLayer($aid = "", $currentlayer)
	{
	$query = "SELECT MIN(`xmin`), MIN(`ymin`), MAX(`xmax`), MAX(`ymax`) FROM `" . $this->databasePrefix
		. "featureclass` WHERE `visiable` = '1' AND `layer` = '$currentlayer' " . (empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'");
	@$rows = mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Query error in getRowsMinMaxXYByLayer(). We tried querying with the command %query.', array('%query' => $query)));
	}
	return $rows;
	}

/**
 *
 * @Description :Get the rows of minx miny maxx maxy which srs and layer is given
 * @params : tablename: table name
 * @params : currentsrs: current srs name
 * @params : currentlayername: current layer name
 * @return : rows
 */
function getRowsMinMaxXYBySrsLayer($aid = "", $currentsrs, $currentlayername, $isFeaturegeometry)
	{
	if ($isFeaturegeometry) {
		$query = "SELECT MIN(`xmin`), MIN(`ymin`), MAX(`xmax`), MAX(`ymax`) FROM `" . $this->databasePrefix . "featuregeometry` "
			. "WHERE `srs` = '$currentsrs' AND `layer` ='$currentlayername' " . (empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'");
	} else {
		$query = "SELECT MIN(`xmin`), MIN(`ymin`), MAX(`xmax`), MAX(`ymax`) FROM `" . $this->databasePrefix . "featureclass` "
			. "WHERE `srs` = '$currentsrs' AND `layer` ='$currentlayername' " . (empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'");
	}
	@$rows = mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Query error in getRowsMinMaxXYBySrsLayer(). We tried querying with the command %query.',
			array('%query' => $query)));
	}
	return $rows;
	}

/**
 *
 * @Description :Get the rows when layer and srs is given
 * @params : currentsrs: current srs name
 * @params : currentlayername: current layer name
 * @return : rows
 */
function getRowsBySrsLayer($aid = "", $currentsrs, $currentlayername)
	{
	@$rows = mysql_query("SELECT * FROM `" . $this->databasePrefix
		. "featureclass` where `visiable` = '1' AND `srs` = '$currentsrs' AND `layer` ='$currentlayername' " . (empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'"), $this->databaseConnection);
	if ($error = mysql_error())
		$this->databaseErrorHandle("Query error in getRowsBySrsLayer");
	
	return $rows;
	}

/**
 *
 * @Description :Get the layers' priority array
 * @params : currentsrs: current srs name
 * @params : layerarray: layers array
 * @return : priority array
 * @TODO could use select .... layer in ();?
 */
function getPriorityArray($aid, $currentsrs, $layerarray)
	{
	$numberofvalueslayer = count($layerarray);
	for ($i = 0; $i < $numberofvalueslayer; $i++) {
		@$rows = mysql_query("SELECT priority FROM `" . $this->databasePrefix
			. "featureclass` where visiable = '1' AND srs = '$currentsrs' AND layer ='$layerarray[$i]' ". (empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'"), $this->databaseConnection);
		if ($error = mysql_error())
			$this->databaseErrorHandle("Query error in getPriorityArray");
		
		$line = $this->getColumns($rows);
		$arrayPriority[$i] = $line[0];
	}
	return $arrayPriority;
	}

/**
 *
 * @Description :Get the rows where layer is currentlayer and group by layer and srs
 * @params : tablename: table name(for featureclass table)
 * @params : $currentlayername: current layername
 * @return : rows																																				**
 */
function getRowsByLayerGroupBy($aid, $currentlayername, $columns)
	{
	@$rows = mysql_query("SELECT * FROM  " . $this->databasePrefix . "featureclass  WHERE `visiable` = '1' AND `layer` = '$currentlayername' "
		.(empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'")." GROUP BY $columns", $this->databaseConnection);
	if ($error = mysql_error())
		$this->databaseErrorHandle("Query error in getRowsByLayerGroupBy");
	
	return $rows;
	}
// ======================================For Metadata==========================================================================
/**
 * For what?
 */
function getRowsInBboxBySrsLayer($tablename, $minx, $miny, $maxx, $maxy, $currentsrs, $currentlayername)
	{
	@$rows = mysql_query("SELECT * FROM  $tablename  WHERE xmin >= $minx AND ymin >= $miny AND xmax <= $maxx AND ymax <= $maxy AND srs = '$currentsrs' AND layer ='$currentlayername'", $this->databaseConnection);
	if ($error = mysql_error())
		$this->databaseErrorHandle("Query error in getRowsInBboxBySrsLayer");
	
	return $rows;
	}

/**
 * special for Getfeature create GML
 */
function getGeomAsTextBySrsLayer($aid = "", $currentsrs, $currentlayername, $orderby = "id")
	{
	$query = "SELECT id,layer,recid,geomtype,xmin,ymin,xmax,ymax,AsText(geom),xlink,srs,attributes FROM  `"
		. $this->databasePrefix . "featuregeometry` WHERE  srs = '$currentsrs' AND layer ='$currentlayername' ". (empty($aid) || $aid == "0"?"":"AND `aid` = '$aid' ")
		.(empty($orderby)?"":"ORDER BY $orderby ");
	@$rows = mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error()){
		$this->databaseErrorHandle(t('Query error in getGeomAsTextBySrsLayer(). We tried querying with the command %query.',
			array('%query' => $query)));
	}
	return $rows;
	}

/**
 * For featuregeometry
 * Using BBOX spatial analysis to select bbox, faster but can not display the geometies completly
 *
 * Using WKT spatial analysis to select bbox, can display the geometies completly but slowly
 */
function getGeomAsTextInBboxBySrsLayer($aid = "", $minx, $miny, $maxx, $maxy, $currentsrs, $currentlayername)
	{
	if (blnGetMap25D) $ratio = GetMap25DOverlapRatio;
	else $ratio = OverlapRatio;
	// $rows = mysql_query("SELECT id,layer,recid,geomtype,xmin,ymin,xmax,ymax,AsText(geom),svgxlink,srs,attributes,style FROM  $tablename  WHERE xmin >= $minx AND ymin >= $miny AND xmax <= $maxx AND ymax <= $maxy AND srs = '$currentsrs' AND layer ='$currentlayername'", $this->databaseConnection);
	$distanceX = $maxx - $minx;
	$distanceY = $maxy - $miny;
	$overlapX = $distanceX * $ratio;
	$overlapY = $distanceY * $ratio;
	$x0 = $minx - $overlapX;
	$y0 = $miny - $overlapY;
	$x1 = $minx - $overlapX;
	$y1 = $maxy + $overlapY;
	$x2 = $maxx + $overlapX;
	$y2 = $maxy + $overlapY;
	$x3 = $maxx + $overlapX;
	$y3 = $miny - $overlapY;
	
	$query = "SET @bbox = GeomFromText('Polygon(($x0 $y0,$x1 $y1,$x2 $y2,$x3 $y3,$x0 $y0))')";
	//echo $query;
	@mysql_query($query, $this->databaseConnection);
	//  @mysql _query("SET @bbox = GeomFromText('Polygon(($minx $miny,$minx $maxy,$maxx $maxy,$maxx $miny,$minx $miny))')", $this->databaseConnection);
	$query = "SELECT `id`,`layer`,`recid`,`geomtype`,`xmin`,`ymin`,`xmax`,`ymax`,AsText(`geom`),`xlink`,`srs`,`attributes` FROM  `"
		. $this->databasePrefix . "featuregeometry` WHERE MBRIntersects(geom,@bbox) = '1' AND `srs` = '$currentsrs' AND `layer` ='$currentlayername' "
		. (empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'");
	@$rows = mysql_query($query, $this->databaseConnection);
	//echo $query;
	if ($error = mysql_error()){
		$this->databaseErrorHandle(t('Query error in getGeomAsTextInBboxBySrsLayer(). We tried querying with the command %query.',
			array('%query' => $query)));
	}
	
	return $rows;
	}

/**
 *
 * @Description :Get the rows in BBox and in the select square where layer is currentlayer in GetFeatureInfo
 * @params : aid
 * @params : minx,miny,maxx,maxy: BBox
 * @params : x_plus,x_minus,y_plus,y_minus: select point square
 * @params : currentlayername: current layername
 * @return : rows																																				**
 */
function getSelectFeatureInBoxBy($aid, $minx, $miny, $maxx, $maxy, $x_plus, $x_minus, $y_plus, $y_minus, $currentlayername)
	{
	@$rows = mysql_query("SELECT * FROM  " . $this->databasePrefix . "featuregeometry WHERE xmin > $minx AND ymin > $miny AND xmax < $maxx AND ymax < $maxy AND xmin >= '$x_minus' AND xmax <= '$x_plus' AND ymin >= '$y_minus' AND ymax <= '$y_plus' AND layer = '" . $currentlayername . "'" .
		" ".(empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'"), $this->databaseConnection);
	if ($error = mysql_error())
		$this->databaseErrorHandle("Query error in getSelectFeatureInBoxBy");
	
	return $rows;
	}

/**
 *
 * @Description :Get the rows in the select square where layer is currentlayer in GetFeatureInfo
 * used for featuregeometry
 * @params : aid
 * @params : x_plus,x_minus,y_plus,y_minus: select point square
 * @params : currentlayername: current layername
 * @return : rows																																				**
 */
function getSelectFeatureInSquareBy($aid, $x_plus, $x_minus, $y_plus, $y_minus, $currentlayername)
	{
	// $rows = mysql_query("SELECT * FROM $tablename WHERE xmin >= '$x_minus' AND xmax <= '$x_plus' AND ymin >= '$y_minus' AND ymax <= '$y_plus' AND layer = '" . $currentlayername . "'", $this->databaseConnection);
	$query = "SET @square = GeomFromText('Polygon(($x_minus $y_minus,$x_minus $y_plus,$x_plus $y_plus,$x_plus $y_minus,$x_minus $y_minus))')";
	//echo  $query;
	@mysql_query($query, $this->databaseConnection);
	$query = "SELECT * FROM " . $this->databasePrefix . "featuregeometry WHERE MBRIntersects(geom,@square) = '1' AND layer = '$currentlayername'" .
		" ".(empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'");
	@$rows = mysql_query($query, $this->databaseConnection);
	//echo $query;
	if ($error = mysql_error())
		$this->databaseErrorHandle(t('Query error in getSelectFeatureInSquareBy(). We tried querying with the command %query.',
			array('%query' => $query)));
	return $rows;
	}

/**
 *
 * @Description :Get the rows by queryable layer
 * @params : layer: where = '' in sql
 * @return : rows																																				**
 */
function getRowsByQueryableLayer($aid, $currentlayer)
	{
	@$rows = mysql_query("SELECT * FROM " . $this->databasePrefix . "featureclass WHERE queryable = '1' AND layer = '" . $currentlayer . "'" .
		" ".(empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'"), $this->databaseConnection);
	if ($error = mysql_error())
		$this->databaseErrorHandle("Query error in getRowsByQueryableLayer");
	
	return $rows;
	}

/**
 *
 * @Description :Insert the records into database featuregeometry
 * @params : tablename: table name
 * @params : $values: field value list
 * @return : ture or false																																				**
 */
function databaseInsertGeometry($aid, $layer, $recid, $geomtype, $xmin, $ymin, $xmax, $ymax, $geom, $xlink, $srs, $attributes)
	{
	$fields = 'id,aid,layer,recid,geomtype,xmin,ymin,xmax,ymax,geom,xlink,srs,attributes';
	$values = "'NULL','" . $aid . "','" . $layer . "','" . $recid . "','" . $geomtype . "','" . $xmin . "','" . $ymin . "','" . $xmax . "','" . $ymax . "',GeomFromText('" . $geom . "'),'" . $xlink . "','" . $srs . "','" . $attributes . "'";
	$query = "INSERT INTO " . $this->databasePrefix . "featuregeometry (" . $fields . ") VALUES (" . $values . ")";
	// echo $sql."\n";
	$result = @mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error()) {
		$this->recordbad++;
		$this->log .= "Error($this->recordbad):<br/>" . $query . "<br/>";
		$this->databaseErrorHandle(t('Error when insert records into database. We tried querying with the command %query.', array('%query' => $query)));
	} else {
		$this->recordgood ++;
	}
	}

function getAidFromRS($rs)
	{
	return $rs["aid"];
	}
function getIdFromRS($rs)
	{
	return $rs["id"];
	}
function getRecidFromRS($rs)
	{
	return $rs["recid"];
	}
function getGeometryTextFromRS($rs)
	{
	return $rs[8];
	}
function getGeomtypeFromRS($rs)
	{
	return strtoupper($rs["geomtype"]);
	}
function getXlinkFromRS($rs)
	{
	return $rs["xlink"];
	}
function getAttributesFromRS($rs)
	{
	return $rs["attributes"];
	}
function getSrsFromRS($rs)
	{
	return $rs["srs"];
	}
// ==========================================For CSV input data===========================================================
/**
 *
 * @Description :Insert the records into database featuregeometry with LOAD DATA INFILE method
 * @params : tablename: table name
 * @params : $values: field value list
 * @return : ture or false																																				**
 */
/*
 function inputCSV2DatabaseOld($filename,$tablename,$csvTerminated,$csvEnclosed,$csvEscaped,$bCsvheader,$arrCsvColumns){
 $sql = "LOAD DATA INFILE '".@mysql_escape_string($filename).
 "' INTO TABLE `".$tablename.
 "` FIELDS TERMINATED BY '".@mysql_escape_string($csvTerminated).
 "' OPTIONALLY ENCLOSED BY '".@mysql_escape_string($csvEnclosed).
 "' ESCAPED BY '".@mysql_escape_string($csvEscaped).
 "' ".
 ($bCsvheader ? " IGNORE 1 LINES " : "")
 ."(`".implode("`,`", $arrCsvColumns)."`)";
 
 $result = @mysql_query($sql, $this->databaseConnection);
 if (!$result) {
 $this->databaseErrorHandle("Error when input CSV file into database!");
 }
 
 }
 */
/**
 *
 * @Description :Insert the records into database featuregeometry
 * @params : tablename: table name
 * @params : $values: field value list
 * @return : ture or false																																				**
 */
function inputCSV2Database($aid, $data_encode, $layername, $filename, $srsname, $useSrsName, 
						   $csvTerminated, $csvEnclosed, $csvEscaped, $bCsvheader, $arrCsvColumns)
	{
	// delete the ' in array
	$arrCsvColumns = str_replace('\'', ' ', $arrCsvColumns);
	$handle = fopen ($filename, "r");
	if ($handle) {
		// ignore the first line
		if ($bCsvheader) {
			$data = fgetcsv ($handle, 10 * 1024, $csvTerminated);
		}
		$query = "INSERT INTO " . $this->databasePrefix . "featuregeometry (" . implode(",", $arrCsvColumns) . ")" . "VALUES(";
		
		//setlocale(LC_ALL, 'de_DE.UTF8');
		while ($data = fgetcsv ($handle, 10 * 1024, $csvTerminated)) {
			$num = count ($data); //echo $num;
			for ($c = 0; $c < $num; $c++) {
				// Replace single quote ' with 2 singe quotes '' , the single quote will not change in the database
				$data[$c] = str_replace('\'', '\'\'', $data[$c]);
				// if id is empty, give it value NULL to be compatible MySQL 5.1 strict
				if ($data[0] == "") {
					$data[0] = "NULL";
				}
				$data[1] = $aid;
				if (!empty($layername)) {
					$data[3] = $layername;
				}
				if ($useSrsName && !empty($srsname)) {
					$data[5] = $srsname;
				}
				if(!empty($data_encode)){
					$data[12] = iconv($data_encode, "UTF-8//IGNORE", $data[12]);
				}                    
				if ($c == $num-1) {
					$query .= "'" . $data[$c] . "')";
					break;
				} else {
					// Geometry column 10
					if ($c == 10) {
						$data[$c] = "GeomFromText('" . $data[$c] . "')";
						$query .= $data[$c] . ",";
					} else{
						//avoid 'null' id for mysql
						if($c == 0 ){
							if(strtoupper($data[$c]) == "NULL"){
								$query .= "" . $data[$c] . ",";
							}else{
								$query .= "'" . $data[$c] . "',";
							}							
						}else{
							$query .= "'" . $data[$c] . "',";
						}
					}
				}
			}
			// echo $query."\n";
			$result = @mysql_query($query, $this->databaseConnection);
			if ($error = mysql_error()) {
				$this->recordbad++;
				$this->log .= "Error  $this->recordbad :" . $query . "";
				$this->databaseErrorHandle(t('Error when import CSV file into database. We tried querying with the command %query.', array('%query' => $query)));
			} else {
				$this->recordgood ++;
			}
			
			$query = "INSERT INTO " . $this->databasePrefix . "featuregeometry (" . implode(",", $arrCsvColumns) . ")" . "VALUES(";
		}
	}
	fclose ($handle);
	}
// ==========================================For CSV import data===========================================================
/**
 *
 * @Description :This function will escape the unescaped_string , so that it is safe to place it in a mysql_query()
 */
function getMysql_escape_string($filename)
	{
	$filename = @mysql_escape_string($filename);
	return $filename;
	}

/**
 * used in MIF2DB
 */
function updateTextAngle($xmin, $ymin, $xmax, $ymax, $geom, $recid, $attributes)
	{
	$query = "UPDATE `" . $this->databasePrefix . "featuregeometry` SET `attributes` = '$attributes',`xmin` = '$xmin',`ymin` = '$ymin',`xmax` = '$xmax', " . "`ymax` = '$ymax',`geom` = GeomFromText('" . $geom . "') WHERE `recid` = '$recid' LIMIT 1";
	$result = @mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error()) {
		$this->recordbad++;
		$this->log .= "Error  $this->recordbad :</br>" . $query . "</br>";
		$this->databaseErrorHandle(t('Error when insert Angle attribute for Text. We tried querying with the command %query.', array('%query' => $query)));
	}
	}

/**
 *
 * @Description :record log when meet error during the data inputting
 */
function getLog4Database()
	{
	$log = $this->log;
	return $log;
	}

/**
 *
 * @Description :Get the geometry as WKT test
 * @params : geometry object
 * @return : text																																				**
 */
function getGeometryAsText($geometry)
	{
	$text = @mysql_query("SELECT AsText($geometry)", $this->databaseConnection);
	
	if (!$text)
		$this->databaseErrorHandle("Error when read Geometry!");
	
	return $text;
	}
// ================================For Installation create style==============================================================
/**
 *
 * @Description :Get all the layer name from table featuregeometry, without repeat name!
 * @return :	layer names list																					    *
 */
function getAllLayersNames($aid = "")
	{
	$query = "SELECT `layer`,`geomtype` FROM `" . $this->databasePrefix . "featuregeometry` "
		.(empty($aid) || $aid == "0"?"":" WHERE `aid` = '$aid'")." GROUP by layer "
		;
	$layersnames = @mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Error in getAllLayersNames(). We tried querying with the command %query.', array('%query' => $query)));
		return false;
	}
	
	return $layersnames;
	}

/**
 *
 * @Description :Get all the layer name from table featuregeometry, without repeat name!
 * @params : tablename
 * @return :	layer names list																					    *
 */
function getAllLayersNamesInSrs($aid, $srsname)
	{
	$query = "SELECT layer, count(distinct `geomtype`) as typecount, geomtype FROM `" . $this->databasePrefix . "featuregeometry` WHERE aid = '$aid' AND srs = '$srsname' GROUP by layer";
	$layersnames = @mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Error in getAllLayersNamesInSrs(). We tried querying with the command %query.', array('%query' => $query)));
		return false;
	}
	
	return $layersnames;
	}

/**
 *
 * @Description :Get all the layer names by distint srs
 * @params : 
 * @return :	layer names list order by srs																			    *
 */
function getAllLayersSrss($aid)
	{
	//$query = "SELECT DISTINCT srs, layer as typecount FROM `" . $this->databasePrefix . "featuregeometry` WHERE aid = '$aid' ORDER BY srs";
	$query = "SELECT srs, layer, geomtype, count(distinct `geomtype`) as typecount FROM `" . $this->databasePrefix . "featuregeometry` WHERE aid = '$aid' GROUP BY srs, layer";
	
	$result = @mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Error in getAllLayersSrss(). We tried querying with the command %query.', array('%query' => $query)));
		return false;
	}
	
	return $result;
	}

/**
 *
 * @Description :Get all the srs by srs
 * @params : tablename: table name(for featuregeometry table)
 * @return : rows																							    *
 */
function getAllSrssNames($aid)
	{
	$query = "SELECT DISTINCT srs FROM `" . $this->databasePrefix . "featuregeometry` WHERE aid = '$aid'";
	@$rows = mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Error in getAllSrssNames(). We tried querying with the command %query.', array('%query' => $query)));
		return false;
	}
	
	return $rows;
	}

/**
 *
 * @Description :Get all the layer name from table featuregeometry, layer to srs:1 to n, srs to layer:1 to n
 * used in atlas.inc
 * @params : aid
 * @return :	layernames and srs list																					    *
 */
function getLayersNamesWithDiffSrs($aid)
	{
	$query = "SELECT DISTINCT layer,srs FROM `" . $this->databasePrefix . "featuregeometry` WHERE aid = '$aid'";
	$layersnamesrs = @mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Can not get layers\' names and srs, error in getLayersNamesWithDiffSrs(). We tried querying with the command %query.', array('%query' => $query)));
		return false;
	}
	
	return $layersnamesrs;
	}

/**
 *
 * @Description :make the old featureclass table empty for one atlas
 * @params : $tablename
 * @return :ture if successful																																		**
 */
function emptyLayerRecordsInFeatureClass($aid)
	{
	$query = "DELETE FROM `" . $this->databasePrefix . "featureclass` WHERE aid ='$aid'" ;
	$result = @mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Can not empty the records in featureclass table, error in emptyLayerRecordsInFeatureClass(). We tried querying with the command %query.', array('%query' => $query)));
		return false;
	}
	return true;
	}

/**
 *
 * @Description :make the old table empty
 * @params : $tablename
 * @return :ture if successful																																		**
 */
function makeTableEmpty($tablename)
	{
	$query = "TRUNCATE TABLE $tablename";
	$result = @mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Can not empty the table %tablename, error in makeTableEmpty(). We tried querying with the command %query.', array('%query' => $query, '%tablename' => $tablename)));
		return false;
	}
	return true;
	}

/**
 *
 * @Description :Insert the records into database featureclass
 * @params : tablename: table name
 * @params : $values: field value list
 * @return : ture or false
 */
function createFeatureClassMetadata($aid, $layertype, $layer, $description, $geomtype, $xmin, $ymin, $xmax, $ymax, $srs, $styleid, $queryable, $visiable, $priority, $elevation)
	{
	$fields = 'aid,stid,layertype,layer,description,geomtype,xmin,ymin,xmax,ymax,srs,queryable,visiable,priority,elevation';
	$values = "'" . $aid . "','" . $styleid . "','" . $layertype . "','" . $layer . "','" . $description . "','" . $geomtype . "','" . $xmin . "','" . $ymin . "','" . $xmax . "','" . $ymax . "','" . $srs . "','" . $queryable . "','" . $visiable . "','" . $priority . "','" . $elevation . "'";
	$query = "INSERT INTO `" . $this->databasePrefix . "featureclass` (" . $fields . ") VALUES (" . $values . ")";
	$result = @mysql_query($query, $this->databaseConnection);
	
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Failed to create layer info data in featureclass in createFeatureClassMetadata(). We tried querying with the command %query.', array('%query' => $query)));
		return false;
	}        
	return true;
	}

function deleteLayersInSrs($tablename, $srs)
	{
	$result = @mysql_query("DELETE FROM $tablename WHERE srs ='$srs'", $this->databaseConnection);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle("Can not delete layers in srs " . $srs);
		return false;
	}
	return true;
	}

/**
 *
 * @Description :delete one featureclass
 * @params : tablename: table name
 * @params : $srs: srs name
 * @params : $layers: layers names string, layer1,layer2,layer3,........
 * @return : ture or false																																				**
 */
function deleteLayersBySRS($aid, $srs, $layers, $isFeaturegeometry)
	{        
	$layers = str_replace(",", "','", $layers);
	$layers = "'" . $layers . "'";
	if ($isFeaturegeometry) {
		$query = "DELETE FROM `" . $this->databasePrefix . "featuregeometry` WHERE `srs` ='$srs' "
			. (empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'")." AND `layer` IN ($layers) ";
	} else {
		$query = "DELETE FROM `" . $this->databasePrefix . "featureclass` WHERE `srs` ='$srs' "
			. (empty($aid) || $aid == "0"?"":"AND `aid` = '$aid'")." AND `layer` IN ($layers) ";
	}
	@mysql_query($query, $this->databaseConnection);
	if ($error = mysql_error()) {
		$this->databaseErrorHandle(t('Failed to delete layer deleteLayersBySRS(). We tried querying with the command %query.', array('%query' => $query)));
		return false;
	}
	return true;
	}
// ================================For Installation==============================================================

}

?>