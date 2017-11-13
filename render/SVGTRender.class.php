<?php

/**
 *
 * @version 3.0
 * @copyright 2007
 * @author leelight
 */

class SVGTRender {
    private $minx;
    private $miny;
    private $maxx;
    private $maxy;
    private $width;
    private $height;
    private $enablestretchmap;
    private $image;

    public function setSVGTRender($minx, $miny, $maxx, $maxy, $width, $height, $enablestretchmap)
    {
        $this->minx = $minx;
        $this->miny = $miny;
        $this->maxx = $maxx;
        $this->maxy = $maxy;
        $this->width = $width;
        $this->height = $height;
        $this->enablestretchmap = $enablestretchmap;
    }

    /**
     *
     * @DESCRIPTION :Class Constructor.	**
     */
    public function SVGTRender()
    {
    }

}
/**
 * This is the base class for the different Svg Element Objects. Extend this
 *      class to create a new Svg Element.
 */
class SvgElement {
    private $mElements = ""; // Initialize so warnings aren't issued when not used.
    //private $mStyle;
    //private $mTransform;
    // The constructor.
    public function SvgElement()
    {
        // Do nothing.
    }
    // Most Svg elements can contain child elements. This method calls the
    // printElement method of any child element added to this object by use
    // of the addChild method.
    public function printElement()
    {
        // Loop and call
        if (is_array($this->mElements)) {
            foreach ($this->mElements as $child) {
                $child->printElement();
            }
        }
    }
    // This method adds an object reference to the mElements array.
    public function addChild(&$element)
    {
        $this->mElements[] = &$element;
    }
    // This method sends a message to the passed element requesting to be
    // added as a child.
    public function addParent(&$parent)
    {
        if (is_subclass_of($parent, "SvgElement")) {
            $parent->addChild($this);
        }
    }
    // Most Svg elements have a style attribute.
    // It is up to the dervied class to call this method.
    public function printStyle()
    {
        if ($this->mStyle != "") {
            //print("style=\"$this->mStyle\" ");
            print("$this->mStyle ");
        }
    }
    // This enables the style property to be set after initialization.
    public function setStyle($string)
    {
        $this->mStyle = $string;
    }
    // Most Svg elements have a transform attribute.
    // It is up to the dervied class to call this method.
    public function printTransform()
    {
        if ($this->mTransform != "") {
            print("transform=\"$this->mTransform\" ");
        }
    }
    // This enables the transform property to be set after initialization.
    public function setTransform($string)
    {
        $this->mTransform = $string;
    }
    public function printId()
    {
        if ($this->mId != "") {
            print("id=\"$this->mId\" ");
        }
    }
    // This enables the class property to be set after initialization.
    public function setId($string)
    {
        $this->mId = $string;
    }
    // Print out the object for debugging.
    public function debug()
    {
        print("<pre>");
        print_r($this);
        print("</pre>");
    }
}
/**
 *  This extends the SvgFragment class. It wraps the SvgFrament output with
 *  a content header, xml definition and doctype.
 */

class SvgDocument extends SvgFragment
{

    public function SvgDocument($width="100%", $height="100%",$minx=0,$miny=0,$maxx,$maxy, $style="")
    {
        // Call the parent class constructor.
        $this->SvgFragment($width, $height, $minx,$miny,$maxx,$maxy, $style);
    }

    public function printElement()
    {
        #header("Content-Type: image/svg+xml");

        print('<?xml version="1.0" encoding="utf-8"?>'."\n");

        print('<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Tiny//EN"
         "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11-tiny.dtd">'."\n");

        parent::printElement();
    }
}

class SvgFragment extends SvgElement {
    private $mWidth;
    private $mHeight;
    private $mX;
    private $mY;
    private $mMinx;
    private $mMiny;
    private $mMaxx;
    private $mMaxy;
    private $mW;
    private $mH;

