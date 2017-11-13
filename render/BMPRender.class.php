<?php
/**
 * Create BMP image
 *
 * @author : legend(legendsky@hotmail.com)
 * @link
 * @description : create Bitmap-File with GD library
 * @version : 0.1
 * @param resource $im image source
 * @param string $filename save as, if empty, output to browser
 * @param integer $bit image quality(1、4、8、16、24、32bit)
 * @param integer $compression copression method, 0 nocompression��1 use RLE8 algrithm to compress
 * @return integer
 */
function imagebmp(&$im, $filename = '', $bit = 8, $compression = 0)
{
    if (!in_array($bit, array(1, 4, 8, 16, 24, 32))) {
        $bit = 8;
    } else if ($bit == 32) { // todo:32 bit
        $bit = 24;
    }

    $bits = pow(2, $bit);
    // adjust colour palette
    imagetruecolortopalette($im, true, $bits);
    $width = imagesx($im);
    $height = imagesy($im);
    $colors_num = imagecolorstotal($im);

    if ($bit <= 8) {
        // colour index
        $rgb_quad = '';
        for ($i = 0; $i < $colors_num; $i ++) {
            $colors = imagecolorsforindex($im, $i);
            $rgb_quad .= chr($colors['blue']) . chr($colors['green']) . chr($colors['red']) . "\0";
        }
        // bmp data
        $bmp_data = '';
        // no compression
        if ($compression == 0 || $bit < 8) {
            if (!in_array($bit, array(1, 4, 8))) {
                $bit = 8;
            }

            $compression = 0;
            // each line's bytes must be 4 times number
            $extra = '';
            $padding = 4 - ceil($width / (8 / $bit)) % 4;
            if ($padding % 4 != 0) {
                $extra = str_repeat("\0", $padding);
            }

            for ($j = $height - 1; $j >= 0; $j --) {
                $i = 0;
                while ($i < $width) {
                    $bin = 0;
                    $limit = $width - $i < 8 / $bit ? (8 / $bit - $width + $i) * $bit : 0;

                    for ($k = 8 - $bit; $k >= $limit; $k -= $bit) {
                        $index = imagecolorat($im, $i, $j);
                        $bin |= $index << $k;
                        $i ++;
                    }

                    $bmp_data .= chr($bin);
                }

                $bmp_data .= $extra;
            }
        }
        // RLE8 compression
        else if ($compression == 1 && $bit == 8) {
            for ($j = $height - 1; $j >= 0; $j --) {
                $last_index = "\0";
                $same_num = 0;
                for ($i = 0; $i <= $width; $i ++) {
                    $index = imagecolorat($im, $i, $j);
                    if ($index !== $last_index || $same_num > 255) {
                        if ($same_num != 0) {
                            $bmp_data .= chr($same_num) . chr($last_index);
                        }

                        $last_index = $index;
                        $same_num = 1;
                    } else {
                        $same_num ++;
                    }
                }

                $bmp_data .= "\0\0";
            }

            $bmp_data .= "\0\1";
        }

        $size_quad = strlen($rgb_quad);
        $size_data = strlen($bmp_data);
    } else {
        // each line's bytes must be 4 times number
        $extra = '';
        $padding = 4 - ($width * ($bit / 8)) % 4;
        if ($padding % 4 != 0) {
            $extra = str_repeat("\0", $padding);
        }
        // bmp data
        $bmp_data = '';

        for ($j = $height - 1; $j >= 0; $j --) {
            for ($i = 0; $i < $width; $i ++) {
                $index = imagecolorat($im, $i, $j);
                $colors = imagecolorsforindex($im, $index);

                if ($bit == 16) {
                    $bin = 0 << $bit;

                    $bin |= ($colors['red'] >> 3) << 10;
                    $bin |= ($colors['green'] >> 3) << 5;
                    $bin |= $colors['blue'] >> 3;

                    $bmp_data .= pack("v", $bin);
                } else {
                    $bmp_data .= pack("c*", $colors['blue'], $colors['green'], $colors['red']);
                }
                // todo: 32bit;
            }

            $bmp_data .= $extra;
        }

        $size_quad = 0;
        $size_data = strlen($bmp_data);
        $colors_num = 0;
    }
    // image header
    $file_header = "BM" . pack("V3", 54 + $size_quad + $size_data, 0, 54 + $size_quad);
    // image header info
    $info_header = pack("V3v2V*", 0x28, $width, $height, 1, $bit, $compression, $size_data, 0, 0, $colors_num, 0);
    // write image
    if ($filename != '') {
        $fp = fopen($filename, "wb");

        fwrite($fp, $file_header);
        fwrite($fp, $info_header);
        fwrite($fp, $rgb_quad);
        fwrite($fp, $bmp_data);
        fclose($fp);

        return 1;
    }
    // output to browser
    // header("Content-Type: image/bmp");
    echo $file_header . $info_header;
    echo $rgb_quad;
    echo $bmp_data;

    return 1;
}

