<?php

include_once 'setting.inc';

// ALTER DATABASE `tt` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci 
/**
* Create featuregeometry table
*/
$tables_sql[0] = "
CREATE TABLE `%SUAS%featuregeometry` (
`id` bigint( 20 ) NOT NULL auto_increment,
`aid` int( 11 ) UNSIGNED NOT NULL,
`recid` varchar( 64 ) default '',
`layer` varchar( 64 ) NOT NULL default 'LayerNotDefined',
`geomtype` varchar( 20 ) NOT NULL default 'Unknown',
`srs` varchar( 64 ),
`xmin` double default NULL ,
`ymin` double default NULL ,
`xmax` double default NULL ,
`ymax` double default NULL ,
`geom` GEOMETRY ,
`xlink` varchar( 256 ),
`attributes` text NOT NULL,
PRIMARY KEY ( `id` ),
INDEX srs_layer (srs,layer)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

//$tables_sql[1] = "DROP TABLE IF EXISTS `featureclass`;";
//TODO stylename is being used temperorily, later could only use stid and table style in featureclass: `stylename` varchar( 32 ) NOT NULL default 'default',
//or, store one style for one atlas(with all layers) in style table, save/save as
//`stid` int( 11 ) UNSIGNED NOT NULL default 1,
/**
* Create featureclass table
*/
$tables_sql[1] = "
CREATE TABLE `%SUAS%featureclass` (
`fcid` bigint( 20 ) NOT NULL auto_increment,
`aid` int( 11 ) UNSIGNED NOT NULL,
`stid` int( 11 ) UNSIGNED NOT NULL,
`layer` varchar( 64 ),
`description` varchar( 256 ),
`geomtype` varchar( 32 ) NOT NULL default 'Unknown',
`layertype` varchar( 32 ) NOT NULL default 'Unknown',
`srs` varchar( 64 ),
`xmin` double default NULL ,
`ymin` double default NULL ,
`xmax` double default NULL ,
`ymax` double default NULL ,
`queryable` boolean NOT NULL default TRUE,
`visiable` boolean NOT NULL default TRUE,
`priority` tinyint(3),
`elevation` double default 0 ,
`recnum` bigint( 20 ) default 0,
`size` int( 11 ) default 0,
`created` int( 11 ) NOT NULL default 0,
`modified` int( 11 ) NOT NULL default 0,
PRIMARY KEY ( `fcid` ),
INDEX fcid_aid (fcid,aid),
INDEX srs_layer(srs,layer)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

/**
* Create atlas table
*/
$tables_sql[2] = "
CREATE TABLE `%SUAS%atlas` (
`aid` int( 11 ) UNSIGNED NOT NULL auto_increment,
`uid` int( 11 ) UNSIGNED NOT NULL ,
`stid` int( 11 ) UNSIGNED default 0,
`key` varchar( 32 ) NOT NULL,
`variable` longtext default '',
`status` tinyint(2),
`name` varchar( 32 ) NOT NULL ,
`title` varchar( 32 ) NOT NULL,
`abstract` varchar( 256 )  NOT NULL,
`layertitle` varchar( 64 ) NOT NULL,
`keyword1` varchar( 64 ) NOT NULL,
`keyword2` varchar( 64 ) default '',
`keyword3` varchar( 64 ) default '',
`keyword4` varchar( 64 ) default '',
`person` varchar( 64 ) default '',
`organization` varchar( 64 ) default '',
`position` varchar( 64 ) default '',
`contactaddress` varchar( 64 ) default '',
`addresstype` varchar( 32 ) default '',
`address` varchar( 64 ) default '',
`city` varchar( 64 ) default '',
`stateorprovince` varchar( 64 ) default '',
`postcode` varchar( 32 ) default '',
`country` varchar( 64 ) default '',
`phone` varchar( 32 ) default '',
`mail` varchar( 64 ) default '',
`fees` varchar( 32 ) default 'none',
`accessconstraints` varchar( 64 ) default '',
`type` varchar( 32 ) default 'others',
`tags` varchar( 128 ) default '',
`created` int( 11 ) NOT NULL default 0,
`modified` int( 11 ) NOT NULL default 0,
PRIMARY KEY ( `aid` ),
INDEX aid_uid (aid,uid),
INDEX aid_stid (aid,stid),
INDEX title (title),
INDEX abstract (abstract),
INDEX layertitle (layertitle),
INDEX keyword (keyword1,keyword2,keyword3,keyword4),
INDEX person (person),
INDEX country (country)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

//`fcid` int( 11 ) NOT NULL,
//`type` varchar( 32 ) NOT NULL default 'Unknown',
//`org` varchar( 32 ) NOT NULL default 'Unknown',
//`public` boolean NOT NULL default FALSE,
/**
* Create style table
*/
$tables_sql[3] = "
CREATE TABLE `%SUAS%style` (
`stid` int( 11 ) NOT NULL auto_increment,
`aid` int( 11 ) NOT NULL,
`name` varchar( 32 ) NOT NULL,
`style` longtext default NULL,
PRIMARY KEY ( `stid` ),
INDEX stid_aid (stid, aid)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

//`fcid` int( 11 ) NOT NULL,
/**
* Create symbol image table
*/
$tables_sql[4] = "
CREATE TABLE `%SUAS%style_img` (
`siid` int( 11 ) NOT NULL auto_increment,
`name` varchar( 32 ) NOT NULL,
`path` varchar( 64 )  NOT NULL,
`format` varchar( 8 )  NOT NULL default 'png',
`size` int( 8 )  NOT NULL default 0,
`type` varchar( 32 ) NOT NULL default 'Unknown',
`org` varchar( 32 ) NOT NULL default 'Unknown',
`public` boolean NOT NULL default FALSE,
PRIMARY KEY ( `siid` ),
INDEX siid (siid),
INDEX size (size)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";


$tables_sql[5] = "
CREATE TABLE `%SUAS%atlas_favorite` (
`afid` int( 11 ) NOT NULL auto_increment,
`uid` int( 11 ) UNSIGNED NOT NULL ,
`aid` int( 11 ) NOT NULL,
`desc` varchar( 255 ) default '',
`created` int( 11 ) NOT NULL default 0,
PRIMARY KEY ( `afid` ),
INDEX afid_aid (afid, aid),
INDEX afid_uid (afid, uid),
INDEX afid_uid_aid (afid, uid, aid)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

$tables_sql[6] = "
CREATE TABLE `%SUAS%atlas_counter` (
`aid` int( 11 ) NOT NULL auto_increment,
`totalcount` bigint( 20 ) UNSIGNED NOT NULL ,
`daycount` mediumint( 8 ) NOT NULL,
`timestamp` int( 11 ) NOT NULL default 0,
PRIMARY KEY ( `aid` ),
INDEX totalcount (totalcount),
INDEX daycount (daycount),
INDEX timestamp (timestamp)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

//boolean NOT NULL default FALSE,
$tables_sql[7] = "
CREATE TABLE `%SUAS%msg_index` (
`mid` int( 11 ) UNSIGNED NOT NULL,
`thread_id` int( 11 ) UNSIGNED NOT NULL default 0,
`uid` int( 11 ) UNSIGNED NOT NULL,
`is_new` int( 1 ) UNSIGNED NOT NULL default 1,
`deleted` int( 1 ) UNSIGNED NOT NULL default 0,
`type` int( 2 ) UNSIGNED NOT NULL default 0,
`status` int( 2 ) UNSIGNED NOT NULL default 0,
INDEX mid (mid),
INDEX thread_id (thread_id),
INDEX uid (uid),
INDEX is_new (mid, uid, is_new),
INDEX type_status (mid, uid, type, status)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

$tables_sql[8] = "
CREATE TABLE `%SUAS%messages` (
`mid` int( 11 ) UNSIGNED NOT NULL auto_increment,
`uid` int( 11 ) UNSIGNED NOT NULL,
`subject` varchar( 64 ) NOT NULL default '',
`body` longtext NOT NULL default '',
`timestamp` int( 11 ) NOT NULL default 0,
PRIMARY KEY ( `mid` ),
INDEX uid (uid),
INDEX subject (subject),
INDEX timestamp (timestamp)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

$tables_sql[9] = "
CREATE TABLE `%SUAS%comments` (
`cid` int( 11 ) UNSIGNED NOT NULL auto_increment,
`pid` int( 11 ) UNSIGNED NOT NULL Default 0,
`aid` int( 11 ) UNSIGNED NOT NULL Default 0,
`uid` int( 11 ) UNSIGNED NOT NULL Default 0,
`subject` varchar( 64 ) NOT NULL ,
`comment` longtext NOT NULL ,
`hostname` varchar( 128 ) NOT NULL ,
`timestamp` int( 11 ) NOT NULL default 0,
`status` tinyint( 3 ) UNSIGNED NOT NULL default 0,
`format` tinyint( 6 ) NOT NULL default 0,
`thread` varchar( 255 ) NOT NULL default '01/',
`name` varchar( 60 ) Default NULL ,
`mail` varchar( 64 ) Default NULL ,
`homepage` varchar( 255 ) Default NULL ,
PRIMARY KEY ( `cid` ),
INDEX pid (pid),
INDEX aid (aid),
INDEX status (status)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

/**
* Create user table
*/
$tables_sql[10] = "
CREATE TABLE `%SUAS%users` (
`uid` int( 11 ) UNSIGNED NOT NULL auto_increment,
`name` varchar( 60 ) NOT NULL,
`pass` varchar( 32 ) NOT NULL,
`mail` varchar( 64 ) ,
`mode` tinyint(4) NOT NULL default 0 ,
`sort` tinyint(4) default 0 ,
`threshold` tinyint(4) default 0 ,
`theme` varchar(255) NOT NULL default '',
`signature` varchar(255) NOT NULL default '',
`created` int( 11 ) NOT NULL default 0,
`access` int( 11 ) NOT NULL default 0,
`login` int( 11 ) NOT NULL default 0,
`status` int( 4 ) NOT NULL default 0,
`timezone` varchar( 8 ) default NULL,
`language` varchar( 12 ) NOT NULL default 'en',
`picture` varchar(255) NOT NULL default '',
`init` varchar(64) default '',
`data` longtext  default NULL,
PRIMARY KEY ( `uid` ),
UNIQUE name (name),
INDEX access (access),
INDEX created (created),
INDEX mail (mail)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

/**
* Create user and role relation table
*
*/
$tables_sql[11] = "
CREATE TABLE `%SUAS%users_roles` (
`uid` int( 10 ) UNSIGNED NOT NULL default 0,
`rid` int( 10 ) UNSIGNED NOT NULL default 0,
PRIMARY KEY ( `uid`, `rid`),
INDEX rid (rid)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

/**
* Create role table
*
*/
$tables_sql[12] = "
CREATE TABLE `%SUAS%role` (
`rid` int( 10 ) UNSIGNED NOT NULL auto_increment,
`name` varchar( 64 ) NOT NULL ,
PRIMARY KEY ( `rid` ),
UNIQUE name (name)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

$tables_sql[13] = "
INSERT INTO `%SUAS%role` (`rid`, `name`) VALUES
(1, '".$siteinfo['role']['role_anonymous']."'),
(2, '".$siteinfo['role']['role_authenticated']."'),
(3, '".$siteinfo['role']['role_administrator']."');
";


/**
* Create permission table for role
*
*/
$tables_sql[14] = "
CREATE TABLE `%SUAS%permission` (
`pid` int( 11 ) NOT NULL auto_increment,
`rid` int( 10 ) UNSIGNED NOT NULL default 0,
`perm` longtext  default NULL,
`tid` int( 10 ) UNSIGNED NOT NULL default 0,
PRIMARY KEY ( `pid` ),
INDEX rid (rid)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";


/**
* Create permission table for role
*
*/
$tables_sql[15] = "
CREATE TABLE `%SUAS%sessions` (
`uid` int( 10 ) UNSIGNED NOT NULL ,
`sid` varchar( 64 ) NOT NULL ,
`hostname` varchar( 128 ) NOT NULL,
`timestamp` int( 11 ) NOT NULL default 0,
`cache` int( 11 ) NOT NULL default 0,
`session` longtext default NULL,
PRIMARY KEY ( `sid` ),
INDEX timestamp (timestamp),
INDEX uid (uid)
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

/**
* Create system variable table for SUAS
*
*/
$tables_sql[16] = "
CREATE TABLE `%SUAS%variable` (
`name` varchar( 128 ) NOT NULL,
`value` longtext NOT NULL,
PRIMARY KEY ( `name` )
) TYPE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

/**
* Add some system variables for SUAS
*
*/
$tables_sql[17] = "
INSERT INTO `%SUAS%variable` (`name`, `value`) VALUES
('site_version', 's:4:\"".SITE_VERSION."\";'),
('site_version_edtion', 's:10:\"".SITE_VERSION_EDITION."\";');
";

/**
* Drop all tables for SUAS
*
*/
$drop_tables_sql = "
DROP TABLE IF EXISTS `%SUAS%featuregeometry`,
`%SUAS%featureclass`,
`%SUAS%atlas`,
`%SUAS%style`,
`%SUAS%style_img`,
`%SUAS%users`,
`%SUAS%users_roles`,
`%SUAS%role`,
`%SUAS%permission`,
`%SUAS%sessions`,
`%SUAS%variable`;";

/**
* This temporary table is used for OSM data importing
* created by Qian.M
*/
$tables_sql_osm_tmp = "
CREATE TEMPORARY TABLE `osm_tmp` (
`recid` varchar( 64 ) NOT NULL default '',
`lon` double default NULL ,
`lat` double default NULL ,
`attributes` varchar( 256 ),
PRIMARY KEY ( `recid` )
) engine = MEMORY DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

?>