    public function SvgFragment($width = "100%", $height = "100%", $minx=0,$miny=0,$maxx=0,$maxy=0, $style = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mWidth = $width;
        $this->mHeight = $height;
        $this->mStyle = $style;
        $this->mX = $x;
        $this->mY = $y;
        $this->mMinx = $minx;
        $this->mMiny = $miny;
        $this->mMaxx = $maxx;
        $this->mMaxy = $maxy;
        $this->mW = $this->mMaxx-$this->mMinx;
        $this->mH = $this->mMaxy-$this->mMiny;
    }

    public function printElement()
    {
        print("<svg version=\"1.1\" width=\"$this->mWidth"."px\" height=\"$this->mHeight"."px\" ");
        $mMaxy_tem = -$this->mMaxy;
        print("viewBox=\"$this->mMinx $mMaxy_tem $this->mW $this->mH\" ");

#        if ($this->mX != "") {
#            print("x=\"$this->mX\" ");
#        }
#        if ($this->mY != "") {
#            print("y=\"$this->mY\" ");
#        }
        print('baseProfile="tiny" ');
        print('preserveAspectRatio="xMidYMid meet" ');
        //print('xml:space="preserve" ');
        print('xml:space="default" ');
        print('xmlns:SUASsvg="http://suas.easywms.com" ');
        print('xmlns="http://www.w3.org/2000/svg" ');
        print('xmlns:xlink="http://www.w3.org/1999/xlink" ');
        #$this->printStyle();
        print(">\n");
        parent::printElement();
        print("</svg>\n");
    }

    public function bufferObject()
    {
        ob_start();
        $this->printElement();
        $buff = ob_get_contents();
        ob_end_clean();
        return $buff;
    }
}
class SvgDefs extends SvgElement {
    public function SvgDefs($style = "", $transform = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mStyle = $style;
        $this->mTransform = $transform;
    }

    public function printElement()
    {
        print("<defs ");
        $this->printStyle();
        $this->printTransform();
        print(">\n");
        parent::printElement();
        print("</defs>\n");
    }
}
class SvgDesc extends SvgElement {
    private $mDesc;

    public function SvgDesc($desc, $style = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mDesc = $desc;
        $this->mStyle = $style;
    }

    public function printElement()
    {
        print("<desc ");
        $this->printStyle();
        print(">\n");
        print($this->mDesc . "\n");
        parent::printElement();
        print("</desc>\n");
    }
}
class SvgGroup extends SvgElement {
    public function SvgGroup($id = "", $style = "", $transform = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mId = $id;
        $this->mStyle = $style;
        $this->mTransform = $transform;
    }

    public function printElement()
    {
        print("<g ");
        $this->printId();
        $this->printStyle();
        $this->printTransform();
        print(">\n");
        parent::printElement();
        print("</g>\n");
    }
}
class SvgPoint extends SvgElement {
    private $mId;
    private $mX;
    private $mY;
    private $mR;
    private $mPointstyle;

    public function SvgPoint($id = "", $cx = 0, $cy = 0, $r = 0, $pointstyle = "SQUARE", $style = "", $transform = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mId = $id;
        $this->mX = $cx;
        $this->mY = $cy;
        $this->mR = $r;
        $this->mPointstyle = $pointstyle;
        $this->mStyle = $style;
        $this->mTransform = $transform;
    }

