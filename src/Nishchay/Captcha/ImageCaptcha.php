<?php

namespace Nishchay\Captcha;

use Processor;
use Nishchay\Utility\StringUtility;
use Nishchay\Http\Response\Response;

class ImageCaptcha
{

    /**
     * Fonts
     * 
     * @var array 
     */
    private $font = [__DIR__ . DS . 'fonts/arial.ttf'];

    /**
     * Font colors
     * 
     * @var array 
     */
    private $fontColor = [];

    /**
     * Image width
     * 
     * @var int 
     */
    private $height = 50;

    /**
     * Image object
     * 
     * @var object 
     */
    private $image = null;

    /**
     * Margin left for font
     * 
     * @var int 
     */
    private $left = 15;

    /**
     * opacity of the font(alpha)
     * 
     * @var int 
     */
    private $opacity = 70;

    /**
     * Spacing between characters
     * Applicable when addSpacing() is used.
     * 
     * @var int 
     */
    private $spacing = 15;

    /**
     * Maximum Spacing between characters
     * Applicable by default. Inactive if addSpacing() is used.
     * @var type 
     */
    private $maxSpacing = 20;

    /**
     * Minimum Spacing between characters
     * Applicable by default. Inactive if addSpacing() is used.
     * 
     * @var type 
     */
    private $minSpacing = 10;

    /**
     * Whether the spacing between character is fixed or random.
     * 
     * @var int 
     */
    private $fixedSpacing = false;

    /**
     * String length of $string variable.
     * 
     * @var int 
     */
    private $length = 5;

    /**
     * Top margin of font.
     * 
     * @var int 
     */
    private $top = 40;

    /**
     * Width of captcha image.
     * 
     * @var int 
     */
    private $width = 150;

    /**
     *
     * @var type 
     */
    private $name;

    /**
     * Initializes basic variables and prepares image object.
     * 
     * @param int|double    $width
     * @param int|double    $height
     * @param int           $opacity
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->image = imagecreatetruecolor($this->width, $this->height);
        $this->createRectangle();
    }

    /**
     * Add color of font.
     * 
     * @param   int     $red
     * @param   int     $green
     * @param   int     $blue
     * @return  NULL
     */
    public function addColor($red, $green, $blue)
    {
        $this->fontColor[] = imagecolorallocatealpha($this->image, (int) $red, (int) $green, (int) $blue, rand(0, $this->opacity));
    }

    /**
     * Add font.
     * 
     * @param   string  $font
     * @return  NULL
     */
    public function addFont($font)
    {
        $this->font[] = $font;
    }

    /**
     * Put rectangle with white background on image.
     * 
     * @return NULL
     */
    public function createRectangle()
    {
        imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, imagecolorallocate($this->image, 255, 255, 255));
    }

    /**
     * Get random font style.
     * 
     * @return string
     */
    public function getFont()
    {
        return $this->font[rand(0, count($this->font) - 1)];
    }

    /**
     * Get Random font color.
     * 
     * @return object
     */
    private function getFontColor()
    {
        if (empty($this->fontColor)) {
            return imagecolorallocatealpha($this->image, rand(0, 255), rand(0, 255), rand(0, 255), rand(0, $this->opacity));
        } else {
            return $this->fontColor[rand(0, count($this->fontColor) - 1)];
        }
    }

    /**
     * Get spacing size between character.
     * 
     * @return int
     */
    private function getSpacing()
    {
        if ($this->fixedSpacing) {
            return $this->spacing;
        } else {
            return rand($this->minSpacing, $this->maxSpacing);
        }
    }

    /**
     * Constructs and render captcha.
     * 
     * @return NULL
     */
    public function render()
    {
        Response::setContentType('png');

        $fontSize = rand(0, 30);
        $string = StringUtility::getRandomString($this->length, true);
        foreach (str_split($string) as $char) {
            if (!$this->fixedSpacing) {
                $fontSize = rand(0, 30);
            }
            imagettftext($this->image, 30, $fontSize, $this->left, $this->top, $this->getFontColor(), $this->getFont(), $char);
            $this->left += $this->getSpacing();
        }

        Processor::setInternalSessionValue($this->name, $string);
        imagepng($this->image);
        imagedestroy($this->image);
    }

    /**
     * Sets font style.
     * 
     * @param   string  $font
     * @return  NULL
     */
    public function setFont($font)
    {
        $this->font = [$font];
    }

    /**
     * Sets maximum and minimum spacing between characters.
     * 
     * @param   int     $min
     * @param   int     $max
     * @return  NULL
     */
    public function setRandomSpacing($min, $max)
    {
        $this->fixedSpacing = false;
        $this->minSpacing = (int) $min;
        $this->maxSpacing = $this->spacing = (int) $max;
    }

    /**
     * Sets fixed spacing between characters.
     * 
     * @param   int     $spacing
     * @return  NULL
     */
    public function setSpacing($spacing)
    {
        $this->fixedSpacing = true;
        $this->spacing = (int) $spacing;
    }

    /**
     * Sets starting point of captcha string.
     * 
     * @param   int     $left
     * @param   int     $top
     * @return  NULL
     */
    public function setStartingPoint($left, $top)
    {
        $this->left = (int) $left;
        $this->top = (int) $top;
    }

}
