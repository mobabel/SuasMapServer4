# SUAS MapServer 4

## Overview

The name SUAS comes from Stuttgart University Applied Science, it sounds like [sju:as] and also similar to "Show Us" what is one of the goals for this tool.

This Open Source SUAS MapServer is implemented using free ware tools: Apache Web Server, PHP with Libriaries, MySQL database and PHPMyAdmin to deliver SVG(T), PDF, SWF vector maps and PNG, GIF, JPG, WBMP raster maps over the net.

The Server is based on Version 1.1.1 of the OGC Web Map Server specification.

SUAS MapServer 4 is designed for multiple user map service.

The handbook and tutorial can be found in http://www.easywms.com/?q=suas


### Developers: 

* Msc. LI Hui
* Prof. Franz-Josef Behr

## Features

**WMS:**
* GetCapabilities
* GetMap
* GetFeatureInfo
* DescribeLayer
* GetLegendGraphic
* GetStyle

**WMS Extension:**
* SUAS Map Client
* Map Viewers
* 2.5D Navigation
* 3D Navigation
* GetThematicMap

**WFS:**
* GetCapabilities
* DescribeFeatureType
* GetFeature
* GetGmlObject
* Transaction

**Multi-User:**
* Multi-user system
* User Authentication and Authorisation
* Each user can publish his own map and share it with other users or publicly.


## Installation

Please visit http://www.easywms.com/?q=taxonomy/term/18


### Requirement

**Mandatory**

1. [Apache][Apache] 2.0.54 or later
Http server to build your web server

2. [MySQL][MySQL] 5.0.16 or later
to create your database, using WKT format to store geometries

3. [PHP][PHP]5.05 or later
is used as developing and excuting language. Edit php.ini
```php
;short_open_tag = Off
short_open_tag = On
```

```php
error_reporting = E_ALL & ~E_NOTICE
```


4. [GD library][GD_library]
for raster image, PNG, JPEG, GIF and WBMP generating

5. LibXML library
for parsing XML data with PHP

[Apache]:http://www.apache.org/

[MySQL]:http://www.mysql.com/

[PHP]:http://www.php.net/

[GD_library]:http://www.boutell.com/gd/


**Optional**

1. [phpMyAdmin][phpMyAdmin]2.7.0 or later
It is strongly recommanded to install to manage your database

2. PHP Extensions:
 
2.1 [MING library][MING_library]
for SWF image generating

2.2 [PDF library][PDF_library]
for PDF image generating

2.3 [Expat library][Expat_library]
for XML parsing using PHP

2.4 Dbase
parsing the DBF file when inputting Shapefile data

[phpMyAdmin]:http://www.phpmyadmin.net/

[MING_library]:http://ming.sourceforge.net/

[PDF_library]:http://www.pdflib.com/

[Expat_library]:http://www.jclark.com/xml/expat.html


### Install

edit SuasMapServer4/config.php and change the following parameters with yours

```php
$dbserver 	= 'localhost';
$dbusername   = 'root';
$dbpassword   = 'test';
$dbname     	= 'tt';
$dbprefix     = 'suas';
$baseserverhost 	= 'http://localhost/suas/';
```


run http://localhost/SuasMapServer4/install/install.php and following the wizard.

Some test data such as shape file, svg are available under SuasMapServer4/test_data


### Tutorial

[Show Us Map! 30 Minutes to Build Your Own Map Server with SUAS MapServer 3][Tutorial_1]

[Show Us Map! 45 Minutes to Build Your Own 3D City Map Server with SUAS MapServer 3][Tutorial_2]


[Tutorial_1]:http://www.easywms.com/?q=show-us-map-30-minutes-build-your-own-map-server-suas-mapserver3

[Tutorial_2]:http://www.easywms.com/?q=show-us-map-45-minutes-build-your-own-3d-city-map-server-suas-mapserver3