    public function printElement()
    {
        switch ($this->mPointstyle) {
            case 'CIRCLE': {
                    print("<circle id=\"$this->mId\" cx=\"$this->mX\" cy=\"$this->mY\" r=\"$this->mR\" ");

                    if (is_array($this->mElements)) { // Print children, start and end tag.
                        $this->printStyle();
                        $this->printTransform();
                        print(">\n");
                        parent::printElement();
                        print("</circle>\n");
                    } else { // Print short tag.
                        $this->printStyle();
                        $this->printTransform();
                        print("/>\n");
                    } // end else
                }
                break;
            case 'SQUARE': {
                    $width = $height = $this->mR*2;
                    print("<rect id=\"$this->mId\" x=\"$this->mX\" y=\"$this->mY\" width=\"$width\" height=\"$height\" ");

                    if (is_array($this->mElements)) { // Print children, start and end tag.
                        $this->printStyle();
                        $this->printTransform();
                        print(">\n");
                        parent::printElement();
                        print("</rect>\n");
                    } else { // Print short tag.
                        $this->printStyle();
                        $this->printTransform();
                        print("/>\n");
                    } // end else
                }
                break;
            case 'TRIANGLE': {
                    $tem = getShapeTriangle($this->mX, $this->mY, $this->mR);
                    $polygonpoints = "";
                    for($j = 0;$j < 3;$j++) {
                        $polygonpoints .= $tem[0][$j] . " " . $tem[1][$j] . " ";
                    }

                    print("<polygon id=\"$this->mId\" points=\"$polygonpoints\" ");

                    if (is_array($this->mElements)) { // Print children, start and end tag.
                        $this->printStyle();
                        $this->printTransform();
                        print(">\n");
                        parent::printElement();
                        print("</polygon>\n");
                    } else { // Print short tag.
                        $this->printStyle();
                        $this->printTransform();
                        print("/>\n");
                    } // end else
                }
                break;
            case 'STAR': {
                    $tem = getShapeFiveCornerStar($this->mX, $this->mY, $this->mR);
                    $polygonpoints = "";
                    for($j = 0;$j < 10;$j++) {
                        $polygonpoints .= $tem[0][$j] . " " . $tem[1][$j] . " ";
                    }

                    print("<polygon id=\"$this->mId\" points=\"$polygonpoints\" ");

                    if (is_array($this->mElements)) { // Print children, start and end tag.
                        $this->printStyle();
                        $this->printTransform();
                        print(">\n");
                        parent::printElement();
                        print("</polygon>\n");
                    } else { // Print short tag.
                        $this->printStyle();
                        $this->printTransform();
                        print("/>\n");
                    } // end else
                }
                break;
            case 'CROSS': {
                    $tem = getShapeCross($this->mX, $this->mY, $this->mR);
                    $polygonpoints = "";
                    for($j = 0;$j < 12;$j++) {
                        $polygonpoints .= $tem[0][$j] . " " . $tem[1][$j] . " ";
                    }

                    print("<polygon id=\"$this->mId\" points=\"$polygonpoints\" ");

                    if (is_array($this->mElements)) { // Print children, start and end tag.
                        $this->printStyle();
                        $this->printTransform();
                        print(">\n");
                        parent::printElement();
                        print("</polygon>\n");
                    } else { // Print short tag.
                        $this->printStyle();
                        $this->printTransform();
                        print("/>\n");
                    } // end else
                }
                break;
            case 'X': {
                    $tem = getShapeX($this->mX, $this->mY, $this->mR);
                    $polygonpoints = "";
                    for($j = 0;$j < 12;$j++) {
                        $polygonpoints .= $tem[0][$j] . " " . $tem[1][$j] . " ";
                    }

                    print("<polygon id=\"$this->mId\" points=\"$polygonpoints\" ");

                    if (is_array($this->mElements)) { // Print children, start and end tag.
                        $this->printStyle();
                        $this->printTransform();
                        print(">\n");
                        parent::printElement();
                        print("</polygon>\n");
                    } else { // Print short tag.
                        $this->printStyle();
                        $this->printTransform();
                        print("/>\n");
                    } // end else
                }
                break;
            default: {
                    print("<rect id=\"$this->mId\" x=\"$this->mX\" y=\"$this->mY\" width=\"($this->mR*2)\" height=\"($this->mR*2)\" ");

                    if (is_array($this->mElements)) { // Print children, start and end tag.
                        $this->printStyle();
                        $this->printTransform();
                        print(">\n");
                        parent::printElement();
                        print("</rect>\n");
                    } else { // Print short tag.
                        $this->printStyle();
                        $this->printTransform();
                        print("/>\n");
                    } // end else
                }
        }
    } // end printElement
}
class SvgCircle extends SvgElement {
    private $mId;
    private $mCx;
    private $mCy;
    private $mR;

