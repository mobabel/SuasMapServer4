<?php
/**
 * Installation.class.php
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

/**
 *
 * @params : $database object
 * @description : Check the extension in PHP before installation
 */
function checkMandatoryExtensionInPHP($database)
{
    $requirements = true;
    $mysql = true;
    $pgsql = true;
    // PHP Version Checking
    $phpversion = phpversion();
    if ($phpversion >= '5.0.0') {
        print('<p>PHP 5.0.0+ <span style="color:#009900">Test Passed - You are running PHP ' . $phpversion . ' </span></p>');
    } else {
        print('<p>PHP 5.0.0+ <span style="color:#CC0000">Test Failed - You are running PHP ' . $phpversion . ' </span></p>');
        $requirements = false;
    }
    // MySQL Version Checking
    if (extension_loaded('mysql')) {
        print('<p>&#8226; 1 MySQL <span style="color:#009900">Test Passed - MySQL Lib is available.</span></p>');
    } else {
        print('<p>&#8226; 1 MySQL <span style="color:#CC0000">Test Failed - MySQL Lib is not available.</span></p>');
        $mysql = false;
    }
    if (extension_loaded('pgsql')) {
        print('<p>&#8226; 2 PostgreSQL <span style="color:#009900">Test Passed - PostgreSQL Lib is available.</span></p>');
    } else {
        print('<p>&#8226; 2 PostgreSQL <span style="color:#CC0000">Test Failed - PostgreSQL Lib is not available.</span></p>');
        $pgsql = false;
    }
    // libXML Version Checking
    if (extension_loaded('libxml')) {
        print('<p>libXML <span style="color:#009900">Test Passed - libXML is available. (Version 2.6.11 is mandatory required for parsing&generating XML file functionality).</span></p>');
    } else {
        print('<p>&libXML <span style="color:#CC0000">Test Failed - libXML is not available. It is mandatory required for parsing&generating XML file functionality</span></p>');
        $requirements = false;
    }
    // GD Version Checking
    if (extension_loaded('gd')) {
        print('<p>GD <span style="color:#009900">Test Passed - GD is available. (Version 2.0 is mandatory required for generating raster images functionality).</span></p>');
    } else {
        print('<p>GD <span style="color:#CC0000">Test Failed - GD is not available. It is mandatory required for generating raster images functionality</span></p>');
        $requirements = false;
    }

    if (!$mysql && !$pgsql)
        $requirements = false;
    // print('<hr/>');
    return $requirements;
}

function checkOptionalExtensionInPHP()
{
    // PDF Version Checking
    if (extension_loaded('pdf')) {
        print('<p>PDF <span style="color:blue">Test Passed - PDF is available. (Version 6.0.3 is optional required for generating PDF file functionality).</span></p>');
    } else {
        print('<p>PDF <span style="color:#CC0000">Test Failed - PDF is not available. It is optional required for generating PDF file functionality</span></p>');
    }
    // Ming Version Checking
    if (extension_loaded('ming')) {
        print('<p>Ming <span style="color:blue">Test Passed - Ming is available. (Version 0.3.0 is optional required for generating SWF file functionality).</span></p>');
    } else {
        print('<p>Ming <span style="color:#CC0000">Test Failed - Ming is not available. It is optional required for generating SWF file functionality</span></p>');
    }
    // Dbase Version Checking
    if (extension_loaded('Dbase')) {
        print('<p>Dbase <span style="color:blue">Test Passed - Dbase is available. (Optional required for parsing the DBF file when importing SHP data).</span></p>');
    } else {
        print('<p>Dbase <span style="color:#CC0000">Test Failed - Dbase is not available. It is optional required for parsing the DBF file when importing SHP data</span></p>');
    }
    // print('<hr/>');
}

