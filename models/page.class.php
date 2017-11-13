<?php
/**
 * filename: ext_page.class.php
 * @package:phpbean
 * @author :feifengxlq<feifengxlq#gmail.com>
 * @copyright :Copyright 2006 feifengxlq
 * @license:version 2.0
 * @create:2006-5-31
 * @modify:2006-6-1
 * @modify:feifengxlq 2006-11-4
 * description:
 * 2.0
 * to see detail,please visit http://www.phpobject.net/blog/read.php
 * http://www.21andy.com/blog/20071219/726.html
 * example:
 * how to use:
 require_once('../libs/classes/page.class.php');
 $page=new page(array('total'=>1000,'perpage'=>20));
 echo 'mode:1<br>'.$page->show();
 echo '<hr>mode:2<br>'.$page->show(2);
 echo '<hr>mode:3<br>'.$page->show(3);
 echo '<hr>mode:4<br>'.$page->show(4);
 open AJAX:
 $ajaxpage=new page(array('total'=>1000,'perpage'=>20,'ajax'=>'ajax_page','page_name'=>'test'));
 echo 'mode:1<br>'.$ajaxpage->show();
 
 demo:[url=http://www.phpobject.net/blog]http://www.phpobject.net/blog[/url]
 */
class page
{
	/**
	 * config ,public
	 */
	var $page_name="pg";//page parameter xxx.php?PB_page=2
	var $next_page='>';//next page
	var $pre_page='<';//prevoius page
	var $first_page='First';//first page
	var $last_page='Last';//last page
	var $pre_bar='<<';//
	var $next_bar='>>';//
	var $format_left=' ';
	var $format_right=' ';
	var $is_ajax=false;//
	
	/**
	 * private
	 *
	 */
	var $pagebarnum=10;//
	var $totalpage=0;//
	var $ajax_action_name='';//
	var $nowindex=1;//current page
	var $url="";//url 
	var $offset=0;
	
	/**
	 * constructor
	 *
	 * @param array $array['total'],$array['perpage'],$array['nowindex'],$array['url'],$array['ajax']...
	 */
	function page($array)
		{
		if(is_array($array)){
			if(!array_key_exists('total',$array))$this->error(__FUNCTION__,'need a param of total');
			$total=intval($array['total']);
			$perpage=(array_key_exists('perpage',$array))?intval($array['perpage']):10;
			$nowindex=(array_key_exists('nowindex',$array))?intval($array['nowindex']):'';
			$url=(array_key_exists('url',$array))?$array['url']:'';
		}else{
			$total=$array;
			$perpage=10;
			$nowindex='';
			$url='';
		}
		if((!is_int($total))||($total<0))$this->error(__FUNCTION__,$total.' is not a positive integer!');
		if((!is_int($perpage))||($perpage<=0))$this->error(__FUNCTION__,$perpage.' is not a positive integer!');
		if(!empty($array['page_name']))$this->set('page_name',$array['page_name']);//set pagename
		$this->_set_nowindex($nowindex);//set current page
		$this->_set_url($url);//set url
		$this->totalpage=ceil($total/$perpage);
		$this->offset=($this->nowindex-1)*$perpage;
		if(!empty($array['ajax']))$this->open_ajax($array['ajax']);//open ajax mode
		}
	/**
	 * 
	 *
	 * @param string $var
	 * @param string $value
	 */
	function set($var,$value)
		{
		if(in_array($var,get_object_vars($this)))
			$this->$var=$value;
		else {
			$this->error(__FUNCTION__,$var." does not belong to PB_Page!");
		}
		
		}
	/* function set($var,$value)
	 {
	 if(array_key_exists($var,get_object_vars($this)))
	 $this->$var=$value; //$this->$var should be $this->page_name
	 else {
	 $this->error(__FUNCTION__,$var." does not belong to PB_Page!");
	 }
	 }*/
	/**
	 * 
	 *
	 * @param string $action
	 */
	function open_ajax($action)
		{
		$this->is_ajax=true;
		$this->ajax_action_name=$action;
		}
	/**
	 * 
	 *
	 * @param string $style
	 * @return string
	 */
	function next_page($style='cui-icon cui-icon-seek-next')
		{
		if($this->nowindex<$this->totalpage){
			return $this->_get_link($this->_get_url($this->nowindex+1),$this->next_page,$style,"Next Page");
		}
		return '<span class="'.$style.'" title="Next Page">'.$this->next_page.'</span>';
		}
	