    public function SvgCircle($id = "", $cx = 0, $cy = 0, $r = 0, $style = "", $transform = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mId = $id;
        $this->mCx = $cx;
        $this->mCy = $cy;
        $this->mR = $r;
        $this->mStyle = $style;
        $this->mTransform = $transform;
    }

    public function printElement()
    {
        print("<circle id=\"$this->mId\" cx=\"$this->mCx\" cy=\"$this->mCy\" r=\"$this->mR\" ");

        if (is_array($this->mElements)) { // Print children, start and end tag.
            $this->printStyle();
            $this->printTransform();
            print(">\n");
            parent::printElement();
            print("</circle>\n");
        } else { // Print short tag.
            $this->printStyle();
            $this->printTransform();
            print("/>\n");
        } // end else
    } // end printElement
    public function setShape($cx, $cy, $r)
    {
        $this->mCx = $cx;
        $this->mCy = $cy;
        $this->mR = $r;
    }
}
class SvgEllipse extends SvgElement {
    private $mId;
    private $mCx;
    private $mCy;
    private $mRx;
    private $mRy;

    public function SvgEllipse($id = "", $cx = 0, $cy = 0, $rx = 0, $ry = 0, $style = "", $transform = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mId = $id;
        $this->mCx = $cx;
        $this->mCy = $cy;
        $this->mRx = $rx;
        $this->mRy = $ry;
        $this->mStyle = $style;
        $this->mTransform = $transform;
    }

    public function printElement()
    {
        print("<ellipse id=\"$this->mId\" cx=\"$this->mCx\" cy=\"$this->mCy\" rx=\"$this->mRx\" ry=\"$this->mRy\" ");

        if (is_array($this->mElements)) { // Print children, start and end tag.
            $this->printStyle();
            $this->printTransform();
            print(">\n");
            parent::printElement();
            print("</ellipse>\n");
        } else { // Print short tag.
            $this->printStyle();
            $this->printTransform();
            print("/>\n");
        } // end else
    }

    public function setShape($cx, $cy, $rx, $ry)
    {
        $this->mCx = $cx;
        $this->mCy = $cy;
        $this->mRx = $rx;
        $this->mRy = $ry;
    }
}
class SvgLine extends SvgElement {
    private $mId;
    private $mX1;
    private $mY1;
    private $mX2;
    private $mY2;

    public function SvgLine($id = "", $x1 = 0, $y1 = 0, $x2 = 0, $y2 = 0, $style = "", $transform = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mId = $id;
        $this->mX1 = $x1;
        $this->mY1 = $y1;
        $this->mX2 = $x2;
        $this->mY2 = $y2;
        $this->mStyle = $style;
        $this->mTransform = $transform;
    }

    public function printElement()
    {
        print("<line id=\"$this->mId\" x1=\"$this->mX1\" y1=\"$this->mY1\" x2=\"$this->mX2\" y2=\"$this->mY2\" ");

        if (is_array($this->mElements)) { // Print children, start and end tag.
            $this->printStyle();
            $this->printTransform();
            print(">\n");
            parent::printElement();
            print("</line>\n");
        } else { // Print short tag.
            $this->printStyle();
            $this->printTransform();
            print("/>\n");
        } // end else
    }

    public function setShape($x1, $y1, $x2, $y2)
    {
        $this->mX1 = $x1;
        $this->mY1 = $y1;
        $this->mX2 = $x2;
        $this->mY2 = $y2;
    }
}
class SvgPath extends SvgElement {
    private $mId;
    private $mD;

    public function SvgPath($id = "", $d = "", $style = "", $transform = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mId = $id;
        $this->mD = $d;
        $this->mStyle = $style;
        $this->mTransform = $transform;
    }

