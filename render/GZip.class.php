<?
/**********************************************************************************

 gzip - PHP gzip compression library
               Version 1.3

 Copyright (c) 2001, 2002 Moiseenko V. Maxim <m.moiseenko@sys4tec.com>
 All Rights Reserved.

 This library is free software; you can redistribute it and/or modify it
 under the terms of the GNU Lesser General Public License as published
 by the Free Software Foundation; either version 2.1 of the License, or any
 later version.

 This library is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public
 License for more details.



 Official ZIP file format: http://www.pkware.com/appnote.txt
**********************************************************************************/
/*
$path = "test.gz";
$filedata = "some file content";

# new object
$gz = new gzip();


# step 1
# add data to archive
$gz->add( $filedata, $path );
# and write gzip file
$gz->write_file($path);



#step 2
# extracting content from archive
if( $g = $gz->extract($path) ){
  # print result structure
  print_r($g);
*/

define("_GZIP",true);
define("_GZIP_VER", 1.3);
define("_GZIP_BUILD", '03.04.2002');


###################################################################################
class GZip {

  # Array to store compressed data
  # private string[]
  var $_datasec = array();

  # public boolean
  var $debug = true;


  /******************************************************************************
  *
  * public void
  */
  function GZip(){
  }



  /******************************************************************************
  * Adds "file content" to archive
  *
  * public void
  * string @data - file contents
  * string @name - name of the file in the archive (may contains the path)
  */
  function add($data, $name){
    $unc_len = strlen($data);
    $crc     = crc32($data);
    $zdata = gzdeflate($data,9);
    $c_len = strlen($zdata);

    $fr=
      "\x1f".                    # ID1                              1
      "\x8B".                    # ID2                              1
      "\x08".                    # Compression Method "deflate"     1
      "\x08".                    # FLaGs "FNAME"                    1
      "\x00\x00\x00\x00".        # last mod time & date             4
      "\x00".                    # eXtra FLags "2"-max "4"-fast     1
      "\x00".                    # OS "\x00" - FAT                  1
      $name.                     # orig. file name                var
      "\x00".                    # zero term.                       1
      $zdata.
      $this->_pack($crc, 4).      # crc32                           4
      $this->_pack($unc_len, 4);  # uncompressed filesize           4


    $this->_datasec[] = $fr;
  }




  /********************************************************************************
  *
  * public object
  */
  function extract($name){
    if(!file_exists($name))return null;
    $fd = fopen($name,'rb');
    if(! $content = fread($fd, filesize($name)) ) return null;
    @fclose($fd);


    $ret = new stdClass;

    #
    $ret->part = array();




    #
    $pointer=0;
    #
    $fpointer = 0;
    $ret->part[$fpointer]->head = array();


    if("\x1f\x8b" != substr($content, $pointer,2) ){
      $this->_debug("It's not .gzip format");
      return null;
    }
    $pointer+=2;

    if("\x08" != substr($content, $pointer,1) ){
      $this->_debug("Compression method must be 'deflate'");
      return null;
    }
    $pointer++;


    # This flag byte is divided into individual bits as follows:
    # bit 0   FTEXT
    # bit 1   FHCRC
    # bit 2   FEXTRA
    # bit 3   FNAME
    # bit 4   FCOMMENT
    switch( substr($content, $pointer,1) ){
      #~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      # FNAME
      case "\x08":

        $pointer++;

        # Modification time
        $ret->part[$fpointer]->head['mod_time'] =
          $this->_unpack( substr($content, $pointer,2) );
        $pointer+=2;

        # Modification date
        $ret->part[$fpointer]->head['mod_date'] =
          $this->_unpack( substr($content, $pointer,2) );
        $pointer+=2;

        # eXtra FLags
        # 2 - compressor used maximum compression, slowest algorithm
        # 4 - compressor used fastest algorithm
        $ret->part[$fpointer]->head['xfl'] =
          $this->_unpack( substr($content, $pointer,1) );
        $pointer++;

        # Operating System
        # 0 - FAT filesystem (MS-DOS, OS/2, NT/Win32)
        # 3 - Unix
        # 7 - Macintosh
        # 11 - NTFS filesystem (NT)
        # 255 - unknown
        $ret->part[$fpointer]->head['os'] = $this->_unpack( substr($content, $pointer,1) );
        $pointer++;

        #file name
        for($ret->part[$fpointer]->head['file_name']=""; substr($content, $pointer,1) != "\x00"; $pointer++)
          $ret->part[$fpointer]->head['file_name'] .= substr($content, $pointer,1);
        $pointer++;

        # compressed blocks...
        $zdata = substr($content, $pointer, -8);
        $pointer = strlen($content) - 8;

        # Cyclic Redundancy Check
        $ret->part[$fpointer]->head['crc32'] =
          $this->_unpack( substr($content, $pointer,4) );
        $pointer+=4;

        # size of the original (uncompressed) input data modulo 2^32
        $ret->part[$fpointer]->head['uncompressed_filesize'] =
          $this->_unpack( substr($content, $pointer,4) );
        $pointer+=4;


        # decompress data and store it at array
        $ret->part[$fpointer]->body = gzinflate($zdata);

        break;


      #~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      default:
        return null;

    }#switch

    return $ret;
  }









  /******************************************************************************
  * Dumps out file
  *
  * public string - the gzipped file
  */
  function file(){
    $data = implode('', $this->_datasec);
    return $data;
  }




  /******************************************************************************
  * print error message
  * public void
  */
  function _debug($str){
    if($this->debug) echo $str;
  }




  /********************************************************************************
  * pack data into binary string
  * private string
  */
  function _pack($val, $bytes=2){
    for($ret='', $i=0; $i<$bytes; $i++, $val=floor($val/256) )
      $ret .= chr($val % 256);
    return $ret;
  }


  /********************************************************************************
  * unpack data from binary string
  * private string
  */
  function _unpack($val){
    for($len = strlen($val), $ret=0, $i=0; $i < $len; $i++)
      $ret += (int)ord(substr($val,$i,1)) * pow(2, 8 * $i);
    return $ret;
  }



  /********************************************************************************
  *
  * public boolean
  */
  function add_file($name, $binary=false){
    if(!file_exists($name))return false;
    $fd = $binary? fopen($name,'rb') : fopen($name,'r');
    if(! $content = fread($fd, filesize($name)) )return false;
    fclose($fd);

    $this->add($content,$name);
    return true;
  }


  /********************************************************************************
  *
  * public int
  */
  function write_file($name){
    $size = -1;
    if( $fd=fopen($name,'wb') ){
      $size = fwrite($fd,$this->file());
      fclose($fd);
    }
    return $size;
  }

    /********************************************************************************
  *
  * print the gzip, for example svgz on the screen
  */
  function print_file(){
    echo $this->file();
  }


}#class
###################################################################################
?>