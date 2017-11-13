<?
/**
 * This class will produce captcha image, and validate the code sent by user
 * 
 * @author  bingo [coolhpy@163.com]
 * @since   2006-6-17
 * @modify  feifengxlq <feifengxlq@gmail.com> http://www.phpobject.net/blog
 * @modify  2006-11-9
 * 
 * $auth_code = new AuthCode();
 * $auth_code->setImage(array('width'=>200,'height'=>20));
 * $auth_code->paint(); 
 */

class AuthCode 
{
    /**
     * validate code
     *  char:  char
     *  angle: char disort angle (-30 <= angle <= 30)
     *  color: char color
     * 
     * @var     array
     * @access  private
     */
    var $code = array();

    /**
     * font info
     *  space: char space (px)
     *  size:  char size (px)
     *  left:  padding left (px)
     *  top:   padding top (px)
     *  file:  font file path
     * 
     * @var     array
     * @access  private
     */
    var $font = array();

    /**
     * image info
     *  type:   
     *  mime:   MIME 
     *  width:   (px)
     *  height:  (px)
     *  func:   function to create image
     * 
     * @var     array
     * @access  private
     */
    var $image = array();

    /**
     * molestation
     *  type:    type (false not use)
     *  density: 
     * 
     * @var     array
     * @access  private
     */
    var $molestation = array();

    /**
     * bg color (RGB)
     *  r: red (0 - 255)
     *  g: green (0 - 255)
     *  b: blue (0 - 255)
     * 
     * @var     array
     * @access  private
     */
    var $bg_color = array();

    /**
     * default foreground color (RGB)
     *  r: red (0 - 255)
     *  g: green (0 - 255)
     *  b: blue (0 - 255)
     * 
     * @var     array
     * @access  private
     */
    var $fg_color = array();
    
    var $authcode='';

    /**
     * construct
     * 
     * @access  public
     */
    function AuthCode() 
    {
        
        $this->setCode();
        $this->setMolestation();
        $this->setImage();
        $this->setFont();
        $this->setBgColor();
    }
    
    /**
      * get authcode
    */
    function getcode()
    {
        return $this->authcode;
    }

    /**
     * draw image
     * 
     * @access  public
     * @param   string  filename, default empty to output to browser
     * @param   string  filename, valid code from browser
     * @return  void
     */
    function paint($validecode = "", $filename='') 
    {
        //create image
        $im = imagecreatetruecolor($this->image['width'], 
                                   $this->image['height']);

        //set background

        $bg_color = imagecolorallocate($im, $this->bg_color['r'], 
                                       $this->bg_color['g'], 
                                       $this->bg_color['b']);
        imagefilledrectangle($im, 0, 0, $this->image['width'], 
                             $this->image['height'], $bg_color);

        //generate code
        $code = $this->generateCode($validecode);

        //write code into image
        $num = count($code);
        $current_left = $this->font['left'];
        $current_top  = $this->font['top'];
        for ($i=0; $i<$num; $i++) 
        {
            $font_color = imagecolorallocate($im, $code[$i]['color']['r'],$code[$i]['color']['g'],$code[$i]['color']['b']);
            @imagettftext($im, $this->font['size'], $code[$i]['angle'], 
                         $current_left, $current_top, $font_color, 
                         $this->font['file'], $code[$i]['char']);
            $current_left += $this->font['size'] + $this->font['space'];

            $the_code .= $code[$i]['char'];
        }
        $this->authcode=$the_code;//save authcode
	//echo $this->authcode;
        //paint Molestation on image
        $this->paintMolestation($im);
	
        //output image
        if (isset($filename) && $filename!='') 
        {
            $this->image['func']($im, $filename.$this->image['type']);
        } else 
        {
            header("Cache-Control: no-cache, must-revalidate");
            header("Content-Type: ".$this->image['mime']);
            $this->image['func']($im);
        }
        imagedestroy($im);
    }