    public function printElement()
    {
        print("<path id=\"$this->mId\" d=\"$this->mD\" ");

        if (is_array($this->mElements)) { // Print children, start and end tag.
            $this->printStyle();
            $this->printTransform();
            print(">\n");
            parent::printElement();
            print("</path>\n");
        } else { // Print short tag.
            $this->printStyle();
            $this->printTransform();
            print("/>\n");
        } // end else
    }

    public function setShape($d)
    {
        $this->mD = $d;
    }
}
class SvgPolygon extends SvgElement {
    private $mId;
    private $mPoints;

    public function SvgPolygon($id = "", $points = "", $style = "", $transform = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mId = $id;
        $this->mPoints = $points;
        $this->mStyle = $style;
        $this->mTransform = $transform;
    }

   public  function printElement()
    {
        print("<polygon id=\"$this->mId\" points=\"$this->mPoints\" ");

        if (is_array($this->mElements)) { // Print children, start and end tag.
            $this->printStyle();
            $this->printTransform();
            print(">\n");
            parent::printElement();
            print("</polygon>\n");
        } else { // Print short tag.
            $this->printStyle();
            $this->printTransform();
            print("/>\n");
        } // end else
    }

   public  function setShape($points)
    {
        $this->mPoints = $points;
    }
}
class SvgPolyline extends SvgElement {
    private $mId;
    private $mPoints;

    public function SvgPolyline($id = "", $points = 0, $style = "", $transform = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mId = $id;
        $this->mPoints = $points;
        $this->mStyle = $style;
        $this->mTransform = $transform;
    }

    public function printElement()
    {
        print("<polyline id=\"$this->mId\" points=\"$this->mPoints\" ");

        if (is_array($this->mElements)) { // Print children, start and end tag.
            $this->printStyle();
            $this->printTransform();
            print(">\n");
            parent::printElement();
            print("</polyline>\n");
        } else { // Print short tag.
            $this->printStyle();
            $this->printTransform();
            print("/>\n");
        } // end else
    }

    public function setShape($points)
    {
        $this->mPoints = $points;
    }
}

class SvgRect extends SvgElement {
    private $mId;
    private $mX;
    private $mY;
    private $mWidth;
    private $mHeight;

    public function SvgRect($id = "", $x = 0, $y = 0, $width = 0, $height = 0, $style = "", $transform = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mId = $id;
        $this->mX = $x;
        $this->mY = $y;
        $this->mWidth = $width;
        $this->mHeight = $height;
        $this->mStyle = $style;
        $this->mTransform = $transform;
    }

    public function printElement()
    {
        print("<rect id=\"$this->mId\" x=\"$this->mX\" y=\"$this->mY\" width=\"$this->mWidth\" height=\"$this->mHeight\" ");

        if (is_array($this->mElements)) { // Print children, start and end tag.
            $this->printStyle();
            $this->printTransform();
            print(">\n");
            parent::printElement();
            print("</rect>\n");
        } else { // Print short tag.
            $this->printStyle();
            $this->printTransform();
            print("/>\n");
        } // end else
    }

    public function setShape($x, $y, $width, $height)
    {
        $this->mX = $x;
        $this->mY = $y;
        $this->mWidth = $width;
        $this->mHeight = $height;
    }
}
class SvgImage extends SvgElement {
    private $mId;
    private $mX;
    private $mY;
    private $mWidth;
    private $mHeight;
    private $mXlink;

    public function SvgImage($id = "", $x = 0, $y = 0, $width = 0, $height = 0, $xlink="",$style = "", $transform = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mId = $id;
        $this->mX = $x;
        $this->mY = $y;
        $this->mWidth = $width;
        $this->mHeight = $height;
        $this->mXlink = $xlink;
        $this->mStyle = $style;
        $this->mTransform = $transform;
    }

