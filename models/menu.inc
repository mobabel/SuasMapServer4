<?php
/**
 *
 * @version $Id$
 * @copyright 2007
 */

class menu{

public static function getFooter(){
	print '<table class="tableNone">
<tr>
<td class="footCopyright">
Powered by <a href="http://www.easywms.com" target="_blank">'.SUAS_NAME.'</a>  ' . SITE_VERSION .' ' .SITE_VERSION_EDITION.'
<p style="font-size: 80%;">2005-2009 EasyWMS.com.</p>
</td>
<td>
</td>
</tr>
</table>';
	
}


public static function CreateDemoMenu($type)
{
    print('<ul>');
    print('		<li class="first"><span><a href="index.php">Home</a></span></li> ');
    //========================WMS Demo======================
    print('			<li class="first"><span>WMS Demo</span></li>');
    print('				<ul class="second">');

    if ($type == "wms_GetCapabilities")
        print('					<li class="active">GetCapabilities</li>');
    else
        print('					<li class="unactive"><a href="wms_GetCapabilities.php">GetCapabilities</a></li>');

    if ($type == "GetMap")
        print('					<li class="active">GetMap</li>');
    else
        print('					<li class="unactive"><a href="wms_GetMap.php">GetMap</a></li>');

    if ($type == "GetFeatureInfo")
        print('		            <li class="active">GetFeatureInfo</li>');
    else
        print('		            <li class="unactive"><a href="wms_GetFeatureInfo.php">GetFeatureInfo</a></li>');

    if ($type == "DescribeLayer")
        print('		            <li class="active">DescribeLayer</li>');
    else
        print('		            <li class="unactive"><a href="wms_DescribeLayer.php">DescribeLayer</a></li>');

    if ($type == "GetLegendGraphic")
        print('		            <li class="active">GetLegendGraphic</li>');
    else
        print('		            <li class="unactive"><a href="wms_GetLegendGraphic.php">GetLegendGraphic</a></li>');

    if ($type == "GetStyles")
        print('		            <li class="active">GetStyles</li>');
    else
        print('		            <li class="unactive"><a href="wms_GetStyles.php">GetStyles</a></li>');

    print('				</ul>');

    //========================WMS Extension======================
    print('		    <li class="first"><span>WMS Extension</span></li>');
    print('		        <ul class="second">');

   // if ($type == "SUAS Map Client")
        //print('		            <li class="active">SUAS Map Client</li>');
    //else
        print('		            <li class="unactive"><a href="../Plugin/suasclient/index.html" target="_blank">SUAS Map Client</a></li>');

    if ($type == "Map Viewers")
        print('		            <li class="active">Map Viewers</li>');
    else
        print('		            <li class="unactive"><a href="wms_GetMapViewers.php">Map Viewers</a></li>');

    if ($type == "2.5D Navigation")
        print('		            <li class="active">2.5D Navigation</li>');
    else
        print('		            <li class="unactive"><a href="wms_GetMap25D.php">2.5D Navigation</a></li>');

    if ($type == "3D Navigation")
        print('		            <li class="active">3D Navigation</li>');
    else
        print('		            <li class="unactive"><a href="wms_GetMap3D.php">3D Navigation</a></li>');

    if ($type == "GetThematicMap")
        print('		            <li class="active">GetThematicMap</li>');
    else
        print('		            <li class="unactive"><a href="wms_GetThematicMap.php">GetThematicMap</a></li>');

    print('				</ul>');

    //========================WFS Demo======================
    print('		    <li class="first"><span>WFS Demo</span></li>');
    print('		        <ul class="second">');

    if ($type == "wfs_GetCapabilities")
        print('		            <li class="active">GetCapabilities</li>');
    else
        print('		            <li class="unactive"><a href="wfs_GetCapabilities.php">GetCapabilities</a></li>');

    if ($type == "DescribeFeatureType")
        print('		            <li class="active">DescribeFeatureType</li>');
    else
        print('		            <li class="unactive"><a href="wfs_DescribeFeatureType.php">DescribeFeatureType</a></li>');

    if ($type == "GetFeature")
        print('		            <li class="active">GetFeature</li>');
    else
        print('		            <li class="unactive"><a href="wfs_GetFeature.php">GetFeature</a></li>');

    if ($type == "GetGmlObject")
        print('		            <li class="active">GetGmlObject</li>');
    else
        print('		            <li class="unactive"><a href="wfs_GetGmlObject.php">GetGmlObject</a></li>');

    if ($type == "Transaction")
        print('		            <li class="active">Transaction</li>');
    else
        print('		            <li class="unactive"><a href="wfs_Transaction.php">Transaction</a></li>');

    print('				</ul>');
    print('</ul>');
}

public static function CreateToolsMenu($type)
{

    print('			<li class="first"><span>Tools</span></li>');
    print('				<ul class="second">');

    if ($type == "cacheclear")
        print('					<li class="active">Cahce Clear</li>');
    else
        print('					<li class="unactive"><a href="cacheclear.php" title="Clear Cache">Cahce Clear</a></li>');

    if ($type == "sldbackup")
        print('					<li class="active">SLD Style File Backup</li>');
    else
        print('					<li class="unactive"><a href="sldbackup.php" title="Backup your SLD style file">SLD Style File Backup</a></li>');

    if ($type == "databackup")
        print('		            <li class="active">Data Backup</li>');
    else
        print('		            <li class="unactive"><a href="databackup.php" title="Backup the spatial data or metadata">Data Backup</a></li>');

    if ($type == "datadelete")
        print('		            <li class="active">Data Delete</li>');
    else
        print('		            <li class="unactive"><a href="datadelete.php"  title="Delete the spatial data">Data Delete</a></li>');

    if ($type == "checklog")
        print('		            <li class="active">Check Log</li>');
    else
        print('		            <li class="unactive"><a href="checklog.php"  title="Check delete, or download the log file">Check Log</a></li>');

    if ($type == "checkupdate")
        print('		            <li class="active">Check Update</li>');
    else
        print('		            <li class="unactive"><a href="checkupdate.php" title="Check the new verion of SUAS">Check Update</a></li>');

    if ($type == "help")
        print('		            <li class="active">Help</li>');
    else
        print('		            <li class="unactive"><a href="http://suas.easywms.com" target="_blank" title="Help&Handbook">Help</a></li>');

    if ($type == "bugreport")
        print('		            <li class="active">Report Bugs</li>');
    else
        print('		            <li class="unactive"><a href="http://www.easywms.com/easywms/?q=en/node/158" target="_blank" title="Help&Handbook">Report Bugs</a></li>');

    print('				</ul>');

}


public static function get_login_block($destination, $allow_register, $page = ""){
	$output = '	<div class="block block-user" id="block-login">
	  			<div class="block-panel-header">User login</div>
	  			<div class="block-content">
				  	<form action="'.get_base_server_host().'user/login.php"  accept-charset="UTF-8" method="post" id="user-login-form" onSubmit="return chk_form_required_field(\'user-login-form\')">
		 			<div class="form-item" id="edit-name-wrapper">
					<label for="edit-name">Username: <span class="form-required" title="This field is required.">*</span></label>
		 			<input type="text" maxlength="60" name="username" id="edit-username"  size="15" value="" class="form-text form-required" />
					</div>
		 			<div class="form-item" id="edit-name-wrapper">
		 			<label for="edit-password" >Password: <span class="form-required" title="This field is required.">*</span></label>
		 			<input type="password" name="password" id="edit-password"  maxlength="60"  size="15"  class="form-text form-required" />
					</div>';
	$output .= self::get_captcha_field($page);
	$output .= '<input type="submit" name="login" id="edit-submit" value="Log in"  class="ui-button ui-state-default ui-corner-all" />';
	
	if($allow_register){
		$output .=	'<div class="form-item" id="edit-name-wrapper">
				<a href="'.get_base_server_host().'user/register.php" title="Create a new user account.">Create new account</a>
				</div>';
	}

	$output .=	'<!--a href="'.get_base_server_host().'user/password.php" title="Request new password via e-mail.">Request new password</a-->
					<input type="hidden" name="destination" id="edit-destination" value="'.$destination.'"/>';
	$output .=	'</form>
				</div>
			</div>';
	echo $output;
	self::print_jsscript_captcha($page);
}

public static function print_jsscript_captcha($page = ""){

//EOT can not contain semicolon; inside
//echo <<< EOT
echo '
<script type="text/javascript">
function change_captcha(){
	$.ajax({
		type: "GET",
		url: "'.($page!='home'?'../':'').'models/image_captcha.php?op=changeCode",
		data: "",
		async: true,
		dataType: \'text\',
		success: function(msg) {
		var flag = msg.substring(0, 3);
		var message = msg.substring(4, msg.length);//flag:xxxx
		if(flag == \'suc\'){
			$(\'#edit-code\').val(\'\');
			//update the favocucount
			var count = parseInt($(\'#text_atlas_favocucount\').html()==\'\'?\'0\':$(\'#text_atlas_favocucount\').html());
			var divPos =  message.lastIndexOf(\'|\');
			var md5 = message.substr(0, divPos);
			var base64 = message.substring(divPos+1, message.length);
			//alert(md5.length);
			$(\'#edit-validcode\').val(md5);//md5 code
			var url= $(\'#edit-captchaimage\').attr(\'src\');
			var equaPos =  url.lastIndexOf(\'?\');
			url = url.substr(0, equaPos+1) +"vcode="+ base64;
			$(\'#edit-captchaimage\').attr(\'src\',url);
			//alert(url);
		}else{
			growlError(message);
		}
	}
	}); 
}
</script>
';
//EOT ;
}

public static function get_latestuser_block($database){
	$output = '
		<div class="block block-atlas" id="block-atlas">
		<div class="atlas-panel-header">Latest Users</div>
		<div class="block-content">

		<div class="atlas-pane-section">';
		
	if($result = $database->db_get_latestuser()){
		while ($row = $database->getColumns($result)) {
			$output .='<div class="atlas-pane-line">'
			.'<span class="atlas-pane-label"></span><a href="'.get_base_server_host().'user/user.php?uid='.$row['uid'].'"> '.$row['name'].'</a></div>';
		}
	}else{
		setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		return;
	}
	
	$output .= '</div>';
	$output .= '</div></div>';
	print $output;	
}

public static function get_randomatlas_block($database, $listmax = 8){
	print '
		<div id="randomatlas">';
		
		block_randomatlas_list($database, $listmax);
	
	print '</div>';	
}

public static function get_atlas_block($database){
	global $atlas;
	global $user;
	if($atlas['aid'] ){
		if($user){
			$info['favocucount'] = 0;
			$info['favocount'] = 0; 
			if($info = $database->db_get_atlas_favoinfo($atlas['aid'], $user['uid'])){
				
			}else{
				setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
			}
		}
		
		$output = '<input type="hidden" id="aid" name="aid" value="'.$atlas['aid'].'">
			<input type="hidden" id="uid" name="uid" value="'.$user['uid'].'">
			<div class="block block-atlas" id="block-atlas">
			<div class="atlas-panel-header">'.$atlas['name'].'</div>
			<div class="block-content">
			<div class="atlas-pane-overview">
			<a href="#" title="'.$atlas['name'].'\'s overview">
			<img src="../files/atlas/'.$atlas['aid'].'/overview.gif" alt="'.$atlas['name'].'\'s overview" title="'.$atlas['name'].'\'s overview" width="64px" height="64px" border="0"></a>
			</div>
			<div class="atlas-pane-section">
			<div class="atlas-pane-line">
			<span class="atlas-pane-label">Author:</span> <a href="../user/user.php?uid='.$atlas['uid'].'">'.$atlas['username'].'</a>        </div>
			
			<div class="atlas-pane-line">
			<span class="atlas-pane-label">Updated:</span> '.date('d/m/y', $atlas['modified']).'        </div>
			
			<div class="atlas-pane-line">
			<span class="atlas-pane-label">Created:</span> '.date('d/m/y', $atlas['created']).'        </div>
			
			<div class="atlas-pane-line">
			<span class="atlas-pane-label">Views:</span> '.(empty($atlas['totalcount'])?'0':$atlas['totalcount']).'</div>
			
			<div class="atlas-pane-line">
			<span class="atlas-pane-label">Status:</span> '.($atlas['status']==0?'private':'public').'        </div>';
		
		if($user){
			$output .= 	'<div class="atlas-pane-line">
				<span class="atlas-pane-label">Favo: </span> ';
			$output .= '<span id="text_atlas_favocucount">'.$info['favocount'].'</span>';

			//has added before, add remove button
			if($info['favocucount'] > 0){
				$output .= "&nbsp;&nbsp;<img class=\"block-pane-icon\" id=\"icon_atlas_removefavo\" src=\"../img/atlas_removefavo.png\" title=\"Remove from my favorite\" 
					onclick=\"remove_favorite(".$atlas['aid'] .",". $user['uid'].", false);\" >";
			}
			//not yet added, and not his own atlas, add add button
			else if($atlas['uid'] != $user['uid']){
				$output .= "&nbsp;&nbsp;<img class=\"block-pane-icon\" id=\"icon_atlas_addfavo\" src=\"../img/atlas_addfavo.png\" title=\"Add to my favorite\"
					onclick=\"add_favorite(".$atlas['aid'].",". $user['uid'].");\" >";
			}
			
			$output .= ' <span class="block-pane-icon" id="id_div_process_loading"></span></div>';
		}
		
		$output .= '</div>';
		
		$output .= '</div>';
		print $output;

	}
}

public static function get_navigation_block($page, $op = "", $database){
	global $user;
	global $atlas;
	global $siteinfo;
	$burl = get_base_server_host();
	$op = strtoupper($op);
	$permoper = perm_atlas_oper( $atlas, $database);
	$permview = perm_atlas_view( $atlas );
	$aid = $atlas['aid'];
	if(!$permoper){
		$aid = '';
	}
	
	$output = '<div class="block block-user" id="block-user">
	  			<div class="block-panel-header">'.($user?$user['name']:'Navigation').'</div>
	  			<div class="block-content">
				<ul class="menu">';

	$output .= '<li class="leaf-c first"><a href="'.$burl.'index.php" title="Home">Home</a></li>';

	if($page == 'home'){
		if($user)
		$output .= '	<li class="collapsed"><a href="user/user.php?uid='.$user['uid'].'" title="User Account">My account</a></li>';

		$output .= '	<li class="collapsed"><a href="atlas/atlas.php" title="Atlas">Atlas</a></li>';
		if($user['role'] == $siteinfo['role']['role_administrator']){
	        $output .= '	<li class="collapsed"><a href="admin/admin.php" title="Site Administration">Administer</a></li>';
		}
	}		
	else if($page == 'user'){
		if($user)
        	$output .= '<li class="expanded active-trail"><a href="../user/user.php?uid='.$user['uid'].'" title="User Account">My account</a></li>';
		
		$output .= '	<li class="collapsed"><a href="../atlas/atlas.php" title="Atlas">Atlas</a></li>';
		if($user['role'] == $siteinfo['role']['role_administrator']){
	        $output .= '	<li class="collapsed"><a href="../admin/admin.php" title="Site Administration">Administer</a></li>';
		}
	}
    else if($page == 'atlas'){
		if($user)	
	    	$output .= '	<li class="collapsed"><a href="../user/user.php?uid='.$user['uid'].'" title="User Account">My account</a></li>';
	    	
		if ($op == "" || $op == "ATLASLIST"  || $op == "MYATLAS" 
			|| $op == "ATLASVIEW_VIEWER"  || $op == "ATLASVIEW" 
			|| $op == "ATLASVIEWFAVO_VIEWER" || $op == "ATLASVIEWFAVO"
			|| $op == "ATLAS_SELECT" || $op == "ATLAS_CONFIGURATION"
			|| $op == "ATLAS_SAVE" || $op == "ATLAS_CFG_SAVE" 
			|| $op == "ATLAS_SLD_SAVE" || $op == "ATLAS_SLD_CHANGENAME" || $op == "ATLAS_SLD_SAVEAS"
			|| $op == "ATLAS_SLD_SETDEFAULT" || $op == "ATLAS_SLD_DELETE" || $op == "ATLAS_LAYERINFO_SAVE"
			|| $op == "ATLAS_DELETE_LAYERS" 
			|| $op == "ATLAS_DELETE"
			|| $op == "ATLAS_CREATE"
			|| $op == "ATLAS_CREATE_STEP0" || $op == "ATLAS_CREATE_STEP1_IMPORT" || $op == "ATLAS_CREATE_STEP2_SLD"
			|| $op == "ATLAS_CREATE_STEP3_LAYERINFO" || $op == "ATLAS_CREATE_STEP4_CFG" || $op == "ATLAS_CREATE_STEP5_FINISH"		
			|| $op == "ATLAS_DEMO" || equalIgnoreCase(substr($op, 0,5), "demo_")
			|| $op == "FAVORITE"
			){
	        $output .= '	<li class="expanded active-trail"><a href="../atlas/atlas.php" title="Atlas">Atlas</a>';
	        
	        $output .= '	<ul class="menu">';
	        if($op == "" || $op == "ATLASLIST" 
	        			|| $op == "ATLASVIEW_VIEWER"  || $op == "ATLASVIEW" 
	        			|| $op == "ATLASVIEWFAVO_VIEWER" || $op == "ATLASVIEWFAVO" ){
			if($user)
	        	$output .= '<li class="collapsed"><a href="../atlas/atlas.php?op=MYATLAS" title="My Atlas">My Atlas</a>';
	        }else{
	        	$output .= '<li class="expanded active-trail"><a href="../atlas/atlas.php?op=MYATLAS" title="My Atlas">My Atlas</a>';
	        }
	        $output .= '<ul class="menu">';
	        
		    if($op == "MYATLAS" || $op == "ATLAS_SELECT" || $op == "ATLAS_CONFIGURATION"
		        || $op == "ATLAS_SAVE" || $op == "ATLAS_CFG_SAVE" 
				|| $op == "ATLAS_SLD_SAVE" || $op == "ATLAS_SLD_CHANGENAME" || $op == "ATLAS_SLD_SAVEAS"
				|| $op == "ATLAS_SLD_SETDEFAULT" || $op == "ATLAS_SLD_DELETE" || $op == "ATLAS_LAYERINFO_SAVE"
				|| $op == "ATLAS_DELETE_LAYERS" ){
		        $output .= '<li class="leaf-c active-trail"><a href="../atlas/atlas.php?op=ATLAS_CONFIGURATION&tab=meta&aid='.$aid.'" title="Configuration">Configuration</a></li>';       	
		    	$output .= '<li class="collapsed"><a href="../atlas/atlas.php?op=ATLAS_DEMO&aid='.$aid.'" title="Demo">Demo</a></li>';
		    }
		    else{
		    	//$output .= '<li class="leaf-c"><a href="../atlas/atlas.php?op=ATLAS_CONFIGURATION&tab=meta&aid='.$aid.'" title="Configuration">Configuration</a></li>';
		    }
		    
		    if($op == "ATLAS_DEMO" || equalIgnoreCase(substr($op, 0,5), "demo_")){
		    	$output .= '<li class="leaf-c"><a href="../atlas/atlas.php?op=ATLAS_CONFIGURATION&tab=meta&aid='.$aid.'" title="Configuration">Configuration</a></li>';
		    	$output .= '<li class="expanded active-trail"><a href="../atlas/atlas.php?op=ATLAS_DEMO&aid='.$aid.'" title="Demo">Demo</a>';
		    	$output .= '<ul class="menu">';
		    	
		    	if(equalIgnoreCase(substr($op, 0, 9), "demo_wms_")){
		    		$output .= '<li class="expanded active-trail"><a href="../atlas/atlas.php?op=ATLAS_DEMO&aid='.$aid.'" title="WMS">WMS</a>';
		    		$output .= '<ul class="menu">';		
					$output .= '<li class="leaf-c '.($op == "DEMO_WMS_GETCAPABILITIES"?'active-trail':"").'"><a href="../atlas/atlas.php?op=demo_wms_GetCapabilities&aid='.$aid.'" title="WMS GetCapabilities">GetCapabilities</a></li>';
		    		$output .= '<li class="leaf-c '.($op == "DEMO_WMS_GETMAP" || $op == "DEMO_WMS_GETMAP_R"?'active-trail':"").'"><a href="../atlas/atlas.php?op=DEMO_WMS_GETMAP&aid='.$aid.'" title="WMS Getmap">Getmap</a></li>';    			
		    		$output .= '<li class="leaf-c '.($op == "DEMO_WMS_GETFEATUREINFO" || $op == "DEMO_WMS_GETFEATUREINFO_R"?'active-trail':"").'"><a href="../atlas/atlas.php?op=demo_wms_GetFeatureInfo&aid='.$aid.'" title="WMS GetFeatureInfo">GetFeatureInfo</a></li>';
		    		$output .= '<li class="leaf-c '.($op == "DEMO_WMS_DESCRIBELAYER"?'active-trail':"").'"><a href="../atlas/atlas.php?op=demo_wms_DescribeLayer&aid='.$aid.'" title="WMS DescribeLayer">DescribeLayer</a></li>';
		    		$output .= '<li class="leaf-c '.($op == "DEMO_WMS_GETLEGENDGRAPHIC"?'active-trail':"").'"><a href="../atlas/atlas.php?op=demo_wms_GetLegendGraphic&aid='.$aid.'" title="WMS GetLegendGraphic">GetLegendGraphic</a></li>';
		    		$output .= '<li class="leaf-c '.($op == "DEMO_WMS_GETSTYLE"?'active-trail':"").'"><a href="../atlas/atlas.php?op=demo_wms_GetStyles&aid='.$aid.'" title="WMS GetStyles">GetStyles</a></li>';  		
		    		$output .= '</ul></li>';
		    		
		    		$output .= '<li class="collapsed"><a href="../atlas/atlas.php?op=ATLAS_DEMO&aid='.$aid.'" title="WMS Extension">WMS Extension</a></li>';
		    		$output .= '<li class="collapsed"><a href="../atlas/atlas.php?op=ATLAS_DEMO&aid='.$aid.'" title="WFS">WFS</a></li>';
		    	}	    	
		    	else if(equalIgnoreCase(substr($op, 0, 17), "demo_wmsextension")){
		    		$output .= '<li class="collapsed"><a href="../atlas/atlas.php?op=ATLAS_DEMO&aid='.$aid.'" title="WMS">WMS</a></li>';
		    		
		    		$output .= '<li class="expanded active-trail"><a href="../atlas/atlas.php?op=ATLAS_DEMO&aid='.$aid.'" title="WMS Extension">WMS Extension</a>';
		    		$output .= '<ul class="menu">';		
					$output .= '<li class="leaf-c"><a href="../plugin/suasclient/index.html" target="_blank" title="SUAS Map Client">SUAS Map Client</a></li>';
					$output .= '<li class="leaf-c '.($op == "DEMO_WMSEXTENSION_GETMAPVIEWERS" || $op == "DEMO_WMSEXTENSION_GETMAPVIEWERS_R"?'active-trail':"").'"><a href="../atlas/atlas.php?op=demo_wmsextension_GetMapViewers&aid='.$aid.'" title="WMS Extension Map Viewers">Map Viewers</a></li>';
		    		$output .= '<li class="leaf-c '.($op == "DEMO_WMSEXTENSION_GETMAP25D" || $op == "DEMO_WMSEXTENSION_GETMAP25D_R"?'active-trail':"").'"><a href="../atlas/atlas.php?op=demo_wmsextension_GetMap25D&aid='.$aid.'" title="WMS Extension Getmap25D">GetMap25D</a></li>';    			
		    		$output .= '<li class="leaf-c '.($op == "DEMO_WMSEXTENSION_GETMAP3D" || $op == "DEMO_WMSEXTENSION_GETMAP3D_R"?'active-trail':"").'"><a href="../atlas/atlas.php?op=demo_wmsextension_GetMap3D&aid='.$aid.'" title="WMS Extension GetMap3D">GetMap3D</a></li>';
		    		$output .= '<li class="leaf-c '.($op == "DEMO_WMSEXTENSION_GETTHEMATICMAP"?'active-trail':"").'"><a href="../atlas/atlas.php?op=demo_wmsextension_GetThematicMap&aid='.$aid.'" title="WMS Extension GetThematicMap">GetThematicMap</a></li>';	
		    		$output .= '</ul></li>';
		    		
		    		$output .= '<li class="collapsed"><a href="../atlas/atlas.php?op=ATLAS_DEMO&aid='.$aid.'" title="WFS">WFS</a></li>';
		    	}
		    	else if(equalIgnoreCase(substr($op, 0, 9), "demo_wfs_")){
		    		$output .= '<li class="collapsed"><a href="../atlas/atlas.php?op=ATLAS_DEMO&aid='.$aid.'" title="WMS">WMS</a></li>';
		    		$output .= '<li class="collapsed"><a href="../atlas/atlas.php?op=ATLAS_DEMO&aid='.$aid.'" title="WMS Extension">WMS Extension</a></li>';
		    		$output .= '<li class="expanded active-trail"><a href="../atlas/atlas.php?op=ATLAS_DEMO&aid='.$aid.'" title="WFS">WFS</a>';
		    		$output .= '<ul class="menu">';		
					$output .= '<li class="leaf-c '.($op == "DEMO_WFS_GETCAPABILITIES"?'active-trail':"").'"><a href="../atlas/atlas.php?op=demo_wfs_GetCapabilities&aid='.$aid.'" title="WFS GetCapabilities">GetCapabilities</a></li>';
		    		$output .= '<li class="leaf-c '.($op == "DEMO_WFS_DESCRIBEFEATURETYPE" || $op == "DEMO_WMS_GETMAP_R"?'active-trail':"").'"><a href="../atlas/atlas.php?op=demo_wfs_DescribeFeatureType&aid='.$aid.'" title="WFS DescribeFeatureType">DescribeFeatureType</a></li>';    			
		    		$output .= '<li class="leaf-c '.($op == "DEMO_WFS_GETFEATURE" || $op == "DEMO_WMS_GETFEATUREINFO_R"?'active-trail':"").'"><a href="../atlas/atlas.php?op=demo_wfs_GetFeature&aid='.$aid.'" title="WFS GetFeature">GetFeature</a></li>';
		    		$output .= '<li class="leaf-c '.($op == "DEMO_WFS_GETGMLOBJECT"?'active-trail':"").'"><a href="../atlas/atlas.php?op=demo_wfs_GetGmlObject&aid='.$aid.'" title="WFS GetGmlObject">GetGmlObject</a></li>';
		    		$output .= '<li class="leaf-c '.($op == "DEMO_WFS_TRANSACTION"?'active-trail':"").'"><a href="../atlas/atlas.php?op=demo_wfs_Transaction&aid='.$aid.'" title="WFS Transaction">Transaction</a></li>';  		
		    		$output .= '</ul></li>';		
		    	}
		    	//empty child
		    	else{
		    		$output .= '<li class="collapsed"><a href="../atlas/atlas.php?op=ATLAS_DEMO&aid='.$aid.'" title="WMS">WMS</a></li>';
		    		$output .= '<li class="collapsed"><a href="../atlas/atlas.php?op=ATLAS_DEMO&aid='.$aid.'" title="WMS Extension">WMS Extension</a></li>';
		    		$output .= '<li class="collapsed"><a href="../atlas/atlas.php?op=ATLAS_DEMO&aid='.$aid.'" title="WFS">WFS</a></li>';
		    	}
		    	
		    	$output .= '</ul></li>';
		    }	        
	        $output .= '</ul></li>';//my atlas
	        
	        if($op == "ATLASVIEWFAVO_VIEWER" || $op == "ATLASVIEWFAVO" ){
	        	$output .= '<li class="expanded active-trail"><a href="../atlas/atlas.php?op=favorite" title="Favorite">Favorite</a>';
	        }else{
			if($user)
	        	$output .= '<li class="collapsed"><a href="../atlas/atlas.php?op=favorite" title="Favorite">Favorite</a>';
	        }
	        
	    	$output .= '</ul></li>';//atlas
	    }
	    
	    if($user['role'] == $siteinfo['role']['role_administrator']){
	        $output .= '	<li class="collapsed"><a href="../admin/admin.php" title="Site Administration">Administer</a></li>';
		}
	}
	else if($page == 'admin'){
		if($user)	
	    	$output .= '	<li class="collapsed"><a href="../user/user.php?uid='.$user['uid'].'" title="User Account">My account</a></li>';
		
		$output .= '	<li class="collapsed"><a href="../atlas/atlas.php" title="Atlas">Atlas</a></li>';
		if($user['role'] == $siteinfo['role']['role_administrator']){
	        $output .= '	<li class="expanded active-trail"><a href="../admin/admin.php" title="Site Administration">Administer</a></li>';
		}
	}
    
	if($user)
		$output .= '	<li class="leaf-c last"><a href="'.$burl.'user/login.php" title="Logout">Logout</a></li>';
		
	$output .= '</ul>
				</div>
			</div>';
	print $output;
}

static function get_captcha_field($page = ""){
	require 'AuthCode.class.php';

	$auth_code = new AuthCode();
	$auth_code->setCode(array('characters'=>'0-9,a-z','length'=>4)); 
	$auth_code->generateCode();
	$vcode= $auth_code->getcode();
	$output .=  '<div class="form-item" id="edit-name-wrapper">
			<input type="hidden" name="validcode" id="edit-validcode" value="'.md5($vcode).'"/>';
	$output .=  '<label for="edit-code">Validate code: <span class="form-required" title="This field is required.">*</span></label>
	<input type="text" name="code" id="edit-code" size="15" maxlength="4" class="form-text form-required" autocomplete="off"/>
	</div><div><img class="form-item mouse_over" id="edit-captchaimage" title="Click to change image" src="';
	if($page != 'home'){
		$output .=  "../";
	}
	$output .= 'models/image_captcha.php?vcode='.base64_encode ($vcode).'" onclick="return change_captcha();" /></div>';
	return $output;
}


}
?>