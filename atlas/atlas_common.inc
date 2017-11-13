<?php
/*
 * Created on 16.05.2009
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
 * atlas_common.inc
 * @version $Id$
 * @copyright (C) 2006-2009 Hui LI
 * @Description : 
 * @contact webmaster@easywms.com
 * 
 */
 
function atlas_get_default_cfg(){
	$atlas_cfg['status']  = 1;
	$atlas_cfg['enablestretchmap']  = 1;
	$atlas_cfg['showCopyright']  = 1;
	$atlas_cfg['cacheExpiredTime']  = DEFAULT_VALUE_cacheExpiredTime;
	$atlas_cfg['enableSVGPixelCoordinate']  = 0;
	$atlas_cfg['enableStreamSVG'] = 1;
	$atlas_cfg['outputEncodeCountry']  = "en";
	$atlas_cfg['OverlapRatio']  = DEFAULT_VALUE_OverlapRatio;
	$atlas_cfg['GetMap25DOverlapRatio']  = DEFAULT_VALUE_GetMap25DOverlapRatio;
	$atlas_cfg['GoogleMapKey']  = "";
	$atlas_cfg['enableCache'] = 0;
	return $atlas_cfg;
}
?>