    /**
     * generate random validate code
     * 
     * @access  private
     * @param   string  filename, custom valid code from browser
     * @return  array   code string
     */
    function generateCode($validecode = "") 
    {
	if(!empty($validecode)){
		for($i=0; $i<strlen($validecode); $i++){
			if($i != strlen($validecode)-1){
				$temp .= substr($validecode, $i, 1).",";
			}
			else
				$temp .= substr($validecode, $i, $i+1);
		}
		$this->code['characters'] = $temp;
	}
        // create allowed string
        $characters = explode(',', $this->code['characters']);
        $num = count($characters);
        for ($i=0; $i<$num; $i++) 
        {
            if (substr_count($characters[$i], '-') > 0) 
            {
                $character_range = explode('-', $characters[$i]);
                for ($j=ord($character_range[0]); $j<=ord($character_range[1]);
                     $j++) 
                {
                    $array_allow[] = chr($j);
                }
            }
            else 
            {
		//added by lee
                $array_allow[] = $characters[$i];  //$array_allow[$i];
            }
        }

        $index = 0;
        while (list($key, $val) = each($array_allow)) 
        {
            $array_allow_tmp[$index] = $val;
            $index ++;
        }
        $array_allow = $array_allow_tmp;
	//print_r($array_allow);
        // generate random string
        mt_srand((double)microtime() * 1000000);
        $code = array();
        $index = 0;
        $i = 0;
        while ($i < $this->code['length']) 
        {
            $index = mt_rand(0, count($array_allow) - 1);
            if(!empty($validecode)){
		$index = $i;
            }
            $code[$i]['char'] = $array_allow[$index];
            if ($this->code['deflect']) 
            {
                $code[$i]['angle'] = mt_rand(-30, 30);
            } else
            {
                $code[$i]['angle'] = 0;
            }
            if ($this->code['multicolor']) 
            {
		//if from 0-255 and background is white, some color is unclear
		//this is custom hardcoded, depends on your bgcolor
                $code[$i]['color']['r'] = mt_rand(0, 200); 
                $code[$i]['color']['g'] = mt_rand(0, 200);
                $code[$i]['color']['b'] = mt_rand(0, 200);
            } else
            {
                $code[$i]['color']['r'] = $this->fg_color['r'];
                $code[$i]['color']['g'] = $this->fg_color['g'];
                $code[$i]['color']['b'] = $this->fg_color['b'];
            }
            $i++;
        }
	//print_r($code);
	$this->generateAuthoCode($code);
        return $code;
    }

    function generateAuthoCode($code){
        $num = count($code);
        for ($i=0; $i<$num; $i++) 
        {
            $the_code .= $code[$i]['char'];
        }
        $this->authcode=$the_code;

    }

    /**
     * get image type
     * 
     * @access  private
     * @param   string  extension string
     * @return  [mixed] fail to return false
     */
    function getImageType($extension) 
    {
        switch (strtolower($extension)) 
        {
            case 'png':
                $information['mime'] = image_type_to_mime_type(IMAGETYPE_PNG);
                $information['func'] = 'imagepng';
                break;
            case 'gif':
                $information['mime'] = image_type_to_mime_type(IMAGETYPE_GIF);
                $information['func'] = 'imagegif';
                break;
            case 'wbmp':
                $information['mime'] = image_type_to_mime_type(IMAGETYPE_WBMP);
                $information['func'] = 'imagewbmp';
                break;
            case 'jpg':
                $information['mime'] = image_type_to_mime_type(IMAGETYPE_JPEG);
                $information['func'] = 'imagejpeg';
                break;
            case 'jpeg':
                $information['mime'] = image_type_to_mime_type(IMAGETYPE_JPEG);
                $information['func'] = 'imagejpeg';
                break;
            case 'jpe':
                $information['mime'] = image_type_to_mime_type(IMAGETYPE_JPEG);
                $information['func'] = 'imagejpeg';
                break;
            default:
                $information = false;
        }
        return $information;
    }