/*
*------------------------------------------------------------
*                   BMP Image functions
*------------------------------------------------------------
*                      By JPEXS
*------------------------------------------------------------
*                    ImageBMP
*------------------------------------------------------------
*            - Creates new BMP file
*
*         Parameters:  $img - Target image
*                      $file - Target file to store
*                            - if not specified, bmp is returned
*
*           Returns: if $file specified - true if OK
                     if $file not specified - image data
* z.cz/?page=php&Language=eng
*/
function imagebmp_($img, $file = "", $RLE = 0)
{
    $ColorCount = imagecolorstotal($img);

    $Transparent = imagecolortransparent($img);
    $IsTransparent = $Transparent != -1;

    if ($IsTransparent) $ColorCount--;

    if ($ColorCount == 0) {
        $ColorCount = 0;
        $BitCount = 24;
    } ;
    if (($ColorCount > 0)and($ColorCount <= 2)) {
        $ColorCount = 2;
        $BitCount = 1;
    } ;
    if (($ColorCount > 2)and($ColorCount <= 16)) {
        $ColorCount = 16;
        $BitCount = 4;
    } ;
    if (($ColorCount > 16)and($ColorCount <= 256)) {
        $ColorCount = 0;
        $BitCount = 8;
    } ;

    $Width = imagesx($img);
    $Height = imagesy($img);

    $Zbytek = (4 - ($Width / (8 / $BitCount)) % 4) % 4;

    if ($BitCount < 24) $palsize = pow(2, $BitCount) * 4;

    $size = (floor($Width / (8 / $BitCount)) + $Zbytek) * $Height + 54;
    $size += $palsize;
    $offset = 54 + $palsize;
    // Bitmap File Header
    $ret = 'BM'; // header (2b)
    $ret .= int_to_dword($size); // size of file (4b)
    $ret .= int_to_dword(0); // reserved (4b)
    $ret .= int_to_dword($offset); // byte location in the file which is first byte of IMAGE (4b)
    // Bitmap Info Header
    $ret .= int_to_dword(40); // Size of BITMAPINFOHEADER (4b)
    $ret .= int_to_dword($Width); // width of bitmap (4b)
    $ret .= int_to_dword($Height); // height of bitmap (4b)
    $ret .= int_to_word(1); // biPlanes = 1 (2b)
    $ret .= int_to_word($BitCount); // biBitCount = {1 (mono) or 4 (16 clr ) or 8 (256 clr) or 24 (16 Mil)} (2b)
    $ret .= int_to_dword($RLE); // RLE COMPRESSION (4b)
    $ret .= int_to_dword(0); // width x height (4b)
    $ret .= int_to_dword(0); // biXPelsPerMeter (4b)
    $ret .= int_to_dword(0); // biYPelsPerMeter (4b)
    $ret .= int_to_dword(0); // Number of palettes used (4b)
    $ret .= int_to_dword(0); // Number of important colour (4b)
    // image data
    $CC = $ColorCount;
    $sl1 = strlen($ret);
    if ($CC == 0) $CC = 256;
    if ($BitCount < 24) {
        $ColorTotal = imagecolorstotal($img);
        if ($IsTransparent) $ColorTotal--;

        for($p = 0;$p < $ColorTotal;$p++) {
            $color = imagecolorsforindex($img, $p);
            $ret .= inttobyte($color["blue"]);
            $ret .= inttobyte($color["green"]);
            $ret .= inttobyte($color["red"]);
            $ret .= inttobyte(0); //RESERVED
        } ;

        $CT = $ColorTotal;
        for($p = $ColorTotal;$p < $CC;$p++) {
            $ret .= inttobyte(0);
            $ret .= inttobyte(0);
            $ret .= inttobyte(0);
            $ret .= inttobyte(0); //RESERVED
        } ;
    } ;

    if ($BitCount <= 8) {
        for($y = $Height-1;$y >= 0;$y--) {
            $bWrite = "";
            for($x = 0;$x < $Width;$x++) {
                $color = imagecolorat($img, $x, $y);
                $bWrite .= decbinx($color, $BitCount);
                if (strlen($bWrite) == 8) {
                    $retd .= inttobyte(bindec($bWrite));
                    $bWrite = "";
                } ;
            } ;

            if ((strlen($bWrite) < 8)and(strlen($bWrite) != 0)) {
                $sl = strlen($bWrite);
                for($t = 0;$t < 8 - $sl;$t++)
                $sl .= "0";
                $retd .= inttobyte(bindec($bWrite));
            } ;
            for($z = 0;$z < $Zbytek;$z++)
            $retd .= inttobyte(0);
        } ;
    } ;

    if (($RLE == 1)and($BitCount == 8)) {
        for($t = 0;$t < strlen($retd);$t += 4) {
            if ($t != 0)
                if (($t) % $Width == 0)
                    $ret .= chr(0) . chr(0);

                if (($t + 5) % $Width == 0) {
                    $ret .= chr(0) . chr(5) . substr($retd, $t, 5) . chr(0);
                    $t += 1;
                }
                if (($t + 6) % $Width == 0) {
                    $ret .= chr(0) . chr(6) . substr($retd, $t, 6);
                    $t += 2;
                } else {
                    $ret .= chr(0) . chr(4) . substr($retd, $t, 4);
                } ;
            } ;
            $ret .= chr(0) . chr(1);
        } else {
            $ret .= $retd;
        } ;

        if ($BitCount == 24) {
            for($z = 0;$z < $Zbytek;$z++)
            $Dopl .= chr(0);

            for($y = $Height-1;$y >= 0;$y--) {
                for($x = 0;$x < $Width;$x++) {
                    $color = imagecolorsforindex($img, ImageColorAt($img, $x, $y));
                    $ret .= chr($color["blue"]) . chr($color["green"]) . chr($color["red"]);
                }
                $ret .= $Dopl;
            } ;
        } ;

        if ($file != "") {
            $r = ($f = fopen($file, "w"));
            $r = $r and fwrite($f, $ret);
            $r = $r and fclose($f);
            return $r;
        } else {
            echo $ret;
        } ;
    } ;

    /*
*------------------------------------------------------------
*                    ImageCreateFromBmp
*------------------------------------------------------------
*            - Reads image from a BMP file
*
*         Parameters:  $file - Target file to load
*
*            Returns: Image ID
*
*/

    function imagecreatefrombmp($file)
    {
        global $CurrentBit, $echoMode;

        $f = fopen($file, "r");
        $Header = fread($f, 2);

        if ($Header == "BM") {
            $Size = freaddword($f);
            $Reserved1 = freadword($f);
            $Reserved2 = freadword($f);
            $FirstByteOfImage = freaddword($f);

            $SizeBITMAPINFOHEADER = freaddword($f);
            $Width = freaddword($f);
            $Height = freaddword($f);
            $biPlanes = freadword($f);
            $biBitCount = freadword($f);
            $RLECompression = freaddword($f);
            $WidthxHeight = freaddword($f);
            $biXPelsPerMeter = freaddword($f);
            $biYPelsPerMeter = freaddword($f);
            $NumberOfPalettesUsed = freaddword($f);
            $NumberOfImportantColors = freaddword($f);

            if ($biBitCount < 24) {
                $img = imagecreate($Width, $Height);
                $Colors = pow(2, $biBitCount);
                for($p = 0;$p < $Colors;$p++) {
                    $B = freadbyte($f);
                    $G = freadbyte($f);
                    $R = freadbyte($f);
                    $Reserved = freadbyte($f);
                    $Palette[] = imagecolorallocate($img, $R, $G, $B);
                } ;

                if ($RLECompression == 0) {
                    $Zbytek = (4 - ceil(($Width / (8 / $biBitCount))) % 4) % 4;

                    for($y = $Height-1;$y >= 0;$y--) {
                        $CurrentBit = 0;
                        for($x = 0;$x < $Width;$x++) {
                            $C = freadbits($f, $biBitCount);
                            imagesetpixel($img, $x, $y, $Palette[$C]);
                        } ;
                        if ($CurrentBit != 0) {
                            freadbyte($f);
                        } ;
                        for($g = 0;$g < $Zbytek;$g++)
                        freadbyte($f);
                    } ;
                } ;
            } ;

            if ($RLECompression == 1) { // $BI_RLE8
                    $y = $Height;

                $pocetb = 0;

                while (true) {
                    $y--;
                    $prefix = freadbyte($f);
                    $suffix = freadbyte($f);
                    $pocetb += 2;

                    $echoit = false;

                    if ($echoit)echo "Prefix: $prefix Suffix: $suffix<BR>";
                    if (($prefix == 0)and($suffix == 1)) break;
                    if (feof($f)) break;

                    while (!(($prefix == 0)and($suffix == 0))) {
                        if ($prefix == 0) {
                            $pocet = $suffix;
                            $Data .= fread($f, $pocet);
                            $pocetb += $pocet;
                            if ($pocetb % 2 == 1) {
                                freadbyte($f);
                                $pocetb++;
                            } ;
                        } ;
                        if ($prefix > 0) {
                            $pocet = $prefix;
                            for($r = 0;$r < $pocet;$r++)
                            $Data .= chr($suffix);
                        } ;
                        $prefix = freadbyte($f);
                        $suffix = freadbyte($f);
                        $pocetb += 2;
                        if ($echoit) echo "Prefix: $prefix Suffix: $suffix<BR>";
                    } ;

                    for($x = 0;$x < strlen($Data);$x++) {
                        imagesetpixel($img, $x, $y, $Palette[ord($Data[$x])]);
                    } ;
                    $Data = "";
                } ;
            } ;

            if ($RLECompression == 2) { // $BI_RLE4
                    $y = $Height;
                $pocetb = 0;

                /*while(!feof($f))
 echo freadbyte($f)."_".freadbyte($f)."<BR>";*/
                while (true) {
                    // break;
                    $y--;
                    $prefix = freadbyte($f);
                    $suffix = freadbyte($f);
                    $pocetb += 2;

                    $echoit = false;

                    if ($echoit)echo "Prefix: $prefix Suffix: $suffix<BR>";
                    if (($prefix == 0)and($suffix == 1)) break;
                    if (feof($f)) break;

                    while (!(($prefix == 0)and($suffix == 0))) {
                        if ($prefix == 0) {
                            $pocet = $suffix;

                            $CurrentBit = 0;
                            for($h = 0;$h < $pocet;$h++)
                            $Data .= chr(freadbits($f, 4));
                            if ($CurrentBit != 0) freadbits($f, 4);
                            $pocetb += ceil(($pocet / 2));
                            if ($pocetb % 2 == 1) {
                                freadbyte($f);
                                $pocetb++;
                            } ;
                        } ;
                        if ($prefix > 0) {
                            $pocet = $prefix;
                            $i = 0;
                            for($r = 0;$r < $pocet;$r++) {
                                if ($i % 2 == 0) {
                                    $Data .= chr($suffix % 16);
                                } else {
                                    $Data .= chr(floor($suffix / 16));
                                } ;
                                $i++;
                            } ;
                        } ;
                        $prefix = freadbyte($f);
                        $suffix = freadbyte($f);
                        $pocetb += 2;
                        if ($echoit) echo "Prefix: $prefix Suffix: $suffix<BR>";
                    } ;

                    for($x = 0;$x < strlen($Data);$x++) {
                        imagesetpixel($img, $x, $y, $Palette[ord($Data[$x])]);
                    } ;
                    $Data = "";
                } ;
            } ;

            if ($biBitCount == 24) {
                $img = imagecreatetruecolor($Width, $Height);
                $Zbytek = $Width % 4;

                for($y = $Height-1;$y >= 0;$y--) {
                    for($x = 0;$x < $Width;$x++) {
                        $B = freadbyte($f);
                        $G = freadbyte($f);
                        $R = freadbyte($f);
                        $color = imagecolorexact($img, $R, $G, $B);
                        if ($color == -1) $color = imagecolorallocate($img, $R, $G, $B);
                        imagesetpixel($img, $x, $y, $color);
                    }
                    for($z = 0;$z < $Zbytek;$z++)
                    freadbyte($f);
                } ;
            } ;
            return $img;
        } ;

        fclose($f);
    } ;

    /*
* Helping functions:
*-------------------------
*
* freadbyte($file) - reads 1 byte from $file
* freadword($file) - reads 2 bytes (1 word) from $file
* freaddword($file) - reads 4 bytes (1 dword) from $file
* freadlngint($file) - same as freaddword($file)
* decbin8($d) - returns binary string of d zero filled to 8
* RetBits($byte,$start,$len) - returns bits $start->$start+$len from $byte
* freadbits($file,$count) - reads next $count bits from $file
* RGBToHex($R,$G,$B) - convert $R, $G, $B to hex
* int_to_dword($n) - returns 4 byte representation of $n
* int_to_word($n) - returns 2 byte representation of $n
*/

    function freadbyte($f)
    {
        return ord(fread($f, 1));
    } ;

    function freadword($f)
    {
        $b1 = freadbyte($f);
        $b2 = freadbyte($f);
        return $b2 * 256 + $b1;
    } ;

    function freadlngint($f)
    {
        return freaddword($f);
    } ;

    function freaddword($f)
    {
        $b1 = freadword($f);
        $b2 = freadword($f);
        return $b2 * 65536 + $b1;
    } ;

    function RetBits($byte, $start, $len)
    {
        $bin = decbin8($byte);
        $r = bindec(substr($bin, $start, $len));
        return $r;
    } ;

    $CurrentBit = 0;
    function freadbits($f, $count)
    {
        global $CurrentBit, $SMode;
        $Byte = freadbyte($f);
        $LastCBit = $CurrentBit;
        $CurrentBit += $count;
        if ($CurrentBit == 8) {
            $CurrentBit = 0;
        } else {
            fseek($f, ftell($f)-1);
        } ;
        return RetBits($Byte, $LastCBit, $count);
    } ;

    function RGBToHex($Red, $Green, $Blue)
    {
        $hRed = dechex($Red);
        if (strlen($hRed) == 1) $hRed = "0$hRed";
        $hGreen = dechex($Green);
        if (strlen($hGreen) == 1) $hGreen = "0$hGreen";
        $hBlue = dechex($Blue);
        if (strlen($hBlue) == 1) $hBlue = "0$hBlue";
        return($hRed . $hGreen . $hBlue);
    } ;

    function int_to_dword($n)
    {
        return chr($n &255) . chr(($n >> 8) &255) . chr(($n >> 16) &255) . chr(($n >> 24) &255);
    }
    function int_to_word($n)
    {
        return chr($n &255) . chr(($n >> 8) &255);
    }

    function decbin8($d)
    {
        return decbinx($d, 8);
    } ;

    function decbinx($d, $n)
    {
        $bin = decbin($d);
        $sbin = strlen($bin);
        for($j = 0;$j < $n - $sbin;$j++)
        $bin = "0$bin";
        return $bin;
    } ;

    function inttobyte($n)
    {
        return chr($n);
    } ;

?>