function checkDirectoryWritabale()
{
    $directoryWritbale = true;
    // Check config.php is writable
    if (is_writable('../config.php') && file_exists('../config.php')) {
        print('<p>config.php file writable <span style="color:#009900">Test Passed (It is mandatory required for storing database access data).</span></p>');
    } else {
        $directoryWritbale = false;

        print('<p>config.php file writable <span style="color:#CC0000">Test Failed - You need to chmod this file to 777 and/or change file permissions to allow server writing. It is mandatory required for storing database access data. Path: "suasRoot/config.php"</span></p>');
    }
    // Check cache Directory is writable
	@createFile('../files');
    if (is_writable('../files')) {
        print('<p>files Directory writable <span style="color:#009900">Test Passed (It is mandatory required for storing the files of user and atlas).</span></p>');
    } else {
        $directoryWritbale = false;
        print('<p>files Directory writable <span style="color:#CC0000">Test Failed - You need to chmod this directory to 777 and/or change cache Directory permissions to allow server writing. It is mandatory required for storing the files of user and atlas.P ath: "suasRoot/files"</span></p>');
    }
    return $directoryWritbale;
}

/**
 * check if the cache directory is writable
 */
function isCacheDirectoryWritabale()
{
    if (is_writable('../cache')) {
        return true;
    } else {
        return false;
    }
}

function checkExtensionMySqlInPHP()
{
    if (extension_loaded('mysql')) {
        return true;
    } else {
        return false;
    }
}

function checkExtensionPgSqlInPHP()
{
    if (extension_loaded('pgsql')) {
        return true;
    } else {
        return false;
    }
}
/**
 *
 * @description : Check the extension Ming in PHP before installation
 */
function checkExtensionSwfInPHP()
{
    if (extension_loaded('ming')) {
        return true;
    } else {
        return false;
    }
}
/**
 *
 * @description : Check the extension Dbase in PHP before installation
 */
function checkExtensionDbaseInPHP()
{
    if (extension_loaded('Dbase')) {
        return true;
    } else {
        return false;
    }
}

/**
 *
 * @description : Check the extension PDF in PHP before installation
 */
function checkExtensionPdfInPHP()
{
    if (extension_loaded('pdf')) {
        return true;
    } else {
        return false;
    }
}

/**
 *
 * @description : Detect MySQL version - greater than 5.0.16
 * Detect PgSql version - greater than 5.0.16
 */
function checkDatabaseVersion($version, $databasetype)
{
    if ($databasetype == 0) {
        $explode = explode('.', $version['version']);
        $version['major'] = $explode[0];
        $version['minor'] = $explode[1];
        $version['patch'] = $explode[2];

        $explode = explode('-', $version['patch']);
        $version['patch'] = $explode[0];
        $strVersion = $version['major'] . "." . $version['minor'] . "." . $version['patch'];

        if ($version['major'] >= 5 && $version['minor'] >= 0 && $version['patch'] >= 12) {
            print('<span style="color:#009900">Test Passed - MySQL ' . $strVersion . ' is running.
				(Version 5.0.16+ is mandatory required for Database operation functionality).</span>');
            return true;
        } else {
            print('<span style="color:#CC0000">Test Failed - Your MySQL ' . $strVersion . ' is not suitable.
				(Version 5.0.16+ is mandatory required for Database operation functionality)</span>');
            return false;
        }
    }
}

function printOption4PrioritySelected($minlimit, $maxlimit, $index)
{
    if ($maxlimit < $index) $index = $maxlimit;
    else if ($minlimit > $index) $index = $minlimit;
    // create options for priority selection
    $strOption4Priority = "";
    for($j = $minlimit;$j <= $maxlimit;$j++) {
        if ($j == $index)
            $strOption4Priority .= "<option value=\"$j\" selected>$j</option>\n";
        else
            $strOption4Priority .= "<option value=\"$j\">$j</option>\n";
    }
    return $strOption4Priority;
}

/**
 *
 * @function : getPrefixOfTablename
 * @description : get the table prefix from the table name
 * @param  $tbname table name for featuregeometry table in SUAS
 */
function getPrefixOfTablename($tbname)
{
    return substr($tbname, 0, strripos($tbname, mapTableFeaturegeometry));
}

?>