    public function printElement()
    {
        print("<image id=\"$this->mId\" x=\"$this->mX\" y=\"$this->mY\" width=\"$this->mWidth\" height=\"$this->mHeight\" xlink:href=\"$this->mXlink\" ");

        if (is_array($this->mElements)) { // Print children, start and end tag.
            $this->printStyle();
            $this->printTransform();
            print(">\n");
            parent::printElement();
            print("</image>\n");
        } else { // Print short tag.
            $this->printStyle();
            $this->printTransform();
            print("/>\n");
        } // end else
    }

    public function setShape($x, $y, $width, $height, $xlink)
    {
        $this->mX = $x;
        $this->mY = $y;
        $this->mWidth = $width;
        $this->mHeight = $height;
        $this->mXlink = $xlink;
    }
}
class SvgText extends SvgElement {
    private $mId;
    private $mX;
    private $mY;
    private $mText;

    public function SvgText($id = "", $x = 0, $y = 0, $text = "", $style = "", $transform = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mId = $id;
        $this->mX = $x;
        $this->mY = $y;
        $this->mText = $text;
        $this->mStyle = $style;
        $this->mTransform = $transform;
    }

    public function printElement()
    {
        print("<text id=\"$this->mId\" x=\"$this->mX\" y=\"$this->mY\" ");
        $this->printStyle();
        $this->printTransform();
        print(">\n");
        print($this->mText . "\n");
        parent::printElement();
        print("</text>\n");
    }

    public function setShape($x, $y, $text)
    {
        $this->mX = $x;
        $this->mY = $y;
        $this->mText = $text;
    }
}
class SvgTextPath extends SvgElement {
    private $mId;
    private $mX;
    private $mY;
    private $mText;

    public function SvgTextPath($id = "", $d = "", $text = "", $style = "", $transform = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mId = $id;
        $this->mD = $d;
        $this->mText = $text;
        $this->mStyle = $style;
        $this->mTransform = $transform;
    }

    public function printElement()
    {
        print("<def><path id=\"$this->mId"."_path"."\" d=\"$this->mD\" /></def>");

        print("<text id=\"$this->mId\" ");
        $this->printStyle();
        $this->printTransform();
        print(">\n");
        print("<textPath xlink:href=\"#".$this->mId."_path\">");
        print($this->mText . "\n");
        print("</textPath>");
        parent::printElement();
        print("</text>\n");
    }

    public function setShape($d, $text)
    {
        $this->mD = $d;
        $this->mText = $text;
    }
}
class SvgTitle extends SvgElement {
    private $mTitle;

    function SvgTitle($title, $style = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mTitle = $title;
        $this->mStyle = $style;
    }

    function printElement()
    {
        print("<title ");
        $this->printStyle();
        print(">\n");
        print($this->mTitle . "\n");
        parent::printElement();
        print("</title>\n");
    }
}
class SvgTspan extends SvgElement {
    private $mX;
    private $mY;
    private $mText;

    function SvgTspan($x = 0, $y = 0, $text = "", $style = "", $transform = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mX = $x;
        $this->mY = $y;
        $this->mText = $text;
        $this->mStyle = $style;
        $this->mTransform = $transform;
    }

    function printElement()
    {
        print("<tspan x=\"$this->mX\" y=\"$this->mY\" ");

        if (is_array($this->mElements)) { // Print children, start and end tag.
            $this->printStyle();
            $this->printTransform();
            print(">\n");
            print($this->mText);
            parent::printElement();
            print("</tspan>\n");
        } else { // Print short tag.
            $this->printStyle();
            $this->printTransform();
            print(">\n");
            print($this->mText);
            print("\n</tspan>\n");
        } // end else
    }

    function setShape($x, $y, $text)
    {
        $this->mX = $x;
        $this->mY = $y;
        $this->mText = $text;
    }
}
class SvgMarker extends SvgElement {
    private $mId;
    private $mRefX;
    private $mRefY;
    private $mMarkerUnits;
    private $mMarkerWidth;
    private $mMarkerHeight;
    private $mOrient;

