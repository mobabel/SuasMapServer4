<?php
/**
 * atlas.inc
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
 * @version $Id: atlas.inc,v 1.2 2008/04/17 22:45 leelight Exp $
 * @copyright (C) 2006-2009  LI Hui(leelight)
 * @Description : 
 * @contact webmaster@easywms.com
 */

function get_myatlas_list($user, $database, $listmax = 5, $orderby, $sort, $pg = 1, $total = 0) {
		$colnumber = 4;
	$database->dbEmptyErrorMessage();
	$uid = $user['uid'];
	$result = $database->db_get_atlas_list($listmax, $orderby, $sort, $pg, $total, $uid);
	$recordcount = $database->getRowsNumber($result['result']);
	if(!$result){
		setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		return;
	}
	$total = $result['total'];
	$page=new page(array('total'=>$total,'perpage'=>$listmax));	
	
	print '<script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/imgPreview/imgpreview.min.0.22.jquery.js"  defer="defer"></script>';
	echo '<form action="atlas.php"  accept-charset="UTF-8" method="post" name="atlas_list_form" id="atlas_list_form">';
	print '<table id="table_atlas_list" class="tableNone" >
	<tr class="title">
	<td colspan="' . $colnumber . '">My Atlas List</td>
	</tr>';
	if($recordcount > 0){
		print '<tr class="name">
		<td>Id</td>
		<td>Name</td>
		<td>Key</td>
		<td></td>
		</tr>';
	}
	$i = 0;

	while ($row = $database->getColumns($result['result'])) {
		if ($i % 2 == 0) {
			echo "<tr class=\"even mouse_over\" onclick=\"show_deatil_block($i," . $recordcount . ")\" height=\"35px\">";
		} else {
			echo "<tr class=\"odd mouse_over\" onclick=\"show_deatil_block($i," . $recordcount . ")\" height=\"35px\">";
		}
		
		echo "<td width=\"100px\">" . ($i +1);
		echo "<input type=\"radio\" name= \"atlas_aid\"  value=\"";
		echo $row["aid"] . "\"";
		if ($i == 0) {
			// echo "checked";
		}
		echo ">\n";
		echo "</td>\n";
		echo "<td>\n";
		echo $row['name'] . "</td>\n";
		echo "<td>\n";
		echo $row['key'] . "</td>\n";
		echo "<td>&nbsp;\n";

		// block for detail
		echo "</td><tr><td colspan=\"$colnumber\" ><div class=\"tableNone\" id=\"block_detail_$i\" style=\"display:none\">
			<table width=\"100%\" class=\"tableNone\">
			<tr><td id=\"imgpreview\" width=\"96px\">";
		print '<a href="#" src="'.get_wms_path($row['aid']).'overview.gif" title="'.$row['name'] . '">
			<img src="'.get_wms_path($row['aid']).'overview.gif" width="96px" height="96px" style="border:1px solid #808080; -moz-border-radius: 4px; -webkit-border-radius: 4px;" class="imgPrevBlock" ></a>';

		echo "</td><td colspan=\"".($colnumber-1)."\" width=\"100%\">";
		echo '<div class="atlas-pane-line"><span class="">Abstract: </span>'.$row['abstract'].'</div>';
		echo '<div class="tags" >';
		//echo '<div class="listattr">Layers: '.$row['layercount'].'</div>';
		echo '<div class="listattr">Views: '.(empty($row['totalcount'])?'0':$row['totalcount']).'</div>';
		echo '<div class="listattr">Created on  '.date("F d, Y", $row['created']).'</div>';
		echo '<div class="listattr">Updated on  '.date("F d, Y", $row['modified']).'</div>';
		echo  '</div>';
		echo "</td></tr></table>
				</div>
			</td></tr>";
		$i++;
	}
	if ($i == 0) {
		echo "<tr class=\"none\">
		<td colspan=\"" . $colnumber . "\" align=\"center\">No Atlas</td>
		</tr>";
	}
	print '<tr>
	<td colspan="' . $colnumber . '">&nbsp;</td>
	</tr><tr>
	<td colspan="' . $colnumber . '" align="center" >&nbsp;';
	if ($i > 0) {
		echo $page->show(6);
	}
	print '</td>
	</tr>';
	print '<tr class="footer">
	<td colspan="' . $colnumber . '">
	<input onclick="return chkSelectAtlas(\'create\');" type="button" name="button" id="button" value="Create"  class="ui-button ui-state-default ui-corner-all" />
	';
	if ($i != 0) {
		print '	
		<input onclick="return chkSelectAtlas(\'demo\');" type="button" name="button" id="button" value="Demo" class="ui-button ui-state-default ui-corner-all" />
		<input onclick="return chkSelectAtlas(\'delete\');" type="button" name="button" id="button" value="Delete"  class="ui-button ui-state-default ui-corner-all" />
		<input onclick="return chkSelectAtlas(\'select\');" type="button" name="button" id="button" value="Configuration"  class="ui-button ui-state-default ui-corner-all" />
		';
	}
	print '<input type="hidden" name="op" id="op" value="ATLAS_SELECT"/>';
	print '			</td>
	</tr>';
	print '</table>';
	print '</form>';
	echo "\n<script type=\"text/javascript\">\n";
	echo "function chkSelectAtlas(type)";
	echo "{\n";
	echo "if(type == 'create'){
	document.atlas_list_form.op.value=\"Atlas_Create\";
	document.atlas_list_form.action =\"atlas.php\";
	document.atlas_list_form.submit();
	return true;
	}";	
	echo "if(";
	echo '$("#table_atlas_list [input:radio]:checked").length == 0';
	echo ")";
	echo "{\n";
	echo "growlError(\"Please select a atlas!\");\n";
	echo "return false;\n";
	echo "}\n";
	echo "if(type == 'delete'){
	if(confirm(\"Are you sure to delete this atlas?\")){
	document.atlas_list_form.op.value=\"Atlas_Delete\";
	document.atlas_list_form.action =\"atlas.php\";
	document.atlas_list_form.submit();
	}
	else{return false;}
	}else if(type == 'select'){
	document.atlas_list_form.op.value=\"Atlas_Select\";
	document.atlas_list_form.action =\"atlas.php\";
	document.atlas_list_form.submit();
	}else if(type == 'demo'){
	document.atlas_list_form.op.value=\"Atlas_Demo\";
	document.atlas_list_form.action =\"atlas.php\";
	document.atlas_list_form.submit();
	}";
	echo "}\n";
	
	echo "</script>\n";
	print_selectable_listtable_script('table_atlas_list');
	if($recordcount>0)
	print '<script>
	$(document).ready(function(){
	$(\'td#imgpreview a\').imgPreview({
    containerID: \'imgPreviewWithStyles\',
	srcAttr: \'src\',
    imgCSS: {
        height: 256
    },
    onShow: function(link){
        $(\'<span>\' + $(link).attr(\'title\') + \'</span>\').appendTo(this);
        $(link).stop().animate({opacity:0.4});
        //Reset image
        $(\'img\', this).stop().css({opacity:0.8});
    },
    onLoad: function(){
        $(this).animate({opacity:1}, 300);
    },
    onHide: function(link){
        $(\'span\', this).remove();
        $(link).stop().animate({opacity:1});
    }
	
	});
	});
	</script>';
	
}

function get_atlas_list($database, $listmax, $orderby, $sort, $pg = 1, $total = 0, $retry = false) {
	$colnumber = 4;
	$database->dbEmptyErrorMessage();
	$result = $database->db_get_atlas_list($listmax, $orderby, $sort, $pg, $total, 0);
	$recordcount = $database->getRowsNumber($result['result']);
	if(!$result){
		setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		//still display the list, but only one time
		if(!$retry){
			get_atlas_list($database, $listmax, "modified", "desc", $pg, $total, true);
		}
		return;
	}
	$total = $result['total'];
	$page=new page(array('total'=>$total,'perpage'=>$listmax));	
	
	print '<script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/imgPreview/imgpreview.min.0.22.jquery.js"  defer="defer"></script>';
	print '<table id="table_atlas_list" class="tableNone" >
	<tr class="title">
	<td colspan="' . $colnumber . '">Atlas List</td>
	</tr>';
	if($recordcount>0){
		print '<tr class="name">
		<td width="100px">Overview</td>
		<td >';
		print '<a href="atlas.php?op=atlaslist&total='.$total.'&order=name&sort='.($sort=='desc'?'asc':'desc').'&pg='.$pg.'">Name</a>';
		print '</td>
		<td>';
		print '<a href="atlas.php?op=atlaslist&total='.$total.'&order=modified&sort='.($sort=='desc'?'asc':'desc').'&pg='.$pg.'">Last updated</a>';
		print '</td>
		<td>';
		print '<a href="atlas.php?op=atlaslist&total='.$total.'&order=status&sort='.($sort=='desc'?'asc':'desc').'&pg='.$pg.'">Status</a>';
		print '</td>
		</tr>';
	}
	$i = 0;
	while ($row = $database->getColumns($result['result'])) {
		if ($i % 2 == 0) {
			echo '<tr class="even mouse_over" onclick="javascript:document.location = \'atlas.php?op=atlasview_viewer&aid='.$row['aid'].'&total='.$total.'&order='.$orderby.'&sort='.$sort.'&pg='.$pg.'\';" height="35px">';
		} else {
			echo '<tr class="even mouse_over" onclick="javascript:document.location = \'atlas.php?op=atlasview_viewer&aid='.$row['aid'].'&total='.$total.'&order='.$orderby.'&sort='.$sort.'&pg='.$pg.'\';" height="35px">';
		}
		echo '<td rowspan="2" id="imgpreview" style="background-color:#ffffff">';
		print '<a href="#" src="'.get_wms_path($row['aid']).'overview.gif" title="'.$row['aname'] . '">
			<img src="'.get_wms_path($row['aid']).'overview.gif" width="96px" height="96px" style="border:1px solid #808080; -moz-border-radius: 4px; -webkit-border-radius: 4px;" class="imgPrevBlock"></a>';
		echo "</td>\n";
		echo "<td>\n";
		echo '<a href="atlas.php?op=atlasview_viewer&aid='.$row['aid'].'&total='.$total.'&order='.$orderby.'&sort='.$sort.'&pg='.$pg.'" >'.$row['aname'] . '</a></td>';
		echo "<td>\n";
		echo date("Y-m-d H:i:s ", $row['modified']) . "</td>\n";
		echo "<td>\n";
		echo ($row['status']?"public":"private") . "</td>\n";
		echo "</tr>";

		print '<tr >';

		print '
		<td colspan="'.($colnumber-1).'" >';
		echo '<div class="atlas-pane-line"><span class="">Abstract: </span>'.$row['abstract'].'</div>';
		echo '<div class="tags" >';
		echo '<div class="listattr">Layers: '.$row['layercount'].'</div>';
		echo '<div class="listattr">Views: '.(empty($row['totalcount'])?'0':$row['totalcount']).'</div>';
		echo '<div class="listattr">Author: '.$row['uname'].'</div>';
		echo '<div class="listattr">on  '.date("F d, Y", $row['created']).'</div>';
		echo  '</div>';
		print '</td>
		</tr>';
		$i++;
	}
	if ($i == 0) {
		echo "<tr class=\"none\" >
		<td colspan=\"" . $colnumber . "\" align=\"center\">No Atlas</td>
		</tr>";
	}
	print '<tr>
	<td colspan="' . $colnumber . '">&nbsp;</td>
	</tr>';
	print '<tr class="footer" >
	<td colspan="' . $colnumber . '" align="center">&nbsp;';
	if ($i > 0) {
		echo $page->show(6); 
	}
	print '</td>
	</tr>';
	print '</table>';	
	
	if($i>0)
	print '<script>
	$(document).ready(function(){
	$(\'td#imgpreview a\').imgPreview({
	srcAttr: \'src\',
    containerID: \'imgPreviewWithStyles\',
    imgCSS: {
        height: 256
    },
    onShow: function(link){
        $(\'<span>\' + $(link).attr(\'title\') + \'</span>\').appendTo(this);
        $(link).stop().animate({opacity:0.4});
        //Reset image
        $(\'img\', this).stop().css({opacity:0.8});
    },
    onLoad: function(){
        $(this).animate({opacity:1}, 300);
    },
    onHide: function(link){
        $(\'span\', this).remove();
        $(link).stop().animate({opacity:1});
    }

	});
	});
	</script>';
}

/**
*
* @params: showOverview	, false will be used in mapviewer, with block gui, true will be used when visitor has no permission
* 
*/
function get_atlas_detail($database, $perview = false, $showOverview = false){
	global $atlas;
	global $user;
	if($atlas['aid'] ){
		if(!$showOverview){
		$output = '<table class="block-panel ui-corner-all" style="width:100%;" align="top" id="table_atlas_detail">
		<tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			Atlas Detail
		</td>
		</tr>';

		}else{
		$output = '
		<table id="table_atlas_detail" class="tableNone" width="100%">
		<tr class="title">
			<td colspan="2">Atlas Detail</td>
		</tr>';
		}
		if($showOverview){
			$info['favocount'] = 0; 
			if($info = $database->db_get_atlas_favoinfo($atlas['aid'], $user['uid'])){	
			}else{
				setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
			}
		
		$output .= '<tr align="center"><td colspan="2"><table class="tableNone">
			<tr><td>	
			<div class="atlas-detail-overview">
			<img src="../files/atlas/'.$atlas['aid'].'/overview.gif" alt="'.$atlas['name'].'\'s overview" title="'.$atlas['name'].'\'s overview" width="256px" height="256px" border="0">
			</div><td><td>';
		$output .= '
			<div id="block-atlas">
			<div class="block-content">
			<div class="atlas-pane-section">

			<div class="atlas-pane-line">
			<span class="atlas-pane-label">Title:</span> '.$atlas['name'].' </div>

			<div class="atlas-pane-line">
			<span class="atlas-pane-label">Author:</span> <a href="../user/user.php?uid='.$atlas['uid'].'">'.$atlas['username'].'</a>        </div>
			
			<div class="atlas-pane-line">
			<span class="atlas-pane-label">Updated:</span> '.date('Y-m-d H:i:s', $atlas['modified']).'        </div>
			
			<div class="atlas-pane-line">
			<span class="atlas-pane-label">Created:</span> '.date('Y-m-d H:i:s', $atlas['created']).'        </div>
			
			<div class="atlas-pane-line">
			<span class="atlas-pane-label">Views:</span> '.(empty($atlas['totalcount'])?'0':$atlas['totalcount']).'</div>

			<div class="atlas-pane-line">
			<span class="atlas-pane-label">Favo:</span> '.$info['favocount'].'</div>
			
			<div class="atlas-pane-line">
			<span class="atlas-pane-label">Status:</span> '.($atlas['status']==0?'private':'public').'        </div>

			<div class="atlas-pane-line">
			<span class="atlas-pane-label">Category:</span> '.$atlas['type'].' </div>

			<div class="atlas-pane-line">
			<span class="atlas-pane-label">Tags:</span> '.$atlas['tags'].' </div>
			';
		$output .= '</div>';		
		$output .= '</div>';
		$output .= '</td></tr></table></td></tr>';
		}

		$count = 0;
		foreach($atlas as $key=>$val){
			if($key != 'variable' && $key != 'aid' && $key!='uid' && $key!='key'
				&& $key!='name' && $key!='title'
				&& $key!='stid' && $key!='status' && $key!='created' && $key!='modified'
				&& $key!='username' && $key!='totalcount' && $key!='tags' && $key!='type'
			){
				if($key == 'abstract' && !$showOverview){
					$val = '<div class="block-pane-abstract-scroll">'.$val.'</div>';
				}
				if($key == 'country'){
					global $cty;
					$val = get_country_name($cty, $val);
					$key .= '/area';
				}
				$output .='<tr class="'.($count%2==1?'odd':'even').'">
					<td >'.$key.'</td>
				<td >'.$val.'</td>
				</tr>
				'; 	
			}
			$count++;
		}

		$output .= '</table>';

		print $output;
	}
}


function print_selectable_listtable_script( $id , $isCheckBox = false, $flag = ""){
	$curTableCell = 'curTableCell'.$flag;
	print '<script >
	$(document).ready(function(){
	var '.$curTableCell.' = null;

	function softclick'.$flag.'(element) {';
	if(!$isCheckBox){
		print 'if('.$curTableCell.'!=null){';
		print '$('.$curTableCell.').removeClass(\'marked\');';
		print '$('.$curTableCell.').find("input:radio").removeAttr("checked");';
		//print 'alert($('.$curTableCell.').find("input:radio").length);';
		print '}';
	}
	else{
		//first time the length is all inputs
		//print 'alert($('.$curTableCell.').find("input:checkbox").length);';
	}
	print ''.$curTableCell.' = element;
	$(element).addClass(\'marked\');';
	
	if(!$isCheckBox)
		print '$(element).find("input:radio").attr(\'checked\', \'true\');';
	else{
		print '
		if($(element).find("input:checkbox").attr(\'checked\') == true ){
			$(element).find("input:checkbox").removeAttr("checked");
			$(element).removeClass(\'marked\');
		}else{
			$(element).find("input:checkbox").attr(\'checked\', \'true\');
		}';
	}
	print '}';
	
	print '$(\'#'.$id.' tr, th\').click(function() {
		var classname = $(this).attr(\'class\'); ';
	print 'if( classname == \'even\' || classname == \'odd\' || classname == \'even mouse_over\' || classname == \'odd mouse_over\') {';	 
 	
	print 'softclick'.$flag.'(this);		
	}';
	if($isCheckBox){
		print 'if( classname == \'even marked\' || classname == \'odd marked\' || classname == \'even mouse_over marked\' || classname == \'odd mouse_over marked\') {';

		print 'softclick'.$flag.'(this);
		}';
	}
	print '});
	});
	</script>';
	/*$('#table_atlas_list tr, th').hover(
	 function() {	
	 var classname = $(this).attr('class'); 		
	 if( classname.indexOf('even')>0 || classname.indexOf( 'odd')>0) {
	 $(this).addClass('hover');
	 }
	 },
	 function() {			
	 var classname = $(this).attr('class'); 
	 if( classname.indexOf('even')>0 || classname.indexOf('odd')>0 || classname.indexOf('hover')>0){
	 $(this).removeClass('hover');
	 }
	 }
	 );*/
}

/**
 * 
 */
function get_atlas_favo_list($database, $user, $listmax, $orderby, $sort, $pg = 1, $total = 0) {
	$colnumber = 5;
	$database->dbEmptyErrorMessage();
	$result = $database->db_get_atlas_favorite_list($listmax, $orderby, $sort, $pg, $total, $user['uid']);
	$recordcount = $database->getRowsNumber($result['result']);
	if(!$result){
		setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		return;
	}
	$total = $result['total'];
	$page=new page(array('total'=>$total,'perpage'=>$listmax));		
	
	print '<script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/imgPreview/imgpreview.min.0.22.jquery.js"  defer="defer"></script>';
	print '<table id="table_atlas_list" class="tableNone" >
	<tr class="title">
	<td colspan="' . $colnumber . '">My Favorite</td>
	</tr>';
	if($recordcount != 0){
		print '<tr class="name">
		<td width="100px">Overview</td>
		<td >';
		print '<a href="atlas.php?op=favorite&total='.$total.'&order=name&sort='.($sort=='desc'?'asc':'desc').'&pg='.$pg.'">Name</a>';
		print '</td>
		<td>';
		print '<a href="atlas.php?op=favorite&total='.$total.'&order=modified&sort='.($sort=='desc'?'asc':'desc').'&pg='.$pg.'">Last updated</a>';
		print '</td>
		<td>';
		print '<a href="atlas.php?op=favorite&total='.$total.'&order=status&sort='.($sort=='desc'?'asc':'desc').'&pg='.$pg.'">Status</a>';
		print '</td>
		<td>
		<a href="atlas.php?op=favorite&total='.$total.'&order=created&sort='.($sort=='desc'?'asc':'desc').'&pg='.$pg.'">Favo Added</a>
		<span class="block-pane-icon" id="id_div_process_loading"></span></div>
		</td>
		</tr>';
	}
	$i = 0;
	while ($row = $database->getColumns($result['result'])) {
		echo '<tr id="favolistitemaid_'.$row['aid'].'" class="even mouse_over" onclick="javascript:document.location = \'atlas.php?op=atlasviewfavo_viewer&aid='.$row['aid'].'&total='.$total.'&order='.$orderby.'&sort='.$sort.'&pg='.$pg.'\';" height="35px">';
		echo '<td rowspan="2" id="imgpreview" style="background-color:#ffffff">';
		print '<a href="#" src="'.get_wms_path($row['aid']).'overview.gif" title="'.$row['aname'] . '">
			<img src="'.get_wms_path($row['aid']).'overview.gif" width="96px" height="96px" style="border:1px solid #808080; -moz-border-radius: 4px; -webkit-border-radius: 4px;" class="imgPrevBlock"></a>';
		echo "</td>\n";
		echo "<td>\n";
		echo '<a href="atlas.php?op=atlasviewfavo_viewer&aid='.$row['aid'].'&total='.$total.'&order='.$orderby.'&sort='.$sort.'&pg='.$pg.'" >'.$row['aname'] . '</a></td>';
		echo "<td>\n";
		echo date("Y-m-d H:i:s ", $row['modified']) . "</td>\n";
		echo "<td>\n";
		echo ($row['status']?"public":"private") . "</td>\n";
		echo "<td>\n";
		echo date("Y-m-d H:i ", $row['favocreated']) ;
		echo "	</td>\n";
		echo "</tr>";

		print '<tr id="favolistitembid_'.$row['aid'].'">';
		print '
		<td colspan="'.($colnumber-1).'" >';
		echo '<div class="atlas-pane-line"><span class="">Abstract: </span>'.$row['abstract'].'</div>';
		echo '<div class="atlas-pane-line"><span class="">My description: </span>'.$row['desc'].'</div>';
		echo '<div class="tags" >';
		echo '<div class="listattr">Layers: '.$row['layercount'].'</div>';
		echo '<div class="listattr">Views: '.(empty($row['totalcount'])?'0':$row['totalcount']).'</div>';
		echo '<div class="listattr">Author: '.$row['uname'].'</div>';
		echo '<div class="listattr">on  '.date("F d, Y", $row['created']).'</div>';
		echo "<img class=\"block-pane-icon\" id=\"icon_atlas_removefavo\" src=\"../img/atlas_removefavo.png\" title=\"Remove from my favorite\" 
					onclick=\"remove_favorite(".$row['aid'] .",". $user['uid'].", true);\" >";
		echo  '</div>';
		print '</td>
		</tr>';
		$i++;
	}
	if ($i == 0) {
		echo "<tr class=\"none\" >
		<td colspan=\"" . $colnumber . "\" align=\"center\">No Atlas</td>
		</tr>";
	}
	print '<tr>
	<td colspan="' . $colnumber . '">&nbsp;</td>
	</tr>';
	print '<tr class="footer" >
	<td colspan="' . $colnumber . '" align="center">&nbsp;';
	if ($i > 0) {
		echo $page->show(6); 
	}
	print '</td>
	</tr>';
	print '</table>';	
	
	if($i>0)
	print '<script>
	$(document).ready(function(){
	$(\'td#imgpreview a\').imgPreview({
	srcAttr: \'src\',
    containerID: \'imgPreviewWithStyles\',
    imgCSS: {
        height: 256
    },
    onShow: function(link){
        $(\'<span>\' + $(link).attr(\'title\') + \'</span>\').appendTo(this);
        $(link).stop().animate({opacity:0.4});
        //Reset image
        $(\'img\', this).stop().css({opacity:0.8});
    },
    onLoad: function(){
        $(this).animate({opacity:1}, 300);
    },
    onHide: function(link){
        $(\'span\', this).remove();
        $(link).stop().animate({opacity:1});
    }

	});
	});
	</script>';
	
}

function block_atlas_list($database, $listmax, $orderby, $sort) {
	$colnumber = 3;
	$database->dbEmptyErrorMessage();
	$result = $database->db_get_atlas_list($listmax, $orderby, $sort, 1, 0, 0);
	$recordcount = $database->getRowsNumber($result['result']);
	if(!$result){
		setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		return;
	}
	$total = $result['total'];
	
	print '<table id="table_atlas_list" class="block-panel block-panel-newestatlas" >
	<tr class="block-panel-header block-panel-header-newestatlas">
	<td colspan="' . $colnumber . '">New Atlas</td>
	</tr>';
	$i = 0;
	while ($row = $database->getColumns($result['result'])) {
		echo '<tr class="even mouse_over" onclick="javascript:document.location = \'atlas/atlas.php?op=atlasview_viewer&aid='.$row['aid'].'\';" >';
		echo '<td rowspan="2" id="imgpreview" style="background-color:#ffffff">';
		print '<img src="'.get_wms_path($row['aid']).'overview.gif" width="48px" height="48px" style="border:1px solid #808080;" >';
		echo "</td>\n";
		echo "<td class=\"block-pane-home-item\">\n";
		echo '<a href="atlas/atlas.php?op=atlasview_viewer&aid='.$row['aid'].'" >'.$row['aname'] . '</a></td>';
		echo "<td class=\"block-pane-home-item\">\n";
		echo date("F d, Y", $row['created']) . "</td>\n";
		echo "</tr>";

		print '<tr >';

		print '
		<td colspan="'.($colnumber-1).'" >';
		echo '<div class="block-pane-home-item">'.(strlen($row['abstract'])>45?substr($row['abstract'],0,45)."...":$row['abstract'])."</div>";
		echo '<div class="" >';
		echo '<div class="listattr">Layers: '.$row['layercount'].'</div>';
		echo '<div class="listattr">Views: '.(empty($row['totalcount'])?'0':$row['totalcount']).'</div>';
		echo '<div class="listattr">Author: '.$row['uname'].'</div>';
		echo '<div class="listattr"> '.($row['status']?"public":"private").'</div>';
		echo  '</div>';
		print '</td>
		</tr>';
		$i++;
	}
	if ($i == 0) {
		echo "<tr class=\"none\" >
		<td colspan=\"" . $colnumber . "\" align=\"center\">No Atlas</td>
		</tr>";
	}
	print '<tr>
	<td colspan="' . $colnumber . '" class="block-pane-home-item" align="right"><a href="atlas/atlas.php">more</a></td>
	</tr>';
	print '</table>';	
}

function block_randomatlas_list($database, $listmax) {
	$database->dbEmptyErrorMessage();
	$result = $database->db_get_randomatlas($listmax);
	$recordcount = $database->getRowsNumber($result);
	if(!$result){
		setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		return;
	}
	if($recordcount>0){
		print '<ul id="mycarousel" class="jcarousel-skin-tango">';
	}
	while ($row = $database->getColumns($result)) {	
	    print '<li><a href="atlas/atlas.php?op=atlasview_viewer&aid='.$row['aid'].'">
			<img src="'.get_wms_path($row['aid']).'overview.gif" width="58" height="58" alt="'.$row['aname'] . '"  border="0"/></a></li>';
	}
	if($recordcount>0){
		print '</ul>';
		print '<script type="text/javascript" src="cssjs/lib/jquery/js/plugin/jcarousel/lib/jquery.jcarousel.pack.js"></script>
		<link rel="stylesheet" type="text/css" href="cssjs/lib/jquery/js/plugin/jcarousel/lib/jquery.jcarousel.css" />
		<link rel="stylesheet" type="text/css" href="cssjs/lib/jquery/js/plugin/jcarousel/skins/tango/skin.css" />
		<script type="text/javascript">
		jQuery(document).ready(function() {
		    jQuery(\'#mycarousel\').jcarousel({visible: 2, scroll: 2});
		});
		</script>
		<style type="text/css">
		.jcarousel-skin-tango .jcarousel-container-horizontal {
		    width: 114px;
		}
		.jcarousel-skin-tango .jcarousel-clip-horizontal {
		    width: 120px;
		}
		</style>

		';
	}	
}

function block_hotestatlas($database, $page = "") {
	if($page != "home"){
		$urlpre = "../";
	}
	$colnumber = 3;
	$database->dbEmptyErrorMessage();
	$result = $database->db_get_hotestatlas();
	$recordcount = $database->getRowsNumber($result);
	if(!$result){
		setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		return;
	}
	
	print '<table id="table_atlas_list" class="block-panel block-panel-hotestatlas" width="97%">
	<tr class="block-panel-header block-panel-header-hotestatlas">
	<td colspan="' . $colnumber . '">Today\'s Hot</td>
	</tr>';
	$i = 0;
	while ($row = $database->getColumns($result)) {
		echo '<tr class="even mouse_over" onclick="javascript:document.location = \'atlas/atlas.php?op=atlasview_viewer&aid='.$row['aid'].'\';" >';
		echo '<td rowspan="2" id="imgpreview" style="background-color:#ffffff">';
		print '<img src="'.get_wms_path($row['aid']).'overview.gif" width="48px" height="48px" style="border:1px solid #808080;" >';
		echo "</td>\n";
		echo "<td class=\"block-pane-home-item\" colspan=\"".($colnumber-1)."\">\n";
		echo '<a href="';
		if($page!="home")echo $urlpre;
		echo 'atlas/atlas.php?op=atlasview_viewer&aid='.$row['aid'].'" title="view map">'.$row['aname'].'</a></td>';
		echo '</tr>';

		print '<tr >';
		print '
		<td colspan="'.($colnumber-1).'" >';
		echo '<div class="block-pane-home-item">'.(strlen($row['abstract'])>45?substr($row['abstract'],0,45)."...":$row['abstract'])."</div>";
		print '</td>
		</tr>';
		$i++;	
		$aid = $row['aid'];
		$mapname = $row['aname'];
	}
	if ($i == 0) {
		echo "<tr class=\"none\" >
		<td colspan=\"" . $colnumber . "\" align=\"center\">No Atlas</td>
		</tr>";
	}else{
?>		
	<tr>
		<td colspan="<?=$colnumber?>" style="padding:0px;">
		<iframe src ="atlas/atlas_hotest.php?aid=<?=$aid?>&mapname=<?=$mapname?>" 
			width="100%" height="255px" scrolling="no" frameborder="0" align="top" 
			marginheight="2px" marginwidth="2px"
			style="margin:0px;padding:0px">
		</iframe>
		</td>
	</tr>	
<?		
	}
	print '</table>';	
}




/**
 *
 * @Description : get the atlas metadata formular
 * @params : $info, if not null, uset the data to fill the formular
 * @params : $aid, if not 0, connect database to get the atlas metadata
 * @return false if failed
 * @usage :
 */
function get_atlas_create_form($user, $database, $info = null, $aid = 0) {
	$uid = $user['uid'];
	if ($aid != 0) {
		$data = $database->db_get_atlas($aid, $uid);
		if (!$data) {
			setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
			return false;
		} else {
			$info['AtlasName'] = $data['name'];
			//$info['enablestretchmap'] = $data['stretch'];
			$info['AtlasStatus'] = $data['status'];
			$info['ServerTitle'] = $data['title'];
			$info['ServerAbstract'] = $data['abstract'];
			$info['LayerTitle'] = $data['layertitle'];
			$info['Keyword1'] = $data['keyword1'];
			$info['Keyword2'] = $data['keyword2'];
			$info['Keyword3'] = $data['keyword3'];
			$info['Keyword4'] = $data['keyword4'];
			$info['ContactPerson'] = $data['person'];
			$info['ContactOrganization'] = $data['organization'];
			$info['ContactPosition'] = $data['position'];
			$info['ContactAddress'] = $data['contactaddress'];
			$info['AddressType'] = $data['addresstype'];
			$info['Address'] = $data['address'];
			$info['City'] = $data['city'];
			$info['StateOrProvince'] = $data['stateorprovince'];
			$info['PostCode'] = $data['postcode'];
			$info['Country'] = $data['country'];
			$info['ContactVoiceTelephone'] = $data['phone'];
			$info['ContactFacsimileTelephone'] = $data['phone'];
			$info['ContactElectronicMailAddress'] = $data['mail'];
			$info['ServerFee'] = $data['fees'];
			$info['ServerAccessconstraints'] = $data['accessconstraints'];
			$info['ServerType'] = $data['type'];
		}
	}
	echo '<form action="atlas.php"  accept-charset="UTF-8" method="post" name="atlas_create_form" id="atlas_create_form" onSubmit="return chk_atlas_create_form();">';
	print '<table class="tableNone">';
	if($info == null){
		print '<tr class="title"><td colspan="2">Atlas '.($info == null?'':'(<b>'.$_SESSION['atlas']['name'].'</b>)').' Metadata</td></tr>';
		print '<tr><td align="left"><img src="../img/steps_1.png"></td><td></td></tr>';
	}else{
		print '<tr class="title"><td colspan="2">'.($info == null?'':''.$_SESSION['atlas']['name']).'</td></tr>';
	}
	echo '<tr  class="odd">
	<td>Name: <span class="form-required" title="This field is required.">*</span></td>
	<td><input name="AtlasName" type="text" id="AtlasName" size="32" value="' . $info['AtlasName'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
	</tr>';
	
	print '
	<tr  class="even">
	<td>Server Title: <span class="form-required" title="This field is required.">*</span></td>
	<td><input name="ServerTitle" type="text" id="ServerTitle" size="32" value="' . $info['ServerTitle'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
	</tr>
	<tr  class="odd">
	<td>Server Abstract: <span class="form-required" title="This field is required.">*</span></td>
	<td><TEXTAREA name="ServerAbstract" id="ServerAbstract" size="256" wrap="VIRTUAL" class="smallInput, editbox2" onmouseover="txtfieldSelectAll(this);">' . $info['ServerAbstract'] . '</TEXTAREA>
	</td>
	</tr>
	<tr  class="even">
	<td>Layer Title: <span class="form-required" title="This field is required.">*</span></td>
	<td><input name="LayerTitle" type="text" id="LayerTitle" size="64" value="' . $info['LayerTitle'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);"  /></td>
	</tr>
	<tr  class="odd">
	<td>Keyword1: <span class="form-required" title="This field is required.">*</span></td>
	<td><input name="Keyword1" type="text" id="Keyword1" size="64" value="' . $info['Keyword1'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
	</tr>
	<tr  class="even">
	<td>Keyword2:</td>
	<td><input name="Keyword2" type="text" id="Keyword2" size="64" value="' . $info['Keyword2'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
	</tr>
	<tr  class="odd">
	<td>Keyword3: </td>
	<td><input name="Keyword3" type="text" id="Keyword3" size="64" value="' . $info['Keyword3'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);"  /></td>
	</tr>
	<tr  class="even">
	<td>Keyword4:</td>
	<td><input name="Keyword4" type="text" id="Keyword4" size="64" value="' . $info['Keyword4'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);"  /></td>
	</tr>
	<tr  class="odd">
	<td>ContactPerson: </td>
	<td><input name="ContactPerson" type="text" id="ContactPerson" size="64" value="' . $info['ContactPerson'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);"  /></td>
	</tr>
	<tr  class="even">
	<td>ContactOrganization:</td>
	<td><input name="ContactOrganization" type="text" id="ContactOrganization" size="64" value="' . $info['ContactOrganization'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);"  /></td>
	</tr>
	
	<tr  class="odd">
	<td>ContactPosition:</td>
	<td><input name="ContactPosition" type="text" id="ContactPosition" size="64" value="' . $info['ContactPosition'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);"/></td>
	</tr>
	<tr  class="even">
	<td>ContactAddress:</td>
	<td><input name="ContactAddress" type="text" id="ContactAddress" size="64" value="' . $info['ContactAddress'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
	</tr>
	<tr  class="odd">
	<td>AddressType:</td>
	<td><input name="AddressType" type="text" id="AddressType" size="32" value="' . $info['AddressType'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
	</tr>
	<tr  class="even">
	<td>Address:</td>
	<td><input name="Address" type="text" id="Address" size="64" value="' . $info['Address'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);"  /></td>
	</tr>
	<tr  class="odd">
	<td>City:</td>
	<td><input name="City" type="text" id="City" size="32" value="' . $info['City'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);"  /></td>
	</tr>
	<tr  class="even">
	<td>StateOrProvince:</td>
	<td><input name="StateOrProvince" type="text" id="StateOrProvince" size="64" value="' . $info['StateOrProvince'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
	</tr>
	<tr  class="odd">
	<td>PostCode:</td>
	<td><input name="PostCode" type="text" id="PostCode" size="32" value="' . $info['PostCode'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
	</tr>
	<tr  class="even">
	<td>Country(or area):</td>
	<td>';
	global $cty;
	print get_country_list($cty, $info['Country']);
	print '</td>
	</tr>
	<tr  class="odd">
	<td>ContactVoiceTelephone:</td>
	<td><input name="ContactVoiceTelephone" type="text" id="ContactVoiceTelephone" size="32" value="' . $info['ContactVoiceTelephone'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);"  /></td>
	</tr>
	<tr  class="even">
	<td>ContactFacsimileTelephone:</td>
	<td><input name="ContactFacsimileTelephone" type="text" id="ContactFacsimileTelephone" size="32" value="' . $info['ContactFacsimileTelephone'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
	</tr>
	<tr  class="odd">
	<td>ContactElectronicMailAddress:</td>
	<td><input name="ContactElectronicMailAddress" type="text" id="ContactElectronicMailAddress" size="64" value="' . $info['ContactElectronicMailAddress'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
	</tr>
	<tr  class="even">
	<td>Fee:</td>
	<td><input name="ServerFee" type="text" id="ServerFee" size="32" value="' . $info['ServerFee'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
	</tr>
	<tr  class="odd">
	<td>AccessConstraints:</td>
	<td><input name="ServerAccessconstraints" type="text" id="ServerAccessconstraints" size="32" value="' . $info['AccessConstraints'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
	</tr>
	<tr  class="even">
	<td>Type:</td>
	<td><input name="ServerType" type="text" id="ServerType" size="32" value="' . $info['ServerType'] . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" /></td>
	</tr>
	';
	print '<tr class="footer">
	<td align="left">';
	//this step can not go back, only can be canceled
	print '<input onclick="javascript:window.location.href=\'atlas.php?op=myatlas\'" type="button" value="Cancel" class="ui-button ui-state-default ui-corner-all">';
	print '</td>
	<td>' .
	'<input onclick="reset_atlas_create_form();" type="button" value="Reset" class="ui-button ui-state-default ui-corner-all">&nbsp;&nbsp;';
	if ($aid != 0 ) {
		print '<input type="hidden" name="atlas_aid" id="atlas_aid" value="' . $aid . '"/>';
		print '<input type="hidden" name="op" id="op" value="ATLAS_SAVE"/>';
		print '<input type="submit" name="button" id="button" value="Save"  class="ui-button ui-state-default ui-corner-all" />';
	} else {
		print '<input type="hidden" name="op" id="op" value="ATLAS_CREATE_STEP0"/>';
		print '<input type="submit" name="button" id="button" value="Continue"  class="ui-button ui-state-default ui-corner-all" />';
	}
	
	print '</td>
	</tr>';
	print '</table>';
	print '</form>';
}

function atlas_create($user, $database, $POST) {
	$uid = $user['uid'];
	$info['AtlasName'] = $POST['AtlasName'];
	//$info['enablestretchmap'] = $POST['enablestretchmap'];
	$info['AtlasStatus'] = $POST['AtlasStatus'];
	$info['ServerTitle'] = $POST['ServerTitle'];
	$info['ServerAbstract'] = $POST['ServerAbstract'];
	$info['LayerTitle'] = $POST['LayerTitle'];
	$info['Keyword1'] = $POST['Keyword1'];
	$info['Keyword2'] = $POST['Keyword2'];
	$info['Keyword3'] = $POST['Keyword3'];
	$info['Keyword4'] = $POST['Keyword4'];
	$info['ContactPerson'] = $POST['ContactPerson'];
	$info['ContactOrganization'] = $POST['ContactOrganization'];
	$info['ContactPosition'] = $POST['ContactPosition'];
	$info['ContactAddress'] = $POST['ContactAddress'];
	$info['AddressType'] = $POST['AddressType'];
	$info['Address'] = $POST['Address'];
	$info['City'] = $POST['City'];
	$info['StateOrProvince'] = $POST['StateOrProvince'];
	$info['PostCode'] = $POST['PostCode'];
	$info['Country'] = $POST['Country'];
	$info['ContactVoiceTelephone'] = $POST['ContactVoiceTelephone'];
	$info['ContactFacsimileTelephone'] = $POST['ContactFacsimileTelephone'];
	$info['ContactElectronicMailAddress'] = $POST['ContactElectronicMailAddress'];
	$info['ServerFee'] = $POST['ServerFee'];
	$info['ServerAccessconstraints'] = $POST['ServerAccessconstraints'];
	$info['ServerType'] = $POST['ServerType'];
	
	
	$key = md5(time());
	if (!$aid = $database->db_create_atlas($uid, $key, $info)) {
		setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		return false;
	} else {
		setSessionMessage(t('Atlas <b>%name</b> has been created successfully, please continue to finish the installation.', array (
			'%name' => $info['AtlasName']
		)), SITE_MESSAGE_INFO);
		//create folder with name '$aid' in files/atlas/
		 /*
		  -atlas
		  --aid
		  wms.php
		  wfs.php
		  ----rasterdata(raster map)
		  ----sld
		  -----legends
		  ------stid(pngs)
		  ----cache
		  ----logs(import) need expried-time
		  ----fonts
		  en.ttc,cn.ttc
		  */
		//only create this for first atlas
		if($aid == 1){
			if(!createFile("../files/atlas/cache/")){
				setSessionMessage(t('Atlas <b>%name</b> has been created successfully, please continue to finish the installation. But %folder folder can not be created under %atlasroot. Please chmod this directory to 777 if under Unix or set it to writable under Windows.', array (
					'%name' => $info['AtlasName'], '%folder' => "cache", '%atlasroot' => "files/atlas/"
				)), SITE_MESSAGE_INFO);
			}
		}
		if(!createFile("../files/atlas/".$aid."/")){
			setSessionMessage(t('Atlas <b>%name</b> has been created successfully, please continue to finish the installation. But %file file can not be created under %atlasroot. Please chmod this directory to 777 if under Unix or set it to writable under Windows.', array (
				'%name' => $info['AtlasName'], '%file' => "$aid", '%atlasroot' => "files/atlas/"
			)), SITE_MESSAGE_INFO);
			return false;
		}
		
		if(!createFile("../files/atlas/".$aid."/wms.php")){
			setSessionMessage(t('Atlas <b>%name</b> has been created successfully, please continue to finish the installation. But %file file can not be created under %atlasroot. Please chmod this directory to 777 if under Unix or set it to writable under Windows.', array (
				'%name' => $info['AtlasName'], '%file' => "wms.php", '%atlasroot' => "files/atlas/$aid/"
			)), SITE_MESSAGE_INFO);
			return false;
		}
		if(!createFile("../files/atlas/".$aid."/wfs.php")){
			setSessionMessage(t('Atlas <b>%name</b> has been created successfully, please continue to finish the installation. But %file file can not be created under %atlasroot. Please chmod this directory to 777 if under Unix or set it to writable under Windows.', array (
				'%name' => $info['AtlasName'], '%file' => "wfs.php", '%atlasroot' => "files/atlas/$aid/"
			)), SITE_MESSAGE_INFO);
			return false;
		}
		atlas_create_query_interface($aid, 0);
		atlas_create_query_interface($aid, 1);
		
		if(!createFile("../files/atlas/".$aid."/rasterdata/")){
			setSessionMessage(t('Atlas <b>%name</b> has been created successfully, please continue to finish the installation. But %folder folder can not be created under %atlasroot. Please chmod this directory to 777 if under Unix or set it to writable under Windows.', array (
				'%name' => $info['AtlasName'], '%folder' => "rasterdata", '%atlasroot' => "files/atlas/$aid/"
			)), SITE_MESSAGE_INFO);
		}
		if(!createFile("../files/atlas/".$aid."/sld/legends/")){
			setSessionMessage(t('Atlas <b>%name</b> has been created successfully, please continue to finish the installation. But %folder folder can not be created under %atlasroot. Please chmod this directory to 777 if under Unix or set it to writable under Windows.', array (
				'%name' => $info['AtlasName'], '%folder' => "sld/legends", '%atlasroot' => "files/atlas/$aid/"
			)), SITE_MESSAGE_INFO);
		}
		if(!createFile("../files/atlas/".$aid."/cache/")){
			setSessionMessage(t('Atlas <b>%name</b> has been created successfully, please continue to finish the installation. But %folder folder can not be created under %atlasroot. Please chmod this directory to 777 if under Unix or set it to writable under Windows.', array (
				'%name' => $info['AtlasName'], '%folder' => "cache", '%atlasroot' => "files/atlas/$aid/"
			)), SITE_MESSAGE_INFO);
		}
		if(!createFile("../files/atlas/".$aid."/logs/")){
			setSessionMessage(t('Atlas <b>%name</b> has been created successfully, please continue to finish the installation. But %folder folder can not be created under %atlasroot. Please chmod this directory to 777 if under Unix or set it to writable under Windows.', array (
				'%name' => $info['AtlasName'], '%folder' => "logs", '%atlasroot' => "files/atlas/$aid/"
			)), SITE_MESSAGE_INFO);
		}
		if(!createFile("../files/atlas/".$aid."/fonts/")){
			setSessionMessage(t('Atlas <b>%name</b> has been created successfully, please continue to finish the installation. But %folder folder can not be created under %atlasroot. Please chmod this directory to 777 if under Unix or set it to writable under Windows.', array (
				'%name' => $info['AtlasName'], '%folder' => "fonts", '%atlasroot' => "files/atlas/$aid/"
			)), SITE_MESSAGE_INFO);
		}
		if(@!xFileCopy("../img/overview.gif", "../files/atlas/".$aid."/" , false)){

		}
		
		return $aid;
	}
}

/**
 * type:
 * 		0: wms;
 * 		1: wfs
 */
function atlas_create_query_interface($aid, $type=0){
	include 'atlas.tmpl.v';
	$temp_file = "";
	$tmparray = array("##AID##", "##INTERFACE_VERSION##");
	if($type == 0){
		$temp_file = "wms.tmpl";
		$paramsarray = array($aid, $wms_interface_version);
	}else if($type == 1){
		$temp_file = "wfs.tmpl";
		$paramsarray = array($aid, $wfs_interface_version);
	}
	if (file_exists($temp_file)) {
		@ $fp = fopen($temp_file, "r");
		while (!feof($fp)) {
			$line .= fgets($fp, 1024);
		}
		$line = str_replace($tmparray, $paramsarray, $line);
		@ fclose($fp);
	} else {
		echo "Template " . $temp_file . " does not exist, please check it.";
		return false;
	}
	//=====================
	if($type == 0){
		$query_filename = "../files/atlas/".$aid."/wms.php";
	}else if($type == 1){
		$query_filename = "../files/atlas/".$aid."/wfs.php";
	}
	@ $query_file = fopen($query_filename, 'w+');
	if (!$query_file) {
		return false;
	}
	@ fwrite($query_file, $line);
	@ fclose($query_file);
}

function atlas_update($user, $aid, $database, $info) {
	$uid = $user['uid'];
	if (!$database->db_update_atlas($uid, $aid, $info)) {
		setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		return false;
	} else {
		//TODO delete later
		if(!file_exists("../files/atlas/".$aid."/overview.gif")){
			xFileCopy("../img/overview.gif", "../files/atlas/".$aid."/" , false);
		}
		//TODO later will not use this
		atlas_create_query_interface($aid, 0);
		atlas_create_query_interface($aid, 1);
		setSessionMessage(t('Atlas <b>%name</b> has been updated successfully.', array (
			'%name' => $info['AtlasName']
		)), SITE_MESSAGE_INFO);
		return true;
	}
}

function atlas_delete($user, $aid, $database, $atlasName){
	$uid = $user['uid'];
	if (!$database->db_drop_atlas($uid, $aid)) {
		setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		return false;
	} else {
		//delete atlas directory
		//-atlas
		//--aid
		$aidpath = "../files/atlas/".$aid;
		if(file_exists($aidpath)){
			removeDir($aidpath, true);
		}
		
		setSessionMessage(t('Atlas <b>%name</b> has been deleted successfully.', array (
			'%name' => $atlasName
		)), SITE_MESSAGE_INFO);
		return true;
	}
}

function get_atlas_cfg_form($user, $aid, $database, $isInProcess = false){
	$database->dbEmptyErrorMessage();
	$uid = $user['uid'];
	if ($aid != 0) {
		$data = $database->db_get_atlas_cfg($uid, $aid);
		if (!$data) {
			setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
			return false;
		} else {
			include_once '../parser/AttributeParser.class.php';
			$cfg = AttributeParser::extractAttribute($data['variable']);
			//print_r($cfg);
		}
	}
	echo '<form action="atlas.php"  accept-charset="UTF-8" method="post" name="atlas_cfg_form" id="atlas_cfg_form" onSubmit="return chk_atlas_cfg_form();">';
	print '<table class="tableNone">';
	if($isInProcess){
		print '<tr class="title"><td colspan="3">Atlas '.($isInProcess?'':'(<b>'.$_SESSION['atlas']['name'].'</b>)').' Configuration</td></tr>';
		print '<tr><td align="left"><img src="../img/steps_5.png"></td><td></td><td></td></tr>';
	}else{
		print '<tr class="title"><td colspan="3">'.($isInProcess?'':''.$_SESSION['atlas']['name']).'</td></tr>';
	}
	echo '<tr  class="odd">
	<td width="20%">Name: </td>
	<td width="30%">' . $data['name'] . '<input name="AtlasName" type="hidden" id="AtlasName" size="32" value="' . ($data['name']) . '" />' .
	'</td><td width="50%"></td>
	</tr>';
	
	echo "<tr class=\"even\">
	<td width=\"30%\">Enable Stretch Map :";
	print_help("Enable Stretch Map", 'Description:', 'In the case that a GetMap request is made where the aspect ratio of the selected BBOX and the ratio of the WIDTH/HEIGHT parameters is different, the returned map will be stretched if Stretch Map is enabled. Otherwise the returned map will be in the center of the image with nochanging WIDTH/HEIGHT.');
	echo "			</td>";
	echo "<td>";
	echo '<input type="radio" name= "enablestretchmap"  class="button3" value="1" '.($cfg['enablestretchmap']==1||$cfg['enablestretchmap']==""?'CHECKED':'').'>yes';
	echo '&nbsp&nbsp&nbsp&nbsp<input type="radio" name= "enablestretchmap"  class="button3" value="0" '.($cfg['enablestretchmap']==0?'CHECKED':'').'>no';
	echo "</td><td></td>
	</tr>";
	print '
	<tr  class="odd">
	<td>Atlas Status: <span class="form-required" title="This field is required.">*</span></td>
	<td>
	<select name="AtlasStatus" id="AtlasStatus">';
	print '<option value="0" '.($data['status'] == 0||$data['status'] == ""?'selected':'').'>private</option>';
	print '<option value="1" '.($data['status'] == 1?'selected':'').'>public</option>';
	print '</select>
	</td><td></td>
	</tr>';
	print '
	<tr  class="even">
	<td>Show Copyright: <span class="form-required" title="This field is required.">*</span>';
	print_help("Show Copyright", 'Description:', 'Show the Copyright information on the map. ');
	print '</td>
	<td>';
	echo '<input type="radio" name= "showCopyright"  class="button3" value="1" '.($cfg['showCopyright']==1||$cfg['showCopyright']==""?'CHECKED':'').'>yes';
	echo '&nbsp&nbsp&nbsp&nbsp<input type="radio" name= "showCopyright"  class="button3" value="0" '.($cfg['showCopyright']==0?'CHECKED':'').'>no';
	print '</td><td></td>
	</tr>';
	print '<tr  class="odd">
	<td>Cache Expired Time: <span class="form-required" title="This field is required.">*</span>';
	print_help("Cache Expired Time", 'Description:', 'Set the cache expired timeout, unit is second. ' .'
		0 will disable cahce
		86400 is 24 hours one day
		3600 is one hour
	1800 is half an hour');
	//can not use empty($cfg['cacheExpiredTime']), always be true, why???
	print '</td>
	<td><input name="cacheExpiredTime" type="text" id="cacheExpiredTime" size="32" value="' . ($cfg['cacheExpiredTime']==''?DEFAULT_VALUE_cacheExpiredTime:$cfg['cacheExpiredTime']) . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" />
	</td><td></td>
	</tr>
	';
	print '<tr  class="even">
	<td>Enable SVG Pixel Coordinate: <span class="form-required" title="This field is required.">*</span>';
	print_help("Enable SVG Pixel Coordinate", 'Description:', 'If enable this, SVG map will use pixel(screen) coornidate instead of real coordinate. If the difference in your coordinate is too small, for example, (122.44220 37.70174) (122.44244 37.70247). The difference between the two points is (0.00024, 0.00073), that is 1/300-400 of one pixel, and it is difficult to display the difference in the screen, because the stroke width of line is normally only 1 pixel!. Unless you insist that the SVG map MUST contain the real coordinate information, or you can set the stroke-width to one value according to the difference in Style Defination(in this case, is 0.0001). ');
	print '</td>
	<td>';
	echo '<input type="radio" name= "enableSVGPixelCoordinate"  class="button3" value="1" '.($cfg['enableSVGPixelCoordinate']==1?'CHECKED':'').'>yes';
	echo '&nbsp&nbsp&nbsp&nbsp<input type="radio" name= "enableSVGPixelCoordinate"  class="button3" value="0" '.($cfg['enableSVGPixelCoordinate']==0||$cfg['enableSVGPixelCoordinate']==""?'CHECKED':'').'>no';
	print '</td><td></td>
	</tr>
	';
	print '<tr  class="odd">
	<td>Enable Stream SVG: <span class="form-required" title="This field is required.">*</span>';
	print_help("Enable SVG Pixel Coordinate", 'Description:', 'SVG map could be outputted in two ways, one is DOM method, the other is stream method</br>No: using DOM method, need more memory, but easy to handle the exception</br>Yes: using Stream method, need less memory, but difficult to handle the exception</br>If you feel that the SVG file is being created slowly, you could try stream method. ');
	print '</td>
	<td>';
	echo '<input type="radio" name= "enableStreamSVG"  class="button3" value="1" '.($cfg['enableStreamSVG']==1||$cfg['enableStreamSVG']==""?'CHECKED':'').'>yes';
	echo '&nbsp&nbsp&nbsp&nbsp<input type="radio" name= "enableStreamSVG"  class="button3" value="0" '.($cfg['enableStreamSVG']==0?'CHECKED':'').'>no';
	print '</td><td></td>
	</tr>
	';
	print '<tr  class="even">
	<td>Output Encode Country: <span class="form-required" title="This field is required.">*</span>';
	print_help("Output Encode Country", 'Description:', 'Set the country code which will be selected as output font for the text string.
		The default language is English.
		If your country are not listed below, please create a folder with ISO country name (ie. it for Italy) in fonts folder
		And find arial.ttf or the most oftenly used font in your system (for Windows, in c:/windows/Fonts/ directory), and rename the ttf file to arial.tff,
	and copy the arial.ttf into the folder you have createn in the fonts folder. ');
	print '</td>
	<td>';
	print '<select name="outputEncodeCountry" id="outputEncodeCountry">';
	print '<option value="cns" '.($cfg['outputEncodeCountry'] == "cns"?'selected':'').'>cns</option>';
	print '<option value="cnt" '.($cfg['outputEncodeCountry'] == "cnt"?'selected':'').'>cnt</option>';
	print '<option value="en" '.($cfg['outputEncodeCountry'] == "en"||$cfg['outputEncodeCountry'] == ""?'selected':'').'>en</option>';
	print '<option value="de" '.($cfg['outputEncodeCountry'] == "de"?'selected':'').'>de</option>';
	print '<option value="ru" '.($cfg['outputEncodeCountry'] == "ru"?'selected':'').'>ru</option>';
	print '</select>';
	print '</td><td></td>
	</tr>
	';
	print '<tr  class="odd">
	<td>Overlap Ratio: <span class="form-required" title="This field is required.">*</span>';
	print_help("Overlap Ratio", 'Description:', 'OverlapRatio defines the ratio that the neighboring tiles could lay over this will avoid that the geometries between the 2 tiles could have gap or the text string was truncated by the boundary of tiles value is between 0 and 1, the bigger the value is, the gap can be reduced more, but notice, this will decelerate the rendering of map ');
	print '</td>
	<td><input name="OverlapRatio" type="text" id="OverlapRatio" size="5" value="' . (empty($cfg['OverlapRatio'])?DEFAULT_VALUE_OverlapRatio:$cfg['OverlapRatio']) . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" />
	</td><td><div style="width:100px;" id="slider_OverlapRatio"></div>
	<script type="text/javascript">
	$(function() {
		$("#slider_OverlapRatio").slider({ min: 0.0, max:0.9, step: 0.1, value: 0.1,
		slide: function(e, ui) {document.atlas_cfg_form.OverlapRatio.value = ui.value;}
		});
	});
	</script>
	</td>
	</tr>
	';
	print '<tr  class="even">
	<td>GetMap25D Overlap Ratio: <span class="form-required" title="This field is required.">*</span>';
	print_help("GetMap25D Overlap Ratio", 'Description:', 'GetMap25DOverlapRatio is same as OverlapRatio, but only used for WMS GetMap25D request ');
	print '</td>
	<td><input name="GetMap25DOverlapRatio" type="text" id="GetMap25DOverlapRatio" size="5" value="' . (empty($cfg['GetMap25DOverlapRatio'])?DEFAULT_VALUE_GetMap25DOverlapRatio:$cfg['GetMap25DOverlapRatio']) . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" />
	</td><td><div style="width:100px;" id="slider_GetMap25DOverlapRatio"></div>
	<script type="text/javascript">
	$(function() {
		$("#slider_GetMap25DOverlapRatio").slider({ min: 0.0, max:0.9, step: 0.1, value: 0.1,
		slide: function(e, ui) {document.atlas_cfg_form.GetMap25DOverlapRatio.value = ui.value;}
		});
	});
	</script>
	</td>
	</tr>
	';
	print '<tr  class="odd">
	<td>Server URL: ';
	print_help("Server URL", 'Description:', 'The query protocol for this atlas. Please use url when you apply for your private Google Map Key. ');
	print '</td>
	<td>'. get_base_server_host().'files/atlas/'.$aid.'/wms.php'.
	'</td><td></td>
	</tr>
	';
	print '<tr  class="odd">
	<td>Google Map Key: <span class="form-required" title="This field is required.">*</span>';
	print_help("Google Map Key", 'Description:', 'Define your google Map key, please replace the string with your google map key for your webhost. If you have not, please go to http://www.google.com/apis/maps/signup.html to apply for one ');
	print '</td>
	<td><input name="GoogleMapKey" type="text" id="GoogleMapKey" size="32" value="' . (empty($cfg['GoogleMapKey'])?'':$cfg['GoogleMapKey']) . '" class="smallInput" onmouseover="txtfieldSelectAll(this);" />
	</td><td></td>
	</tr>
	';
	
	print '<tr class="footer">
	<td align="left">';
	if ($isInProcess) {
		//dont enable goback, need set style information, complex
		//print '<input onclick="javascript:window.location.href=\'atlas.php?op=ATLAS_CREATE_STEP3_LAYERINFO&isBackStep=TRUE&aid=' . $aid . '\'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">';
		print '<input onclick="javascript:GoBack();" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">';
		print '&nbsp;';
		print '<input onclick="javascript:window.location.href=\'atlas.php?op=myatlas\'" type="button" value="Cancel" class="ui-button ui-state-default ui-corner-all">';
	}else{
		print '<input onclick="javascript:window.location.href=\'atlas.php?op=myatlas\'" type="button" value="Cancel" class="ui-button ui-state-default ui-corner-all">';
	}
	print '</td><td></td>
	<td>';
	
	print '<input type="hidden" name="atlas_aid" id="atlas_aid" value="' . $aid . '"/>';
	if ($isInProcess) {
		print '<input type="hidden" name="op" id="op" value="ATLAS_CREATE_STEP5_FINISH"/>
		<input type="submit" name="btnContinue" value="Continue" class="ui-button ui-state-default ui-corner-all"/>';
	}else{
		print '<input type="hidden" name="op" id="op" value="atlas_cfg_save"/>';
		print '<input type="submit" name="button" id="button" value="Save"  class="ui-button ui-state-default ui-corner-all" />';
	}
	print '</td><td></td>
	</tr>';
	print '</table>';
	print '</form>';
	
}

function atlas_cfg_update($user, $aid, $database, $POST) {
	$uid = $user['uid'];
	$info['AtlasName'] = $POST['AtlasName'];
	
	$info['enablestretchmap'] = $POST['enablestretchmap'];
	$info['showCopyright'] = $POST['showCopyright'];
	$info['cacheExpiredTime'] = $POST['cacheExpiredTime'];
	$info['enableSVGPixelCoordinate'] = $POST['enableSVGPixelCoordinate'];
	$info['enableStreamSVG'] = $POST['enableStreamSVG'];
	$info['outputEncodeCountry'] = $POST['outputEncodeCountry'];
	$info['OverlapRatio'] = $POST['OverlapRatio'];
	$info['GetMap25DOverlapRatio'] = $POST['GetMap25DOverlapRatio'];
	$info['GoogleMapKey'] = $POST['GoogleMapKey'];
	
	include_once '../parser/AttributeParser.class.php';
	$variable = AttributeParser::getAttributeFromArray($info);
	$info['variable'] = $variable;
	$info['status'] = $POST['AtlasStatus'];
	
	//TODO delete later
	if(!file_exists("../files/atlas/".$aid."/overview.gif")){
		xFileCopy("../img/overview.gif", "../files/atlas/".$aid."/" , false);
	}
	if (!$database->db_update_atlas_cfg($uid, $aid, $info)) {
		setSessionMessage($database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		return false;
	} else {
		setSessionMessage(t('Atlas <b>%name</b> Configuration has been updated successfully.', array (
			'%name' => $info['AtlasName']
		)), SITE_MESSAGE_INFO);
		return true;
	}
}

function get_atlas_import_form($user, $aid, $database, $isInProcess = false) {
	$database->dbEmptyErrorMessage();
	print '<table class="tableNone">';
	if($isInProcess){
		print '<tr class="title"><td colspan="2">Atlas '.($isInProcess?'':'(<b>'.$_SESSION['atlas']['name'].'</b>)').' Import Data</td></tr>';
		print '<tr><td align="left"><img src="../img/steps_2.png"></td><td></td></tr>';
	}else{
		print '<tr class="title"><td colspan="2">'.($isInProcess?'':''.$_SESSION['atlas']['name']).'</td></tr>';
	}
	print '<tr><td colspan="2">';
	print '</td></tr>
	</table>';
	
	print '  <script type="text/javascript">
	$(document).ready(function(){
	$("#tabs_file_import").tabs();
	});
	</script>
	';
	
	print '
	<table class="tableNone">
	<tr>
	<td width="460" valign="top">
	<div id="tabs_file_import" style="FONT-SIZE: 10px;">
	<ul>
	<li><a href="#fragment_file_import_1"><span>Local Files</span></a></li>
	<li><a href="#fragment_file_import_2"><span>Remote Files</span></a></li>
	<li><a href="#fragment_file_import_3"><span title="Help">?</span></a></li>
	</ul>
	<div id="fragment_file_import_1">
	<table class="tableNone">
	<tr>
	<td>
	<!--local file content-->';
	get_local_file_import_form($user, $aid);
	print '
	<!--local file content-->
	</td>
	</tr>
	<tr>
	<td>&nbsp;</td>
	</tr>
	</table>
	</div>
	<div id="fragment_file_import_2">
	<table class="tableNone">
	<tr>
	<td>
	<!--Remote file content-->';
	get_remote_file_import_form($user, $aid);
	print '
	<!--Remote file content-->
	</td>
	</tr>
	<tr>
	<td>&nbsp;</td>
	</tr>
	</table>
	</div>
	<div id="fragment_file_import_3">
	<!--div class="editsection" style="float: right; margin-left: 5px;">[<a href="#" title="Edit section: Local Files Import:">Edit</a>]</div>
	<a name="Local_Files_Import:"></a--><h4>Local Files Import:</h4>
	<p>
	In most case you are allowed to upload files from local(your) computer less than 2Mb because of server limitation.
	</p>
	<br/>
	<h4>Remote Files Import:</h4>
	<p>
	If you want to input files with more than 2Mb size, please upload the files into <font class=error>suas/files/user/'.$user['uid'].'/data/</font> in Remote Server, using FTP tools such as CuteFTP.
	</p>
	
	</div>
	</div>
	</td>
	<td valign="top">
	<!--class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"
	ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all
	-->
	<table id="div_file_import_options" class="block-panel ui-corner-all" style="width:100%; height:250px;" align="top">
	<tr class="block-panel-header ui-corner-all">
	<td>
	Options
	</td>
	</tr>
	';
	
	print '<tr>
	<td>
	<p>Please select one layer(Geomtype) as the destination layer of data importing: </p>';	
	print '<input name="layernametem" id="layernametem" type="text" size="32" value="' . LayerNotDefined . '"  class="smallInput"/>';
	
	/*print '<select name="layertem" id="layertem" onchange="setLayerName(this)" class="button4">
	<option value="' . LayerNotDefined . '">' . LayerNotDefined . '</option>
	<option value="Use_File_Name" class="error">Use File Name As Layer Name</option>';
	$layernames = $database->getAllLayersNames($aid);
	while ($rec = $database->getColumns($layernames)) {
		if (!equalIgnoreCase($rec['layer'], "LayerNotDefined")) {
			echo '<option value="' . $rec['layer'] . '" class="button4">' . $rec['layer'] . '</option>';
		}
	}
	print '<option value=""></option>
	</select>';*/

	print '<select name="layertem" id="layertem" onchange="setLayerName(this)" class="button4">
	<option value="' . LayerNotDefined . '">' . LayerNotDefined . '</option>
	<option value="Use_File_Name" class="error">Use File Name As Layer Name</option>';
	$optionstr = "";
	$layersrs = $database->getAllLayersSrss($aid);
	$srs_temp = "";
	while ($rec = $database->getColumns($layersrs)) {
		if($rec['srs'] != $srs_temp){
			$optionstr .= "<optgroup label=\"".$rec['srs']."\" >";
		}
		if($rec['typecount'] > 1){
			$optionstr .= '<option value="' . $rec['layer'] . '">' . $rec['layer'] . ' ('. GeometryTypeCompond .')'.  '</option>'; 
		}else{
			$optionstr .= '<option value="' . $rec['layer'] . '">' . $rec['layer'] . ' ('. $rec['geomtype'] .')'.  '</option>'; 
		}
		if($rec['srs'] != $srs_temp){
			$optionstr .= "</optgroup>";
		}
		$srs_temp = $rec['srs'];
	}
	print $optionstr.'<option value=""></option>
	</select>';

	print '</td></tr>';
	
	print '<tr><td>';
	print '<p>SRS: ';
	print_help_srs();
	print '</p>';
	Import::printEPSGList(Import::$TYPE_DEFAULT);
	print '</td></tr>';
	
	print '<tr><td>';
	print '<p>Input Encode: ';
	print '</p>';
	Import::get_encode_list("", "data_encode");
	print '</td></tr>';
	
	print '
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	</table>
	</td>
	</tr>
	</table>
	';
	
	print '
	<iframe name="uploadhideiframe" width="100" height="10" ';
	if(!debugMode) print 'style="display:none"';
	print '></iframe>
	';
	
	print '<table class="tableNone">' .
	'<form action="atlas.php"  accept-charset="UTF-8" method="post" name="atlas_import_form" id="atlas_import_form">
	<tr>
	<td align="left">';
	if($isInProcess){
		//can not go back to create atlas again
		print '<input type="button" onclick="javascript:window.location.href=\'atlas.php?op=myatlas\'" name="button" id="button" value="Cancel"  class="ui-button ui-state-default ui-corner-all" />';
	}else{
		print '<input type="button" onclick="javascript:window.location.href=\'atlas.php?op=myatlas\'" name="button" id="button" value="Cancel"  class="ui-button ui-state-default ui-corner-all" />';
	}
	print '</td>';
	print '<td align="right">' ;
	if($isInProcess){
		print '<input type="hidden" name="atlas_aid" id="atlas_aid" value="' . $aid . '"/>';
		print '<input type="hidden" name="op" id="op" value="ATLAS_CREATE_STEP2_SLD" />
		<input type="submit" name="submit" id="submit" value="Continue"  class="ui-button ui-state-default ui-corner-all" />
		';
	}
	print '</td>
	</tr>
	</form></table>';
}

function get_local_file_import_form($user, $aid) {
	$uid = $user['uid'];
	print '<div id="PARENT">
	<ul id="nav">';
	
	print '
	<li><a href="#Menu=ChildMenu1"  onclick="DoMenu(\'ChildMenu1\')">&nbsp;&nbsp;&nbsp;&nbsp;SVG To Database</a>
	<ul id="ChildMenu1" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_SVG.'" id="Form_'.Import::$TYPE_SVG.'" enctype="multipart/form-data" method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="1">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr>
	<td colspan="2"></td>
	</tr>
	<tr>
	<td colspan="2">
	Use Group Name As Layer Name: <input type="checkbox" name="SVG_Use_Group_Name" value="1" class="button3">
	</td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_SVG.'">
	<td>Select SVG File:</td>
	<td><input type="file" name="file_'.Import::$TYPE_SVG.'" id="file_'.Import::$TYPE_SVG.'"  accept="'.Import::$TYPE_SVG.'"/></td>
	</tr>
	<tr>
	<td><input type="hidden" name="max_file_size" value="' . SITE_MAX_UPLOAD_SIZE . '">
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_SVG.'">
	<input type="hidden" name="layername_'.Import::$TYPE_SVG.'" id="layername_'.Import::$TYPE_SVG.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_SVG.'" id="srs_'.Import::$TYPE_SVG.'" value="' . SRSNotDefined . '">
	</td>
	<td>
	<input type="button" onClick="return checkImportForm(\''.Import::$TYPE_SVG.'\')" name="import_'.Import::$TYPE_SVG.'" value="Import" class="ui-button ui-state-default ui-corner-all"/>
	</td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	print '
	<li><a href="#Menu=ChildMenu2" onclick="DoMenu(\'ChildMenu2\')">&nbsp;&nbsp;&nbsp;&nbsp;CSV To Database</a>
	<ul id="ChildMenu2" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_CSV.'" id="Form_'.Import::$TYPE_CSV.'" enctype="multipart/form-data" method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="1">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr>
	<td colspan="2"></td>
	</tr>
	<tr>
	<td colspan="2">
	<p>&#8226; You should make sure that CSV file has the following standard format<br>
	NULL|NULL|recid|layer|geomtype|srs|xmin|ymin|xmax|ymax<br>|geom|xlink||attributes</p></td>
	</tr>
	<!--tr>
	<td></td>
	<td><input type="checkbox" name="csv_replace" class="button3" value="something" id="csv_replace" /> Replace table data with file <br>
	<input type="checkbox" name="csv_ignore" class="button3" value="something" id="csv_ignore" /> Ignore duplicate rows
	</td>
	</tr-->
	<tr>
	<td colspan="2">
	Use CSV default Layer Name As Layer Name: <input type="checkbox" name="CSV_Use_Default_Name_'.Import::$TYPE_CSV.'" id="CSV_Use_Default_Name_'.Import::$TYPE_CSV.'" value="1" checked="true" ""class="button3">
	</td>
	</tr>
	<tr>
	<td colspan="2">
	Use CSV default SRS As SRS: <input type="checkbox" name="CSV_Use_Default_SRS_'.Import::$TYPE_CSV.'" id="CSV_Use_Default_SRS_'.Import::$TYPE_CSV.'" value="1" checked="true" ""class="button3">
	</td>
	</tr>
	<tr>
	<td>Fields terminated by:</td>
	<td><input type="text" name="csv_terminated"  id="csv_terminated" size="2" maxlength="1" class="smallInput" value=";"/></td>
	</tr>
	<tr>
	<td>Fields enclosed by:</td>
	<td><input type="text" name="csv_enclosed"  id="csv_enclosed" size="2" maxlength="1" class="smallInput" value=","/></td>
	</tr>
	<tr>
	<td>Fields escaped by:</td>
	<td><input type="text" name="csv_escaped"  id="csv_escaped" size="2" maxlength="1" class="smallInput" value="\"/></td>
	</tr>
	<!--tr>
	<td>Lines terminated by:</td>
	<td><input type="text" name="csv_new_line" value="auto" id="csv_new_line" size="2" class="smallInput"/></td>
	</tr>
	<tr>
	<td>Column names:</td>
	<td><input type="text" name="csv_columns" value="" id="csv_columns" class="smallInput"/></td>
	</tr>
	
	<tr>
	<td></td>
	<td><input type="hidden" name="ldi_local_option" value="something" id="checkbox_ldi_local_option"  checked="checked" valuse=" Use LOCAL keyword"/>
	<input type="checkbox" name="use_csv_header" class="button3" id="use_csv_header" style="cursor:pointer;"/> Use CSV header
	</td>
	</tr-->
	
	<tr id="filecontainer_'.Import::$TYPE_CSV.'">
	<td>Select CSV File:</td>
	<td><input type="file" name="file_'.Import::$TYPE_CSV.'" id="file_'.Import::$TYPE_CSV.'" class="button2 browseFileInput"/></td>
	</tr>
	<tr>
	<td><input type="hidden" name="max_file_size" value="' . SITE_MAX_UPLOAD_SIZE . '">
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_CSV.'">
	<input type="hidden" name="layername_'.Import::$TYPE_CSV.'" id="layername_'.Import::$TYPE_CSV.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_CSV.'" id="srs_'.Import::$TYPE_CSV.'" value="' . SRSNotDefined . '">

	</td>
	<td><input type="button" onClick="return checkImportForm(\''.Import::$TYPE_CSV.'\')" name="import_'.Import::$TYPE_CSV.'" value="Import" class="ui-button ui-state-default ui-corner-all"/></td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	print '
	<li><a href="#Menu=ChildMenu3" onclick="DoMenu(\'ChildMenu3\')">&nbsp;&nbsp;&nbsp;&nbsp;SHP To Database</a>
	<ul id="ChildMenu3" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_SHAPE.'" id="Form_'.Import::$TYPE_SHAPE.'" enctype="multipart/form-data" method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="1">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr style="display: block;">
	<td colspan="2"></td>
	</tr>
	<tr style="display: block;">
	<td colspan="2">
	<p>&#8226; You <font class="error">MAY</font> have not *.dbf or *.shx file.<br>
	&#8226; But the *.shp file is required.<br>
	</p>
	</td>
	</tr>
	<tr style="display: block;">
	<td>&nbsp;</td>
	<td>
	<input type="checkbox" name="usefile_'.Import::$TYPE_SHAPE.'" id="usefile_'.Import::$TYPE_SHAPE.'" checked disabled/> Use SHP file <br>
	<input type="checkbox" name="usefile_'.Import::$TYPE_DBF.'" id="usefile_'.Import::$TYPE_DBF.'" style="cursor:pointer;"';
	if (!checkExtensionDbaseInPHP()) {
		echo " disabled";
	}
	print ' onClick="chk_upload_file(\''.Import::$TYPE_DBF.'\');"/>
	Use DBF file';
	print_help("Use DBF file", "Description:", "This will store the attributes of geometries meanwhile storing geometries of SHP file. If your checkbox here is inactivive, please modify you php.ini file, delete the semicolon before <font class=error>extension=php_dbase.dll</font> to open the Dbase PHP function.");
	
	print '			    <br>
	<input type="checkbox" name="usefile_'.Import::$TYPE_SHX.'" id="usefile_'.Import::$TYPE_SHX.'" style="cursor:pointer;" onClick="chk_upload_file(\''.Import::$TYPE_SHX.'\');"/>
	Use SHX file <br>
	</td>
	</tr>
	<tr style="display: block;" align="left">
	<td>Select SHP File:</td>
	<td width="20px"><input type="file" name="file_'.Import::$TYPE_SHAPE.'" id="file_'.Import::$TYPE_SHAPE.'" /></td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_DBF.'" style="display: none;" align="left">
	<td>Select DBF File:</td>
	<td width="20px"><input type="file" name="file_'.Import::$TYPE_DBF.'" id="file_'.Import::$TYPE_DBF.'" /></td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_SHX.'" style="display: none;" align="left">
	<td>Select SHX File:</td>
	<td width="20px"><input type="file" name="file_'.Import::$TYPE_SHX.'" id="file_'.Import::$TYPE_SHX.'" /></td>
	</tr>
	
	<tr style="display: block;">
	<td><input type="hidden" name="max_file_size" value="' . SITE_MAX_UPLOAD_SIZE . '">
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_SHAPE.'">
	<input type="hidden" name="layername_'.Import::$TYPE_SHAPE.'" id="layername_'.Import::$TYPE_SHAPE.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_SHAPE.'" id="srs_'.Import::$TYPE_SHAPE.'" value="' . SRSNotDefined . '">
	</td>
	<td><input type="button" name="import_'.Import::$TYPE_SHAPE.'" onClick="return checkImportForm(\''.Import::$TYPE_SHAPE.'\')"  value="Import" class="ui-button ui-state-default ui-corner-all"/></td>
	</tr>
	</table>
	</form>
	</ul>
	</li>' ;
	
	print '
	<li><a href="#Menu=ChildMenu4" onclick="DoMenu(\'ChildMenu4\')">&nbsp;&nbsp;&nbsp;&nbsp;MIF To Database</a>
	<ul id="ChildMenu4" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_MIF.'" id="Form_'.Import::$TYPE_MIF.'" enctype="multipart/form-data"  method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="1">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr style="display: block;">
	<td colspan="2"></td>
	</tr>
	<tr style="display: block;">
	<td>&nbsp;</td>
	<td>
	<input type="checkbox" name="usefile_'.Import::$TYPE_MID.'" id="usefile_'.Import::$TYPE_MID.'" style="cursor:pointer;" onClick="chk_upload_file(\''.Import::$TYPE_MID.'\');"/>
	Use MID file <br>
	</td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_MIF.'" style="display: block;">
	<td>Select MIF File:</td>
	<td>
	<input type="file" name="file_'.Import::$TYPE_MIF.'" id="file_'.Import::$TYPE_MIF.'" />
	</td>
	</tr>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_MID.'" style="display: none;" align="left">
	<td>Select MID File:</td>
	<td width="20px">
	<input type="file" name="file_'.Import::$TYPE_MID.'" id="file_'.Import::$TYPE_MID.'" />
	</td>
	</tr>
	<tr style="display: block;">
	<td>
	<input type="hidden" name="max_file_size" value="' . SITE_MAX_UPLOAD_SIZE . '">
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_MIF.'">
	<input type="hidden" name="layername_'.Import::$TYPE_MIF.'" id="layername_'.Import::$TYPE_MIF.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_MIF.'" id="srs_'.Import::$TYPE_MIF.'" value="' . SRSNotDefined . '">
	</td>
	<td><input type="button" name="import_'.Import::$TYPE_MIF.'" onClick="return checkImportForm(\''.Import::$TYPE_MIF.'\')" value="Import" class="ui-button ui-state-default ui-corner-all"/></td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	print '
	<li><a href="#Menu=ChildMenu5" onclick="DoMenu(\'ChildMenu5\')">&nbsp;&nbsp;&nbsp;&nbsp;E00 To Database</a>
	<ul id="ChildMenu5" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_E00.'" id="Form_'.Import::$TYPE_E00.'" enctype="multipart/form-data"  method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="1">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr>
	<td colspan="2"></td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_E00.'">
	<td>Select E00 File:</td>
	<td><input type="file" name="file_'.Import::$TYPE_E00.'" id="file_'.Import::$TYPE_E00.'" class="button2 browseFileInput"/></td>
	</tr>
	<tr>
	<td><input type="hidden" name="max_file_size" value="' . SITE_MAX_UPLOAD_SIZE . '">
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_E00.'">
	<input type="hidden" name="layername_'.Import::$TYPE_E00.'" id="layername_'.Import::$TYPE_E00.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_E00.'" id="srs_'.Import::$TYPE_E00.'" value="' . SRSNotDefined . '">
	</td>
	<td><input type="button" name="import_'.Import::$TYPE_E00.'" onClick="return checkImportForm(\''.Import::$TYPE_E00.'\')" value="Import" class="ui-button ui-state-default ui-corner-all"/></td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	print '
	<li><a href="#Menu=ChildMenu6" onclick="DoMenu(\'ChildMenu6\')">&nbsp;&nbsp;&nbsp;&nbsp;OSM To Database</a>
	<ul id="ChildMenu6" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_OSM.'" id="Form_'.Import::$TYPE_OSM.'" enctype="multipart/form-data"  method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="1">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr>
	<td colspan="2"></td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_OSM.'">
	<td>Select OSM File:</td>
	<td><input type="file" name="file_'.Import::$TYPE_OSM.'" id="file_'.Import::$TYPE_OSM.'" class="button2 browseFileInput"/></td>
	</tr>
	<tr>
	<td><input type="hidden" name="max_file_size" value="' . SITE_MAX_UPLOAD_SIZE . '">
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_OSM.'">
	<input type="hidden" name="layername_'.Import::$TYPE_OSM.'" id="layername_'.Import::$TYPE_OSM.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_OSM.'" id="srs_'.Import::$TYPE_OSM.'" value="' . SRSNotDefined . '">
	</td>
	<td><input type="button" name="import_'.Import::$TYPE_OSM.'" onClick="return checkImportForm(\''.Import::$TYPE_OSM.'\')" value="Import" class="ui-button ui-state-default ui-corner-all"/></td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	print '
	<li><a href="#Menu=ChildMenu7"  onclick="DoMenu(\'ChildMenu7\')">&nbsp;&nbsp;&nbsp;&nbsp;KML To Database</a>
	<ul id="ChildMenu7" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_KML.'" id="Form_'.Import::$TYPE_KML.'" enctype="multipart/form-data" method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="1">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr>
	<td colspan="2"></td>
	</tr>
	<tr>
	<td colspan="2">
	Use Group Name As Layer Name: <input type="checkbox" name="KML_Use_Group_Name" value="1" class="button3">
	</td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_KML.'">
	<td>Select KML File:</td>
	<td><input type="file" name="file_'.Import::$TYPE_KML.'" id="file_'.Import::$TYPE_KML.'" class="button2 browseFileInput"/></td>
	</tr>
	<tr>
	<td><input type="hidden" name="max_file_size" value="' . SITE_MAX_UPLOAD_SIZE . '">
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_KML.'">
	<input type="hidden" name="layername_'.Import::$TYPE_KML.'" id="layername_'.Import::$TYPE_KML.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_KML.'" id="srs_'.Import::$TYPE_KML.'" value="' . SRSNotDefined . '">
	</td>
	<td>
	<input type="button" onClick="return checkImportForm(\''.Import::$TYPE_KML.'\')" name="import_'.Import::$TYPE_KML.'" value="Import" class="ui-button ui-state-default ui-corner-all"/>
	</td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	print '
	<li><a href="#Menu=ChildMenu8"  onclick="DoMenu(\'ChildMenu8\')">&nbsp;&nbsp;&nbsp;&nbsp;GPX To Database</a>
	<ul id="ChildMenu8" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_GPX.'" id="Form_'.Import::$TYPE_GPX.'" enctype="multipart/form-data" method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="1">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr>
	<td colspan="2"></td>
	</tr>
	<tr>
	<td colspan="2">
	Use Group Name As Layer Name: <input type="checkbox" name="GPX_Use_Group_Name" value="1" class="button3">
	</td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_GPX.'">
	<td>Select GPX File:</td>
	<td><input type="file" name="file_'.Import::$TYPE_GPX.'" id="file_'.Import::$TYPE_GPX.'" class="button2 browseFileInput"/></td>
	</tr>
	<tr>
	<td><input type="hidden" name="max_file_size" value="' . SITE_MAX_UPLOAD_SIZE . '">
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_GPX.'">
	<input type="hidden" name="layername_'.Import::$TYPE_GPX.'" id="layername_'.Import::$TYPE_GPX.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_GPX.'" id="srs_'.Import::$TYPE_GPX.'" value="' . SRSNotDefined . '">
	</td>
	<td>
	<input type="button" onClick="return checkImportForm(\''.Import::$TYPE_GPX.'\')" name="import_'.Import::$TYPE_GPX.'" value="Import" class="ui-button ui-state-default ui-corner-all"/>
	</td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	
	print '
	</ul>
	</div>';
}

function get_remote_file_import_form($user, $aid) {
	$uid = $user['uid'];
	global $user;
	print '<div id="PARENTr">
	<ul id="navr">';
	
	print '
	<li><a href="#Menur=ChildMenur1"  onclick="DoMenur(\'ChildMenur1\')">&nbsp;&nbsp;&nbsp;&nbsp;SVG To Database</a>
	<ul id="ChildMenur1" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_SVG_REMOTE.'" id="Form_'.Import::$TYPE_SVG_REMOTE.'" enctype="multipart/form-data"  method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="0">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr>
	<td colspan="2"></td>
	</tr>
	<tr>
	<td colspan="2">
	Use Group Name As Layer Name: <input type="checkbox" name="SVG_Use_Group_Name" value="1" class="button3">
	</td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_SVG_REMOTE.'">
	<td>Select SVG File:</td>
	<td>';
	Import::listDataInDirectory(Import::$TYPE_SVG_REMOTE);
	print '						</td>
	</tr>
	<tr>
	<td>
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_SVG_REMOTE.'">
	<input type="hidden" name="layername_'.Import::$TYPE_SVG_REMOTE.'" id="layername_'.Import::$TYPE_SVG_REMOTE.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_SVG_REMOTE.'" id="srs_'.Import::$TYPE_SVG_REMOTE.'" value="' . SRSNotDefined . '">
	</td>
	<td>
	<input type="button" name="import_'.Import::$TYPE_SVG_REMOTE.'" onClick="return checkImportForm(\''.Import::$TYPE_SVG_REMOTE.'\')" value="Import" class="ui-button ui-state-default ui-corner-all"/>
	</td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	print '
	<li><a href="#Menur=ChildMenur2" onclick="DoMenur(\'ChildMenur2\')">&nbsp;&nbsp;&nbsp;&nbsp;CSV To Database</a>
	<ul id="ChildMenur2" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_CSV_REMOTE.'" id="Form_'.Import::$TYPE_CSV_REMOTE.'" enctype="multipart/form-data" method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="0">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr>
	<td colspan="2"></td>
	</tr>
	<tr>
	<td colspan="2">
	<p>&#8226; You should make sure that CSV file has the following standard format<br>
	id|recid|layer|geomtype|srs|xmin|ymin|xmax|ymax|geom|xlink|<br>textcontent|attributes</p></td>
	</tr>
	<!--tr>
	<td></td>
	<td><input type="checkbox" name="csv_replace" class="button3" value="something" id="csv_replace" /> Replace table data with file <br>
	<input type="checkbox" name="csv_ignore" class="button3" value="something" id="csv_ignore" /> Ignore duplicate rows
	</td>
	</tr-->
	<tr>
	<td colspan="2">
	Use CSV default Layer Name As Layer Name: <input type="checkbox" name="CSV_Use_Default_Name_'.Import::$TYPE_CSV.'" id="CSV_Use_Default_Name_'.Import::$TYPE_CSV.'" checked="true" value="1" class="button3">
	</td>
	</tr>
	<tr>
	<td colspan="2">
	Use CSV default SRS As SRS: <input type="checkbox" name="CSV_Use_Default_SRS_'.Import::$TYPE_CSV.'" id="CSV_Use_Default_SRS_'.Import::$TYPE_CSV.'" value="1" checked="true" ""class="button3">
	</td>
	</tr>
	<tr>
	<td>Fields terminated by:</td>
	<td><input type="text" name="csv_terminated"  id="csv_terminated" size="2" maxlength="1" class="smallInput" value=";"/></td>
	</tr>
	<tr>
	<td>Fields enclosed by:</td>
	<td><input type="text" name="csv_enclosed"  id="csv_enclosed" size="2" maxlength="1" class="smallInput" value=","/></td>
	</tr>
	<tr>
	<td>Fields escaped by:</td>
	<td><input type="text" name="csv_escaped"  id="csv_escaped" size="2" maxlength="1" class="smallInput" value="\"/></td>
	</tr>
	<!--tr>
	<td>Lines terminated by:</td>
	<td><input type="text" name="csv_new_line" value="auto" id="csv_new_line" size="2" class="smallInput"/></td>
	</tr>
	<tr>
	<td>Column names:</td>
	<td><input type="text" name="csv_columns" value="" id="csv_columns" class="smallInput"/></td>
	</tr>
	
	<tr>
	<td></td>
	<td><input type="hidden" name="ldi_local_option" value="something" id="ldi_local_option"  checked="checked" value=" Use LOCAL keyword"/>
	<input type="checkbox" name="use_csv_header" class="button3" id="use_csv_header" style="cursor:pointer;"/> Use CSV header
	</td>
	</tr-->
	
	<tr id="filecontainer_'.Import::$TYPE_CSV_REMOTE.'">
	<td>Select CSV File:</td>
	<td>';
	Import::listDataInDirectory(Import::$TYPE_CSV_REMOTE);
	print '						</td>
	</tr>
	<tr>
	<td>
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_CSV_REMOTE.'">
	<input type="hidden" name="layername_'.Import::$TYPE_CSV_REMOTE.'" id="layername_'.Import::$TYPE_CSV_REMOTE.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_CSV_REMOTE.'" id="srs_'.Import::$TYPE_CSV_REMOTE.'" value="' . SRSNotDefined . '">
	</td>
	<td><input type="button" name="import_'.Import::$TYPE_CSV_REMOTE.'" onClick="return checkImportForm(\''.Import::$TYPE_CSV_REMOTE.'\')" value="Import" class="ui-button ui-state-default ui-corner-all"/></td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	print '
	<li><a href="#Menur=ChildMenur3" onclick="DoMenur(\'ChildMenur3\')">&nbsp;&nbsp;&nbsp;&nbsp;SHP To Database</a>
	<ul id="ChildMenur3" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_SHAPE_REMOTE.'" id="Form_'.Import::$TYPE_SHAPE_REMOTE.'" enctype="multipart/form-data" method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="0">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr style="display: block;">
	<td colspan="2"></td>
	</tr>
	<tr style="display: block;">
	<td colspan="2">
	<p>&#8226; You <font class="error">MAY</font> have not *.dbf or *.shx file.<br>
	&#8226; But the *.shp file is required.<br>
	</p>
	</td>
	</tr>
	<tr style="display: block;">
	<td></td>
	<td>
	<input type="checkbox" name="usefile_'.Import::$TYPE_SHAPE_REMOTE.'" id="usefile_'.Import::$TYPE_SHAPE_REMOTE.'" checked disabled/> Use SHP file <br>
	<input type="checkbox" name="usefile_'.Import::$TYPE_DBF_REMOTE.'" id="usefile_'.Import::$TYPE_DBF_REMOTE.'" style="cursor:pointer;"
	';
	if (!checkExtensionDbaseInPHP()) {
		echo " disabled";
	}
	print '	onClick="chk_upload_file(\''.Import::$TYPE_DBF_REMOTE.'\');" />
	Use DBF file';
	print_help("Use DBF file", "Description:", "This will store the attributes of geometries meanwhile storing geometries of SHP file. If your checkbox here is inactivive, please modify you php.ini file, delete the semicolon before <font class=error>extension=php_dbase.dll</font> to open the Dbase PHP function.");
	print '			<br>
	<input type="checkbox" name="usefile_'.Import::$TYPE_SHX_REMOTE.'" id="usefile_'.Import::$TYPE_SHX_REMOTE.'" style="cursor:pointer;" onClick="chk_upload_file(\''.Import::$TYPE_SHX_REMOTE.'\');"/>
	Use SHX file <br>
	</td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_SHAPE_REMOTE.'" style="display: block;">
	<td>Select SHP File:</td>
	<td>';
	Import::listDataInDirectory(Import::$TYPE_SHAPE_REMOTE);
	print '						</td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_DBF_REMOTE.'"  style="display:none">
	<td>Select DBF File:</td>
	<td>';
	Import::listDataInDirectory(Import::$TYPE_DBF_REMOTE);
	print '						</td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_SHX_REMOTE.'"  style="display:none">;
	<td>Select SHX File:</td>
	<td>';
	Import::listDataInDirectory(Import::$TYPE_SHX_REMOTE);
	print '						</td>
	</tr>
	<tr style="display: block;">
	<td>
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_SHAPE_REMOTE.'">
	<input type="hidden" name="layername_'.Import::$TYPE_SHAPE_REMOTE.'" id="layername_'.Import::$TYPE_SHAPE_REMOTE.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_SHAPE_REMOTE.'" id="srs_'.Import::$TYPE_SHAPE_REMOTE.'" value="' . SRSNotDefined . '">
	</td>
	<td><input type="button" name="import_'.Import::$TYPE_SHAPE_REMOTE.'" onClick="return checkImportForm(\''.Import::$TYPE_SHAPE_REMOTE.'\')" value="Import" class="ui-button ui-state-default ui-corner-all"/></td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	print '
	<li><a href="#Menur=ChildMenur4" onclick="DoMenur(\'ChildMenur4\')">&nbsp;&nbsp;&nbsp;&nbsp;MIF To Database</a>
	<ul id="ChildMenur4" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_MIF_REMOTE.'" id="Form_'.Import::$TYPE_MIF_REMOTE.'" enctype="multipart/form-data" method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="0">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr style="display: block;">
	<td colspan="2"></td>
	</tr>
	<tr style="display: block;">
	<td></td>
	<td>
	<input type="checkbox" name="usefile_'.Import::$TYPE_MID_REMOTE.'" id="usefile_'.Import::$TYPE_MID_REMOTE.'" style="cursor:pointer;" onClick="chk_upload_file(\''.Import::$TYPE_MID_REMOTE.'\');"/>
	Use MID file <br>
	</td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_MIF_REMOTE.'" style="display: block;">
	<td>Select MIF File:</td>
	<td>';
	Import::listDataInDirectory(Import::$TYPE_MIF_REMOTE);
	print '						</td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_MID_REMOTE.'"  style="display:none">;
	<td>Select MID File:</td>
	<td>';
	Import::listDataInDirectory(Import::$TYPE_MID_REMOTE);
	print '						</td>
	</tr>
	<tr style="display: block;">
	<td>
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_MIF_REMOTE.'">
	<input type="hidden" name="layername_'.Import::$TYPE_MIF_REMOTE.'" id="layername_'.Import::$TYPE_MIF_REMOTE.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_MIF_REMOTE.'" id="srs_'.Import::$TYPE_MIF_REMOTE.'" value="' . SRSNotDefined . '">
	</td>
	<td><input type="button" name="import_'.Import::$TYPE_MIF_REMOTE.'" onClick="return checkImportForm(\''.Import::$TYPE_MIF_REMOTE.'\')" value="Import" class="ui-button ui-state-default ui-corner-all"/></td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	print '
	<li><a href="#Menur=ChildMenur5" onclick="DoMenur(\'ChildMenur5\')">&nbsp;&nbsp;&nbsp;&nbsp;E00 To Database</a>
	<ul id="ChildMenur5" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_E00_REMOTE.'" id="Form_'.Import::$TYPE_E00_REMOTE.'" enctype="multipart/form-data" method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="0">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr>
	<td colspan="2"></td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_E00_REMOTE.'">
	<td>Select E00 File:</td>
	<td>';
	Import::listDataInDirectory(Import::$TYPE_E00_REMOTE);
	print '						</td>
	</tr>
	<tr>
	<td>
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_E00_REMOTE.'">
	<input type="hidden" name="layername_'.Import::$TYPE_E00_REMOTE.'" id="layername_'.Import::$TYPE_E00_REMOTE.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_E00_REMOTE.'" id="srs_'.Import::$TYPE_E00_REMOTE.'" value="' . SRSNotDefined . '">
	</td>
	<td><input type="button" name="import_'.Import::$TYPE_E00_REMOTE.'" onClick="return checkImportForm(\''.Import::$TYPE_E00_REMOTE.'\')" value="Import" class="ui-button ui-state-default ui-corner-all"/></td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	print '
	<li><a href="#Menur=ChildMenur6" onclick="DoMenur(\'ChildMenur6\')">&nbsp;&nbsp;&nbsp;&nbsp;OSM To Database</a>
	<ul id="ChildMenur6" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_OSM_REMOTE.'" id="Form_'.Import::$TYPE_OSM_REMOTE.'" enctype="multipart/form-data" method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="0">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr>
	<td colspan="2"></td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_OSM_REMOTE.'">
	<td>Select OSM File:</td>
	<td>';
	Import::listDataInDirectory(Import::$TYPE_OSM_REMOTE);
	print '						</td>
	</tr>
	<tr>
	<td>
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_OSM_REMOTE.'">
	<input type="hidden" name="layername_'.Import::$TYPE_OSM_REMOTE.'" id="layername_'.Import::$TYPE_OSM_REMOTE.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_OSM_REMOTE.'" id="srs_'.Import::$TYPE_OSM_REMOTE.'" value="' . SRSNotDefined . '">
	</td>
	<td><input type="button" name="import_'.Import::$TYPE_OSM_REMOTE.'" onClick="return checkImportForm(\''.Import::$TYPE_OSM_REMOTE.'\')" value="Import" class="ui-button ui-state-default ui-corner-all"/></td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	print '
	<li><a href="#Menur=ChildMenur7"  onclick="DoMenur(\'ChildMenur7\')">&nbsp;&nbsp;&nbsp;&nbsp;KML To Database</a>
	<ul id="ChildMenur7" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_KML_REMOTE.'" id="Form_'.Import::$TYPE_KML_REMOTE.'" enctype="multipart/form-data"  method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="0">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr>
	<td colspan="2"></td>
	</tr>
	<tr>
	<td colspan="2">
	Use Group Name As Layer Name: <input type="checkbox" name="KML_Use_Group_Name" value="1" class="button3">
	</td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_KML_REMOTE.'">
	<td>Select KML File:</td>
	<td>';
	Import::listDataInDirectory(Import::$TYPE_KML_REMOTE);
	print '						</td>
	</tr>
	<tr>
	<td>
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_KML_REMOTE.'">
	<input type="hidden" name="layername_'.Import::$TYPE_KML_REMOTE.'" id="layername_'.Import::$TYPE_KML_REMOTE.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_KML_REMOTE.'" id="srs_'.Import::$TYPE_KML_REMOTE.'" value="' . SRSNotDefined . '">
	</td>
	<td>
	<input type="button" name="import_'.Import::$TYPE_KML_REMOTE.'" onClick="return checkImportForm(\''.Import::$TYPE_KML_REMOTE.'\')" value="Import" class="ui-button ui-state-default ui-corner-all"/>
	</td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	print '
	<li><a href="#Menur=ChildMenur8"  onclick="DoMenur(\'ChildMenur8\')">&nbsp;&nbsp;&nbsp;&nbsp;GPX To Database</a>
	<ul id="ChildMenur8" class="collapsed">
	<li></li>
	<form name="Form_'.Import::$TYPE_GPX_REMOTE.'" id="Form_'.Import::$TYPE_GPX_REMOTE.'" enctype="multipart/form-data"  method="post">
	<input type="hidden" id="uploadMethod" name="uploadMethod" value="0">
	<table width="450"  border="0" cellspacing="1" cellpadding="4" bgcolor="white">
	<tr>
	<td colspan="2"></td>
	</tr>
	<tr>
	<td colspan="2">
	Use Group Name As Layer Name: <input type="checkbox" name="GPX_Use_Group_Name" value="1" class="button3">
	</td>
	</tr>
	<tr id="filecontainer_'.Import::$TYPE_GPX_REMOTE.'">
	<td>Select GPX File:</td>
	<td>';
	Import::listDataInDirectory(Import::$TYPE_GPX_REMOTE);
	print '						</td>
	</tr>
	<tr>
	<td>
	<input type="hidden" name="uid" value="' . $uid . '">
	<input type="hidden" name="aid" value="' . $aid . '">
	<input type="hidden" name="flag" value="'.Import::$TYPE_GPX_REMOTE.'">
	<input type="hidden" name="layername_'.Import::$TYPE_GPX_REMOTE.'" id="layername_'.Import::$TYPE_GPX_REMOTE.'" value="' . LayerNotDefined . '">
	<input type="hidden" name="srs_'.Import::$TYPE_GPX_REMOTE.'" id="srs_'.Import::$TYPE_GPX_REMOTE.'" value="' . SRSNotDefined . '">
	</td>
	<td>
	<input type="button" name="import_'.Import::$TYPE_GPX_REMOTE.'" onClick="return checkImportForm(\''.Import::$TYPE_GPX_REMOTE.'\')" value="Import" class="ui-button ui-state-default ui-corner-all"/>
	</td>
	</tr>
	</table>
	</form>
	</ul>
	</li>';
	
	print '
	</ul>
	</div>';
}

function print_help_srs() {
	print_help("SRS", "Description:", "Spatial Reference System, for example: EPSG:31467.<br>EPSG:4326 = WGS84.<br>Please use default value as displayed if you do not know which SRS is using.");
}

function print_atlas_tab($tabflag, $aid, $database, $user, $info) {
	if ($tabflag == "") {
		return;
	}
	print '<div id="header">
	<ul id="primary">';
	
	if ($tabflag == "meta") {
		print '		<li><a href="#" id="content1_1_tab" class="current">Metadata</a>
		<ul id="secondary">
		<div id="content1_1_tabtitle" align="right">
		</div>
		</ul>
		</li>';
	} else {
		print '		<li><a href="atlas.php?op=atlas_configuration&tab=meta&aid=' . $aid . '" id="content1_1_tab">Metadata</a>
		<ul id="secondary">
		<div id="content1_1_tabtitle" align="right" style="display:none">
		</div>
		</ul>
		</li>';
	}
	
	if ($tabflag == "cfg") {
		print '		<li><a href="#" id="content1_1_tab" class="current">Configuration</a>
		<ul id="secondary">
		<div id="content1_1_tabtitle" align="right">
		</div>
		</ul>
		</li>';
	} else {
		print '		<li><a href="atlas.php?op=atlas_configuration&tab=cfg&aid=' . $aid . '" id="content1_1_tab">Configuration</a>
		<ul id="secondary">
		<div id="content1_1_tabtitle" align="right" style="display:none">
		</div>
		</ul>
		</li>';
	}
	
	if ($tabflag == "import") {
		print '		<li><a href="#" id="content1_1_tab" class="current">Import Data</a>
		<ul id="secondary">
		<div id="content1_1_tabtitle" align="right">
		</div>
		</ul>
		</li>';
	} else {
		print '		<li><a href="atlas.php?op=atlas_configuration&tab=import&aid=' . $aid . '" id="content1_1_tab">Import Data</a>
		<ul id="secondary">
		<div id="content1_1_tabtitle" align="right" style="display:none">
		</div>
		</ul>
		</li>';
	}
	
	if ($tabflag == "sld") {
		print '		<li><a href="#" id="content1_1_tab" class="current">Style Setting</a>
		<ul id="secondary">
		<div id="content1_1_tabtitle" align="right">
		</div>
		</ul>
		</li>';
	} else {
		print '		<li><a href="atlas.php?op=atlas_configuration&tab=sld&aid=' . $aid . '" id="content1_1_tab">Style Setting</a>
		<ul id="secondary">
		<div id="content1_1_tabtitle" align="right" style="display:none">
		</div>
		</ul>
		</li>';
	}
	
	if ($tabflag == "layerinfo") {
		print '		<li><a href="#" id="content1_1_tab" class="current">Layer Info</a>
		<ul id="secondary">
		<div id="content1_1_tabtitle" align="right">
		</div>
		</ul>
		</li>';
	} else {
		print '		<li><a href="atlas.php?op=atlas_configuration&tab=layerinfo&aid=' . $aid . '" id="content1_1_tab">Layer Info</a>
		<ul id="secondary">
		<div id="content1_1_tabtitle" align="right" style="display:none">
		</div>
		</ul>
		</li>';
	}
	
	if ($tabflag == "geoedit") {
		print '		<li><a href="#" id="content1_1_tab" class="current">Geo Edit</a>
		<ul id="secondary">
		<div id="content1_1_tabtitle" align="right">
		</div>
		</ul>
		</li>';
	} else {
		print '		<li><a href="atlas.php?op=atlas_configuration&tab=geoedit&aid=' . $aid . '" id="content1_1_tab">Geo Edit</a>
		<ul id="secondary">
		<div id="content1_1_tabtitle" align="right" style="display:none">
		</div>
		</ul>
		</li>';
	}
	
	print '</ul>
	</div>';
	print '	<div id="main">
	<div id="contents">';
	if ($tabflag == "meta") {
		get_atlas_create_form($user, $database, null, $aid);
	} elseif ($tabflag == "cfg") {
		get_atlas_cfg_form($user, $aid, $database);
	} elseif ($tabflag == "import") {
		get_atlas_import_form($user, $aid, $database, false);
	} elseif ($tabflag == "sld") {
		get_atlas_sld_form($aid, $database, false);
	} elseif ($tabflag == "layerinfo") {
		get_atlas_layerinfo_form($aid, $database, $info, false);
	} elseif ($tabflag == "geoedit") {
		get_atlas_geoedit_form($aid, $database, $info);
	}
	
	print '    </div>
	</div>';
	
}

/**
 * not use now
 */
function print_atlas_tab_ajax($tabflag, $aid, $database, $user, $info) {
	if ($tabflag == "") {
		return;
	}
	
	print '<div id="tabs_atlas_configuration" style="FONT-SIZE: 11px;">
	<ul>'."\n";
	
	if ($tabflag == "meta") {
		print '<li><a href="#fragment_file_import_meta"><span>Metadata</span></a></li>'."\n";
	} else {
		print '<li><a href="atlas.php?op=ATLAS_CONFIGURATION&tab=meta&aid=' . $aid . '#fragment_file_import_meta"><span>Metadata</span></a></li>'."\n";
	}
	
	if ($tabflag == "cfg") {
		print '<li><a href="#fragment_file_import_cfg"><span>Configuration</span></a></li>'."\n";
	} else {
		print '<li><a href="atlas.php?op=ATLAS_CONFIGURATION&tab=cfg&aid=' . $aid . '#fragment_file_import_cfg"><span>Configuration</span></a></li>'."\n";
	}
	
	if ($tabflag == "import") {
		print '<li><a href="#fragment_file_import_import"><span>Import Data</span></a></li>'."\n";
	} else {
		print '<li><a href="atlas.php?op=ATLAS_CONFIGURATION&tab=import&aid=' . $aid . '#fragment_file_import_import"><span>Import Data</span></a></li>'."\n";
	}
	
	if ($tabflag == "sld") {
		print '<li><a href="#fragment_file_import_sld"><span>Style Setting</span></a></li>'."\n";
	} else {
		print '<li><a href="atlas.php?op=ATLAS_CONFIGURATION&tab=sld&aid=' . $aid . '#fragment_file_import_sld"><span>Style Setting</span></a></li>'."\n";
	}
	
	if ($tabflag == "layerinfo") {
		print '<li><a href="#fragment_file_import_layerinfo"><span>Layer Info</span></a></li>'."\n";
	} else {
		print '<li><a href="atlas.php?op=ATLAS_CONFIGURATION&tab=layerinfo&aid=' . $aid . '#fragment_file_import_layerinfo"><span>Layer Info</span></a></li>'."\n";
	}
	
	print '</ul>';
	
	
	if ($tabflag == "meta") {
		get_atlas_create_form($user, $database, null, $aid);
	} elseif ($tabflag == "cfg") {
		get_atlas_cfg_form($user, $aid, $database);
	} elseif ($tabflag == "import") {
		get_atlas_import_form($user, $aid, $database, false);
	} elseif ($tabflag == "sld") {
		get_atlas_sld_form($aid, $database, false);
	} elseif ($tabflag == "layerinfo") {
		get_atlas_layerinfo_form($aid, $database, $info, false);
	}
	
	print '<div id="fragment_file_import_'.$tabflag.'">'."\n";
	print '</div>'."\n";
	print '<div id="fragment_file_import_cfg"></div>'."\n";
	print '<div id="fragment_file_import_import"></div>'."\n";
	print '<div id="fragment_file_import_sld"></div>'."\n";
	print '<div id="fragment_file_import_layerinfo"></div>'."\n";
	
	print '</div>';
	
	print '  <script type="text/javascript">
	$("#tabs_atlas_configuration").tabs({
	';
	if ($tabflag == "meta") {
		print ' selected: 0 ,';
	} elseif ($tabflag == "cfg") {
		print ' selected: 1 ,';
	} elseif ($tabflag == "import") {
		print ' selected: 2 ,';
	} elseif ($tabflag == "sld") {
		print ' selected: 3 ,';
	} elseif ($tabflag == "layerinfo") {
		print ' selected: 4 ,';
	}
	
	/*print '
	 select: function(event, ui) {
	 var url = $.data(ui.tab, \'load.tabs\');
	 if( url ) {
	 location.href = url;
	 return false;
	 }
	 return true;
	 }
	 ';*/
	print '});
	</script>
	';
}

/**
 * use in getsld.php, dynamically get style container
 */
function atlas_get_style($database, $aid, $stid) {
	$database->dbEmptyErrorMessage();
	if ($srsnameslist = $database->getAllSrssNames($aid)) {
		require_once '../parser/StyleReader.class.php';
		$i = 0;
		$styleparser = new StyleParser($aid, $database);
		$aryXmlUserLayerNode = $styleparser->createStyleNode4layer(false, $stid);
		$srsnum = $database->getRowsNumber($srsnameslist);
		while ($srsnames = $database->getColumns($srsnameslist)) {
			$srsname = "";
			$srsname = $srsnames["srs"];
			
			$layersnameslist = $database->getAllLayersNamesInSrs($aid, $srsname);
			$layerselectbox .= "<optgroup label=\"$srsname\" class=\"button4\">";
			while ($row = $database->getColumns($layersnameslist)) {
				if ($row["layer"] != "") {
					// ===============begin to read style for each node============
					$styleparser->getLayerStyleFromStyleNode($row["layer"], $row["geomtype"], $aryXmlUserLayerNode);
					$xmlUserLayerName = $styleparser->xmlUserLayerName;
					$xmlUserLayerTitle = $styleparser->xmlUserLayerTitle;
					$xmlMinScaleDenominator = $styleparser->xmlMinScaleDenominator;
					$xmlMaxScaleDenominator = $styleparser->xmlMaxScaleDenominator;
					// For point,linestring,polygon,,text
					$xmlSize = $styleparser->xmlSize;
					// For point,polygon,text,linestring
					$xmlFillColor = $styleparser->xmlFillColor;
					// For linestring,polygon
					$xmlStrokeColor = $styleparser->xmlStrokeColor;
					// For text
					$xmlFont = $styleparser->xmlFont;
					// For point
					$xmlWellKnownName = $styleparser->xmlWellKnownName;
					// For linestring, point
					$xmlStrokeOpacity = $styleparser->xmlStrokeOpacity;
					// For polygon, point, image
					$xmlFillOpacity = $styleparser->xmlFillOpacity;
					// For text
					$xmlFont = $styleparser->xmlFont;
					$xmlFontStyle = $styleparser->xmlFontStyle;
					$xmlFontWeight = $styleparser->xmlFontWeight;
					// For linestring
					$xmlLineJoin = $styleparser->xmlLineJoin;
					$xmlLineCap = $styleparser->xmlLineCap;
					// =======================finish reading========================
					$strStyleInfoContainer[$i] = StyleParser::getStyleInfoContainerFromSLD($srsname . "_" . $row["layer"], $row["geomtype"], $xmlUserLayerName, $xmlUserLayerTitle, $xmlMinScaleDenominator, $xmlMaxScaleDenominator, $xmlSize, $xmlFillColor, $xmlStrokeColor, $xmlFont, $xmlWellKnownName, $xmlStrokeOpacity, $xmlFillOpacity, $xmlFont, $xmlFontStyle, $xmlFontWeight, $xmlLineJoin, $xmlLineCap);
					
					$i++;
					// ===============initialize  the variant============
					$xmlUserLayerName = "";
					$xmlUserLayerTitle = "";
					$xmlMinScaleDenominator = "";
					$xmlMaxScaleDenominator = "";
					// For point,linestring,polygon,,text
					$xmlSize = "";
					// For point,polygon,text,linestring
					$xmlFillColor = "";
					// For linestring,polygon
					$xmlStrokeColor = "";
					// For text
					$xmlFont = "";
					// For point
					$xmlWellKnownName = "";
					// For linestring, point
					$xmlStrokeOpacity = "";
					// For polygon, point, image
					$xmlFillOpacity = "";
					// For text
					$xmlFont = "";
					$xmlFontStyle = "";
					$xmlFontWeight = "";
					// For linestring
					$xmlLineJoin = "";
					$xmlLineCap = "";
					// =======================finish initializing========================
				}
			}
		}
		return $strStyleInfoContainer;
	} else {
		return null;
	}
}

function get_atlas_sld_form($aid, $database, $isInProcess = false) {
	$database->dbEmptyErrorMessage();
	if ($srsnameslist = $database->getAllSrssNames($aid)) {
		require_once '../parser/StyleReader.class.php';
		$i = 0;
		$styleparser = new StyleParser($aid, $database);
		$aryXmlUserLayerNode = $styleparser->createStyleNode4layer(true, 0);
		
		$srsnum = $database->getRowsNumber($srsnameslist);
		//has no layers
		if($srsnum == 0){
			echo '<form action="atlas.php"  accept-charset="UTF-8" method="post" name="atlas_sld_form" id="atlas_sld_form">';
			print '<table class="tableContent">';
			if($isInProcess){
				print '<tr class="title"><td colspan="2">Atlas '.($isInProcess?'':'(<b>'.$_SESSION['atlas']['name'].'</b>)').' Style Setting</td></tr>';
				print '<tr>
				<td align="left"><img src="../img/steps_3.png"></td><td></td>
				</tr>';
			}else{
				print '<tr class="title"><td colspan="2">'.($isInProcess?'':''.$_SESSION['atlas']['name']).'</td></tr>';
			}
			print '<tr>
			<td colspan="2">' .
			'<div class="messages info" style="display:block">Sorry, there is no layer in atalas, please go back to import data!</div></td>
			</tr>';
			print '<tr>
			<td>' ;
			if ($isInProcess) {
				print '<input type="hidden" name="atlas_aid" id="atlas_aid" value="' . $aid . '"/>';
				print '<input type="hidden" name="isBackStep" id="isBackStep" value="TRUE"/>';
				print '<input type="hidden" name="op" id="op" value="ATLAS_CREATE_STEP1_IMPORT"/>';
				print '<input type="button" onclick="javascript:window.location.href=\'atlas.php?op=ATLAS_CREATE_STEP1_IMPORT&isBackStep=TRUE&aid=' . $aid . '\'" value="Back" class="ui-button ui-state-default ui-corner-all">';
				print '&nbsp;';
				print '<input onclick="javascript:window.location.href=\'atlas.php?op=myatlas\'" type="button" value="Cancel" class="ui-button ui-state-default ui-corner-all">';
			}else{
				print '<input onclick="javascript:window.location.href=\'atlas.php?op=myatlas\'" type="button" value="Cancel" class="ui-button ui-state-default ui-corner-all">';
			}
			print '</td><td></td>
			</tr></table>';
			echo '</form>';
		}
		while ($srsnames = $database->getColumns($srsnameslist)) {
			$srsname = "";
			$srsname = $srsnames["srs"];
			
			$layersnameslist = $database->getAllLayersNamesInSrs($aid, $srsname);
			$layerselectbox .= "<optgroup label=\"$srsname\" class=\"button4\">";
			while ($row = $database->getColumns($layersnameslist)) {
				if ($row["layer"] != "") {
					$layerselectbox .= "<OPTION value=\"" . $srsname . "_" . $row["layer"] . "\" class=\"button4\">" . $row["layer"] . "</OPTION>" . "\n";
					// ===============begin to read style for each node============
					if($row["typecount"]>1){
						$row["geomtype"] = GeometryTypeCompond;
					}
					$styleparser->getLayerStyleFromStyleNode($row["layer"], $row["geomtype"], $aryXmlUserLayerNode);
					$xmlUserLayerName = $styleparser->xmlUserLayerName;
					$xmlUserLayerTitle = $styleparser->xmlUserLayerTitle;
					$xmlMinScaleDenominator = $styleparser->xmlMinScaleDenominator;
					$xmlMaxScaleDenominator = $styleparser->xmlMaxScaleDenominator;
					// For point,linestring,polygon,,text
					$xmlSize = $styleparser->xmlSize;
					// For point,polygon,text,linestring
					$xmlFillColor = $styleparser->xmlFillColor;
					// For linestring,polygon
					$xmlStrokeColor = $styleparser->xmlStrokeColor;
					// For text
					$xmlFont = $styleparser->xmlFont;
					// For point
					$xmlWellKnownName = $styleparser->xmlWellKnownName;
					// For linestring, point
					$xmlStrokeOpacity = $styleparser->xmlStrokeOpacity;
					// For polygon, point, image
					$xmlFillOpacity = $styleparser->xmlFillOpacity;
					// For text
					$xmlFont = $styleparser->xmlFont;
					$xmlFontStyle = $styleparser->xmlFontStyle;
					$xmlFontWeight = $styleparser->xmlFontWeight;
					// For linestring
					$xmlLineJoin = $styleparser->xmlLineJoin;
					$xmlLineCap = $styleparser->xmlLineCap;
					// =======================finish reading========================
					$strStyleInfoContainer[$i] = StyleParser::getStyleInfoContainerFromSLD($srsname . "_" . $row["layer"], $row["geomtype"], 
						$xmlUserLayerName, $xmlUserLayerTitle, $xmlMinScaleDenominator, $xmlMaxScaleDenominator, 
						$xmlSize, $xmlFillColor, $xmlStrokeColor, $xmlFont, $xmlWellKnownName, $xmlStrokeOpacity, 
						$xmlFillOpacity, $xmlFont, $xmlFontStyle, $xmlFontWeight, $xmlLineJoin, $xmlLineCap);
					
					$i++;
					// ===============initialize  the variant============
					$xmlUserLayerName = "";
					$xmlUserLayerTitle = "";
					$xmlMinScaleDenominator = "";
					$xmlMaxScaleDenominator = "";
					// For point,linestring,polygon,,text
					$xmlSize = "";
					// For point,polygon,text,linestring
					$xmlFillColor = "";
					// For linestring,polygon
					$xmlStrokeColor = "";
					// For text
					$xmlFont = "";
					// For point
					$xmlWellKnownName = "";
					// For linestring, point
					$xmlStrokeOpacity = "";
					// For polygon, point, image
					$xmlFillOpacity = "";
					// For text
					$xmlFont = "";
					$xmlFontStyle = "";
					$xmlFontWeight = "";
					// For linestring
					$xmlLineJoin = "";
					$xmlLineCap = "";
					// =======================finish initializing========================
				}
			}
			
			$layerselectbox .= "</optgroup>";
		}
		$error = $database->databaseGetErrorMessage();
		if ($database->getFieldsNumber($layersnameslist) == 0) {
			$error .= "\nSorry, there is no records in database!";
		}
		if (empty ($error)) {
			print '<script type="text/javascript" src="../cssjs/sld.js"></script>';
			print '<table class="tableNone">';
			if($isInProcess){
				print '<tr class="title"><td colspan="2">Atlas '.($isInProcess?'':'(<b>'.$_SESSION['atlas']['name'].'</b>)').'  Style Setting</td></tr>';
				print '<tr><td align="left"><img src="../img/steps_3.png"></td><td></td></tr>';
			}
			else{
				print '<tr class="title"><td colspan="2">'.($isInProcess?'':''.$_SESSION['atlas']['name']).'</td></tr>';
			}
			print '<tr><td colspan="2">Create a Style (display range and symbology) for each Layer that created in the previous step.
			</td>
			</tr></table>
			
			<!--sld panel begin-->
			<table class="tableNone">
			<tr valign="top">
			<td>
			
			<TABLE class="block-panel ui-corner-all" width="600px">
			<FORM name="frmStyles" id="frmStyles">
			<tr class="block-panel-header ui-corner-all">
			<td colspan="3">
			SLD
			</td>
			</tr>';
			
			$stid_rec = $database->db_get_stylelist($aid);
			//if is 0, there is no style for atlas
			$stid_default = $stid_rec['stid_default'];
			$stid_records = $stid_rec['stid_records'];
			$stylecount = 0;
			
			$container_style_list .= '<select onchange="changeStyle(this.options [this.selectedIndex].value); "
				name="sltStylelist" id="sltStylelist">';
			while ($rec = $database->getColumns($stid_records)) {
				if ($rec['stid'] == $stid_default) {
					$container_style_list .= "<option value=\"" . $rec['stid'] . "\" selected>" . $rec['name'] . " (default)</option>";
				} else {
					$container_style_list .= "<option value=\"" . $rec['stid'] . "\">" . $rec['name'] . "</option>";
				}
				$stylecount++;
			}
			if ($stylecount == 0) {
				$container_style_list .= "<option value=\"0\" selected>No Style Exists</option>";
			}
			$container_style_list .= "</select>";			
			//echo $aryXmlUserLayerNode['stylename'];
			
			print '<TR class="title">
			<TD width="100px">Layers</TD>
			<TD width="240px">Range ';
			print_help("Range", "Description", 'Range value calculation: for ' . 'example, a map to be displayed covers a 2-degree by 1-degree area in ' . 'the EPSG:4326 (WGS-1984) coordinate system. <br>The linear size of this ' . 'area for conversion to scales would be considered to be: ' . '2&ordm; &times; (6378137m &times; 2 &times; &Pi;) &divide; 360&ordm; = 222638.9816 meters (m). So, the map extent would be approximately ' . '222639 x 111319 meters linear distance for the purpose of ' . 'calculating the scale. If the image size for the map is 600 x 300 ' . 'pixels, then the standard scale value for the map would be: ' . '222638.9816m / (600 pixels &times; 0.00028m per pixel) = 1325226.19. <br>This ' . 'calculation is based on a standardized rendering pixel size of 28mm ' . 'x 28mm. All most of all computer monitors are using this standard pixel size.<br> ' . 'If your clients pixel size does not match those values, you ' . 'will need to change the calculation. Please refer to the OGC SLD ' . '(Styled Layer Descriptor) specification for details.');
			print '	   and Symbology</TD>
			<TD>Color Picker</TD>
			</TR>
			
			<TR valign="top">
			<TD>
			<TABLE class="ui-corner-all" style="padding:5px;margin:0px auto;">
			<TR>
			<TD><SELECT
			onchange="writeDivStyleDialog(this.options [this.selectedIndex].value); "
			size="15" name="sltLayers" id="sltLayers">';
			print $layerselectbox;
			print '			  </SELECT>
			</TD></TR>
			</TABLE>
			</TD>
			
			<TD>
			<DIV id="divStyleDialog" style="padding:5px;margin:0px auto;"></DIV>
			</TD>
			
			<TD align="center">
			<script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/colorpicker/js/colorpicker.js"></script>
			<link rel="stylesheet" href="../cssjs/lib/jquery/js/plugin/colorpicker/css/colorpicker.css" type="text/css" />
			<p id="colorpickerHolder">
			</p>
			<script type="text/javascript">
			$(\'#colorpickerHolder\').ColorPicker({
			color: \'#0000ff\',
			flat: true,
			onChange: function (hsb, hex, rgb) {
			//$(\'#colorSelector div\').css(\'backgroundColor\', \'#\' + hex);
			getColor(\'#\' + hex);
			}
			});
			</script>
			</TD>
			</TR>
			
			<tr>
			<td align="left">';
			if ($isInProcess) {
				print '<input onclick="javascript:window.location.href=\'atlas.php?op=ATLAS_CREATE_STEP1_IMPORT&isBackStep=TRUE&aid=' . $aid . '\'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">';
				print '&nbsp;';
				print '<input onclick="javascript:window.location.href=\'atlas.php?op=myatlas\'" type="button" value="Cancel" class="ui-button ui-state-default ui-corner-all">';
			}else{
				print '<input onclick="javascript:window.location.href=\'atlas.php?op=myatlas\'" type="button" value="Cancel" class="ui-button ui-state-default ui-corner-all">';
			}
			print '</td>
			<td colspan="2" align="right">
			<input type="hidden" name="sld_style_default_stid" id="sld_style_default_stid" value="' . $stid_default . '"/>
			</FORM>
			<FORM name="frmStyleAction" id="frmStyleAction" method="post" action="atlas.php">
			<div id="sld_styleinfo_containers" name="sld_styleinfo_containers" style="display:none">';
			if (count($strStyleInfoContainer)> 0) {
				foreach ($strStyleInfoContainer as $k => $v) {
					echo $strStyleInfoContainer[$k];
				}
			} else {
				$error = "Sorry, there is no records in database!";
			}
			print '</div>';
			if ($isInProcess) {
				print '<input type="hidden" name="op" id="op" value="ATLAS_CREATE_STEP3_LAYERINFO">
				<input onclick="saveStyle();" type="button" name="btnContinue" value="Continue" class="ui-button ui-state-default ui-corner-all">';
			} else {
				print '<input type="hidden" name="op" id="op" value="ATLAS_SLD_SAVE">';
				if ($stylecount> 1 && $stid_default != 0) {
					print '<input type="button" name="btndeleteStyle" value="Delete" onclick="deleteStyle();" class="ui-button ui-state-default ui-corner-all">';
					print '&nbsp;<input type="button" name="btnSetDefault" value="Set Default" onclick="setDefaultStyle();" class="ui-button ui-state-default ui-corner-all">';
				}
				//show it when there is at least one style
				if ($stid_default != 0 && $stylecount != 0) {
					print '&nbsp;<input type="button" name="btnChangeName" value="Change Name" onclick="ChangeStyleName();" class="ui-button ui-state-default ui-corner-all">';
					print '&nbsp;<input type="button" name="btnSaveAs" value="Save As" onclick="saveAsStyle();" class="ui-button ui-state-default ui-corner-all">';
				}
				print '&nbsp;<input type="button" name="btnSave" value="Save" onclick="saveStyle();" class="ui-button ui-state-default ui-corner-all">';
				
			}
			print '
			<input type="hidden" name="sld_style_name" id="sld_style_name" value="">
			<input type="hidden" name="sld_style_id" id="sld_style_id" value="">
			<input type="hidden" name="atlas_aid" id="atlas_aid" value="' . $aid . '">
			</td>
			</tr>
			</FORM>
			</TABLE>
			<!--sld panel end-->
			</td>
			<td width="170px;">
			
			<table class="block-panel ui-corner-all" style="width:100%;height:250px;">
			<tr class="block-panel-header ui-corner-all">
			<td>
			Options
			</td>
			</tr>
			
			<tr>
			<td>';
			print 'Change SLD Group: <br/>';
			print $container_style_list;
			print '<span id="id_div_sytle_loading"></span>';
			
			print '</td>
			</tr>
			</table>
			
			</td>
			</tr>
			</table>
			';
		}
	}
}

/**
 * save or save as style
 * if saveas is true, stid will be ignored
 * if saveas if false, if stid is 0, save new style
 * if saveas if false, if stid is not 0, modify the current style
 *
 *
 */
function atlas_save_sld($aid, $database, $sld_style_name, $stid = 0, $saveas = false) {
	$database->dbEmptyErrorMessage();
	$srsnameslist = $database->getAllSrssNames($aid);
	$error = $database->databaseGetErrorMessage();
	$i = 0;
	$count = 1;
	if (empty ($error)) {
		$srsnum = $database->getRowsNumber($srsnameslist);
		while ($srsnames = $database->getColumns($srsnameslist)) {
			$srsname = "";
			$srsname = $srsnames["srs"];
			$blnAlreadyCreateStyle = true;
			
			$layersnameslist = null;
			$metadataTable = null;
			$layersnameslist = $database->getAllLayersNamesInSrs($aid, $srsname);
			while ($row = $database->getColumns($layersnameslist)) {
				if ($row["layer"] != "") {
					$arrLayerName[$i] = $row["layer"];
					$arrLayerType[$i] = $row["geomtype"];
					// $arrayFullLayerName[$i] = $srsname."_".$arrLayerName[$i];
					$arraySrsNamePre[$i] = $srsname . "_";
					$i++;
				}
			}
			$count++;
		}
		if (!StyleParser::createWmsStyles($arrLayerName, $arraySrsNamePre, $arrLayerType, $aid, $database, $sld_style_name, $stid, $saveas)) {
			setSessionMessage("Failed to save style. " . $database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
			return false;
		}
		if (equalIgnoreCase($sld_style_name, "use_exist_style_name")) {
			setSessionMessage(t('Style has been saved successfully.', array (
				'%stylename' => $sld_style_name
			)), SITE_MESSAGE_INFO);
		} else {
			setSessionMessage(t('Style %stylename has been saved successfully.', array (
				'%stylename' => $sld_style_name
			)), SITE_MESSAGE_INFO);
		}
		return true;
	} else {
		setSessionMessage("Failed to save style. " . $database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		return false;
	}
}

function atalas_change_style_name($database, $aid, $stid, $newname) {
	if ($database->db_change_style_name($aid, $stid, $newname)) {
		setSessionMessage(t('Style name has been changed successfully.'), SITE_MESSAGE_INFO);
		return true;
	} else {
		setSessionMessage("Failed to change style name." . $database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		return false;
	}
}

function atlas_set_default_sld($aid, $stid, $database) {
	if ($database->db_set_default_style($aid, $stid)) {
		setSessionMessage(t('Style has been set as default successfully.'), SITE_MESSAGE_INFO);
		return true;
	} else {
		setSessionMessage("Failed to save style as default." . $database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		return false;
	}
}

function atlas_delete_sld($aid, $stid, $database) {
	if ($database->db_delete_style($aid, $stid)) {
		setSessionMessage(t('Style has been deleted successfully.'), SITE_MESSAGE_INFO);
		return true;
	} else {
		setSessionMessage("Failed to delete style." . $database->databaseGetErrorMessage(), SITE_MESSAGE_ERROR);
		return false;
	}
}

/**
 * if is in process, should get the validate info from last form
 * if not in process, should validate the sld xml file to check whether the layer
 * has style already
 *
 * Inside has $_POST, Notice!!!
 */
function get_atlas_layerinfo_form($aid, $database, $styleinfo, $isInProcess = false) {
	$database->dbEmptyErrorMessage();
	$srsnameslist = $database->getAllSrssNames($aid);
	$error = $database->databaseGetErrorMessage();
	$i = 0;
	$count = 1;
	if (!$isInProcess) {
		require_once '../parser/StyleReader.class.php';
		$styleparser = new StyleParser($aid, $database);
		$aryXmlUserLayerNode = $styleparser->createStyleNode4layer(false, 0);
	}
	if (empty ($error)) {
		print '  <script type="text/javascript">
		$(document).ready(function(){
		$("#tabs_layerinfo_srs").tabs();
		});
		</script>
		';
		$stids = $database->db_get_stylelist($aid);
		$atlas_defaultid = $stids['stid_default'];
		
		$srsnum = $database->getRowsNumber($srsnameslist);
		$srscount = 0;
		while ($srsnames = $database->getColumns($srsnameslist)) {
			$srsname = "";
			$srsname = $srsnames["srs"];
			// create the tab navigation here
			if ($count == 1) {
				$tabmenu .= "<li><a href=\"#fragment_layerinfo_srs_$count\"><span>$srsname</span></a></li>\n";
				$tabmenucontent .= "<div id=\"fragment_layerinfo_srs_$count\">\n";
			} else {
				$tabmenu .= "<li><a href=\"#fragment_layerinfo_srs_$count\"><span>$srsname</span></a></li>\n";
				$tabmenucontent .= "<div id=\"fragment_layerinfo_srs_$count\">\n";
			}
			
			$tabmenucontent .= "
				<table class=\"tableNone\">
				<tr width=\"100%\">
				<td colspan=\"9\"></td>
				</tr>
				<tr>
				<td colspan=\"9\"></td>
				</tr>
				<TR>
				<TD width=\"3%\">&nbsp;</TD>
				<TD>Layer Name &nbsp;&nbsp;(Type)</TD>
				<TD>Queryable
				<image src=\"../img/help.png\"  border=\"0\" onmouseover=\"tooltip('Queryable Layer','Description:','Indicates if Layer supports GetFeatureInfo operation.Layers with type of image or text typically do not support this operation.</br>');\" onmouseout=\"exit();\">
				</TD>
				<TD>Visible
				<image src=\"../img/help.png\"  border=\"0\" onmouseover=\"tooltip('Visible Layer','Description:','Indicates if Layer could be requested to display.');\" onmouseout=\"exit();\">
				</TD>
				<TD>Priority
				<image src=\"../img/help.png\"  border=\"0\" onmouseover=\"tooltip('Layer Priority','Description:','Set the priority of layer, layer with high value will overlay the layer with low value.');\" onmouseout=\"exit();\">
				</TD>
				<TD>Descriptive Title</TD>
				<TD>Elevation
				<image src=\"../img/help.png\"  border=\"0\" onmouseover=\"tooltip('Layer Elevation','Description:','Set the elevation of layer, the value will be used to create 3D model.');\" onmouseout=\"exit();\">
				</TD>
				<TD>Style</TD>
				<TD>&nbsp;</TD>
				</TR>
				<tr>
				<td colspan=\"9\">&nbsp;</td>
				</tr>
				";
			if (!$isInProcess) {
				$blnAlreadyCreateStyle = true;
			} else {
				$blnAlreadyCreateStyle = false;
			}
			
			$layersnameslist = null;
			$metadataTable = null;
			$layersnameslist = $database->getAllLayersNamesInSrs($aid, $srsname);
			$layerinsrscount = 0;
			while ($row = $database->getColumns($layersnameslist)) {
				if ($row["layer"] != "") {
					if (!$isInProcess) {
						$styleparser->getLayerStyleFromStyleNode($row["layer"], $row["geomtype"], $aryXmlUserLayerNode);
						$arrLayerStyleName[$i] = $styleparser->xmlUserLayerName;
						$arrLayerStyleTitle[$i] = $styleparser->xmlUserLayerTitle;
						// if there is no sytle for one layer, stop layer info setting, should back to style setting
						if (empty ($arrLayerStyleName[$i])) {
							setSessionMessage(t('Layer(%layername) in SRS (%srsname) has no SLD sytle. Please go to <a href=\"atlas.php?tab=sld&aid=%aid\">Style Setting</a> to create style for this layer.', array (
								'%layername' => $row["layer"],
								'%srsname' => $srsname,
								'%aid' => $aid
							)), SITE_MESSAGE_ERROR);
							return false;
						}
					}
					$arrLayerName[$i] = $row["layer"];
					if($row["typecount"] > 1){
						$arrLayerType[$i] = GeometryTypeCompond;
					}else{
						$arrLayerType[$i] = $row["geomtype"];
					}
					
					$layersnameslistinmeta = $database->getRowsByLayer($aid, $row["layer"]);
					$rowinmeta = $database->getColumns($layersnameslistinmeta);
					// if layer existes
					if ($rowinmeta != false) {
						$arrLayerStyleId[$i] = $atlas_defaultid; //$rowinmeta["stid"];
						//echo "this is old in get_atlas_layerinfo_form $arrLayerStyleId[$i]<br>\n";
						$arrLayerQueryable[$i] = $rowinmeta["queryable"];
						$arrLayerVisiable[$i] = $rowinmeta["visiable"];
						$arrLayerPriority[$i] = $rowinmeta["priority"];
						$arrLayerTitle[$i] = $rowinmeta["description"];
						$arrLayerElevation[$i] = $rowinmeta["elevation"];
					} else {
						// if added new layers before, not in the metadata
						//echo "this is new in get_atlas_layerinfo_form $atlas_defaultid<br>\n";
						$arrLayerStyleId[$i] = $atlas_defaultid;
						if (strtoupper($arrLayerType[$i]) == "IMAGE" OR strtoupper($arrLayerType[$i]) == "TEXT") {
							$arrLayerQueryable[$i] = 0;
						} else {
							$arrLayerQueryable[$i] = 1;
						}
						$arrLayerVisiable[$i] = 1;
						$arrLayerPriority[$i] = 0;
						$arrLayerElevation[$i] = 0;
						$arrLayerTitle[$i] = $arrLayerName[$i];
					}
					$arrayFullLayerName[$i] = $srsname . "_" . $arrLayerName[$i];
					$arraySrsNamePre[$i] = $srsname . "_";
					
					if ($isInProcess) {
						// just check whether the style has been createn for one time
						if (!empty ($styleinfo[$srsname . "_" . $row["layer"] . 'txtStyleName'])) {
							$blnAlreadyCreateStyle = true;
						} else {
							setSessionMessage(t('Layer(%layername) in SRS (%srsname) has not SLD sytle. Please <a href=\"atlas.php?op=ATLAS_CREATE_STEP2_SLD&isBackStep=TRUE&aid=' . $aid . '\">go back</a> to <b>Style Setting</b> to create style for this layer.', array (
								'%layername' => $row["layer"],
								'%srsname' => $srsname
							)), SITE_MESSAGE_ERROR);
							return false;
						}
					}
					
					if ($blnAlreadyCreateStyle) {
						if ($i % 2 == 0)
							$metadataTable .= "<TR class=\"odd\">";
						else
							$metadataTable .= "<TR class=\"even\">";
						
						$metadataTable .= "<INPUT type=\"hidden\" value=\"$arrLayerStyleId[$i]\" name=\"sltStyleId_$arrayFullLayerName[$i]\">\n";
						
						$metadataTable .="<TD><input type=\"checkbox\" name=\"srs_$srscount"."_layer_"."$layerinsrscount\" value=\"\" class=\"button3\">" .
							"<input type=\"hidden\" name=\"srs_$srscount"."_layername_".$layerinsrscount."\"  value=\"$arrLayerName[$i]\" class=\"button3\">".
							"</TD>";
						
						$metadataTable .= "\n<TD>\n<INPUT readOnly size=\"16\" value=\"$arrLayerName[$i]\" name=\"txtLayerName_$arrayFullLayerName[$i]\" class=\"smallInput\">&nbsp;".getLayerTypeIcon($arrLayerType[$i])."\n" . "<INPUT type=\"hidden\" value=\"$arrLayerType[$i]\" name=\"LayerType_$srsname" . "_" . "$arrLayerName[$i]\">\n</TD>\n";
						if (strtoupper($arrLayerType[$i]) == "IMAGE" OR strtoupper($arrLayerType[$i]) == "TEXT" OR $arrLayerQueryable[$i] == 0) {
							$metadataTable .= "<TD>\n<INPUT type=\"checkbox\" value=\"1\" name=\"chkQueryable_$arrayFullLayerName[$i]\" class=\"button3\">\n</TD>\n";
						} else {
							$metadataTable .= "<TD>\n<INPUT type=\"checkbox\" checked value=\"1\" name=\"chkQueryable_$arrayFullLayerName[$i]\" class=\"button3\">\n</TD>\n";
						}
						if ($arrLayerVisiable[$i] == 1) {
							$metadataTable .= "<TD>\n<INPUT type=\"checkbox\" checked value=\"1\" name=\"chkVisiable_$arrayFullLayerName[$i]\" class=\"button3\">\n</TD>\n";
						} else {
							$metadataTable .= "<TD>\n<INPUT type=\"checkbox\" value=\"1\" name=\"chkVisiable_$arrayFullLayerName[$i]\" class=\"button3\">\n</TD>\n";
						}
						$metadataTable .= "<TD>\n<SELECT name=\"sltPriority_$arrayFullLayerName[$i]\" class=\"button4\">\n" . printOption4PrioritySelected(-20, 20, $arrLayerPriority[$i]) . "\n</SELECT></TD>\n";
						$metadataTable .= "<TD>\n<INPUT size=\"20\" value=\"$arrLayerTitle[$i]\" name=\"txtLayerTitle_$arrayFullLayerName[$i]\" class=\"smallInput\">\n</TD>\n";
						$metadataTable .= "<TD>\n<INPUT size=\"8\" value=\"$arrLayerElevation[$i]\" name=\"txtLayerElevation_$arrayFullLayerName[$i]\" class=\"smallInput\">\n</TD>\n";
						if (!$isInProcess) {
							$metadataTable .= "<TD>\n<SELECT name=\"sltStyles_$arrayFullLayerName[$i]\" class=\"button4\">\n<OPTION value=\"" . $arrLayerStyleName[$i] . "\" selected>" . $arrLayerStyleName[$i];
						} else {
							$metadataTable .= "<TD>\n<SELECT name=\"sltStyles_$arrayFullLayerName[$i]\" class=\"button4\">\n<OPTION value=\"" . $styleinfo[$arrayFullLayerName[$i] . 'txtStyleName'] . "\" selected>" . $styleinfo[$arrayFullLayerName[$i] . 'txtStyleName'];
						}
						$metadataTable .= "</OPTION>\n</SELECT>\n</TD><TD>&nbsp;</TD>" .
							"</TR>";
						$i++;
					}
					$layerinsrscount++;
				}
			}
			//show delete option
			if(!$isInProcess){
				$checkscript .= "\n<script type=\"text/javascript\">";
				$checkscript .=  "function chkDeleteLayers_".$srscount."()";
				$checkscript .=  "{";
				//here need to load featuregeometry table, because before create featureclass,
				//use can also delete the layers
				$rs2 = $database->getLayersBySrsGroupByLayer($aid, $srsname, true);
				$checkscript .=  "if(";
				$iLayers = 0;
				$num = $database->getRowsNumber($rs2);
				while ($line2 = $database->getColumns($rs2)) {
					$checkscript .=  "(document.formLayerInfo.srs_".$srscount."_layer_" . $iLayers . ".checked != true )";
					if ($iLayers != $num-1) {
						$checkscript .=  "&&";
					}
					++$iLayers;
				}
				$checkscript .=  ")";
				$checkscript .=  " { ";
				$checkscript .=  "growlError(\"Please select a layer!\");";
				$checkscript .=  "return false;";
				$checkscript .=  "}";
				$checkscript .=  " var layerstr = \"\";";
				$checkscript .=  "for  (i = 0; i < $num ; ++ i)";
				$checkscript .=  "{";
				$checkscript .=  "var chk= \"srs_".$srscount."_layer_"."\" + i;";
				$checkscript .=  "var chkname= \"srs_".$srscount."_layername_"."\" + i + \"\";";
				$checkscript .=  "var fila = document.getElementsByName(chk);";
				$checkscript .=  "if (fila[0].checked == true){" .
					"layerstr = layerstr + document.getElementsByName(chkname)[0].value + \",\";";
				$checkscript .=  "}";
				$checkscript .=  "}";
				$checkscript .=  "layerstr = (layerstr.charAt(layerstr.length-1) == \",\") ? layerstr.slice(0,layerstr.length-1) : layerstr;";
				
				//$checkscript .=  "alert(\"you have selected LAYERS=\" + layerstr) ;";
				$checkscript .=  "if(confirm(\"Are you sure to delete layers: \"+layerstr+\"?\")){";
				$checkscript .=  "document.formLayerInfo.CURRENT_DELETE_LAYERS.value = layerstr;";
				$checkscript .=  "document.formLayerInfo.CURRENT_DELETE_SRS.value = '$srsname';";
				$checkscript .=  "document.formLayerInfo.op.value = 'ATLAS_DELETE_LAYERS';";
				$checkscript .=  "document.formLayerInfo.submit();";
				$checkscript .=  "}";
				$checkscript .=  "else{return false;}";
				$checkscript .=  "}";
				$checkscript .=  "</script>\n";
				
				$metadataTable .= "<tr align=\"right\">
					<td colspan=\"9\">";
				$metadataTable .='<input type="button" name="button" value="Delete" onclick="return chkDeleteLayers_'.$srscount.'();" class="ui-button ui-state-default ui-corner-all"/>';
				$metadataTable .="
					</td>
					</tr>";
			}
			
			$count++;
			$srscount++;
			$tabmenucontent .= $metadataTable;
			$tabmenucontent .= "</table></div>";
		}
		
		if ($blnAlreadyCreateStyle) {
			if ($error == "") {
				
				if(!$isInProcess){
					$checkscript .= "\n<script type=\"text/javascript\">";
					$checkscript .=  "function chkSaveLayerInfo()";
					$checkscript .=  "{";
					$checkscript .=  "document.formLayerInfo.op.value = 'ATLAS_LAYERINFO_SAVE';";
					$checkscript .=  "document.formLayerInfo.submit();";
					$checkscript .= ("}");
					$checkscript .=  "</script>\n";
				}
				print '<table class="tableNone">';
				if($isInProcess){
					print '<tr class="title"><td colspan="2">Atlas '.($isInProcess?'':'(<b>'.$_SESSION['atlas']['name'].'</b>)').' Layer Info</td></tr>';
					print '<tr><td align="left"><img src="../img/steps_4.png"></td><td></td></tr>';
				}else{
					print '<tr class="title"><td colspan="2">'.($isInProcess?'':''.$_SESSION['atlas']['name']).'</td></tr>';
				}
				print '<tr><td colspan="2"><img src="../img/i_gis_layer.png" title="layer">&nbsp;Now set the information for layers.
				<span id="id_div_process_loading"></span>
				</td></tr>';
				print '</table>';
				print '
				
				<div id="tabs_layerinfo_srs" style="FONT-SIZE: 10px;">
				<ul>';
				echo $tabmenu;
				print '</ul>				
				
				<form name="formLayerInfo" action="atlas.php" method="post">
				<table class="tableNone">
				<tr>
				<td colspan="2">';
				echo $tabmenucontent;
				echo $checkscript;
				print '     </td>
				</tr>
				<tr>
				<td align="left">';
				if ($isInProcess) {
					print '<input onclick="javascript:window.location.href=\'atlas.php?op=ATLAS_CREATE_STEP2_SLD&isBackStep=TRUE&aid=' . $aid . '\'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">';
					print '&nbsp;';
					print '<input onclick="javascript:window.location.href=\'atlas.php?op=myatlas\'" type="button" value="Cancel" class="ui-button ui-state-default ui-corner-all">';
				}else{
					print '<input onclick="javascript:window.location.href=\'atlas.php?op=myatlas\'" type="button" value="Cancel" class="ui-button ui-state-default ui-corner-all">';
				}
				print '</td>
				<td align="right">';
				if ($isInProcess) {
					print '
					<input type="hidden" name="op" id="op" value="ATLAS_CREATE_STEP4_CFG"/>
					<input type="submit" name="btnContinue" value="Continue" class="ui-button ui-state-default ui-corner-all"/>';
				} else {
					print '<input type="hidden" name="op" id="op" value="ATLAS_LAYERINFO_SAVE"/>' .
					'<input type="hidden" name="CURRENT_DELETE_LAYERS" id="CURRENT_DELETE_LAYERS" value=""/>' .
					'<input type="hidden" name="CURRENT_DELETE_SRS" id="CURRENT_DELETE_SRS" value=""/>' .
					'<input type="button" name="button" value="Save" onclick="return chkSaveLayerInfo();" class="ui-button ui-state-default ui-corner-all"/>';
				}
				print '	 </td>
				</tr>
				</table>
				<input type="hidden" name="atlas_aid" id="atlas_aid" value="' . $aid . '"/>
				</form>
				</div>
				';
				
			}
		}else{
			if ($error == "") {				
				print '<form name="formLayerInfo" action="atlas.php" method="post">' .
				'<table class="tableNone">';				
				if($isInProcess){
					print '<tr class="title"><td colspan="2">Atlas '.($isInProcess?'':'(<b>'.$_SESSION['atlas']['name'].'</b>)').' Layer Info</td></tr>';
					print '<tr><td align="left"><img src="../img/steps_4.png"></td><td></td></tr>';
				}else{
					print '<tr class="title"><td colspan="2">'.($isInProcess?'':''.$_SESSION['atlas']['name']).'</td></tr>';
				}
				print '<tr>
				<td colspan="2">
				<div class="messages info" style="display:block">Sorry, there is no layer in atalas, OR you may have not set the SLD.</a></td>';		
				print '<tr>
				<td colspan="2"></td>
				</tr>
				<tr>
				<td align="left">';
				if ($isInProcess) {
					print '<input onclick="javascript:window.location.href=\'atlas.php?op=ATLAS_CREATE_STEP2_SLD&isBackStep=TRUE&atlas_aid=' . $aid . '\'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">';
				}else{
					print '<input onclick="javascript:window.location.href=\'atlas.php?op=myatlas\'" type="button" value="Cancel" class="ui-button ui-state-default ui-corner-all">';
				}
				print '</td>
				<td align="right">';
				print '	 </td>
				</tr>
				</table>
				<input type="hidden" name="atlas_aid" id="atlas_aid" value="' . $aid . '"/>
				</form>';
			}
		}
	}
	return true;
}

function getLayerTypeIcon($type, $tree = false){
	$last="";
	switch(strtoupper($type)){
		case strtoupper(GeometryTypePoint) : 
			if(!$tree)
				$last = '<img src="../img/i_gis_point.png" title="'.GeometryTypePoint.'">';
			else
				$last = '../img/i_gis_point16.png';
		break;
		case strtoupper(GeometryTypeLineString) : 
			if(!$tree)
				$last = '<img src="../img/i_gis_linestring.png" title="'.GeometryTypeLineString.'">';
			else
				$last = '../img/i_gis_linestring16.png';
		break;
		case strtoupper(GeometryTypePolygon) : 
			if(!$tree)
				$last = '<img src="../img/i_gis_polygon.png" title="'.GeometryTypePolygon.'">';
			else
				$last = '../img/i_gis_polygon16.png';
		break;
		case strtoupper(GeometryTypeImage) : 
			if(!$tree)
				$last = '<img src="../img/i_gis_image.png" title="'.GeometryTypeImage.'">';
			else
				$last = '../img/i_gis_image16.png';
		break;
		case strtoupper(GeometryTypeText) : 
			if(!$tree)
				$last = '<img src="../img/i_gis_text.png" title="'.GeometryTypeText.'">';
			else
				$last = '../img/i_gis_text16.png';
		break;
		case strtoupper(GeometryTypeCompond) : 
			if(!$tree)
				$last = '<img src="../img/i_gis_compond.png" title="'.GeometryTypeCompond.'">';
			else
				$last = '../img/i_gis_compond16.png';
		break;
		case strtoupper(GeometryTypeUnknown):
			if(!$tree)
				$last = '<img src="../img/i_gis_unknown.png" title="'.GeometryTypeText.'">';
			else
				$last = '../img/i_gis_unknown16.png';
		break;
		default:
			if(!$tree)
				$last = '<img src="../img/i_gis_unknown.png" title="'.GeometryTypeText.'">';
			else
				$last = '../img/i_gis_unknown16.png';
	}
	return $last;
}

function atlas_save_layerinfo($aid, $database, $POSTINFO) {
	$database->dbEmptyErrorMessage();
	$layersnameswithsrslist = $database->getLayersNamesWithDiffSrs($aid);
	// empty the old featureclass records
	$database->emptyLayerRecordsInFeatureClass($aid);
	if ($error = $database->databaseGetErrorMessage()) {
		setSessionMessage("Failed to empty old freatureclass records. " . $error, SITE_MESSAGE_ERROR);
		return false;
	}
	$i = 0;
	while ($row = $database->getColumns($layersnameswithsrslist)) {
		if ($row["layer"] != "") {
			$aryLayerName[$i] = $row["layer"];
			$arySrsName[$i] = $row["srs"];
			
			$layertype = "Unknown";
			$layer = $aryLayerName[$i];
			$description = $POSTINFO['txtLayerTitle_' . $arySrsName[$i] . "_" . $aryLayerName[$i]];
			$geomtype = $POSTINFO['LayerType_' . $arySrsName[$i] . "_" . $aryLayerName[$i]];
			
			$xylist = $database->getRowsMinMaxXYBySrsLayer($aid, $arySrsName[$i], $aryLayerName[$i], true);
			$xylistrow = $database->getColumns($xylist);
			$xmin = $xylistrow[0];
			$ymin = $xylistrow[1];
			$xmax = $xylistrow[2];
			$ymax = $xylistrow[3];
			
			$srs = $arySrsName[$i];
			$styleid = $POSTINFO['sltStyleId_' . $arySrsName[$i] . "_" . $aryLayerName[$i]];
			$style = $POSTINFO['sltStyles_' . $arySrsName[$i] . "_" . $aryLayerName[$i]];
			$queryable = false;
			$visiable = false;
			$priority = 0;
			$elevation = 0;
			$priority = $POSTINFO['sltPriority_' . $arySrsName[$i] . "_" . $aryLayerName[$i]];
			$elevation = $POSTINFO['txtLayerElevation_' . $arySrsName[$i] . "_" . $aryLayerName[$i]];
			// $tablename, $layertype, $layer, $description, $geomtype, $xmin, $ymin, $xmax, $ymax, $srs, $style, $queryable, $visiable, $priority,$elevation
			$layertype = $geomtype;
			if ($POSTINFO['chkQueryable_' . $arySrsName[$i] . "_" . $aryLayerName[$i]] == 1) {
				$queryable = true;
			}
			if ($POSTINFO['chkVisiable_' . $arySrsName[$i] . "_" . $aryLayerName[$i]] == 1) {
				$visiable = true;
			}
			
			$database->createFeatureClassMetadata($aid, $layertype, $layer, $description, $geomtype, $xmin, $ymin, $xmax, $ymax, $srs, $styleid, $queryable, $visiable, $priority, $elevation);
			//echo $database->databaseGetErrorMessage();
			$i++;
		}
	}
	if ($error = $database->databaseGetErrorMessage()) {
		setSessionMessage(t('Failed to save layer info, %error', array('%error' => $error)), SITE_MESSAGE_ERROR);
		return false;
	}else{
		$database->db_update_atlas_modified_time($aid);
		//create the overview image now, via Ajax??
		
	}
	
	setSessionMessage(t('Layer info has been saved successfully.'), SITE_MESSAGE_INFO);
	//atlas_create_overview($database, $aid);
	
	
	return true;
}

/**
 * TODO delete all layers in srs
 * $database->deleteLayersInSrs($tb_name,$deletesrsname);
 * $database->deleteLayersInSrs($tb_metaname,$deletesrsname);
 */
function atlas_delete_layers($aid, $database, $current_deleted_layers, $current_deleted_srs){
	if(empty($current_deleted_layers) || empty($current_deleted_srs)){
		setSessionMessage(t('No layers in SRS have been selected.'), SITE_MESSAGE_ERROR);	
		return false;
	}
	if(!$database->deleteLayersBySRS($aid, $current_deleted_srs, $current_deleted_layers, true)){
		setSessionMessage(t('Failed to delete layers %layers in SRS %srs , %error', array('%srs' => $current_deleted_srs, 
			'%layers' => $current_deleted_layers, '%error' => $database->databaseGetErrorMessage())), SITE_MESSAGE_ERROR);
		return false;
	}
	//maybe there is no featureclass data
	$database->deleteLayersBySRS($aid, $current_deleted_srs, $current_deleted_layers, false);
	
	if($error = $database->databaseGetErrorMessage()){
		setSessionMessage(t('Failed to delete layers %layers in SRS %srs , %error', array('%srs' => $current_deleted_srs, 
			'%layers' => $current_deleted_layers, '%error' => $error)), SITE_MESSAGE_ERROR);
		return false;
	}
	setSessionMessage(t('Layers %layers in SRS %srs have been deleted.', array('%srs' => $current_deleted_srs, 
		'%layers' => $current_deleted_layers)), SITE_MESSAGE_INFO);
	return true;
}

/**
 * http://www.birdtheme.org/useful/googletool.html
 * http://www.birdtheme.org/useful/editkmlfile.php
 */
function get_atlas_geoedit_form($aid, $database, $incomingInfo){
	echo '<form action="atlas.php"  accept-charset="UTF-8" method="post" name="atlas_geoedit_form" id="atlas_geoedit_form" >';
	print '<table class="tableNone" style="margin-top:-23px">';
	print '<tr><td>';
	//for menu tab
?>
<div id="menutab">
<ul id="mainmenu">
<li>File
	<ul>
		<li onclick="saveLayer('<?=$aid?>');" title="Save&nbsp;Layer">Save...</li>
		<li>Import...</li>
		<li onclick="validateData();" title="Validate the importing KML data">Validate Data</li>
		<li class="sep"></li>
		<li>Exit</li>
	</ul>
</li>
<li>Edit
	<ul><li onclick="deleteLastPoint();" title="Delete last point">Undo</li>
		<li>Redo</li>
		<li class="sep"></li>
		<li>Cut</li>
		<li onclick="copyTextarea();" title="Select and copy text">Copy</li>
		<li>Paste<ul><li>All</li><li>Something</li></ul></li>
		<li>Delete</li>
	</ul>
</li>
<li>View
	<ul>
	<li>Full&nbsp;screen</li>
	<li class="sep"></li>
	<li>Layer
			<ul>
			<li onclick="ExpandAllLayers();" title="Expand&nbsp;All&nbsp;Layers">Expand&nbsp;All</li>
			<li onclick="CollapseAllLayers();" title="Collapse&nbsp;All&nbsp;Layers">Collapse&nbsp;All</li>
			</ul>
		</li>
	</ul>
</li>
<li>Tool
	<ul>
		<li>Map
			<ul>
			<li onclick="clearMap();" title="Clear Map">Clear&nbsp;Map</li>
			<li onclick="map_SearchAddress();" title="Search&Locate Map">Search&nbsp;Map</li>
			<li onclick="map_Center();" title="Get map center">Map&nbsp;Center</li>
			</ul>
		</li>
		<li class="sep"></li>
		<li>Layer
			<ul>
			<li onclick="$.tree_reference('panellayer').create({ attributes : { 'class' : 'cc' }, data: { title : '<?=LayerNotDefined?>', icon : '<?=getLayerTypeIcon(GeometryTypeUnknown, true)?>' } });" title="New Layer">New</li>
			<li onclick="$.tree_reference('panellayer').rename();" title="Rename Layer">Rename</li>
			</ul>
		</li>
		<li class="sep"></li>
		<li onclick="nextshape();" title="Next Shape&Finish Draw">Next&nbsp;Shape</li>
		<li>Point</li>
		<li>Linestring
			<ul>
			<li onclick="closePolyline();" title="Close&nbsp;LineString">Close</li>
			</ul>
		</li>
		<li>Polygon</li>
		<li class="sep"></li>
		<li onclick="createchildwindow();" title="Style&nbsp;options">Style</li>
	</ul>
</li>
<li>Help
	<ul><li>Help</li>
		<li class="sep"></li>
		<li>About...</li>
	</ul>
</li>
</ul>
</div>
<script type="text/javascript">
<!--
        
function map_SearchAddress() { 
	$.blockUI({ message: $('#div_map_searchaddress'), css: { width: '300px',padding: 5  }, timeout: 20000 }); 
} 
$(function() {
		$("#menutab").draggable();
		$("#tooltab_editor").draggable();
		$("#tooltab_status").draggable();
		$("#tooltab_properties_container").draggable().resizable();
		$("#map").resizable();
		//$("#status").resizable();
		$("#tooltab_log").draggable();
});

$(document).ready(function()
{
	$.fn.clickMenu.setDefaults({arrowSrc:'../img/menu-collapsed.png'});
	$('#mainmenu').clickMenu();

    $('#div_map_searchaddress_search').click(function() {
    		$.unblockUI(); 
        	loacateAddress($('#div_map_searchaddress_tfaddress').val());
        	return false;
        }); 
 
   	$('#div_map_searchaddress_cancel').click(function() { 
            $.unblockUI(); 
            return false; 
        });
});
-->
</script>

<div id="div_map_searchaddress" style="display:none; cursor: default"> 
	<div class="form-item" id="edit-name-wrapper">
	<label for="tf_address">Find addresses and places of interest</label>
	<input type="text" size="30" class="smallInput" id="div_map_searchaddress_tfaddress" name="tf_address" value="" />
	<div>
	<input type="button" id="div_map_searchaddress_search" value="Search" class="ui-button ui-state-default ui-corner-all"/>
	<input type="button" id="div_map_searchaddress_cancel" value="Cancel" class="ui-button ui-state-default ui-corner-all"/>
</div> 
<?	
	print '</td></tr>';
	print '<tr><td>';
	//for map tools tab
?>	
<table class="tableNone" style="width:100%">
<tr style="vertical-align:center">
	<td style="width:15em">
		<div id="tooltab_editor" class="tooltab">
		<table class="tableNone tooltabcontent">
		<tr>
		<td><div id="hand_b"
			 onclick="stopEditing()" title="Move"/></td>
		<td><div id="nextshape_b"
			 onclick="nextshape()" title="Next Shape&Finish Draw"/></td>
		<td><div id="point_b"
			 onclick="drawPoint()" title="Point"/></td>
		<td><div id="linestring_b"
			 onclick="drawLinestring()" title="LineString"/></td>
		<td><div id="polygon_b"
			onclick="drawPolygon()" title="Polygon"/></td>
		<td><div id="circle_b"
			onclick="drawCircle(false)" title="Circle"/></td>
		<td><div id="rectangle_b"
			onclick="drawRectange(false)" title="Rectange"/></td>
		<td><div id="circlef_b"
			onclick="drawCircle(true)" title="Filled Circle"/></td>
		<td><div id="rectanglef_b"
			onclick="drawRectange(true)" title="Filled Rectange"/></td>
		</tr>
		</table>
		</div>
	</td>
	<td >
	
	<div id="tooltab_status" class="tooltab" style="width:20em;">
	<table class="tableNone">
	<tr>
		<td>
		<span class="tooltab-status-label">Longitude&Latitude:</span>
		</td>
		<td>
		<span name="maplonlatvalue" id="maplonlatvalue" class="tooltab-status-item">&nbsp</span>
		</td>
	</tr>
	<tr>
		<td>
		<span class="tooltab-status-label">Zoom:</span>
		</td>
		<td>
		<span name="mapzoom" id="mapzoom" class="tooltab-status-item">&nbsp</span>
		</td>
	</tr>
	</table>
	</div>
	
	<div id="tooltab_properties_container" class="tooltab_float">
	<div class="tooltab_v"></div>
	<div class="tableNone" style="background-color:white;">
	<table id="tooltab_properties" class="title" width="100%">
		<td colspan="2"><span class="tooltab-status-label">Properties</span>
		</td>
	</tr>
	<tr >
		<td>
		<span class="tooltab-status-label">Name:</span>
		</td>
		<td>
		<span class="tooltab-status-label">Value:</span>
		</td>
	</tr>
	<tr>
		<td>
		<input type="text" size="10" class="form-text" value=""/>
		</td>
		<td>
		<input type="text" size="10" class="form-text" value=""/>
		</td>
	</tr>
	</table>
	</div>
	
	</td>
</tr>
</table>
<?	
	print '</td ></tr>';
	print '<tr><td>';
	//for map canvas and layer tree
?>	
<table>
<tr >
<td style="width: 200px; height:100%;">
<?
	
	$layersrs = $database->getAllLayersSrss($aid);
	$srs_temp = "";
	$layercount = 0;
	$srscount = -1;
	$optionstr ='<div id="panellayer" class="panel-layer"><div class="tooltab_v" style="margin-top:-5px;padding:0px;min-width:0em;min-height:16px;text-align:center;valign:top"><span style="valign:top">Layers</span> <img id="map_loading" src="../img/wait.gif" style="display:none"/></div><ul class="tree-default">';
	while ($rec = $database->getColumns($layersrs)) {
		if($rec['srs'] != $srs_temp){
			$srscount++;
			if($srs_temp != ""){
				$optionstr .= "</ul></li>"."\n";	
			}

			if(strtoupper($rec['srs']) == "EPSG:4326"){
				$optionstr .= '<li class="open" id="srs_'.$srscount.'" ref="srs_epsg4326"><a href="#" class="tree-default" style="background-image:url(\'../img/i_gis_layer16.png\');">'.$rec['srs'].'</a><ul>'."\n";
			}else{
				$optionstr .= '<li class="closed" id="srs_'.$srscount.'">'.$rec['srs'].'<ul>'."\n";	
			}		
		}
		//GeometryTypeCompond  $rec['geomtype']
		if($rec['typecount'] > 1){
			$optionstr .= '<li class="leaf" id="layer_'.$layercount.'" ref="layer">
				<a href="#" ';
			if(strtoupper($rec['srs']) == "EPSG:4326"){
				$optionstr .= ' ref="epsg4326" ';
			}
			$optionstr .= 'class="tree-default" style="background-image:url(\''.getLayerTypeIcon(GeometryTypeCompond, true).'\');">' . $rec['layer'].  '</a></li>'."\n"; 
		}else{
			$optionstr .= '<li class="leaf" id="layer_'.$layercount.'" ref="layer">
				<a href="#" ';
			if(strtoupper($rec['srs']) == "EPSG:4326"){
				$optionstr .= ' ref="epsg4326" ';
			}
			$optionstr .= 'class="tree-default" style="background-image:url(\''.getLayerTypeIcon($rec['geomtype'], true).'\');">' . $rec['layer'].  '</a></li>'."\n"; 
		}
		if($rec['srs'] != $srs_temp){
			//$optionstr .= "</ul>";
		}
		$srs_temp = $rec['srs'];
		$layercount++;
	}
	$optionstr .= '</ul></li></ul></div>'."\n";

	if($layercount > 0){
		print $optionstr;
	}else{
		$optionstr ='<div id="panellayer" class="panel-layer"><ul class="tree-default">';
		$optionstr .= '<li class="open" id="srs_0" ref="srs_epsg4326"><a href="#" style="background-image:url(\'../img/i_gis_layer16.png\');">EPSG:4326</a><ul>';
		$optionstr .= '<li class="leaf" id="layer_0">
			<a href="#" ref="epsg4326" class="tree-default" style="background-image:url(\''.getLayerTypeIcon(GeometryTypeUnknown, true).'\');">'.LayerNotDefined.'</a></li>';
		$optionstr .= '</ul></li></ul></div>';
		print $optionstr;
	}

?>
<div id="demo2"></div>
<script type="text/javascript">
$(function () {
	$("#panellayer").tree(
	{
      // Data settings omitted
      rules : { //all
        renameable: 'default',
        creatable: 'default',
        deletable : "default"
        //clickable : [ "srs_epsg4326", "layer" ]      
      },
      lang : {
        new_node    : "<?=LayerNotDefined?>"
  		},
      callback : {
      	ondblclk    : function(NODE, TREE_OBJ) { loadLayer(TREE_OBJ,"<?=get_wfs_interface($aid)?>");TREE_OBJ.toggle_branch.call(TREE_OBJ, NODE); TREE_OBJ.select_branch.call(TREE_OBJ, NODE); },
        beforerename: function(NODE,LANG,TREE_OBJ) { alert(TREE_OBJ.path); return true }
      }
      
    }
    );
});

/*
$("#demo2").tree({
  data  : {
    type  : "json",
    json  : [ 
      { attributes: { id : "pjson_1" }, state: "open", data: "Root node 1", children : [
        { attributes: { id : "pjson_2" }, data: { title : "Custom icon", icon : "../img/i_gis_layer16.png" }
         },
        { attributes: { id : "pjson_3" }, data: "Child node 2" },
        { attributes: { id : "pjson_4" }, data: "Some other child node" }
      ]}, 
      { attributes: { id : "pjson_5" }, data: "Root node 2" } 
    ]
  },
  rules: {
        renameable: 'all', clickable: 'default'
        }, 
  lang : {
        new_node    : "test",
        loading     : "Loading ..."
  }
});

$.tree_reference('demo2').create({ attributes : { 'class' : 'cc' }, data: { title : 'ID and ICON', icon : '../img/i_gis_layer16.png' }
	,rules: {
        renameable: 'default', creatable: 'default'
        }
	 },$('#pjson_2'));
*/
</script>

</td>
<td style="width: 100%; height: 100%;">
<div id="map" style="width: 800px; height: 450px; border: 1px solid gray; margin-top: 1px; margin-left: 1px;"></div>
<script type="text/javascript">
</script>
<div id="status" style="width: 300px; font-family: Arial, sans serif; color: #ff0000;text-align:left"></div>
</td>
</tr>
<tr>
<td colspan="2">

<div id="tooltab_log" style="width:420px">
<div class="tooltab_v"></div>
<textarea id="coords" cols="50" rows="5" style="display:block">
</textarea>
</div>

</td>
</tr>
</table>
<?	
	print '</td ></tr>';
	print '</table>';
	print '</form>';
}

/**
 *
 */
function atlas_get_demo_list($type, $aid) {
	global $atlas;
	print '<table class="tableNone">' .
	'<tr class="title"><td width="100%" colspan="3">'.$atlas['name'].'</td></tr>
	<tr valign="top">
	<td>';
	//========================WMS Demo======================
	print ('			<li class="first"><span>WMS Demo</span></li>');
	print ('				<ul class="second">');
	
	if ($type == "demo_wms_GetCapabilities")
		print ('					<li class="active">GetCapabilities</li>');
	else
		print ('					<li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wms_GetCapabilities">GetCapabilities</a></li>');
	
	if ($type == "demo_wms_GetMap")
		print ('					<li class="active">GetMap</li>');
	else
		print ('					<li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wms_GetMap">GetMap</a></li>');
	
	if ($type == "demo_wms_GetFeatureInfo")
		print ('		            <li class="active">GetFeatureInfo</li>');
	else
		print ('		            <li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wms_GetFeatureInfo">GetFeatureInfo</a></li>');
	
	if ($type == "demo_wms_DescribeLayer")
		print ('		            <li class="active">DescribeLayer</li>');
	else
		print ('		            <li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wms_DescribeLayer">DescribeLayer</a></li>');
	
	if ($type == "demo_wms_GetLegendGraphic")
		print ('		            <li class="active">GetLegendGraphic</li>');
	else
		print ('		            <li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wms_GetLegendGraphic">GetLegendGraphic</a></li>');
	
	if ($type == "demo_wms_GetStyles")
		print ('		            <li class="active">GetStyles</li>');
	else
		print ('		            <li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wms_GetStyles">GetStyles</a></li>');
	
	print ('				</ul>');
	
	print '</td>
	<td>';
	//========================WMS Extension======================
	print ('		    <li class="first"><span>WMS Extension</span></li>');
	print ('		        <ul class="second">');
	
	// if ($type == "SUAS Map Client")
	//print('		            <li class="active">SUAS Map Client</li>');
	//else
	print ('		            <li class="unactive"><a href="../plugin/suasclient/index.html" target="_blank">SUAS Map Client</a></li>');
	
	if ($type == "demo_wmsextension_GetMapViewers")
		print ('		            <li class="active">Map Viewers</li>');
	else
		print ('		            <li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wmsextension_GetMapViewers">Map Viewers</a></li>');
	
	if ($type == "demo_wmsextension_GetMap25D")
		print ('		            <li class="active">2.5D Navigation</li>');
	else
		print ('		            <li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wmsextension_GetMap25D">2.5D Navigation</a></li>');
	
	if ($type == "demo_wmsextension_GetMap3D")
		print ('		            <li class="active">3D Navigation</li>');
	else
		print ('		            <li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wmsextension_GetMap3D">3D Navigation</a></li>');
	
	if ($type == "demo_wmsextension_GetThematicMap")
		print ('		            <li class="active">GetThematicMap</li>');
	else
		print ('		            <li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wmsextension_GetThematicMap">GetThematicMap</a></li>');
	
	print ('				</ul>');
	
	print '</td>
	<td>';
	//========================WFS Demo======================
	print ('		    <li class="first"><span>WFS Demo</span></li>');
	print ('		        <ul class="second">');
	if ($type == "demo_wfs_GetCapabilities")
		print ('		            <li class="active">GetCapabilities</li>');
	else
		print ('		            <li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wfs_GetCapabilities">GetCapabilities</a></li>');
	if ($type == "demo_wfs_DescribeFeatureType")
		print ('		            <li class="active">DescribeFeatureType</li>');
	else
		print ('		            <li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wfs_DescribeFeatureType">DescribeFeatureType</a></li>');
	if ($type == "demo_wfs_GetFeature")
		print ('		            <li class="active">GetFeature</li>');
	else
		print ('		            <li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wfs_GetFeature">GetFeature</a></li>');
	if ($type == "demo_wfs_GetGmlObject")
		print ('		            <li class="active">GetGmlObject</li>');
	else
		print ('		            <li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wfs_GetGmlObject">GetGmlObject</a></li>');
	if ($type == "demo_wfs_Transaction")
		print ('		            <li class="active">Transaction</li>');
	else
		print ('		            <li class="unactive"><a href="atlas.php?aid=' .
			$aid . '&op=demo_wfs_Transaction">Transaction</a></li>');
	print ('				</ul>');
	print '</td>
	</tr>';
	print '<tr>
	<td align="left">
	<input onclick="javascript:window.location.href=\'atlas.php\'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
	</td>
	<td>
	</td>
	<td align="right">
	</td>
	</tr>';
	
	print '</table>';
}

/**
 * go to atlas view page, and add into atlas counter(if from atlas list), except the owner
 */
function get_atlas_view($database, $user, $aid, $op, $map_parameters){
	global $params;
	$atlas = $database->db_get_atlas($aid);
	
	$op = strtolower($op);
	if($op == "atlasview_viewer"){
		$backurl = '&total='.$_GET['total'].'&order='.$_GET['order'].'&sort='.$_GET['sort'].'&pg='.$_GET['pg'];
		atlas_get_mapview_viewer_form($database, $aid, $atlas, 'view', $backurl);
	}
	else if($op == "atlasview"){
		$backurl = $_GET['backurl '];
		atlas_get_mapview_form($database, $aid, $atlas, $map_parameters, 'view', $backurl);
		//add into counter
		if($atlas['uid'] != $user['uid']){
			$database->db_atlas_counter($aid);
		}
	}
	else if($op == "atlasviewfavo_viewer"){
		$backurl = '&total='.$_GET['total'].'&order='.$_GET['order'].'&sort='.$_GET['sort'].'&pg='.$_GET['pg'];
		atlas_get_mapview_viewer_form($database, $aid, $atlas, 'favo', $backurl);
	}
	else if($op == "atlasviewfavo"){
		$backurl = $_GET['backurl '];
		atlas_get_mapview_form($database, $aid, $atlas, $map_parameters, 'favo', $backurl);
		//add into counter
		if($atlas['uid'] != $user['uid']){
			$database->db_atlas_counter($aid);
		}
	}
}

function atlas_get_demo_block($user, $aid, $op, $map_parameters, $database) {
	global $params;
	global $atlas;
	$demo_operation = strtolower(substr($op, 5));
	switch ($demo_operation) {
		case "wms_getcapabilities" :
		{
			print ' <table class="tableNone">
			<tr class="title"><td colspan="2">'.$atlas['name'].'</td></tr>
			
			<tr>
			<td width="65%" valign="top">
			<table class="block-panel ui-corner-all" style="width:100%;" align="top">
			<tr class="block-panel-header ui-corner-all">
			<td colspan="2">
			GetCapabilities
			</td></tr>'
			.'<tr><form action="'.get_wms_interface($aid).'" name="FormDemo" method="get" target="_blank">
			<td width="100">VERSION</td>
			<td width="261">
			<input type="text" name="VERSION" value="' . SUAS_CFG_WMS_VERSION . '" readonly="readonly" class="smallInput"></td>
			</tr>
			
			<tr>
			<td>SERVICE</td>
			<td>
			<input type="text" name="SERVICE" value="WMS" readonly="readonly" class="smallInput"></td>
			</tr>
			
			<tr>
			<td>REQUEST</td>
			<td>
			<input type="text" name="REQUEST" value="GetCapabilities" readonly="readonly" class="smallInput"></td>
			</tr>
			<tr><td></td><td></td></tr>
			<tr><td></td><td></td></tr>
			<tr>
			<td align="left">
			<input onclick="javascript:window.location.href=\'atlas.php?op=ATLAS_DEMO&atlas_aid=' . $aid . '\'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
			</td>
			<td align="right">
			<input type="hidden" name="aid" value="' . $aid . '">
			<input type="submit" name="submit" value="GetCapabilities" class="ui-button ui-state-default ui-corner-all">
			</td></form>
			</tr>
			</table>
			</td>
			<td width="35%" valign="top">
			<table class="block-panel ui-corner-all" style="width:100%;" align="top">
			<tr class="block-panel-header ui-corner-all">
			<td>Options</td></tr>
			<tr><td>
			Description:
			<p>Obtain service-level metadata, which is a machine-readable(and human-readable) description of the WMS\'s information content and acceptable request parameters.</p>
			</td></tr>
			</table>
			</td>
			</tr></table>';
		};
		break;
		case "wms_getmap" :
		{
			echo "<script type=\"text/javascript\">";
			echo "function chkform()";
			echo "{";
			echo "if(";
			echo '$("#table_list_srs [input:radio]:checked").length == 0';
			echo ")";
			echo "{";
			echo "growlError(\"Please select a Coordinate Reference System (CRS)!\");";
			echo "return false;";
			echo "}";
			echo "}";
			echo "</script>";
			
			print '<table class="tableNone">
			<tr class="title">
			<td colspan="2">'.$atlas['name'].'</td></tr>
			<tr>
			<td width="65%">
			
			<table class="block-panel ui-corner-all" style="width:100%;" align="top">
			<tr class="block-panel-header ui-corner-all">
			<td  colspan="2">
			GetMap
			</td>
			</tr>
			
			<tr>
			<td>' .
			'<table class="tableNone" id="table_list_srs">
			<form name="FormDemo" action="atlas.php" method="get" onSubmit="return chkform()">';
			
			$rs4 = $database->getRows4MetaGroupBy($aid, 'srs');
			$count = 0;
			while ($line4 = $database->getColumns($rs4)) {
				$num = $database->getRowsNumber($rs4);
				$data4 = $line4["srs"];
				
				print '<tr ';
				if($count%2==0){
					print 'class="odd"';
				}else{
					print 'class="even"';
				}
				print '>
				<td colspan="2"><input type="radio" name="SRS"  value="' . $data4 . '">' . $data4 . '</td>
				</tr>';
				$count++;
			}
			if (!$data4) {
				print '
				<tr>
				<td colspan="2"><p>These no SRS data, please check your database.</p></td>
				</tr>
				<tr>
				<td align="left">
				<input onclick="javascript:window.location.href=\'atlas.php?op=ATLAS_DEMO&atlas_aid=' . $aid . '\'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
				</td>
				<td align="right">
				</tr>';
			}
			else{
				print '<tr>
				<td align="left">
				<input onclick="javascript:window.location.href=\'atlas.php?op=ATLAS_DEMO&atlas_aid=' . $aid . '\'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
				</td>
				<td align="right">
				<input type="hidden" name="op" value="demo_wms_getmap_r">
				<input type="hidden" name="aid" value="' . $aid . '">
				<input type="submit" name="submit" value="Continue" class="ui-button ui-state-default ui-corner-all"></td>
				</tr>';
			}
			
			print '
			</td>
			</tr>
			</form>			
			</table>			
			</td></tr>
			</table>
			<td width="35%" valign="top">
			<table class="block-panel ui-corner-all" style="width:100%;" align="top">
			<tr class="block-panel-header ui-corner-all">
			<td>Options</td></tr>
			<tr><td>
			Description:
			<p>
			Obtain a map image whose geospatial and dimensional parameters are well-defined.
			</p>
			</td></tr>
			</table>
			</td>
			</table>';
			print_selectable_listtable_script('table_list_srs');		
		}
		break;
		case "wms_getmap_r" :
		{
			echo "<script type=\"text/javascript\">";
			echo "function chkform()";
			echo "{";
			echo "<!-- BBOX -->";
			echo ("\n");
			echo "if(document.form.minx.value == \"\")  {";
			echo ("\n");
			echo "growlError(\"Insert minx!\");";
			echo ("\n");
			echo "document.form.minx.focus();";
			echo ("\n");
			echo "return false;";
			echo ("\n");
			echo "}";
			echo ("\n");
			echo "if(document.form.miny.value == \"\") {";
			echo ("\n");
			echo "growlError(\"Insert miny!\");";
			echo ("\n");
			echo "document.form.miny.focus();";
			echo ("\n");
			echo "return false;";
			echo ("\n");
			echo "}";
			echo ("\n");
			
			echo "if(document.form.maxx.value == \"\") {";
			echo ("\n");
			echo "growlError(\"Insert maxx!\");";
			echo ("\n");
			echo "document.form.miny.focus();";
			echo ("\n");
			echo "return false;";
			echo ("\n");
			echo "}";
			echo ("\n");
			
			echo "if(document.form.maxy.value == \"\") {";
			echo ("\n");
			echo "growlError(\"Insert maxy!\");";
			echo ("\n");
			echo "document.form.miny.focus();";
			echo ("\n");
			echo "return false;";
			echo ("\n");
			echo "}";
			echo ("\n");
			
			echo "document.form.BBOX.value =document.form.minx.value + ',' + document.form.miny.value + ',' + document.form.maxx.value + ',' + document.form.maxy.value;";
			echo ("\n");
			
			echo "if(document.form.WIDTH.value == \"\") {";
			echo ("\n");
			echo "growlError(\"Insert WIDTH!\");";
			echo ("\n");
			echo "document.form.WIDTH.focus();";
			echo ("\n");
			echo "return false;";
			echo ("\n");
			echo "}";
			echo ("\n");
			
			echo "if(document.form.HEIGHT.value == \"\") {";
			echo ("\n");
			echo "growlError(\"Insert HEIGHT!\");";
			echo ("\n");
			echo "document.form.HEIGHT.focus();";
			echo ("\n");
			echo "return false;";
			echo "}";
						
			echo "if(";
			echo '$("#table_list_layers [input:checkbox]:checked").length == 0';
			echo ")";
			echo " { ";
			echo "growlError(\"please select a layer!\");";
			echo ("\n");
			echo "return false;";
			echo ("\n");
			echo "}";
			echo " var layerstr = \"\";";
			echo " var stylestr = \"\";";
			echo '$("#table_list_layers [input:checkbox]:checked").each(function(){
						if($(this).attr(\'name\')==\'layer\'){
							layerstr += $(this).val()+ ",";
							stylestr += document.form.style.value+ ",";
						}					
					});';
			echo "layerstr = (layerstr.charAt(layerstr.length-1) == \",\") ? layerstr.slice(0,layerstr.length-1) : layerstr;";
			echo "stylestr = (stylestr.charAt(stylestr.length-1) == \",\") ? stylestr.slice(0,stylestr.length-1) : stylestr;";
			echo "document.form.LAYERS.value = layerstr;";
			echo "document.form.STYLES.value = stylestr;";
			print 'document.form.action ="'.get_wms_interface($aid).'";';
			print 'document.form.target = "_blank";
			document.form.submit ();';
			echo "}";
			echo "</script>";
			echo ("\n");
?>
<table class="tableNone">
<tr class="title"><td colspan="2" width="100%"><?=$atlas['name']?></td></tr>
<tr>
	<td width="65%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
	<form name="form" action="<?=get_wms_interface($aid)?>" method="get">
		<tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			GetMap
		</td>
		</tr>
        <tr>
                      <td >VERSION</td>
                      <td>
                      <input type="text" name="VERSION" value="<?=SUAS_CFG_WMS_VERSION?>" readonly="readonly"  class="smallInput"></td>
              </tr>

          <tr>
             <td>SERVICE</td>
             <td>
             <input type="text" name="SERVICE" value="<?=SUAS_CFG_WMS_SERVICE?>" readonly="readonly" class="smallInput"></td>
          </tr>
                  <input type="hidden" name="BBOX" value="3444772">
                  <input type="hidden" name="LAYERS" value="LayerProblem">
                  <input type="hidden" name="STYLES" value="styleproblem">
              <tr>
                     <td >REQUEST</td>
                     <td>
                     <input type="text" name="REQUEST" value="GetMap" readonly="readonly"  class="smallInput"></td>
              </tr>
              <tr><td colspan="2">LAYERS: <td></tr>
             <tr align="left">
             <td></td>
			 <td>
            <table class="tableNone" id="table_list_layers">
            <tr align="left">
            <td>Select All?</td><td><input type="checkbox" name="chkall"  onclick="checkall(this.form, 'table_list_layers')"></td>
            </tr>
<?
$rs3 = $database->getRowsBySrsGroupBy($aid, $map_parameters['srs'], 'layer');
$count = 1;
$num = $database->getRowsNumber($rs3);
while ($line3 = $database->getColumns($rs3)) {
	$data3 = $line3["layer"];
	print '<tr ';
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}
?>
                 >
                     <td><?=$data3?></td><td> <input type="checkbox" name="layer" value="<?=$data3?>"></td>
                  </tr>
<?
++ $count;
}
?>
 </table>
      <td></tr>
          <tr>
                  <td >STYLES</td>
                  <td>
                  <input type="text" name="style" value="default" readonly="readonly"  class="smallInput"></td>
          </tr>
            <tr>
                <td >SRS SELECTED IS:</td>
                <td>
                <input type= "text" name="SRS" value="<?=$map_parameters['srs']?>" readonly="readonly" class="smallInput"></td>
            </tr>
<?
$rs4 = $database->getRowsMinMaxXYBySrs($aid, $map_parameters['srs']);
$line4 = $database->getColumns($rs4);
$totalminx = $line4[0];
$totalminy = $line4[1];
$totalmaxx = $line4[2];
$totalmaxy = $line4[3];
?>

              <tr>
                 <td  width="300">MIN. EASTING </td>
                 <td>
                 <input type="text" name="minx" value="<?=$totalminx?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td   width="300">MIN. NORTHING</td>
                 <td>
                 <input type="text" name="miny" value="<?=$totalminy?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td  width="300">MAX. EASTING </td>
                 <td>
                 <input type="text" name="maxx" value="<?=$totalmaxx?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td  width="300">MAX. NORTHING</td>
                 <td>
                 <input type="text" name="maxy" value="<?=$totalmaxy?>" class="smallInput"></td>
              </tr>


              <tr>
                 <td width="170">WIDTH</td>
                 <td>
                 <input type="text" name="WIDTH" value="800" class="smallInput"></td>
              </tr>

              <tr>
                 <td width="170">HEIGHT</td>
                 <td>
                 <input type="text" name="HEIGHT" value="600" class="smallInput"></td>
              </tr>

              <tr>
                 <td width="170">TRANSPARENT</td>
                 <td>
                 <SELECT name="TRANSPARENT">
				 <OPTION value="False">False</OPTION>
				 <OPTION value="True" selected>True</OPTION>
				 </SELECT>
              </tr>

              <tr>
                 <td width="170">FORMAT</td>
                 <td>
				<select name="FORMAT">
                    <optgroup label="Vector Image">
                       <option value="image/svg+xml" selected="selected">SVG</option>
                       <option value="image/svgt+xml">SVGT</option>
                       <option value="image/svgb+xml">SVGB</option>
                       <option value="image/svgz+xml">SVGZ</option>
                       <option value="image/svgtz+xml">SVGTZ</option>
                       <option value="image/svgbz+xml">SVGBZ</option>
                       <option value="application/pdf">PDF</option>
                       <option value="application/ezpdf">ezPDF</option>
                       <option value="application/x-shockwave-flash">SWF</option>
                       <option value="image/vml">VML</option>
                       <option value="application/vnd.google-earth.kml+xml">KML</option>
                       <option value="application/vnd.google-earth.kmz">KMZ</option>
                    </optgroup>
                    <optgroup label="Raster Image">
                       <option value="image/png">PNG</option>
                       <option value="image/jpeg">JPEG</option>
                       <option value="image/gif">GIF</option>
                       <option value="image/wbmp">WBMP</option>
                       <option value="image/bmp">BMP</option>
                       </optgroup>
<?
				//foreach($MAP_RENDER_FORMAT as $k => $v){
				//echo "<option value=\"".$v."\">".$k."</option>";
				//}
?>
        </select>
				 </td>
              </tr>
              <tr>
                 <td width="170">EXCEPTIONS</td>
                 <td>
				 <SELECT name="EXCEPTIONS">
				 <OPTION value=application/vnd.ogc.se_xml selected>application/vnd.ogc.se_xml</OPTION>
				 <OPTION value=application/vnd.ogc.se_inimage>application/vnd.ogc.se_inimage</OPTION>
				 </SELECT>
				 </td>
              </tr>

              <tr>
                 <td colspan="2">&nbsp;</td>
			</tr>

			<tr>
				<td align="left">
				<input type="button" onclick="javascript:window.location.href='atlas.php?op=demo_wms_GetMap&aid=<?=$aid?>&atlas_aid=<?=$aid?>'" name="button" value="Back" class="ui-button ui-state-default ui-corner-all">
		 		</td>
				<td align="right">
				<input type="hidden" name="aid" value="<?=$aid?>">
				<input type="hidden" name="op" value="op">
				<input onclick="chkform();" name="button" value="GetMap" type="button" class="ui-button ui-state-default ui-corner-all">
				</td>
			</tr>
        </form>
        </table>
        <td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td>Options</td>
		</tr>
		<tr>
		<td>
		Description:
		<p>
		
		</p>
		</td>
		</tr>
	</table>
</td>
</tr>
</table>

<?
print_selectable_listtable_script('table_list_layers', true);

}
break;
				case "wms_getfeatureinfo" :
				{
					echo "<script type=\"text/javascript\">";
					echo "function chkform()";
					echo "{";
					echo "if(";
					echo '$("#table_list_srs [input:radio]:checked").length == 0';
					echo ")";
					echo "{";
					echo "growlError(\"Please select a coordinate reference system (CRS)!\");";
					echo ("\n");
					echo "return false;";
					echo "}";
					echo "}";
					echo "</script>";
					echo ("\n");
					?>
    <table class="tableNone">
	<tr class="title"><td colspan="2"><?=$atlas['name']?></td></tr>
	<tr>
		<td width="65%"  valign="top">
			<table class="block-panel ui-corner-all" style="width:100%;" align="top">
				<tr class="block-panel-header ui-corner-all">
				<td colspan="2">
				GetFeatureInfo
				</td>
				</tr>
				<tr>
				<td  colspan="2">
					<table class="tableNone" id="table_list_srs">
					<form name="FormDemo" action="atlas.php" method="get" onSubmit="return chkform()">
<?
$rs4 = $database->getRows4MetaGroupBy($aid, 'srs');
$count = 0;
while ($line4 = $database->getColumns($rs4)) {
	$data4 = $line4["srs"];
	print '<tr ';
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}
	print '>
	<td colspan="2"><input type="radio" name="SRS" value="' . $data4 . '"> ' . $data4 . '</td>
	</tr>';
	$count++;
}
if (!$data4) {
	echo '<tr>
	<td colspan="2"><p>These no SRS data, please check your database.</p></td>
	</tr>';
	print '		<tr>
	<td align="left">
	<input onclick="javascript:window.location.href=\'atlas.php?op=ATLAS_DEMO&atlas_aid=' . $aid . '\'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
	</td>
	<td align="right">
	</tr>';
} else {
	echo "
	<tr>
	<td align=\"left\"><input onclick=\"javascript:window.location.href='atlas.php?op=ATLAS_DEMO&atlas_aid=" . $aid . "'\" name=\"button\" value=\"Back\" type=\"button\" class=\"ui-button ui-state-default ui-corner-all\">
	</td>
	<td align=\"right\">" .
	"<input type=\"hidden\" name=\"aid\" value=\"$aid\">" .
	"<input type=\"hidden\" name=\"op\" value=\"demo_wms_getfeatureinfo_r\">" .
	"<input type=\"submit\" name=\"submit\" value=\"Continue\" class=\"ui-button ui-state-default ui-corner-all\"></td>
	</tr>";
}
print '</form></table>
</td></tr></table>

</td>
<td width="35%" valign="top">
<table class="block-panel ui-corner-all" style="width:100%;" align="top">
<tr class="block-panel-header ui-corner-all">
<td>Options</td>
</tr>
<tr>
<td>
Description:
<p>
Ask for information about particular features shown on a map.
</p>
</td>
</tr>
</table>
</td>
</tr>
</table>';
print_selectable_listtable_script('table_list_srs');
}
break;
	case "wms_getfeatureinfo_r" :
	{
		echo "<script type=\"text/javascript\">";
		echo ("\n");
		echo "function chkform()";
		echo ("\n");
		echo "{";
		echo ("\n");
		
		echo "<!-- BBOX -->";
		echo ("\n");
		echo "if(document.form.minx.value == \"\")  {";
		echo ("\n");
		echo "growlError(\"Insert minx!\");";
		echo ("\n");
		echo "document.form.minx.focus();";
		echo ("\n");
		echo "return false;";
		echo ("\n");
		echo "}";
		echo ("\n");
		echo "if(document.form.miny.value == \"\") {";
		echo ("\n");
		echo "growlError(\"Insert miny!\");";
		echo ("\n");
		echo "document.form.miny.focus();";
		echo ("\n");
		echo "return false;";
		echo ("\n");
		echo "}";
		echo ("\n");
		echo "if(document.form.maxx.value == \"\") {";
		echo ("\n");
		echo "growlError(\"Insert maxx!\");";
		echo ("\n");
		echo "document.form.miny.focus();";
		echo ("\n");
		echo "return false;";
		echo ("\n");
		echo "}";
		echo ("\n");
		echo "if(document.form.maxy.value == \"\") {";
		echo ("\n");
		echo "growlError(\"Insert maxy!\");";
		echo ("\n");
		echo "document.form.miny.focus();";
		echo ("\n");
		echo "return false;";
		echo ("\n");
		echo "}";
		echo ("\n");
		
		echo "if(document.form.RADIUS.value == \"\") {";
		echo ("\n");
		echo "growlError(\"Insert RADIUS!\");";
		echo ("\n");
		echo "document.form.RADIUS.focus();";
		echo ("\n");
		echo "return false;";
		echo ("\n");
		echo "}";
		echo ("\n");
		
		echo "if(document.form.X.value == \"\") {";
		echo ("\n");
		echo "growlError(\"Insert X!\");";
		echo ("\n");
		echo "document.form.X.focus();";
		echo ("\n");
		echo "return false;";
		echo ("\n");
		echo "}";
		echo ("\n");
		
		echo "if(document.form.Y.value == \"\") {";
		echo ("\n");
		echo "growlError(\"Insert Y!\");";
		echo ("\n");
		echo "document.form.Y.focus();";
		echo ("\n");
		echo "return false;";
		echo ("\n");
		echo "}";
		echo ("\n");
		
		echo "document.form.BBOX.value =document.form.minx.value + ',' + document.form.miny.value + ',' + document.form.maxx.value + ',' + document.form.maxy.value;";

		echo "if(";
		echo '$("#table_list_layers [input:checkbox]:checked").length == 0';
		echo ")";
		echo " { ";
		echo "growlError(\"please select a query layer!\");";
		echo "return false;";
		echo "}";
		echo " var layerstr = \"\";";
		echo '$("#table_list_layers [input:checkbox]:checked").each(function(){
						if($(this).attr(\'name\')==\'layer\')
						layerstr += $(this).val()+ ",";						
					});';
		echo "layerstr = (layerstr.charAt(layerstr.length-1) == \",\") ? layerstr.slice(0,layerstr.length-1) : layerstr;";
		echo "document.form.QUERY_LAYERS.value = layerstr;";
		echo "}";
		echo "</script>";
		echo ("\n");
?>
<table class="tableNone">
<tr class="title"><td colspan="2" width="100%"><?=$atlas['name']?></td></tr>
<tr>
<td width="65%" valign="top">
<table class="block-panel ui-corner-all" style="width:100%;" align="top">
	<form name="form" action="<?=get_wms_interface($aid)?>" method="get" onSubmit="return chkform()" target="_blank">
		<tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			GetFeatureinfo
		</td>
		</tr>
        <tr>
            <td >VERSION</td>
            <td>
            	<input type="text" name="VERSION" value="<?=SUAS_CFG_WMS_VERSION?>" readonly="readonly"  class="smallInput">
            </td>
		</tr>
                  <input type="hidden" name="BBOX" value="3444772">
                  <input type="hidden" name="QUERY_LAYERS" value="LayerProblem">
              <tr>
                     <td >SERVICE</td>
                     <td>
                     <input type="text" name="SERVICE" value="<?=SUAS_CFG_WMS_SERVICE?>" readonly="readonly"  class="smallInput"></td>
              </tr>
              <tr>
                     <td >REQUEST</td>
                     <td>
                     <input type="text" name="REQUEST" value="GetFeatureInfo" readonly="readonly"  class="smallInput"></td>
              </tr>
              <tr><td colspan="2">QUERY_LAYERS: <td></tr>
         <tr align="left">
             <td></td>
			 <td>
            <table class="tableNone" id="table_list_layers">
            <td>Select All?</td><td><input type="checkbox" name="chkall" onclick="checkall(this.form, 'table_list_layers')"></td>
<?
$rs3 = $database->getRowsBySrsGroupBy($aid, $map_parameters['srs'], 'layer');
$count = 1;
$num = $database->getRowsNumber($rs3);
while ($line3 = $database->getColumns($rs3)) {
	$data3 = $line3["layer"];
	print '<tr ';
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}
?>
 				>
                     <td><?=$data3?></td><td> <input type="checkbox" name="layer" value="<?=$data3?>"></td>
                  </tr>
<?
++ $count;
}
?>
         </table>
      <td>
      </tr>
      <tr>
                  <td>RADIUS(pixel)</td>
                  <td>
                  <input type="text" name="RADIUS" value="2"  class="smallInput"></td>
          </tr>
            <tr>
                <td>SRS SELECTED IS:</td>
                <td>
                <input type= "text" name="SRS" value="<?=$map_parameters['srs']?>" readonly="readonly" class="smallInput">
                </td>
            </tr>
<?
$rs4 = $database->getRowsMinMaxXYBySrs($aid, $map_parameters['srs']);
$line4 = $database->getColumns($rs4);
$totalminx = $line4[0];
$totalminy = $line4[1];
$totalmaxx = $line4[2];
$totalmaxy = $line4[3];
?>
              <tr>
                 <td>MIN. EASTING </td>
                 <td>
                 <input type="text" name="minx" value="<?=$totalminx?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td>MIN. NORTHING</td>
                 <td>
                 <input type="text" name="miny" value="<?=$totalminy?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td>MAX. EASTING</td>
                 <td>
                 <input type="text" name="maxx" value="<?=$totalmaxx?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td>MAX. NORTHING</td>
                 <td>
                 <input type="text" name="maxy" value="<?=$totalmaxy?>" class="smallInput"></td>
              </tr>
              <tr>
                 <td>WIDTH</td>
                 <td>
                 <input type="text" name="WIDTH" value="800" class="smallInput"></td>
              </tr>

              <tr>
                 <td>HEIGHT</td>
                 <td>
                 <input type="text" name="HEIGHT" value="600" class="smallInput"></td>
              </tr>

             <tr>
                 <td>X</td>
                 <td>
                 <input type="text" name="X" value="400" class="smallInput"></td>
              </tr>

              <tr>
                 <td>Y</td>
                 <td>
                 <input type="text" name="Y" value="400" class="smallInput"></td>
              </tr>

              <tr>
                 <td>INFO_FORMAT</td>
                 <td>
				 <SELECT name="INFO_FORMAT">
				 <OPTION value=text/xml selected>text/xml</OPTION>
				 <OPTION value=text/html>text/html</OPTION>
				 </SELECT>
				 </td>
              </tr>

              <tr>
                 <td colspan="2">&nbsp;</td>
              </tr>

              <tr>
                 <td>
                 <input onclick="javascript:window.location.href='atlas.php?op=demo_wms_getfeatureinfo&aid=<?=$aid?>&atlas_aid=<?=$aid?>'" name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
		 		</td>
				<td align="right">
				<input type="hidden" name="aid" value="<?=$aid?>">
				<input type="hidden" name="op" value="demo_wms_getfeatureinfo">
                <input type="submit" name="submit" value="GetFeatureInfo" class="ui-button ui-state-default ui-corner-all">
                </td>
              </tr>
     </form>
</table>
</td>
<td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td>Options</td>
		</tr>
		<tr>
		<td>
		Description:
		<p>
		
		</p>
		</td>
		</tr>
	</table>
</td>
</tr>
</table>

<?
print_selectable_listtable_script('table_list_layers', true);
}
break;
				case "wms_describelayer" :
				{
					echo "<script type=\"text/javascript\">";
					echo "function chkform()";
					echo "{";
					echo "if(";
					echo '$("#table_list_layers [input:checkbox]:checked").length == 0';
					echo ")";
					echo "{";
					echo "growlError(\"please select a layer!\");";
					echo "return false;";
					echo "}";
					echo " var layerstr = \"\";";
					echo '$("#table_list_layers [input:checkbox]:checked").each(function(){
						if($(this).attr(\'name\')==\'layer\')
						layerstr += $(this).val()+ ",";						
					});';				
					echo "layerstr = (layerstr.charAt(layerstr.length-1) == \",\") ? layerstr.slice(0,layerstr.length-1) : layerstr;";
					echo "document.form.LAYERS.value = layerstr;";
					echo "}";
					echo "</script>";
					echo "\n";
					?>
     <table class="tableNone">
		<tr class="title"><td colspan="2" width="100%"><?=$atlas['name']?></td></tr>
        <tr>
          <td width="65%">
			<table class="block-panel ui-corner-all" style="width:100%;" align="top">
			<form name="form" action="<?=get_wms_interface($aid)?>" method="get" onSubmit="return chkform()" target="_blank">
				<tr class="block-panel-header ui-corner-all"><td colspan="2">DescribeLayer</td></tr>
				<tr>
                      <td>VERSION</td>
                      <td>
                      <input type="text" name="VERSION" value="<?=SUAS_CFG_WMS_VERSION?>" readonly="readonly"  class="smallInput">
                      </td>
				</tr>
          		<tr>
				<td>SERVICE</td>
				<td>
             	<input type="text" name="SERVICE" value="<?=SUAS_CFG_WMS_SERVICE?>" readonly="readonly" class="smallInput"></td>
          		</tr>
                  <input type="hidden" name="BBOX" value="3444772">
                  <input type="hidden" name="LAYERS" value="LayerProblem">
              <tr>
                     <td>REQUEST</td>
                     <td>
                     <input type="text" name="REQUEST" value="DescribeLayer" readonly="readonly"  class="smallInput"></td>
              </tr>
              <tr><td colspan="2">LAYERS: <td></tr>
             <tr align="left">
             <td></td>
			 <td>
            <table class="tableNone" id="table_list_layers">
            <td>Select All?</td><td><input type="checkbox" name="chkall" onclick="checkall(this.form, 'table_list_layers')"></td>
<?

$rs3 = $database->getRows4MetaGroupBy($aid, 'layer');
$count = 1;
$num = $database->getRowsNumber($rs3);
while ($line3 = $database->getColumns($rs3)) {
	$data3 = $line3["layer"];
	
	print '<tr align="left" ';
	
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}
	print '>';
	?>                
                
                     <td><?=$data3?></td><td> <input type="checkbox" name="layer" value="<?=$data3?>"></td>
                  </tr>
<?

++ $count;
}
?>
         </table>
      <td>
	  </tr>
              <tr>
                 <td colspan="2">&nbsp;</td>
              </tr>

              <tr>
                 <td><input  onclick="javascript:window.location.href='atlas.php?op=ATLAS_DEMO&atlas_aid=<?=$aid?>'" name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
				</td>
                 <td align="right">
                 <input type="hidden" name="aid" value="<?=$aid?>">
                 <input type="submit" name="submit" value="DescribeLayer" class="ui-button ui-state-default ui-corner-all">
                 </td>
              </tr>
        </form>
		</table>
      
          </td>        
          <td width="35%" valign="top">
				<table class="block-panel ui-corner-all" style="width:100%;" align="top">
					<tr class="block-panel-header ui-corner-all">
					<td>Options</td>
					</tr>
					<tr>
					<td>
					Description:
					<p>
					
					</p>
					</td>
					</tr>
				</table>
		</td>
		</tr>
	</table>
      
<?
print_selectable_listtable_script('table_list_layers', true);
}
break;
                 case "wms_getlegendgraphic" :
                 {                 
					echo "<script type=\"text/javascript\">";
					echo "function chkform()";
					echo "{";
	                 
					echo "if(document.form.WIDTH.value == \"\") {";
					echo "growlError(\"Insert WIDTH!\");";
					echo "document.form.WIDTH.focus();";
					echo "return false;";
					echo "}";
					
					echo "if(document.form.HEIGHT.value == \"\") {";
					echo "growlError(\"Insert HEIGHT!\");";
					echo "document.form.HEIGHT.focus();";
					echo "return false;";
					echo "}";
					
					echo "if(";
					echo '$("#table_list_layers [input:radio]:checked").length == 0';
					echo ")";
					echo " { ";
					echo "growlError(\"please select a layer!\");";
					echo "return false;";
					echo "}";
					echo "}";
					echo "</script>";
					echo ("\n");
					?>
<table class="tableNone">
	<tr class="title"><td colspan="2" width="100%"><?=$atlas['name']?></td></tr>
	<tr>
		<td width="65%">         
			<table class="block-panel ui-corner-all" style="width:100%;" align="top">
			<form name="form" action="<?=get_wms_interface($aid)?>" method="get" onSubmit="return chkform()" target="_blank">
			<tr class="block-panel-header ui-corner-all">
			<td colspan="2">
				GetLegendGraphic
			</td>
			</tr>
			<tr>
				<td>VERSION</td>
				<td>
				<input type="text" name="VERSION" value="<?=SUAS_CFG_WMS_VERSION?>" readonly="readonly"  class="smallInput"></td>
			</tr>
			<tr>
				<td>SERVICE</td>
				<td>
             	<input type="text" name="SERVICE" value="<?=SUAS_CFG_WMS_SERVICE?>" readonly="readonly" class="smallInput">
             	<input type="hidden" name="BBOX" value="0">
             	</td>
			</tr>                 
			<tr>
				<td>REQUEST</td>
				<td>
				<input type="text" name="REQUEST" value="GetLegendGraphic" readonly="readonly"  class="smallInput">
				</td>
			</tr>
			<tr><td colspan="2">LAYERS: <td></tr>
			<tr>
				<td></td>
				<td>
				<table class="tableNone" id="table_list_layers">
<?php

$rs3 = $database->getRows4MetaGroupBy($aid, 'layer');
$count = 1;
$num = $database->getRowsNumber($rs3);
while ($line3 = $database->getColumns($rs3)) {
	$data3 = $line3["layer"];
	
     print '<tr ';
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}                  
?>                  
					>
                     <td><?=$data3?></td><td> <input type="radio" name="LAYERS" value="<?=$data3?>"></td>
                  </tr>
<?
++ $count;
}
?>
				</table>
				</td>
			</tr>
			<tr>
                 <td width="170">WIDTH</td>
                 <td>
                 <input type="text" name="WIDTH" value="<?=$params['GetLegendGraphicWidth']?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td width="170">HEIGHT</td>
                 <td>
                 <input type="text" name="HEIGHT" value="<?=$params['GetLegendGraphicHeight']?>" class="smallInput"></td>
              </tr>
              <tr>
                 <td width="170">FORMAT</td>
                 <td>
				 <select name="FORMAT">
                    <optgroup label="Vector Image">
                       <!--<option value="image/svg+xml" selected="selected">SVG</option>
                       <option value="image/svgt+xml">SVGT</option>
                       <option value="image/svgb+xml">SVGB</option>
                       <option value="image/vml">VML</option>-->
                    </optgroup>
                    <optgroup label="Raster Image">
                       <option value="image/png">PNG</option>
                       <option value="image/jpeg">JPEG</option>
                       <option value="image/gif">GIF</option>
                       <option value="image/wbmp">WBMP</option>
                       <option value="image/bmp">BMP</option>
                       </optgroup>
                 </select>
				 </td>
              </tr>
              <tr>
                 <td colspan="2">&nbsp;</td>
              </tr>

              <tr>
                 <td><input onclick="javascript:window.location.href='atlas.php?op=ATLAS_DEMO&atlas_aid=<?=$aid?>'"  name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
                 </td>
                 <td align="right">
                 <input type="hidden" name="aid" value="<?=$aid?>">
                 <input type="submit" name="submit" value="GetLegendGraphic" class="ui-button ui-state-default ui-corner-all">
                 </td>
              </tr>
        </form>
        </table>
          
		</td>
		<td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td>Options</td>
		</tr>
		<tr>
		<td>
		Description:
		<p>
		
		</p>
		</td>
		</tr>
	</table>
</td>
	</tr>
	</table>
<?
print_selectable_listtable_script('table_list_layers');

}
break;
                 case "wms_getstyles" :
                 {
					echo "<script type=\"text/javascript\">";
					echo "function chkform()";
					echo "{";
	
					echo "if(";
					echo '$("#table_list_layers [input:checkbox]:checked").length == 0';
					echo ")";
					echo " { ";
					echo "growlError(\"please select a layer!\");";
					echo "return false;";
					echo "}";
					echo " var layerstr = \"\";";
					echo '$("#table_list_layers [input:checkbox]:checked").each(function(){
						if($(this).attr(\'name\')==\'layer\')
						layerstr += $(this).val()+ ",";				
					});';

					echo "layerstr = (layerstr.charAt(layerstr.length-1) == \",\") ? layerstr.slice(0,layerstr.length-1) : layerstr;";
					echo "document.form.LAYERS.value = layerstr;";	
					echo "}";
					echo "</script>";
					echo("\n");
					?>
<table class="tableNone">
<tr class="title"><td colspan="2"><?=$atlas['name']?></td></tr>
<tr>
	<td width="65%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
      <form name="form" action="<?=get_wms_interface($aid)?>" method="get" onSubmit="return chkform()" target="_blank">
	      	<tr class="block-panel-header ui-corner-all">
			<td colspan="2">
				GetStyles
			</td>
			</tr>
			<tr>
				<td>VERSION</td>
				<td>
                <input type="text" name="VERSION" value="<?=SUAS_CFG_WMS_VERSION?>" readonly="readonly"  class="smallInput"></td>
			</tr>
			<tr>
             <td>SERVICE</td>
             <td>
             <input type="text" name="SERVICE" value="<?=SUAS_CFG_WMS_SERVICE?>" readonly="readonly" class="smallInput"></td>
          </tr>
                  <input type="hidden" name="BBOX" value="3444772">
                  <input type="hidden" name="LAYERS" value="LayerProblem">
              <tr>
                     <td>REQUEST</td>
                     <td>
                     <input type="text" name="REQUEST" value="GetStyles" readonly="readonly"  class="smallInput"></td>
              </tr>
              <tr><td colspan="2">LAYERS: <td></tr>
             <tr align="left">
             <td></td>
			 <td>
            <table class="tableNone" id="table_list_layers">
            <td>Select All?</td><td><input type="checkbox" name="chkall" onclick="checkall(this.form, 'table_list_layers')"></td>
<?
$rs3 = $database->getRows4MetaGroupBy($aid,'layer');
$count = 1;
$num = $database->getRowsNumber($rs3);
while ($line3 = $database->getColumns($rs3)) {
	$data3 = $line3["layer"];
	print '<tr ';
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}
?>
                >
                     <td><?=$data3?></td><td> <input type="checkbox" name="layer" value="<?=$data3?>"></td>
                  </tr>
<?
++$count;
}
?>
         </table>
      <td>
	  </tr>
              <tr>
                 <td colspan="2">&nbsp;</td>
              </tr>

              <tr>
                 <td>
                 <input onclick="javascript:window.location.href='atlas.php?op=ATLAS_DEMO&atlas_aid=<?=$aid?>'" name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
                 </td>
                 <td align="right">
                 <input type="hidden" name="aid" value="<?=$aid?>">
                 <input type="submit" name="submit" value="GetStyles" class="ui-button ui-state-default ui-corner-all">
                 </td>
              </tr>
        </form>
		</table>           
	</td>
	<td width="35%" valign="top">
			<table class="block-panel ui-corner-all" style="width:100%;" align="top">
				<tr class="block-panel-header ui-corner-all">
				<td>Options</td>
				</tr>
				<tr>
				<td>
				Description:
				<p>
				
				</p>
				</td>
				</tr>
			</table>
	</td>
</tr>	
</table>
<?
print_selectable_listtable_script('table_list_layers', true);
}
break;

case "wmsextension_getmapviewers":{
	atlas_get_mapview_viewer_form($database, $aid, $atlas, 'demo', "");
}
break;

case "wmsextension_getmapviewers_r":{
	atlas_get_mapview_form($database, $aid, $atlas, $map_parameters, 'demo', "");	
}
print_selectable_listtable_script('table_list_layers', true);
break;
                 case "wmsextension_getmap25d" :
                 {
					echo "<script type=\"text/javascript\">";
					echo("\n");
					echo "function chkform()";
					echo "{";
					echo "if(";
					echo '$("#table_list_srs [input:radio]:checked").length == 0';
					echo ")";
					echo("\n");
					echo "{";
					echo "growlError(\"Please select a coordinate reference system!\");";
					echo("\n");
					echo "return false;";
					echo "}";
					echo "}";
					echo "</script>";
					echo("\n");					
					?>
<table class="tableNone">
<tr class="title"><td colspan="2"><?=$atlas['name']?></td></tr>
<tr>
	<td width="65%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top" id="table_list_srs">
		<tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			GetMap25D
		</td>
		</tr>
	<form name="form" action="atlas.php" method="get" onSubmit="return chkform()">

<?
$rs4 = $database->getRows4MetaGroupBy($aid, 'srs');
$count = 0;
while ($line4 = $database->getColumns($rs4)) {
	$num = $database->getRowsNumber($rs4);
	$data4 = $line4["srs"];
	print '<tr ';
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}
	print '>';
?>	    
			         <td colspan="2"><input type="radio" name="SRS" value="<?=$data4?>"><?=$data4?></td>
				    </tr>
<?
$count++;
}
if (!$data4) {
	
	?>
                    <tr>
					 <td colspan="2"><p>These no SRS data, please check your database.</p></td>
					</tr>
<?
print '		<tr>
<td align="left">
<input onclick="javascript:window.location.href=\'atlas.php?op=ATLAS_DEMO&atlas_aid=' . $aid . '\'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
</td>
<td align="right">
</tr>';
}
if ($data4) {
	
	?>
					<tr>
					<td>
                 <input onclick="javascript:window.location.href='atlas.php?op=ATLAS_DEMO&atlas_aid=<?=$aid?>'" name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
                 </td>
				 	 <td align="right">
				 	 <input type="hidden" name="op" value="demo_wmsextension_GetMap25D_r">
					 <input type="hidden" name="aid" value="<?=$aid?>">
				 	 <input type="submit" name="submit" value="Continue" class="ui-button ui-state-default ui-corner-all">
				 	 </td>
					</tr>
<?php
}
?>
					</form>
			</table>
	
	</td>
<td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td>Options</td>
		</tr>
		<tr>
		<td>
		Description:
		<p>
		
		</p>
		</td>
		</tr>
	</table>
</td>	
	
</tr>
</table>

<?php
print_selectable_listtable_script('table_list_srs');
}
break;
case "wmsextension_getmap25d_r" :
{
	print '<script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/colorpicker/js/colorpicker.js" defer="defer"></script>
	<link rel="stylesheet" href="../cssjs/lib/jquery/js/plugin/colorpicker/css/colorpicker.css" type="text/css" />';
	echo "<script type=\"text/javascript\">";
	echo("\n");
	echo "function chkform()";
	echo "{";
	echo "if(document.form.minx.value == \"\")  {";
	echo "growlError(\"Insert minx!\");";
	echo "document.form.minx.focus();";
	echo "return false;";
	echo "}";
	echo "if(document.form.miny.value == \"\") {";
	echo "growlError(\"Insert miny!\");";
	echo "document.form.miny.focus();";
	echo "return false;";
	echo "}";
	
	echo "if(document.form.maxx.value == \"\") {";
	echo "growlError(\"Insert maxx!\");";
	echo "document.form.miny.focus();";
	echo "return false;";
	echo "}";
	
	echo "if(document.form.maxy.value == \"\") {";
	echo "growlError(\"Insert maxy!\");";
	echo "document.form.miny.focus();";
	echo "return false;";
	echo "}";
	
	echo "document.form.BBOX.value =document.form.minx.value + ',' + document.form.miny.value + ',' + document.form.maxx.value + ',' + document.form.maxy.value;";
	
	echo "if(document.form.WIDTH.value == \"\") {";
	echo "growlError(\"Insert WIDTH!\");";
	echo "document.form.WIDTH.focus();";
	echo "return false;";
	echo "}";
	
	echo "if(document.form.HEIGHT.value == \"\") {";
	echo "growlError(\"Insert HEIGHT!\");";
	echo "document.form.HEIGHT.focus();";
	echo "return false;";
	echo "}";
	echo "if(";
	echo '$("#table_list_layers [input:checkbox]:checked").length == 0';
	echo ")";
	echo "{";
	echo "growlError(\"please select a layer!\");";
	echo "return false;";
	echo "}";
	echo " var layerstr = \"\";";
	echo " var stylestr = \"\";";
	echo '$("#table_list_layers [input:checkbox]:checked").each(function(){
						if($(this).attr(\'name\')==\'layer\'){
						layerstr += $(this).val()+ ",";
						stylestr += document.form.style.value+ ",";
						}					
					});';	
	echo "layerstr = (layerstr.charAt(layerstr.length-1) == \",\") ? layerstr.slice(0,layerstr.length-1) : layerstr;";
	echo "stylestr = (stylestr.charAt(stylestr.length-1) == \",\") ? stylestr.slice(0,stylestr.length-1) : stylestr;";
	echo "document.form.LAYERS.value = layerstr;";
	echo "document.form.STYLES.value = stylestr;";
	print 'document.form.action ="'.get_wms_interface($aid).'";' .
	'document.form.target = "_blank";
	document.form.submit ();';
	echo "}";
	
	echo "</script>";
	echo("\n");
	?>
<table class="tableNone">
<tr class="title"><td colspan="2"><?=$atlas['name']?></td></tr>
<tr>
    <td width="65%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			GetMap25D
		</td>
	</tr>
	<form name="form" action="<?=get_wms_interface($aid)?>" method="get" targe="_blank">
              <tr>
                      <td>VERSION</td>
                      <td>
                      <input type="text" name="VERSION" value="<?=SUAS_CFG_WMS_VERSION?>" readonly="readonly"  class="smallInput"></td>
              </tr>

          <tr>
             <td>SERVICE</td>
             <td>
             <input type="text" name="SERVICE" value="<?=SUAS_CFG_WMS_SERVICE?>" readonly="readonly" class="smallInput"></td>
          </tr>
                  <input type="hidden" name="BBOX" value="3444772">
                  <input type="hidden" name="LAYERS" value="LayerProblem">
                  <input type="hidden" name="STYLES" value="styleproblem">
              <tr>
                     <td>REQUEST</td>
                     <td>
                     <input type="text" name="REQUEST" value="GetMap25D" readonly="readonly"  class="smallInput"></td>
              </tr>
              <tr><td colspan="2">LAYERS: <td></tr>
             <tr align="left">
             <td></td>
			 <td>
            <table class="tableNone" id="table_list_layers">
            <td>Select All?</td><td><input type="checkbox" name="chkall"  onclick="checkall(this.form, 'table_list_layers')"></td>
<?php
$rs3 = $database->getRowsBySrsGroupBy($aid, $map_parameters['srs'], 'layer');
$count = 1;
$num = $database->getRowsNumber($rs3);
while ($line3 = $database->getColumns($rs3)) {
	$data3 = $line3["layer"];
	print '<tr ';
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}
	print '>';
?>         
                     <td><?=$data3?></td><td> <input type="checkbox" name="layer" value="<?=$data3?>"></td>
                  </tr>
<?php
++$count;
}
?>
     </table>
      <td></tr>
          <tr>
                  <td>STYLES</td>
                  <td>
                  <input type="text" name="style" value="default" readonly="readonly"  class="smallInput"></td>
          </tr>
            <tr>
                <td>SRS SELECTED IS:</td>
                <td>
                <input type= "text" name="SRS" value="<?=$map_parameters['srs']?>" readonly="readonly" class="smallInput"></td>
            </tr>


<?php
$rs4 = $database->getRowsMinMaxXYBySrs($aid, $map_parameters['srs']);
$line4 = $database->getColumns($rs4);
$totalminx = $line4[0];
$totalminy = $line4[1];
$totalmaxx = $line4[2];
$totalmaxy = $line4[3];
?>
              <tr>
                 <td>MIN. EASTING </td>
                 <td>
                 <input type="text" name="minx" value="<?=$totalminx?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td>MIN. NORTHING</td>
                 <td>
                 <input type="text" name="miny" value="<?=$totalminy?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td>MAX. EASTING</td>
                 <td>
                 <input type="text" name="maxx" value="<?=$totalmaxx?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td>MAX. NORTHING</td>
                 <td>
                 <input type="text" name="maxy" value="<?=$totalmaxy?>" class="smallInput"></td>
              </tr>


              <tr>
                 <td>WIDTH</td>
                 <td>
                 <input type="text" name="WIDTH" value="800" class="smallInput"></td>
              </tr>

              <tr>
                 <td>HEIGHT</td>
                 <td>
                 <input type="text" name="HEIGHT" value="600" class="smallInput"></td>
              </tr>

              <tr>
                 <td>TRANSPARENT</td>
                 <td>
                 <SELECT name="TRANSPARENT">
				 <OPTION value=False selected>False</OPTION>
				 <OPTION value=True>True</OPTION>
				 </SELECT>
              </tr>

              <tr>
                 <td>FORMAT</td>
                 <td>
				 <select name="FORMAT">
                    <optgroup label="Vector Image">
                       <option value="image/svg+xml">SVG</option>
                       <option value="image/svgt+xml">SVGT</option>
                       <option value="image/svgb+xml">SVGB</option>
                       <option value="image/svgz+xml">SVGZ</option>
                       <option value="image/svgtz+xml">SVGTZ</option>
                       <option value="image/svgbz+xml">SVGBZ</option>
                       <!--
                       <option value="image/pdf">PDF</option>
                       <option value="image/ezpdf">ezPDF</option>
                       <option value="image/swf">SWF</option>
                       <option value="image/vml">VML</option>
                       <option value="image/vrml">VRML</option>
                       -->
                    </optgroup>
                    <optgroup label="Raster Image">
                       <option value="image/png" selected>PNG</option>
                       <option value="image/jpeg">JPEG</option>
                       <option value="image/gif">GIF</option>
                       <option value="image/wbmp">WBMP</option>
                       <option value="image/bmp">BMP</option>
                       </optgroup>
                 </select>
				 </td>
              </tr>

              <tr>
                 <td>EXCEPTIONS</td>
                 <td>
				 <SELECT name="EXCEPTIONS">
				 <OPTION value=application/vnd.ogc.se_xml selected>application/vnd.ogc.se_xml</OPTION>
				 <OPTION value=application/vnd.ogc.se_inimage>application/vnd.ogc.se_inimage</OPTION>
				 </SELECT>
				 </td>
              </tr>
              <tr>
                 <td>BGCOLOR</td>
                 <td>
				 <input name="BGCOLOR" id="BGCOLOR" type="text" maxlength="7"
				 style="background-color: #FFFFFF;color: #000000"
				 size="10">
				 </td>
              </tr>
              <tr>
                 <td>SKYCOLOR</td>
                 <td>
				 <select id="idSKYCOLOR" name="SKYCOLOR">
                                 <option value="">None</option>
				 <option style="background-color: #FFFFFF;" value="FFFFFF">FFFFFF</option>
				 <option style="background-color: #D6D7FF;" value="D6D7FF">D6D7FF</option>
				 <option style="background-color: #CECFFF;" value="CECFFF">CECFFF</option>
				 <option style="background-color: #B5AEFF;" value="B5AEFF">B5AEFF</option>
				 <option style="background-color: #A59AFF;" value="A59AFF">A59AFF</option>
				 <option style="background-color: #8475FF;" value="8475FF">8475FF</option>
				 <option style="background-color: #7262FF;" value="7262FF">7262FF</option>
				 <option style="background-color: #6453FF;" value="6453FF">6453FF</option>
				 <option style="background-color: #5846FF;" value="5846FF">5846FF</option>
				 <option style="background-color: #513EFF;" value="513EFF">513EFF</option>
				 <option style="background-color: #3620FF;" value="3620FF">3620FF</option>
				 <option style="background-color: #1F06FF;" value="1F06FF">1F06FF</option>
				 <option style="background-color: #1700EC;" value="1700EC">1700EC</option>
				 <option style="background-color: #1500D5;" value="1500D5">1500D5</option>
				 <option style="background-color: #1200B7;" value="1200B7" selected>1200B7</option>
				 <option style="background-color: #0F0097;" value="0F0097">0F0097</option>
				 <option style="background-color: #0E0088;" value="0E0088">0E0088</option>
				 <option style="background-color: #0C0073;" value="0C0073">0C0073</option>
				 <option style="background-color: #0A0060;" value="0A0060">0A0060</option>
				 <option style="background-color: #07004D;" value="07004D">07004D</option>
				 </select>
				 </td>
              </tr>

              <tr>
                 <td>Horizonal Angle</td>
                 <td>
                 <input type="text" name="HANGLE" value="0" class="smallInput"></td>
              </tr>
              <tr>
                 <td>Vertical Angle</td>
                 <td>
                 <input type="text" name="VANGLE" value="70" class="smallInput"></td>
              </tr>
              <!--
              <tr>
                 <td  width="300">Distance (Pixels)</td>
                 <td>
                 <input type="hidden" type="text" name="DISTANCE" value="0" class="smallInput"></td>
              </tr>
              -->
              <tr>
                 <td colspan="2">&nbsp;</td>
              </tr>

              <tr>
                 <td>
					<input onclick="javascript:window.location.href='atlas.php?op=demo_wmsextension_GetMap25D&aid=<?=$aid?>&atlas_aid=<?=$aid?>'" name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
		 		</td>
				<td align="right">
				<input type="hidden" name="aid" value="<?=$aid?>">
				<input type="hidden" name="operatioopwms_getmap25d">
                 <input onclick="chkform();" name="button" value="GetMap25D" type="button" class="ui-button ui-state-default ui-corner-all">
                </td>
              </tr>
        </form>
</table>          
		
          </td>
          <td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td>Options</td>
		</tr>
		<tr>
		<td>
		Description:
		<p>
		
		</p>
		</td>
		</tr>
	</table>
</td>
		</tr>
</table>
<script >
$(document).ready(function(){
				 $('#BGCOLOR').ColorPicker({
					onSubmit: function(hsb, hex, rgb, el) {
						$(el).val(hex);
						$(el).ColorPickerHide();
						$(el).css({'background-color': '#'+hex});
					},
					onBeforeShow: function () {
						$(this).ColorPickerSetColor(this.value);
					}
					})
					.bind('keyup', function(){
						$(this).ColorPickerSetColor(this.value);
					});	
});			 
</script>
<?
print_selectable_listtable_script('table_list_layers', true);
}
break;
				case "wmsextension_getmap3d" :
				{
					echo "<script type=\"text/javascript\">";
					echo("\n");
					echo "function chkform()";
					echo "{";
					echo "if(";
					echo '$("#table_list_srs [input:radio]:checked").length == 0';
					echo ")";
					echo("\n");
					echo "{";
					echo "growlError(\"Please select a coordinate reference system!\");";
					echo("\n");
					echo "return false;";
					echo "}";
					echo "}";
					echo "</script>";
					echo("\n");				
?>
<table class="tableNone">
<tr class="title"><td colspan="2"><?=$atlas['name']?></td></tr>
<tr>
	<td width="65%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top" id="table_list_srs">
	<form name="form" action="atlas.php" method="get" onSubmit="return chkform()">
	<tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			GetMap3D
		</td>
	</tr>
<?
$rs4 = $database->getRows4MetaGroupBy($aid, 'srs');
$count = 0;
while ($line4 = $database->getColumns($rs4)) {
	$num = $database->getRowsNumber($rs4);
	$data4 = $line4["srs"];
print '<tr ';
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}
print '>';
	
?>	    
			         <td  colspan="2">
			         <input type="radio" name="SRS" value="<?=$data4?>"><?=$data4?>
			         </td>
				    </tr>
<?
$count++;
}
if (!$data4) {
?>
                    <tr>
					 <td  colspan="2">
					 <p>These no SRS data, please check your database.</p>
					 </td>
					</tr>
<?
print '		<tr>
<td align="left">
<input onclick="javascript:window.location.href=\'atlas.php?op=ATLAS_DEMO&atlas_aid=' . $aid . '\'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
</td>
<td align="right">
</tr>';
}
if ($data4) {
	
?>
					<tr>
				 	 <td>
				 	 <input onclick="javascript:window.location.href='atlas.php?op=ATLAS_DEMO&atlas_aid=<?=$aid?>'" name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
                 </td>
				 	 <td align="right">
				 	 <input type="hidden" name="op" value="demo_wmsextension_GetMap3D_r">
					 <input type="hidden" name="aid" value="<?=$aid?>">
				 	 <input type="submit" name="submit" value="GetMap3D" class="ui-button ui-state-default ui-corner-all">
				 	 </td>
					</tr>
<?
}
?>
					</form>
			</table>     
    </td>
	<td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td>Options</td>
		</tr>
		<tr>
		<td>
		Description:
		<p>
		
		</p>
		</td>
		</tr>
	</table>
</td>
	
</tr>
</table>
<?
print_selectable_listtable_script('table_list_srs');
}
break;
case "wmsextension_getmap3d_r" :
{	
	print '<link rel="stylesheet" href="../cssjs/lib/jquery/js/plugin/colorpicker/css/colorpicker.css" type="text/css" />
		<script type="text/javascript" src="../cssjs/lib/jquery/js/plugin/colorpicker/js/colorpicker.js" defer="defer"></script>';	
	echo "<script type=\"text/javascript\">";
	echo("\n");
	echo "function chkform()";
	echo("\n");
	echo "{";
	echo("\n");
	echo("\n");
	echo "if(document.form.minx.value == \"\")  {";
	echo("\n");
	echo "growlError(\"Insert minx!\");";
	echo("\n");
	echo "document.form.minx.focus();";
	echo("\n");
	echo "return false;";
	echo("\n");
	echo "}";
	echo("\n");
	echo "if(document.form.miny.value == \"\") {";
	echo("\n");
	echo "growlError(\"Insert miny!\");";
	echo("\n");
	echo "document.form.miny.focus();";
	echo("\n");
	echo "return false;";
	echo("\n");
	echo "}";
	echo("\n");
	
	echo "if(document.form.maxx.value == \"\") {";
	echo("\n");
	echo "growlError(\"Insert maxx!\");";
	echo("\n");
	echo "document.form.miny.focus();";
	echo("\n");
	echo "return false;";
	echo("\n");
	echo "}";
	echo("\n");
	
	echo "if(document.form.maxy.value == \"\") {";
	echo("\n");
	echo "growlError(\"Insert maxy!\");";
	echo("\n");
	echo "document.form.miny.focus();";
	echo("\n");
	echo "return false;";
	echo("\n");
	echo "}";
	echo("\n");
	
	echo "document.form.BBOX.value =document.form.minx.value + ',' + document.form.miny.value + ',' + document.form.maxx.value + ',' + document.form.maxy.value;";
	echo("\n");
	
	echo "document.form.POI.value =document.form.POIX.value + ',' + document.form.POIY.value + ',' + document.form.POIZ.value;";
	echo("\n");
	
	echo "if(document.form.WIDTH.value == \"\") {";
	echo("\n");
	echo "growlError(\"Insert WIDTH!\");";
	echo("\n");
	echo "document.form.WIDTH.focus();";
	echo("\n");
	echo "return false;";
	echo("\n");
	echo "}";
	echo("\n");
	
	echo "if(document.form.HEIGHT.value == \"\") {";
	echo("\n");
	echo "growlError(\"Insert HEIGHT!\");";
	echo("\n");
	echo "document.form.HEIGHT.focus();";
	echo("\n");
	echo "return false;";
	echo("\n");
	echo "}";
	echo("\n");
	
	echo "if(";
	echo '$("#table_list_layers [input:checkbox]:checked").length == 0';
	echo ")";
	echo " { ";
	echo "growlError(\"please select a layer!\");";
	echo("\n");
	echo "return false;";
	echo "}";
	echo " var layerstr = \"\";";
	echo " var stylestr = \"\";";
	echo " var elevationstr = \"\";";
	echo "var count = $(\"#table_list_layers [input:checkbox]:checked\").length-1; ";
	
	echo "for  (i = 1; i <= count ; ++ i)";
	echo("\n");
	echo "{";
	echo "var chk= \"layer\" + i;";
	echo("\n");
	echo "var ele= \"elevation\" + i;";
	echo("\n");
	echo "var layerarray=$('#'+chk);";
	echo("\n");
	echo "var elearray=document.getElementById(ele);";
	echo("\n");
	echo "if (layerarray.is(':checked') == true){
		layerstr = layerstr + layerarray.val() + \",\"; ";
	echo("\n");
	echo "stylestr = stylestr + document.form.style.value + \",\";";
	echo("\n");
	echo "elevationstr = elevationstr + elearray.value + \",\";";
	echo("}");
	echo "}";
/*	echo '$("#table_list_layers [input:checkbox]:checked").each(function(){
						if($(this).attr(\'name\')==\'layer\'){
							layerstr += $(this).val()+ ",";	
						}
											
	});';*/	
	echo("\n");
	echo "layerstr = (layerstr.charAt(layerstr.length-1) == \",\") ? layerstr.slice(0,layerstr.length-1) : layerstr;";
	echo("\n");
	echo "stylestr = (stylestr.charAt(stylestr.length-1) == \",\") ? stylestr.slice(0,stylestr.length-1) : stylestr;";
	echo("\n");
	echo "elevationstr = (elevationstr.charAt(elevationstr.length-1) == \",\") ? elevationstr.slice(0,elevationstr.length-1) : elevationstr;";
	echo("\n");
	echo "document.form.LAYERS.value = layerstr;";
	echo("\n");
	echo "document.form.STYLES.value = stylestr;";
	echo("\n");
	echo "document.form.ELEVATIONS.value = elevationstr;";
	print 'document.form.action ="'.get_wms_interface($aid).'";' .
	'document.form.target="_blank";
	document.form.submit();';
	echo "}";
	echo("\n");	
	echo "</script>";
	echo("\n");	
?>
<table class="tableNone">
<tr class="title"><td colspan="2"><?=$atlas['name']?></td></tr>
<tr>
	<td width="65%">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
	<form name="form" action="<?=get_wms_interface($aid)?>" method="get" target="_blank">
		<tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			GetMap3D
		</td>
		</tr>
              <tr>
                      <td>VERSION</td>
                      <td>
                      <input type="text" name="VERSION" value="<?=SUAS_CFG_WMS_VERSION?>" readonly="readonly"  class="smallInput"></td>
              </tr>

          <tr>
             <td>SERVICE</td>
             <td>
             <input type="text" name="SERVICE" value="<?=SUAS_CFG_WMS_SERVICE?>" readonly="readonly" class="smallInput"></td>
          </tr>
                  <input type="hidden" name="BBOX" value="0,0,0,0">
                  <input type="hidden" name="LAYERS" value="LayerProblem">
                  <input type="hidden" name="STYLES" value="styleproblem">
                  <input type="hidden" name="POI" value="0,0,0">
                  <input type="hidden" name="ELEVATIONS" value="elevationproblem">
              <tr>
                     <td>REQUEST</td>
                     <td>
                     <input type="text" name="REQUEST" value="GetMap3D" readonly="readonly"  class="smallInput"></td>
              </tr>
              <tr><td colspan="2">LAYERS: <td></tr>
             <tr align="left">
             <td></td>
			 <td>
            <table class="tableNone" id="table_list_layers">
            <td>Select All?</td>
			<td><input type="checkbox" name="chkall" onclick="checkall(this.form, 'table_list_layers')"></td>
			<td>Elevation</td>
<?php
$rs3 = $database->getRowsBySrsGroupBy($aid, $map_parameters['srs'], 'layer');
$count = 1;
$num = $database->getRowsNumber($rs3);
while ($line3 = $database->getColumns($rs3)) {
	$data3 = $line3["layer"];
	$data4 = $line3["elevation"];
print '<tr ';
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}
print '>';
?>
                     <td><?=$data3?></td>
					 <td>
					 <input type="checkbox" name="layer<?=$count?>" id="layer<?=$count?>" value="<?=$data3?>">
                     </td>
					 <td>
					 <input type="text" name="elevation<?=$count?>" id="elevation<?=$count?>" value="<?=$data4?>"  class="smallInput">
					 </td>
                  </tr>
<?php
++$count;
}

?>
         </table>
      <td></tr>
          <tr>
                  <td>STYLES</td>
                  <td>
                  <input type="text" name="style" value="default" readonly="readonly"  class="smallInput"></td>
          </tr>
            <tr>
                <td>SRS SELECTED IS:</td>
                <td>
                <input type= "text" name="SRS" value="<?=$map_parameters['srs']?>" readonly="readonly" class="smallInput"></td>
            </tr>


          <?php
$rs4 = $database->getRowsMinMaxXYBySrs($aid, $map_parameters['srs']);
$line4 = $database->getColumns($rs4);
$totalminx = $line4[0];
$totalminy = $line4[1];
$totalmaxx = $line4[2];
$totalmaxy = $line4[3];

?>

              <tr>
                 <td>MIN. EASTING</td>
                 <td>
                 <input type="text" name="minx" value="<?=$totalminx?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td>MIN. NORTHING</td>
                 <td>
                 <input type="text" name="miny" value="<?=$totalminy?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td>MAX. EASTING</td>
                 <td>
                 <input type="text" name="maxx" value="<?=$totalmaxx?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td>MAX. NORTHING</td>
                 <td>
                 <input type="text" name="maxy" value="<?=$totalmaxy?>" class="smallInput"></td>
              </tr>


              <tr>
                 <td>WIDTH</td>
                 <td>
                 <input type="text" name="WIDTH" value="800" class="smallInput"></td>
              </tr>

              <tr>
                 <td>HEIGHT</td>
                 <td>
                 <input type="text" name="HEIGHT" value="600" class="smallInput"></td>
              </tr>

              <tr>
                 <td>FORMAT</td>
                 <td>
				 <select name="FORMAT">
                       <option value="model/vrml" selected>VRML</option>
                       <option value="model/vrmlz">VRMLZ</option>
                       <!--<option value="model/vrml">GeoVRML</option>-->
                       <option value="model/x3d+xml">X3D</option>
                       <option value="model/x3dz">X3DZ</option>
                       <option value="application/vnd.google-earth.kml+xml">KML</option>
                       <option value="application/vnd.google-earth.kmz">KMZ</option>
                 </select>
				 </td>
              </tr>

              <tr>
                 <td>EXCEPTIONS</td>
                 <td>
				 <SELECT name="EXCEPTIONS">
				 <OPTION value=application/vnd.ogc.se_xml selected>application/vnd.ogc.se_xml</OPTION>
				 <OPTION value=application/vnd.ogc.se_blank>application/vnd.ogc.se_blank</OPTION>
				 </SELECT>
				 </td>
              </tr>

              <tr>
                 <td>POI(X Y Z)</td>
                 <td>
                 <input type="text" name="POIX" value="<?=($totalmaxx+$totalminx)/2?>" class="smallInput">
                 <input type="text" name="POIY" value="<?=($totalmaxy+$totalminy)/2?>" class="smallInput">
                 <input type="text" name="POIZ" value="0" class="smallInput">
				 </td>
              </tr>
              <tr>
                 <td>PITCH</td>
                 <td>
                 <input type="text" name="PITCH" value="20" class="smallInput"></td>
              </tr>
              <tr>
                 <td>YAW</td>
                 <td>
                 <input type="text" name="YAW" value="180" class="smallInput"></td>
              </tr>
              <tr>
                 <td>ROLL</td>
                 <td>
                 <input type="text" name="ROLL" value="0" class="smallInput"></td>
              </tr>
              <tr>
                 <td>DISTANCE</td>
                 <td>
                 <input type="text" name="DISTANCE" value="<?=sqrt(($totalmaxx-$totalminx)*($totalmaxx-$totalminx)+($totalmaxy-$totalminy)*($totalmaxy-$totalminy))?>" class="smallInput"></td>
              </tr>
              <tr>
                 <td>AOV</td>
                 <td>
                 <input type="text" name="AOV" value="70" class="smallInput"></td>
              </tr>
              <tr>
                 <td>ENVIRONMENT</td>
                 <td>
				 <SELECT name="ENVIRONMENT">
				 <OPTION value=on>on</OPTION>
				 <OPTION value=off selected>off</OPTION>
				 </SELECT>
				 </td>
              </tr>
              <tr>
                 <td>SKYCOLOR</td>
                 <td>
                 <select id="idSKYCOLOR" name="SKYCOLOR">
				 <option style="background-color: #FFFFFF;" value="FFFFFF">FFFFFF</option>
				 <option style="background-color: #D6D7FF;" value="D6D7FF">D6D7FF</option>
				 <option style="background-color: #CECFFF;" value="CECFFF">CECFFF</option>
				 <option style="background-color: #B5AEFF;" value="B5AEFF">B5AEFF</option>
				 <option style="background-color: #A59AFF;" value="A59AFF">A59AFF</option>
				 <option style="background-color: #8475FF;" value="8475FF">8475FF</option>
				 <option style="background-color: #7262FF;" value="7262FF" selected>7262FF</option>
				 <option style="background-color: #6453FF;" value="6453FF">6453FF</option>
				 <option style="background-color: #5846FF;" value="5846FF">5846FF</option>
				 <option style="background-color: #513EFF;" value="513EFF">513EFF</option>
				 <option style="background-color: #3620FF;" value="3620FF">3620FF</option>
				 <option style="background-color: #1F06FF;" value="1F06FF">1F06FF</option>
				 <option style="background-color: #1700EC;" value="1700EC">1700EC</option>
				 <option style="background-color: #1500D5;" value="1500D5">1500D5</option>
				 <option style="background-color: #1200B7;" value="1200B7">1200B7</option>
				 <option style="background-color: #0F0097;" value="0F0097">0F0097</option>
				 <option style="background-color: #0E0088;" value="0E0088">0E0088</option>
				 <option style="background-color: #0C0073;" value="0C0073">0C0073</option>
				 <option style="background-color: #0A0060;" value="0A0060">0A0060</option>
				 <option style="background-color: #07004D;" value="07004D">07004D</option>
				 </select>

				 </td>
              </tr>
              <tr>
                 <td>BGCOLOR</td>
                 <td>
				 <input name="BGCOLOR" id="BGCOLOR" type="text" maxlength="7"
				 style="background-color: #FFFFFF;color: #000000"
				 size="10">
				 
				 </td>
              </tr>
              <tr>
                 <td>BGIMAGE</td>
                 <td>
				 <SELECT name="BGIMAGE">
				 <OPTION value="bg_icemountns.png" selected>icemountns</OPTION>
				 <OPTION value="bg_skyclouds.png">skyclouds</OPTION>
				 </SELECT>
				 </td>
              </tr>
              <tr>
                 <td colspan="2">&nbsp;</td>
              </tr>

              <tr>
                 <td>
					<input onclick="javascript:window.location.href='atlas.php?op=demo_wmsextension_GetMap3D&aid=<?=$aid?>&atlas_aid=<?=$aid?>'" name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
		 		</td>
				<td align="right">
				<input type="hidden" name="aid" value="<?=$aid?>">
				<input type="hidden" name="operatioopwms_getmap3d">
                 <input onclick="chkform();" name="button" value="GetMap3D" type="button" class="ui-button ui-state-default ui-corner-all">
                 </td>
              </tr>
        </form>

</table>
          </td>
          <td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td>Options</td>
		</tr>
		<tr>
		<td>
		Description:
		<p>
		
		</p>
		</td>
		</tr>
	</table>
</td>
		</tr>
</table>
<script >
$(document).ready(function(){
				 $('#BGCOLOR').ColorPicker({
					onSubmit: function(hsb, hex, rgb, el) {
						$(el).val(hex);
						$(el).ColorPickerHide();
						$(el).css({'background-color': '#'+hex});
					},
					onBeforeShow: function () {
						$(this).ColorPickerSetColor(this.value);
					}
					})
					.bind('keyup', function(){
						$(this).ColorPickerSetColor(this.value);
					});	
});			 
</script>
<?
print_selectable_listtable_script('table_list_layers', true);
}
break;
				case "wmsextension_getthematicmap" :
				{
					echo "<script type=\"text/javascript\">";
					echo("\n");
					echo "function chkform()";
					echo("\n");
					echo "{";
					echo("\n");
					
					echo "if(";
					echo '$("#table_list_srs [input:radio]:checked").length == 0';
					echo ")";
					echo("\n");
					echo "{";
					echo "growlError(\"Please select a coordinate reference system!\");";
					echo("\n");
					echo "return false;";
					echo "}";
					echo "}";
					echo "</script>";
					echo("\n");					
?>
<table class="tableNone">
<tr class="title"><td colspan="2"><?=$atlas['name']?></td></tr>
<tr>
	<td width="65%" valign="top">
    <table  class="block-panel ui-corner-all" style="width:100%;" align="top" id="table_list_srs">
   	<form name="form" action="atlas.php" method="get" onSubmit="return chkform()">
	<tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			GetThematicMap
		</td>
		</tr>
<?php
$rs4 = $database->getRows4MetaGroupBy($aid, 'srs');
$count=0;
while ($line4 = $database->getColumns($rs4)) {
	$num = $database->getRowsNumber($rs4);
	$data4 = $line4["srs"];
print '<tr ';
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}
print '>';	
?>	    
			         <td colspan="2"><input type="radio" name="SRS"  value="<?=$data4?>"><?=$data4?>
			         </td>
				    </tr>
<?php
$count++;
}
if (!$data4) {
	
	?>
                    <tr>
					 <td colspan="2"><p>These no SRS data, please check your database.</p></td>
					</tr>
<?php
print '		<tr>
<td align="left">
<input onclick="javascript:window.location.href=\'atlas.php?op=ATLAS_DEMO&atlas_aid=' . $aid . '\'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
</td>
<td align="right">
</tr>';
}
if ($data4) {
	
	?>
					<tr>
				 	 <td>
				 	 <input onclick="javascript:window.location.href='atlas.php?op=ATLAS_DEMO&atlas_aid=<?=$aid?>'" name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
                 </td>
				 	 <td align="right">
				 	 <input type="hidden" name="op" value="demo_wmsextension_GetThematicMap_r">
					 <input type="hidden" name="aid" value="<?=$aid?>">
				 	 <input type="submit" name="submit" value="GetThematicMap" class="ui-button ui-state-default ui-corner-all"></td>
					</tr>
<?php
}

?>
					</form>
			</table>   
          
          
	</td>
          
	<td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td>Options</td>
		</tr>
		<tr>
		<td>
		Description:
		<p>
		
		</p>
		</td>
		</tr>
	</table>
	</td>
</tr>
</table>

<?php
print_selectable_listtable_script('table_list_srs');
}
break;
case "wmsextension_getthematicmap_r" :
{
	echo "in working";
}
break;
case "wfs_getcapabilities" :
{
?>
<table class="tableNone">
<tr class="title"><td colspan="2"><?=$atlas['name']?></td></tr>
	<tr>
		<td width="65%" valign="top">
		<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<form action="<?=get_wfs_interface($aid)?>" method="get" target="_blank">
		<tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			GetCapabilities
		</td>
		</tr>
          <tr>
             <td>VERSION</td>
             <td width="261">
             <input type="text" name="VERSION" value="<?=SUAS_CFG_WFS_VERSION?>" readonly="readonly" class="smallInput"></td>
          </tr>

          <tr>
             <td>SERVICE</td>
             <td>
             <input type="text" name="SERVICE" value="<?=SUAS_CFG_WFS_SERVICE?>" readonly="readonly" class="smallInput"></td>
          </tr>

          <tr>
             <td>REQUEST</td>
             <td>
             <input type="text" name="REQUEST" value="GetCapabilities" readonly="readonly" class="smallInput"></td>
          </tr>
		<tr>
             <td>&nbsp;</td>
             <td>
             </td>
          </tr>
          <tr>
             <td>
				 	 <input onclick="javascript:window.location.href='atlas.php?op=ATLAS_DEMO&atlas_aid=<?=$aid?>'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
                 </td>
				 	 <td align="right">
				 	 <input type="hidden" name="op" value="demo_wfs_getcapabilities">
					 <input type="hidden" name="aid" value="<?=$aid?>">
             <input type="submit" name="submit" value="GetCapabilities" class="ui-button ui-state-default ui-corner-all">
             </td>
          </tr>
			</form>
           </table>
          </td>
          <td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td>Options</td>
		</tr>
		<tr>
		<td>
		Description:
		<p>
		
		</p>
		</td>
		</tr>
	</table>
</td>         
</tr>
</table>	   

<?php
}
break;
					 case "wfs_describefeaturetype" :
					 {
						 echo "<script type=\"text/javascript\">";
						 echo("\n");
						 echo "function chkform()";
						 echo "{";
						 
						 echo "if(";
						echo '$("#table_list_layers [input:checkbox]:checked").length == 0';
						 echo ")";
						 echo " { ";
						 echo("\n");
						 echo "growlError(\"please select a TYPENAME!\");";
						 echo "return false;";
						 echo("\n");
						 echo "}";
						 echo("\n");
						 echo " var layerstr = \"\";";
						echo '$("#table_list_layers [input:checkbox]:checked").each(function(){
						if($(this).attr(\'name\')==\'layer\')
						layerstr += $(this).val()+ ",";						
						});';
						 
						 echo "layerstr = (layerstr.charAt(layerstr.length-1) == \",\") ? layerstr.slice(0,layerstr.length-1) : layerstr;";
						 echo "document.form.TYPENAME.value = layerstr;";
						 echo "}";
						 echo "</script>";
						 echo("\n");
						 ?>
<table class="tableNone">
<tr class="title"><td colspan="2"><?=$atlas['name']?></td></tr>
<tr>
	<td width="65%" valign="top">
		<table class="block-panel ui-corner-all" style="width:100%;" align="top">
      <form name="form" action="<?=get_wfs_interface($aid)?>" method="get" onSubmit="return chkform()" target="_blank">
      <tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			DescribeFeatureType
		</td>
		</tr>
              <tr>
                      <td>SERVICE</td>
                      <td>
                      <input type="text" name="SERVICE" value="<?=SUAS_CFG_WFS_SERVICE?>" readonly="readonly" class="smallInput"></td>
              </tr>
              <tr>
                      <td>VERSION</td>
                      <td>
                      <input type="text" name="VERSION" value="<?=SUAS_CFG_WFS_VERSION?>" readonly="readonly"  class="smallInput"></td>
              </tr>
                  <input type="hidden" name="TYPENAME" value="LayerProblem">
              <tr>
                     <td>REQUEST</td>
                     <td>
                     <input type="text" name="REQUEST" value="DescribeFeatureType" readonly="readonly"  class="smallInput"></td>
              </tr>
              <tr>
                     <td>OUTPUTFORMAT</td>
                     <td>
                     <input type="text" name="OUTPUTFORMAT" value="text/xml" readonly="readonly"  class="smallInput"></td>
              </tr>
              <tr><td colspan="2">TYPENAME: <td></tr>
             <tr align="left">
             <td></td>
			 <td>
            <table class="tableNone" id="table_list_layers">
            <td>Select All?</td><td><input type="checkbox" name="chkall"  onclick="checkall(this.form, 'table_list_layers')"></td>
<?php
$rs3 = $database->getRows4MetaGroupBy($aid,'layer');
$count = 1;
$num = $database->getRowsNumber($rs3);
while ($line3 = $database->getColumns($rs3)) {
	$data3 = $line3["layer"];
	print '<tr ';
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}
	print '>';
?>    
                     <td><?=$data3?></td><td> <input type="checkbox" name="layer" value="<?=$data3?>"></td>
                  </tr>
<?php
++$count;
}
?>
         </table>
      <td>
	  </tr>
              <tr>
                 <td colspan="2">&nbsp;</td>
              </tr>

              <tr>
                 <td>
                 <input onclick="javascript:window.location.href='atlas.php?op=ATLAS_DEMO&atlas_aid=<?=$aid?>'" name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
                 </td>
				 	 <td align="right">
					 <input type="hidden" name="aid" value="<?=$aid?>">
                 <input type="submit" name="submit" value="DescribeFeatureType" class="ui-button ui-state-default ui-corner-all">
                 </td>
              </tr>
        </form>
	</table>
	
	</td>
	<td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td>Options</td>
		</tr>
		<tr>
		<td>
		Description:
		<p>
		
		</p>
		</td>
		</tr>
	</table>
</td>

</tr>
</table>
<?
print_selectable_listtable_script('table_list_layers', true);
}
break;
					 case "wfs_getfeature" :
					 {
						 echo "<script type=\"text/javascript\">";
						 echo("\n");
						 echo "function chkform()";
						 echo "{";

						 echo "if(";
						 echo '$("#table_list_layers [input:checkbox]:checked").length == 0';
						 echo ")";
						 echo("\n");
						 echo " { ";
						 echo("\n");
						 echo "growlError(\"please select a TYPENAME!\");";
						 echo("\n");
						 echo "return false;";
						 echo("\n");
						 echo "}";
						 echo("\n");
						 echo " var layerstr = \"\";";
						 echo '$("#table_list_layers [input:checkbox]:checked").each(function(){
						if($(this).attr(\'name\')==\'layer\')
						layerstr += $(this).val()+ ",";						
						});';
						 
						 echo "layerstr = (layerstr.charAt(layerstr.length-1) == \",\") ? layerstr.slice(0,layerstr.length-1) : layerstr;";

						 echo "document.form.TYPENAME.value = layerstr;";
						 echo " strRef = \"1234567890\"; var obj = eval(document.form.MAXFEATURES);";
						 echo '
						 for (i=0;i<obj.value.length;i++) {
						 tempChar= obj.value.substring(i,i+1);
						 if (strRef.indexOf(tempChar,0)==-1) {
						 growlError("Please input number here!");
						 if(obj.type=="text")
						 obj.focus();
						 return false;
						 }
						 }';
						 echo "}";
						 echo("\n");
						 echo "</script>";
						 echo("\n");						 
						 ?>
<table  class="tableNone">
<tr class="title"><td colspan="2"><?=$atlas['name']?></td></tr>
<tr>
	<td width="65%" valign="top">
		<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<form name="form" action="<?=get_wfs_interface($aid)?>" method="get" onSubmit="return chkform()" target="_blank">
		<tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			GetFeature
		</td>
		</tr>
              <tr>
                      <td>SERVICE</td>
                      <td>
                      <input type="text" name="SERVICE" value="<?=SUAS_CFG_WFS_SERVICE?>" readonly="readonly" class="smallInput"></td>
              </tr>
              <tr>
                      <td>VERSION</td>
                      <td>
                      <input type="text" name="VERSION" value="<?=SUAS_CFG_WFS_VERSION?>" readonly="readonly"  class="smallInput">
                      <input type="hidden" name="BBOX" value="3444772">
                  	<input type="hidden" name="SRSNAME" value="srs_notdefined">
                  	<input type="hidden" name="TYPENAME" value="LayerProblem">
                    </td>
              </tr>                 
              <tr>
                     <td>REQUEST</td>
                     <td>
                     <input type="text" name="REQUEST" value="GetFeature" readonly="readonly"  class="smallInput"></td>
              </tr>
              <tr>
                     <td>OUTPUTFORMAT</td>
                     <td>
                     <input type="text" name="OUTPUTFORMAT" value="text/xml" readonly="readonly"  class="smallInput"></td>
              </tr>
              <tr>
                     <td>MAXFEATURES</td>
                     <td>
                     <input type="text" name="MAXFEATURES" value="100" class="smallInput"></td>
              </tr>
              <tr><td colspan="2">TYPENAME: <td></tr>
             <tr align="left">
             <td></td>
			 <td>
            <table class="tableNone" id="table_list_layers">
            <tr><td>Select All?</td><td><input type="checkbox" name="chkall"  onclick="checkall(this.form, 'table_list_layers')"></td></tr>
<?php
$rs3 = $database->getRows4MetaGroupBy($aid,'layer');
$count = 1;
$num = $database->getRowsNumber($rs3);
while ($line3 = $database->getColumns($rs3)) {
	$data3 = $line3["layer"];
	print '<tr ';
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}
	print '>';
?>
                  
                     <td><?=$data3?></td><td> <input type="checkbox" name="layer" value="<?=$data3?>"></td>
                  </tr>
<?php
++$count;
}
?>
         </table>
      <td>
	  </tr>
          <tr><td></td><td>&nbsp;</td></tr>
          <tr>
             <td>
                 <input onclick="javascript:window.location.href='atlas.php?op=ATLAS_DEMO&atlas_aid=<?=$aid?>'" name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
                 </td>
				 	 <td align="right">
					 <input type="hidden" name="aid" value="<?=$aid?>">
             <input type="submit" name="submit" value="GetFeature" class="ui-button ui-state-default ui-corner-all">
             </td>
          </tr>
			</form>
           </table>
	
	</td>
	<td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td>Options</td>
		</tr>
		<tr>
		<td>
		Description:
		<p>
		
		</p>
		</td>
		</tr>
	</table>
</td>
</tr>
</table>

<?
print_selectable_listtable_script('table_list_layers', true);
}
break;
					 case "wfs_getgmlobject" :
					 {
?>
<table class="tableNone">
<tr class="title"><td colspan="2"><?=$atlas['name']?></td></tr>
<tr>
	<td width="65%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
	<form action="<?=get_wfs_interface($aid)?>" method="get">
	<tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			GetGmlObject
		</td>
		</tr>
		<tr>
		<td colspan="2">
			not finished yet.
		</td>
		</tr>
          <tr>
             <td>
                 <input onclick="javascript:window.location.href='atlas.php?op=ATLAS_DEMO&atlas_aid=<?=$aid?>'" name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
                 </td>
				 	 <td align="right">
					 <input type="hidden" name="aid" value="<?=$aid?>">
             <input type="submit" name="submit" value="GetGmlObject" class="ui-button ui-state-default ui-corner-all">
             </td>
          </tr>
			</form>
           </table>
	
	</td>
	<td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td>Options</td>
		</tr>
		<tr>
		<td>
		Description:
		<p>
		
		</p>
		</td>
		</tr>
	</table>
</td>
</tr>
</table>
<?
}
break;
					 case "wfs_transaction" :
					 {
?>
<table class="tableNone">
<tr class="title"><td colspan="2"><?=$atlas['name']?></td></tr>
<tr>
	<td width="65%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
	<form action="<?=get_wfs_interface($aid)?>" method="get">
	<tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			Transaction
		</td>
	</tr>
	<tr>
		<td colspan="2">
			not finished yet.
		</td>
		</tr>
          <tr>
             <td>
                 <input onclick="javascript:window.location.href='atlas.php?op=ATLAS_DEMO&atlas_aid=<?=$aid?>'" name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
                 </td>
				 	 <td align="right">
					 <input type="hidden" name="aid" value="<?=$aid?>">
             <input type="submit" name="submit" value="Transaction" class="ui-button ui-state-default ui-corner-all">
             </td>
          </tr>
			</form>
           </table>
	
	</td>
<td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td>Options</td>
		</tr>
		<tr>
		<td>
		Description:
		<p>
		
		</p>
		</td>
		</tr>
	</table>
</td>
</tr>
</table>

<?
}
break;
}

}

function atlas_get_mapview_viewer_form($database, $aid, $atlas, $from = 'view', $backurl = ""){
	echo "<script type=\"text/javascript\" >";
	echo("\n");
	echo "function chkform()";
	echo "{";
	echo "if(";
	echo '$("#table_list_srs [input:radio]:checked").length == 0';
	echo ")";
	echo("\n");
	echo "{";
	echo "growlError(\"Please select a coordinate reference system!\");";
	echo("\n");
	echo "return false;";
	echo "}";
	echo "if(";
	echo '$("#table_list_viewers [input:radio]:checked").length == 0';
	echo ")";
	echo("\n");
	echo "{";
	echo "growlError(\"Please select one Viewer!\");";
	echo("\n");
	echo "return false;";
	echo "}";
?>	
	//if is geoxml, only epsg:4326 is allowed
	if($("#table_list_viewers [input:radio]:checked").val() == 'geoxml'){
			if($("#table_list_srs [input:radio]:checked").val().toUpperCase() != 'EPSG:4326'){
				growlError("Only SRS EPSG:4326 can be used for GeoXml Viewer!");
				return false;
			}
	}
	if($("#table_list_viewers [input:radio]:checked").val() == 'gmapswms'){
			if($("#table_list_srs [input:radio]:checked").val().toUpperCase() != 'EPSG:4326'){
				growlError("Only SRS EPSG:4326 can be used for Gmaps WMS Viewer!");
				return false;
			}
	}
	if($("#table_list_viewers [input:radio]:checked").val() == 'g3dg2s'){
			if($("#table_list_srs [input:radio]:checked").val().toUpperCase() != 'EPSG:4326'){
				growlError("Only SRS EPSG:4326 can be used for Gearth 3D&amp;Gmaps 2D Viewer!");
				return false;
			}
	}
<?	
	echo "document.form.viewertype.value = $(\"#table_list_viewers [input:radio]:checked\").val();";
	
	echo "document.form.submit()";
	echo "}";
	echo "</script>";
	echo("\n");
	
	?>
<table class="tableNone">
<tr class="title"><td colspan="2"><?=$atlas['name']?></td></tr>
<tr>
	<td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top" id="table_list_srs">
		<tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			Select SRS:
		</td>
		</tr>
		<form name="form" action="atlas.php" method="get" onSubmit="return chkform()">
<?php
$rs4 = $database->getRows4MetaGroupBy($aid, 'srs');
$count=0;
while ($line4 = $database->getColumns($rs4)) {
	$num = $database->getRowsNumber($rs4);
	$data4 = $line4["srs"];
	print '<tr ';
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}
	print '>';
	?>	    
			         <td colspan="2"><input type="radio" name="SRS"  value="<?=$data4?>"><?=$data4?></td>
				    </tr>
<?php
$count++;
}
if (!$data4) {
	?>
                    <tr>
					 <td colspan="2"><p>These no data.</p></td>
					</tr>

					<tr>
					<td align="left">
				
		<input onclick="javascript:window.location.href='atlas.php?op=<?
		if($from == 'demo'){
			echo  "atlas_demo&aid=".$aid;
		}
		else if($from == 'view'){
			echo  "atlaslist".$backurl;
		}
		else if($from == 'favo'){
			echo "favorite".$backurl;
		}
		?>'" type="button" value="Back" class="ui-button ui-state-default ui-corner-all">
							
					</td>
					<td align="right">
					</tr>

<?php
}
if ($data4) {
	?>
					<tr>
				 	 <td>
                 <input onclick="javascript:window.location.href='atlas.php?op=<?
                 //history.go(-1)
		if($from == 'demo'){
			echo  "atlas_demo&atlas_aid=".$aid;
		}
		else if($from == 'view'){
			echo  "atlaslist".$backurl;
		}
		else if($from == 'favo'){
			echo "favorite".$backurl;
		}
		?>'" name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
                 </td>
                 <td align="right">
                 <input type="hidden" name="aid" value="<?=$aid?>">
                 <input type="hidden" name="viewertype" value="">
                 <input type="hidden" name="op" value="<?
                 	if($from == 'demo'){
                 		echo "demo_wmsextension_GetMapViewers_r";
                 	}else if($from == 'view'){
                 		echo "atlasview";
                 	}
                 	else if($from == 'favo'){
                 		echo "atlasviewfavo";
                 	}
                 ?>">
                 <?
                 if($from == 'view'){
                 	echo '<input type="hidden" name="backurl" value="'.$backurl.'">';
                 }
                 ?>
                 <input type="button" name="button" value="Continue" onClick="return chkform();" class="ui-button ui-state-default ui-corner-all">
                 </td>
					</tr>
<?php
}

?>
	</form>		
    </table><br/>
<?
	get_atlas_detail($database, true, false);
?>
	</td>
	<td width="65%" valign="top">
	<table class="block-panel ui-corner-all nocell" style="width:100%;" align="top" id="table_list_viewers">
		<tr class="block-panel-header ui-corner-all">
		<td colspan="2">Select Map Viewer</td>
		</tr>
		
		<tr class="odd">
		<td>
		<div class="block-pane-title">Open Layers<div>		
		<div class="block-pane-line">
		<span class="block-pane-label">link:</span>
		<a href="http://www.openlayers.org/" target="_blank">openlayers</a>
		</div>	
		<div class="block-pane-line">
		<span class="block-pane-label">Description:</span>
		OpenLayers makes it easy to put a dynamic map in any web page. It can display map tiles and markers loaded from any source.
		</div>
		</td>
		<td><input type="radio" name="viewer" id="viewer" value="openlayers" checked></td>
		</tr>
		
		<tr class="even">
		<td>
		<div class="block-pane-title">Carto SVG Viewer<div>			
		<div class="block-pane-line">
		<span class="block-pane-label">link:</span>
		<a href="http://www.carto.net/" target="_blank">carto</a>
		</div>	
		<div class="block-pane-line">
		<span class="block-pane-label">Description:</span>
		The Carto SVG Viewer supports both SVG and Raster Image formats. But for SVG format please use <b>IE</b> browser, for Raster format you can use <b>IE or FireFox</b> browsers. Because FireFox has problem when loading dynamical SVG map.	
		</div>
		</td>
		<td><input type="radio" name="viewer" id="viewer" value="cartosvg" ></td>
		</tr>

		<tr class="odd">
		<td>
		<div class="block-pane-title">GeoXml Viewer<div>		
		<div class="block-pane-line">
		<span class="block-pane-label">link:</span>
		<a href="http://www.digitalnineveharchives.org/" target="_blank">digitalnineveharchives</a>
		</div>	
		<div class="block-pane-line">
		<span class="block-pane-label">Description:</span>
		This Google Maps Extension enables client-side parsing and viewing of a number of flavors of GeoXml, ncluding GML from WFS servers, GPX from GPS devices or log as well as KML and multiple flavors of GeoRSS,In addition to the XML it supports saving out and re-loading of KJSON (plans are in to split the KJSON parsing/loading and XML based parsing)
		</div>
		</td>
		<td><input type="radio" name="viewer" id="viewer" value="geoxml"></td>
		</tr>
		
		<tr class="even">
		<td>
		<div class="block-pane-title">Gmaps WMS Viewer<div>		
		<div class="block-pane-line">
		<span class="block-pane-label">link:</span>
		<a href="http://johndeck.blogspot.com" target="_blank">Support</a>
		</div>	
		<div class="block-pane-line">
		<span class="block-pane-label">Description:</span>
		Render WMS overlays using GoogleMaps V2. 
		</div>
		</td>
		<td><input type="radio" name="viewer" id="viewer" value="gmapswms"></td>
		</tr>
		
		<tr class="odd">
		<td>
		<div class="block-pane-title">Gearth 3D&amp;Gmaps 2D Viewer<div>		
		<div class="block-pane-line">
		<span class="block-pane-label"></span>
		</div>	
		<div class="block-pane-line">
		<span class="block-pane-label">Description:</span>
		Render overlays using Google Earth 3D and GoogleMaps 2D. 
		</div>
		</td>
		<td><input type="radio" name="viewer" id="viewer" value="g3dg2d"></td>
		</tr>
		
	</table>
	</td>
</tr>
</table>
<?
print_selectable_listtable_script('table_list_srs');
print_selectable_listtable_script('table_list_viewers', false, 'view');
}

function atlas_get_mapview_form($database, $aid, $atlas, $map_parameters, $from = 'view', $backurl = ""){
	global $atlas;
	$viewertype = $_GET['viewertype'];
	
	echo "<script type=\"text/javascript\">";
	echo("\n");

	echo "function chkform()";
	echo "{";
	echo "if(document.form.minx.value == \"\")  {";
	echo "growlError(\"Insert minx!\");";
	echo "document.form.minx.focus();";
	echo "return false;";
	echo "}";
	echo "if(document.form.miny.value == \"\") {";
	echo "growlError(\"Insert miny!\");";
	echo "document.form.miny.focus();";
	echo "return false;";
	echo "}";
	
	echo "if(document.form.maxx.value == \"\") {";
	echo "growlError(\"Insert maxx!\");";
	echo "document.form.miny.focus();";
	echo "return false;";
	echo "}";
	
	echo "if(document.form.maxy.value == \"\") {";
	echo "growlError(\"Insert maxy!\");";
	echo "document.form.miny.focus();";
	echo "return false;";
	echo "}";
	
	echo "document.form.BBOX.value =document.form.minx.value + ',' + document.form.miny.value + ',' + document.form.maxx.value + ',' + document.form.maxy.value;";
	
	echo "if(document.form.WIDTH.value == \"\") {";
	echo "growlError(\"Insert WIDTH!\");";
	echo "document.form.WIDTH.focus();";
	echo "return false;";
	echo "}";
	
	echo "if(document.form.HEIGHT.value == \"\") {";
	echo "growlError(\"Insert HEIGHT!\");";
	echo "document.form.HEIGHT.focus();";
	echo "return false;";
	echo "}";
	
	echo "if(";
	echo '$("#table_list_layers [input:radio]:checked").length == 0';
	echo ")";
	echo " { ";
	echo "growlError(\"please select a layer!\");";
	echo "return false;";
	echo "}";
	echo " var layerstr = \"\";";
	echo " var stylestr = \"\";";
	echo '$("#table_list_layers [input:checkbox]:checked").each(function(){
						if($(this).attr(\'name\')==\'layer\'){
							layerstr += $(this).val()+ ",";	
						}
						stylestr += document.form.style.value+ ",";						
					});';
	
	echo "layerstr = (layerstr.charAt(layerstr.length-1) == \",\") ? layerstr.slice(0,layerstr.length-1) : layerstr;";
	echo "stylestr = (stylestr.charAt(stylestr.length-1) == \",\") ? stylestr.slice(0,stylestr.length-1) : stylestr;";
	echo "document.form.LAYERS.value = layerstr;";
	echo "document.form.STYLES.value = stylestr;";
	
	if($viewertype == 'openlayers'){
		$viewerName = "Open Layers Viewer";
		echo "var format = document.form.FORMAT.value;
		if(format != \"\"  && format != \"image/png\" && format != \"image/jpeg\" && format != \"image/gif\") {";
		echo "growlError(\"OpenLayers Viewer supports only PNG, JPEG and GIF formats.\");";
		echo "document.form.FORMAT.focus();";
		echo "return false;";
		echo "}";
		
		print 'document.form.action ="../models/MapViewerOpenLayers.php";' .
		'document.form.target = "_blank";
		document.form.submit ();';
	}
	else if($viewertype == 'cartosvg'){	
		$viewerName = "Carto SVG viewer";
		echo "if(document.form.FORMAT.value != \"\"  && document.form.FORMAT.value != \"image/svg+xml\"
		&& document.form.FORMAT.value != \"image/svgt+xml\"
		&& document.form.FORMAT.value != \"image/svgb+xml\"
		&& document.form.FORMAT.value != \"image/svgz+xml\"
		&& document.form.FORMAT.value != \"image/svgtz+xml\"
		&& document.form.FORMAT.value != \"image/svgbz+xml\"
		&& document.form.FORMAT.value != \"image/png\" && document.form.FORMAT.value != \"image/jpeg\" && document.form.FORMAT.value != \"image/gif\") {";
		echo "growlError(\"Carto SVG Viewer supports only SVG(T/B)(z) or PNG, JPEG and GIF formats.\");";
		echo "document.form.FORMAT.focus();";
		echo "return false;";
		echo "}";
		
		print 'document.form.action ="../models/MapViewerCartoSVG.php";' .
		'document.form.target = "_blank";
		document.form.submit ();';
	}
	else if($viewertype == 'geoxml'){
		$viewerName = "GeoXml Viewer";
		print 'document.form.action ="../models/MapViewerGeoXml.php";' .
		'document.form.target = "_blank";
		document.form.submit ();';
	}
	else if($viewertype == 'gmapswms'){
		$viewerName = "Gmaps WMS Viewer";
		echo "var format = document.form.FORMAT.value;
		if(format != \"\"  && format != \"image/png\" && format != \"image/jpeg\" && format != \"image/gif\") {";
		echo "growlError(\"Gmaps WMS Viewer supports only PNG, JPEG and GIF formats.\");";
		echo "document.form.FORMAT.focus();";
		echo "return false;";
		echo "}";
		print 'document.form.action ="../models/MapViewerGmapsWMS.php";' .
		'document.form.target = "_blank";
		document.form.submit ();';
	}
	else if($viewertype == 'g3dg2d'){
		$viewerName = "Gearth 3D&amp;Gmaps 2D Viewer";
		print 'document.form.action ="../models/MapViewerG3DG2D.php";' .
		'document.form.target = "_blank";
		document.form.submit ();';
	}
	
	echo "}";
	
	echo "</script>";
	echo("\n");
?>

<form name="form" method="get" target="_blank">
<table class="tableNone">
<tr class="title"><td width="100%" colspan="2"><?=$atlas['name']." -- ".$viewerName?>

</td></tr>
<tr>
	<td width="65%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td colspan="2">
			Map Parameters
		</td>
		</tr>
             <tr>
                      <td>VERSION</td>
                      <td>
                      <input type="text" name="VERSION" value="<?=SUAS_CFG_WMS_VERSION?>" readonly="readonly"  class="smallInput"></td>
              </tr>

          <tr>
             <td>SERVICE</td>
             <td>
             <input type="text" name="SERVICE" value="<?=SUAS_CFG_WMS_SERVICE?>" readonly="readonly" class="smallInput"></td>
          </tr>
                  <input type="hidden" name="BBOX" value="3444772">
                  <input type="hidden" name="LAYERS" value="LayerProblem">
                  <input type="hidden" name="STYLES" value="styleproblem">
              <tr>
                     <td>REQUEST</td>
                     <td>
                     <input type="text" name="REQUEST" value="GetMap" readonly="readonly"  class="smallInput"></td>
              </tr>
              <tr><td colspan="2">LAYERS: <td></tr>
             <tr align="left">
             <td></td>
			 <td>
            <table class="tableInNone" id="table_list_layers">
            <td><div class="begin"><a href="javascript: checkall_s(document.form.layer);">CheckAll</a></div></td>
			<td><div class="begin"><a href="javascript: uncheckAll_s(document.form.layer);">UncheckAll</a></div></td>
<?php
$rs3 = $database->getRowsBySrsGroupBy($aid, $map_parameters['srs'], 'layer');
$count = 1;
$num = $database->getRowsNumber($rs3);
while ($line3 = $database->getColumns($rs3)) {
	$data3 = $line3["layer"];
print '<tr ';
	if($count%2==0){
		print 'class="odd"';
	}else{
		print 'class="even"';
	}
print '>';	
	?>
                     <td><?=$data3?></td><td> <input type="checkbox" name="layer" value="<?=$data3?>"></td>
                  </tr>
<?php
++$count;
}

?>
         </table>
      <td></tr>
          <tr>
                  <td>STYLES</td>
                  <td>
                  <input type="text" name="style" value="default" readonly="readonly"  class="smallInput"></td>
          </tr>
            <tr>
                <td>SRS SELECTED IS:</td>
                <td>
                <input type= "text" name="SRS" value="<?=$map_parameters['srs']?>" readonly="readonly" class="smallInput"></td>
            </tr>


    <?php
$rs4 = $database->getRowsMinMaxXYBySrs($aid, $map_parameters['srs']);
$line4 = $database->getColumns($rs4);
$totalminx = $line4[0];
$totalminy = $line4[1];
$totalmaxx = $line4[2];
$totalmaxy = $line4[3];

?>

              <tr>
                 <td>MIN. EASTING</td>
                 <td>
                 <input type="text" name="minx" value="<?=$totalminx?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td>MIN. NORTHING</td>
                 <td>
                 <input type="text" name="miny" value="<?=$totalminy?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td>MAX. EASTING</td>
                 <td>
                 <input type="text" name="maxx" value="<?=$totalmaxx?>" class="smallInput"></td>
              </tr>

              <tr>
                 <td>MAX. NORTHING</td>
                 <td>
                 <input type="text" name="maxy" value="<?=$totalmaxy?>" class="smallInput"></td>
              </tr>


              <tr>
                 <td>WIDTH</td>
                 <td>
                 <input type="text" name="WIDTH" value="800" class="smallInput"></td>
              </tr>

              <tr>
                 <td width="170">HEIGHT</td>
                 <td>
                 <input type="text" name="HEIGHT" value="600" class="smallInput"></td>
              </tr>

              <tr>
                 <td>TRANSPARENT</td>
                 <td>
                 <SELECT name="TRANSPARENT">
				 <OPTION value="False" selected>False</OPTION>
				 <OPTION value="True">True</OPTION>
				 </SELECT>
              </tr>

              <tr>
                 <td>FORMAT</td>
                 <td>
				 <select name="FORMAT">
                    <optgroup label="Vector Image">
                       <option value="image/svg+xml">SVG</option>
                       <option value="image/svgt+xml">SVGT</option>
                       <option value="image/svgb+xml">SVGB</option>
                       <option value="image/svgz+xml">SVGZ</option>
                       <option value="image/svgtz+xml">SVGTZ</option>
                       <option value="image/svgbz+xml">SVGBZ</option>
                    </optgroup>
                    <optgroup label="Raster Image">
                       <option value="image/png">PNG</option>
                       <option value="image/jpeg">JPEG</option>
                       <option value="image/gif">GIF</option>
                       <option value="image/wbmp">WBMP</option>
                       <option value="image/bmp">BMP</option>
                       </optgroup>
                 </select>
				 </td>
              </tr>

              <tr>
                 <td>EXCEPTIONS</td>
                 <td>
				 <SELECT name="EXCEPTIONS">
				 <OPTION value=application/vnd.ogc.se_xml selected>application/vnd.ogc.se_xml</OPTION>
				 <OPTION value=application/vnd.ogc.se_inimage>application/vnd.ogc.se_inimage</OPTION>
				 </SELECT>
				 </td>
              </tr>

              <tr>
                 <td colspan="2">&nbsp;</td>
              </tr>

              <tr>
                 <td>
					<input onclick="javascript:window.location.href='atlas.php?op=<?
		if($from == 'demo'){
			echo  "demo_wmsextension_GetMapViewers&atlas_aid=".$aid."&aid=".$aid;
		}
		else if($from == 'view'){
			echo  "atlasview_viewer&atlas_aid=".$aid."&aid=".$aid.$_REQUEST['backurl'];
		}
		else if($from == 'favo'){
			echo  "atlasviewfavo_viewer&atlas_aid=".$aid."&aid=".$aid.$_REQUEST['backurl'];
		}
		?>'" name="button" value="Back" type="button" class="ui-button ui-state-default ui-corner-all">
		 		</td>
				<td align="right">
				<input type="hidden" name="aid" value="<?=$aid?>">
				<input type="hidden" name="op" value="demo_wmsextension_GetMapViewers">
                 </td>
              </tr>
          </table>
          
          <table class="block-panel ui-corner-all" style="width:100%;" align="top">
          <tr class="block-panel-header ui-corner-all">
			<td colspan="2">
				Viewer Parameters
			</td>
			</tr>
<?
if($viewertype == 'openlayers'){
?>			
             <tr>
                 <td>
                 <p id="intro">Openlayers Control Tools: </p>
                 ZoomLevel:
                 <SELECT name="OPZOOMLEVEL">
                 <OPTION value=1>1</OPTION>
                 <OPTION value=2>2</OPTION>
				 <OPTION value=3 selected>3</OPTION>
				 <OPTION value=4>4</OPTION>
				 <OPTION value=5>5</OPTION>
				 </SELECT><br>
                 <input type="checkbox" name="OPLayerSwitcher" value="1">LayerSwitcher<br>
                 <input type="checkbox" name="OPMouseDefaults" value="1">MouseDefaults<br>
                 <input type="checkbox" name="OPMousePosition" value="1">MousePosition<br>
                 <input type="checkbox" name="OPMouseToolbar" value="1">MouseToolbar<br>
                 <input type="checkbox" name="OPOverviewMap" value="1">OverviewMap<br>
                 <input type="checkbox" name="OPPanZoom" value="1">PanZoom<br>
                 <input type="checkbox" name="OPPanZoomBar" value="1">PanZoomBar<br>
                 <input type="checkbox" name="OPPermalink" value="1">Permalink<br>
                 <input type="checkbox" name="OPScale" value="1">Scale<br>
                 <input type="checkbox" name="OPDrawFeature" value="1">DrawFeature<br>
                 <input type="checkbox" name="OPGETFEATUREINFO" value="1">GetFeatureInfo <?print_warn("GetFeatureInfo", "Description", "Will not work when <b>One Layer One Layer is active</b>.");?><br>
                 <input type="checkbox" name="OPOPACITYCONTROL" value="1">Opcity Control <?print_help("Opcity Control", "Description", "User can set the transparency of the layers.");?><br>
				 <input type="checkbox" name="OPWFS" value="1">WFS Overlay<br>
				 </td>
                 <td>
                 <p id="intro">Combine With Other Layers: <?print_warn("Overlay Layer", "Description", "All of the layers listed below are using SRS with <b>EPSG:4326(WGS84)</b>, if your SRS used here is not the same(EPSG:4326), the overlaying layers will have exception.");?></p>
                 <input type="checkbox" name="OPSUASLayerAsOverlay" value="1">SUAS Layers as overlays <?print_help("SUAS Layers as overlays", "Description", "Use layers not as one separated layer, but overlayers on other map server.");?><br>
                 <input type="checkbox" name="OPSUASOneLayerOneLayer" value="1">One Layer One Layer<?print_help("One Layer One Layer", "Description", "If you have selected multi-layers, each layer will be displayed as one separated layer.");?><br>
                 <input type="checkbox" name="OPOpenLayersWMS" value="1">OpenLayers WMS<br>
                 <input type="checkbox" name="OPOpenPlansWMS" value="1">OpenPlans WMS<br>
                 <input type="checkbox" name="OPMultiMap" value="1">MultiMap<br>
                 <input type="checkbox" name="OPNASAWORLDWIND" value="1">NASA World Wind<br>
                 <input type="checkbox" name="OPGoogleMap" value="1">Google Map (Need GoogleMap Key)<?print_help("Google Map Key", "Description", "You need your own Google Map Key to brower Google Map. Please go to <b>Atlas->Configuration->Google Map Key</b> and give the key depending on the <b>Server URL</b>.");?><br>
                 <input type="checkbox" name="OPGoogleSatellite" value="1">Google Satellite<br>
                 <input type="checkbox" name="OPGoogleHybrid" value="1">Google Hybrid<br>
                 <input type="checkbox" name="OPVirtualEarth" value="1">Virtual Earth<br>
                 <input type="checkbox" name="OPYahooMap" value="1">Yahoo Map<br>            
                 <input type="checkbox" name="OPOSMMapMapnik" value="1">OpenStreetMap Mapnik<br>
                 <input type="checkbox" name="OPOSMMapOsmarender" value="1">OpenStreetMap Osmarender<br>
                 <input type="checkbox" name="OPOSMMapCycleMap" value="1">OpenStreetMap CycleMap<br>
                 </td>
              </tr>
<?
}
elseif($viewertype == 'cartosvg'){
?>
				<tr>
                 <td></td>
                 <td>
                 &nbsp;
                 </td>
              </tr>
<?
}
elseif($viewertype == 'geoxml'){
?>
				<tr>
                 <td>
                 <label for="NAV_TYPE">Max Features: </label>
		<input type="text" name="maxfeatures" value="100" class="smallInput"></td>
		</td>
                 <td>
                 &nbsp;
                 </td>
              </tr>
<?
}
elseif($viewertype == 'gmapswms'){
?>
				<tr>
                 <td>
                 </td>
                 <td>
                 &nbsp;
                 </td>
              </tr>
<?
}
elseif($viewertype == 'g3dg2d'){
?>
				<tr>
                 <td>
                 <label for="NAV_TYPE">Navigation Type: </label>
                 <SELECT name="NAV_TYPE">
                 <OPTION value="car" selected>car</OPTION>
                 <OPTION value="hiking">hiking</OPTION>
				 <OPTION value="boating">boating</OPTION>
				 <OPTION value="roadbiking">roadbiking</OPTION>
				 <OPTION value="mountainbiking">mountainbiking</OPTION>
				 <OPTION value="urbanhiking-sightseeing">urbanhiking-sightseeing</OPTION>
				 </SELECT>
                 </td>
                 <td>
                 &nbsp;
                 </td>
              </tr>
<?
}
?>	              
             <tr>
                 <td></td>
                 <td align="right">
                 <input onclick="chkform();" type="button" value="Open Viewer" class="ui-button ui-state-default ui-corner-all">
                 </td>
              </tr>
            </table>
            
            
    </td>      
            
	<td width="35%" valign="top">
	<table class="block-panel ui-corner-all" style="width:100%;" align="top">
		<tr class="block-panel-header ui-corner-all">
		<td>Options</td>
		</tr>
		<tr>
		<td>
		Description:
		<p>
		
		</p>
		</td>
		</tr>
	</table>
	</td>
</tr>

</table>
</form>
<?
	
}
?>