	/**
	 * 
	 *
	 * @param string $style
	 * @return string
	 */
	function pre_page($style='cui-icon cui-icon-seek-prev')
		{
		if($this->nowindex>1){
			return $this->_get_link($this->_get_url($this->nowindex-1),$this->pre_page,$style, "Previous Page");
		}
		return '<span class="'.$style.'" title="Previous Page">'.$this->pre_page.'</span>';
		}
	
	/**
	 * 
	 *
	 * @return string
	 */
	function first_page($style='cui-icon cui-icon-seek-first')
		{
		if($this->nowindex==1){
			return '<span class="'.$style.'" title="First Page">'.$this->first_page.'</span>';
		}
		return $this->_get_link($this->_get_url(1),$this->first_page,$style, "First Page");
		}
	
	/**
	 * 
	 *
	 * @return string
	 */
	function last_page($style='cui-icon cui-icon-seek-end')
		{
		if($this->nowindex==$this->totalpage){
			return '<span class="'.$style.'" title="Last Page">'.$this->last_page.'</span>';
		}
		return $this->_get_link($this->_get_url($this->totalpage),$this->last_page,$style, "Last Page");
		}
	
	function nowbar($style='cpage',$nowindex_style='cpagenow')
		{
		$plus=ceil($this->pagebarnum/2);
		if($this->pagebarnum-$plus+$this->nowindex>$this->totalpage)$plus=($this->pagebarnum-$this->totalpage+$this->nowindex);
		$begin=$this->nowindex-$plus+1;
		$begin=($begin>=1)?$begin:1;
		$return='';
		for($i=$begin;$i<$begin+$this->pagebarnum;$i++)
		{
			if($i<=$this->totalpage){
				if($i!=$this->nowindex)
					$return.=$this->_get_text($this->_get_link($this->_get_url($i),$i,$style));
				else
					$return.=$this->_get_text('<span class="'.$nowindex_style.'">'.$i.'</span>');
			}else{
				break;
			}
			$return.="\n";
		}
		unset($begin);
		return $return;
		}
	/**
	 * get jump button page
	 *
	 * @return string
	 */
	function select()
		{
		$return='<select name="PB_Page_Select">';
		for($i=1;$i<=$this->totalpage;$i++)
		{
			if($i==$this->nowindex){
				$return.='<option value="'.$i.'" selected>'.$i.'</option>';
			}else{
				$return.='<option value="'.$i.'">'.$i.'</option>';
			}
		}
		unset($i);
		$return.='</select>';
		return $return;
		}
	
	function select_ajax($url)
		{
		if($this->is_ajax){
			$return= $this->ajax_action_name.'(\''.$this->url.'\'+this.options[this.options.selectedIndex].value)">';
		}
		else{
			$return='';
		}
		for($i=1;$this->totalpage;$i++)
		{
			if($i==$this->nowindex){
				$return.=''.$i.'';
			}else{
				$return.=''.$i.'';
			}
		}
		unset($i);
		$return.='';
		return $return;
		}
	
	/**
	 * 
	 *
	 * @return string
	 */
	function offset()
		{
		return $this->offset;
		}
	