    /**
     * paint Molestation
     * 
     * @access  private
     * @param   resource image resource
     * @return  void
     */
    function paintMolestation(&$im) 
    {
	//
        $num_of_pels = ceil($this->image['width']*$this->image['height']/5);
        switch ($this->molestation['density']) 
        {
            case 'fewness':
                $density = ceil($num_of_pels / 3);
                break;
            case 'muchness':
                $density = ceil($num_of_pels / 3 * 2);
                break;
            case 'normal':
                $density = ceil($num_of_pels / 2);
            default:
        }

        switch ($this->molestation['type']) 
        {
            case 'point':
                $this->paintPoints($im, $density);
                break;
            case 'line':
                $density = ceil($density / 30);
                $this->paintLines($im, $density);
                break;
            case 'both':
                $density = ceil($density / 2);
                $this->paintPoints($im, $density);
                $density = ceil($density / 30);
                $this->paintLines($im, $density);
                break;
            default:
                break;
        }
    }

    /**
     * draw point
     * 
     * @access  private
     * @param   resource image resource
     * @param   int      quantity
     * @return  void
     */
    function paintPoints(&$im, $quantity) 
    {
        mt_srand((double)microtime()*1000000);

        for ($i=0; $i<$quantity; $i++) 
        {
            $randcolor = imagecolorallocate($im, mt_rand(0,255), 
                                            mt_rand(0,255), mt_rand(0,255));
            imagesetpixel($im, mt_rand(0, $this->image['width']), 
                          mt_rand(0, $this->image['height']), $randcolor);
        }
    }

    /**
     * draw line
     * 
     * @access  private
     * @param   resource image resource
     * @param   int      quantity
     * @return  void
     */
    function paintLines(&$im, $quantity) 
    {
        mt_srand((double)microtime()*1000000);

        for ($i=0; $i<$quantity; $i++) 
        {
            $randcolor = imagecolorallocate($im, mt_rand(0,255), 
                                            mt_rand(0,255), mt_rand(0,255));
            imageline($im, mt_rand(0, $this->image['width']), 
                      mt_rand(0, $this->image['height']), 
                      mt_rand(0, $this->image['width']), 
                      mt_rand(0, $this->image['height']), $randcolor);
        }
    }
    /**
     * set foreground color
     * 
     * @access  private
     * @param   array   RGB color
     * @return  void
     */
    function setFgColor($color) 
    {
        if (is_array($color) && is_integer($color['r']) && 
            is_integer($color['g']) && is_integer($color['b']) && 
            ($color['r'] >= 0 && $color['r'] <= 255) && 
            ($color['g'] >= 0 && $color['g'] <= 255) && 
            ($color['b'] >= 0 && $color['b'] <= 255)) 
        {
            $this->fg_color = $color;
        } else 
        {
            $this->fg_color = array('r'=>0,'g'=>0,'b'=>0);
        }
    }
    /**
     * set validate code info
     * 
     * @access  public
     * @param   array   code info 
     * characters    string  allowed chars:  [1234][0-9][0-9,a-z]
     * length        int     code length
     * deflect       boolean if char is deflect
     * multicolor    boolean if char is colorful
     * @return  void
     */
    function setCode($code='') 
    {
        if (is_array($code)) 
        {
            if (!isset($code['characters']) || !is_string($code['characters'])) 
            {
                $code['characters'] = '0-9';
            }
            if (!(is_integer($code['length']) || $code['length']<=0)) 
            {
                $code['length'] = 4;
            }
            if (!is_bool($code['deflect'])) 
            {
                $code['deflect'] = true;
            }
            if (!is_bool($code['multicolor'])) 
            {
                $code['multicolor'] = true;
            }
        } else 
        {
            $code = array('characters'=>'0-9', 'length'=>4, 
                          'deflect'=>true, 'multicolor'=>false);
        }
        $this->code = $code;
    }