    function SvgMarker($id, $refX = "", $refY = "", $markerUnits = "", $markerWidth = "", $markerHeight = "", $orient = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mId = $id;
        $this->mRefX = $refX;
        $this->mRefY = $refY;
        $this->mMarkerUnits = $markerUnits;
        $this->mMarkerWidth = $markerWidth;
        $this->mMarkerHeight = $markerHeight;
        $this->mOrient = $orient;
    }

    function printElement()
    {
        print("<marker id=\"$this->mId\" ");
        // Print the attributes only if they are defined.
        if ($this->mRefX != "") {
            print ("refX=\"$this->mRefX\" ");
        }
        if ($this->mRefY != "") {
            print ("refY=\"$this->mRefY\" ");
        }
        if ($this->mMarkerUnits != "") {
            print ("markerUnits=\"$this->mMarkerUnits\" ");
        }
        if ($this->mMarkerWidth != "") {
            print ("markerWidth=\"$this->mMarkerWidth\" ");
        }
        if ($this->mMarkerHeight != "") {
            print ("markerHeight=\"$this->mMarkerHeight\" ");
        }
        if ($this->mOrient != "") {
            print ("orient=\"$this->mOrient\" ");
        }

        if (is_array($this->mElements)) { // Print children, start and end tag.
            print(">\n");
            parent::printElement();
            print("</marker>\n");
        } else {
            print("/>\n");
        } // end else
    } // end printElement
    function setShape($id, $refX = "", $refY = "", $markerUnits = "", $markerWidth = "", $markerHeight = "", $orient = "")
    {
        $this->mId = $id;
        $this->mRefX = $refX;
        $this->mRefY = $refY;
        $this->mMarkerUnits = $markerUnits;
        $this->mMarkerWidth = $markerWidth;
        $this->mMarkerHeight = $markerHeight;
        $this->mOrient = $orient;
    }
}

class SvgAnimate extends SvgElement {
    private $mAttributeName;
    private $mAttributeType;
    private $mFrom;
    private $mTo;
    private $mBegin;
    private $mDur;
    private $mFill;

    function SvgAnimate($attributeName, $attributeType = "", $from = "", $to = "", $begin = "", $dur = "", $fill = "")
    {
        // Call the parent class constructor.
        $this->SvgElement();

        $this->mAttributeName = $attributeName;
        $this->mAttributeType = $attributeType;
        $this->mFrom = $from;
        $this->mTo = $to;
        $this->mBegin = $begin;
        $this->mDur = $dur;
        $this->mFill = $fill;
    }

    function printElement()
    {
        print("<animate attributeName=\"$this->mAttributeName\" ");
        // Print the attributes only if they are defined.
        if ($this->mAttributeType != "") {
            print ("attributeType=\"$this->mAttributeType\" ");
        }
        if ($this->mFrom != "") {
            print ("from=\"$this->mFrom\" ");
        }
        if ($this->mTo != "") {
            print ("to=\"$this->mTo\" ");
        }
        if ($this->mBegin != "") {
            print ("begin=\"$this->mBegin\" ");
        }
        if ($this->mDur != "") {
            print ("dur=\"$this->mDur\" ");
        }
        if ($this->mFill != "") {
            print ("fill=\"$this->mFill\" ");
        }

        if (is_array($this->mElements)) { // Print children, start and end tag.
            print(">\n");
            parent::printElement();
            print("</animate>\n");
        } else {
            print("/>\n");
        } // end else
    } // end printElement
    function setShape($attributeName, $attributeType = "", $from = "", $to = "", $begin = "", $dur = "", $fill = "")
    {
        $this->mAttributeName = $attributeName;
        $this->mAttributeType = $attributeType;
        $this->mFrom = $from;
        $this->mTo = $to;
        $this->mBegin = $begin;
        $this->mDur = $dur;
        $this->mFill = $fill;
    }
}

?>