	/**
	 * 
	 *
	 * @param int $mode
	 * @return string
	 */
	function show($mode=1)
		{
		switch ($mode)
		{
			case '1':
				$this->next_page='Next Page';
				$this->pre_page='Previous Page';
				return $this->pre_page().$this->nowbar().$this->next_page().''.$this->select().' Page';
				break;
			case '2':
				$this->next_page='Next Page';
				$this->pre_page='Previous Page';
				$this->first_page='First Page';
				$this->last_page='Last Page';
				return $this->first_page().$this->pre_page().'['.$this->nowindex.' Page]'.$this->next_page().$this->last_page().''.$this->select().'Page';
				break;
			case '3':
				$this->next_page='Next Page';
				$this->pre_page='Previous Page';
				$this->first_page='First Page';
				$this->last_page='Last Page';
				return $this->first_page().$this->pre_page().$this->next_page().$this->last_page();
				break;
			case '4':
				$this->next_page='Next Page';
				$this->pre_page='Previous Page';
				return $this->pre_page().$this->nowbar().$this->next_page();
				break;
			case '5':
				return $this->pre_bar().$this->pre_page().$this->nowbar().$this->next_page().$this->next_bar();
				break;
			case '6':
				$this->next_page='';
				$this->pre_page='';
				$this->first_page='';
				$this->last_page='';
				return 
				$this->first_page().$this->pre_page().$this->nowbar().$this->next_page().$this->last_page();
		}
		
		}
	/*----------------private function -----------------------------------------------------------*/
	/**
	 * 
	 * @param: String $url
	 * @return boolean
	 */
	function _set_url($url="")
		{
		if(!empty($url)){
			//set manually
			$this->url=$url.((stristr($url,'?'))?'&':'?').$this->page_name."=";
		}else{
			//set automatically
			if(empty($_SERVER['QUERY_STRING'])){
				//when no QUERY_STRING
				$this->url=$_SERVER['REQUEST_URI']."?".$this->page_name."=";
			}else{
				//
				if(stristr($_SERVER['QUERY_STRING'],$this->page_name.'=')){
					// the parameter when has the url
					$this->url=str_replace($this->page_name.'='.$this->nowindex,'',$_SERVER['REQUEST_URI']);
					$last=$this->url[strlen($this->url)-1];
					if($last=='?'||$last=='&'){
						$this->url.=$this->page_name."=";
					}else{
						$this->url.='&'.$this->page_name."=";
					}
				}else{
					//
					$this->url=$_SERVER['REQUEST_URI'].'&'.$this->page_name.'=';
				}//end if   
			}//end if
		}//end if
		}
	
	/**
	 * 
	 *
	 */
	function _set_nowindex($nowindex)
		{
		if(empty($nowindex)){
			//set automatically
			
			if(isset($_GET[$this->page_name])){
				$this->nowindex=intval($_GET[$this->page_name]);
			}
		}else{
			//set manually
			$this->nowindex=intval($nowindex);
		}
		}
	
	/**
	 * return url for some page
	 *
	 * @param int $pageno
	 * @return string $url
	 */
	function _get_url($pageno=1)
		{
		return $this->url.$pageno;
		}
	
	/**
	 * default: _get_text('<a href="">1</a>')will return [<a href="">1</a>]
	 *
	 * @param String $str
	 * @return string $url
	 */
	function _get_text($str)
		{
		return $this->format_left.$str.$this->format_right;
		}
	
	/**
	 * get url
	 */
	function _get_link($url,$text,$style='', $title=''){
		$style=(empty($style))?'':'class="'.$style.'"';
		if($this->is_ajax){
			//if use ajax
			return '<a '.$style.' href="javascript:'.$this->ajax_action_name.'(\''.$url.'\')" title="'.$title.'">'.$text.'</a>';
		}else{
			return '<a '.$style.' href="'.$url.'" title="'.$title.'">'.$text.'</a>';
		}
	}
	/**
	 * handle error
	 */
	function error($function,$errormsg)
		{
		die('Error in file <b>'.__FILE__.'</b> ,Function <b>'.$function.'()</b> :'.$errormsg);
		}
}
?>