    /**
     * set background color
     * 
     * @access  public
     * @param   array   RGB color
     * @return  void
     */
    function setBgColor($color='') 
    {
        if (is_array($color) && is_integer($color['r']) && 
            is_integer($color['g']) && is_integer($color['b']) && 
            ($color['r'] >= 0 && $color['r'] <= 255) && 
            ($color['g'] >= 0 && $color['g'] <= 255) && 
            ($color['b'] >= 0 && $color['b'] <= 255)) 
        {
            $this->bg_color = $color;
        } else 
        {
            $this->bg_color = array('r'=>255,'g'=>255,'b'=>255);
        }

        // set default foreground color, oppsite from bgcolor
        $fg_color = array(
            'r'=>255-$this->bg_color['r'], 
            'g'=>255-$this->bg_color['g'], 
            'b'=>255-$this->bg_color['b']
        );
        $this->setFgColor($fg_color);
    }

    /**
     * set Molestation info
     * 
     * @access  public
     * @param   array   info array
     *  type    string  type (option: false, 'point', 'line')
     *  density string  density (option: 'normal', 'muchness', 'fewness')
     * @return  void
     */
    function setMolestation($molestation='') 
    {
        if (is_array($molestation)) 
        {
            if (!isset($molestation['type']) || 
                ($molestation['type']!='point' && 
                 $molestation['type']!='line' && 
                 $molestation['type']!='both')) 
            {
                $molestation['type'] = 'point';
            }
            if (!is_string($molestation['density'])) 
            {
                $molestation['density'] = 'normal';
            }
            $this->molestation = $molestation;
        } else 
        {
            $this->molestation = array(
                'type'    => 'point',
                'density' => 'normal'
            );
        }
    }

    /**
     * set font info
     * 
     * @access  public
     * @param   array   font info
     *   space  int     space (px)
     *   size   int     size (px)
     *   left   int     padding-left (px)
     *   top    int     padding-top (px)
     *   file   string  font file path
     * @return  void
     */
    function setFont($font='') 
    {
        if (is_array($font))
        {
            if (!is_integer($font['space']) || $font['space']<0)
            {
                $font['space'] = 5;
            }
            if (!is_integer($font['size']) || $font['size']<0)
            {
                $font['size'] = 12;
            }
            if (!is_integer($font['left']) || $font['left']<0 || 
                $font['left']>$this->image['width']) 
            {
                $font['left'] = 5;
            }
            if (!is_integer($font['top']) || $font['top']<0 || 
                $font['top']>$this->image['height']) 
            {
                $font['top'] = $this->image['height'] - 7;//5
            }
            if (!file_exists($font['file'])) 
            {
                $font['file'] = './arial.ttf';
            }
            $this->font = $font;
        } else
        {
            $this->font = array('space'=>5, 'size'=>12, 'left'=>5, 
                                'top'=>15, 
                                'file'=>'./arial.ttf');
        }
    }

    /**
     * set image info
     * 
     * @access  public
     * @param   array   image info
     *   type   string  type (option: 'png', 'gif', 'wbmp', 'jpg')
     *   width  int     width (px)
     *   height int     height (px)
     * @return  void
     */
    function setImage($image='') 
    {
        if (is_array($image)) 
        {
            if (!is_integer($image['width']) || $image['width'] <= 0) 
            {
                $image['width'] = 70;
            }
            if (!is_integer($image['height']) || $image['height'] <= 0) 
            {
                $image['height'] = 20;
            }
            $this->image = $image;
            $information = $this->getImageType($image['type']);
            if (is_array($information)) 
            {
                $this->image['mime'] = $information['mime'];
                $this->image['func'] = $information['func'];
            } else 
            {
                $this->image['type'] = 'png';
                $information = $this->getImageType('png');
                $this->image['mime'] = $information['mime'];
                $this->image['func'] = $information['func'];
            }
        } else{
            $information = $this->getImageType('png');
            $this->image = array(
                'type'=>'png', 
                'mime'=>$information['mime'], 
                'func'=>$information['func'], 
                'width'=>70, 
                'height'=>20);
        }
    }

}
?>