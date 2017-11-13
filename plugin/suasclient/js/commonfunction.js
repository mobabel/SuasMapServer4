/**
 *
 * @access public
 * @return void
 **/
function txtfieldSelectAll(e){
	try{
		e.focus();
		e.select();
	}catch(ex){
		debug(ex);
	}
}