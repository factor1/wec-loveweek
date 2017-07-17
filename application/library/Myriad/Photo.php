<?php

/**
 * Myriad/Photo.php
 *
 * Photo resizing class
 *
 * @author Myriad Interactive, LLC.
 * @version 1.0.0
 */

require_once 'Zend/Registry.php';
require_once 'Zend/Debug.php';
require_once 'Myriad/Photo/Exception.php';
require_once 'Myriad/Log.php';

class Myriad_Photo
{

    /**
     * Public Variables
     * @access public
     */


    /**
     * Image Source (e.g. '../images/filename.ext')
     * @var string
     */

    public $image_src = '';


    /**
     * Image Directory (e.g. '../dirname')
     * @var string
     */

    public $image_dir = '';


    /**
     * Image Name (e.g. 'newname.ext')
     * @var string
     */

    public $image_name = '';


    /**
     * Image Quality (e.g. '100')
     * @var string
     */

    public $image_quality = '60';


    /**
     * Max Width (e.g. '640')
     * @var string
     */

    public $max_width = '640';


    /**
     * Max Height (e.g. '400')
     * @var string
     */

    public $max_height = '480';


    /**
     * Crop (e.g. true or false)
     * @var boolean
     */

    public $crop = false;



    /**
     * Private Variables
     * @access private
     */

    private $_mime_type   = 'image/jpeg';
    private $_buffer_size = 4096;
    private $_extension   = '.jpg';
    private $_errors      = array();



    /**
     * Sets image source
     * @return string
     */

    function setImageSrc($str_input)
    {
        $str_input = trim($str_input);

        if (file_exists($str_input)) {
            $this->image_src = $str_input;
        } else {
            $this->image_src = '';
            $this->recordError('The source file could not be found.');
        }
    }


    /**
     * Sets the cache folder (e.g. '60')
     * @var string
     */

    function setImageDir($str_input)
    {
        $str_input = trim($str_input);

        if (is_dir($str_input)) {
            $this->image_dir = $str_input;
        } else {
            $this->image_dir = '';
            $this->recordError('The image directory could not be found.');
        }
    }


    /**
     * Sets image name
     * @return string
     */

    function setImageName($str_input)
    {
        $str_input = trim($str_input);

        if ($str_input) {
            $this->image_name = $str_input;
        } else {
            $this->image_name = '';
            $this->recordError('The image name was not specified.');
        }
    }


    /**
     * Sets image quality
     * @return string
     */

    function setImageQuality($str_input)
    {
        $str_input = trim($str_input);

        if (($str_input >= 1) && ($str_input <= 100)) {
            $this->image_quality = $str_input;
        } else {
            $this->image_quality = '60';
            $this->recordError('The image quality must be an integer value between 1 and 100.');
        }
    }


    /**
     * Sets the maximum height (e.g. '480')
     * @var string
     */

    function setMaxWidth($str_input)
    {
        $str_input = trim($str_input);

        if (($str_input > 0) && (($str_input < 1001))) {
            $this->max_width = $str_input;
        } else {
            $this->max_width = '640';
            $this->recordError('The maximum dimension must be an integer value between 1 and 1000.');
        }
    }


    /**
     * Sets the maximum height (e.g. '480')
     * @var string
     */

    function setMaxHeight($str_input)
    {
        $str_input = trim($str_input);

        if (($str_input > 0) && (($str_input < 1001))) {
            $this->max_height = $str_input;
        } else {
            $this->max_height = '480';
            $this->recordError('The maximum dimension must be an integer value between 1 and 1000.');
        }
    }


    /**
     * Should we crop the image? (e.g. true or false)
     * @var boolean
     */

    function setCrop($bln_input)
    {
        if ($bln_input == true) {
            $this->crop = true;
        } else {
            $this->crop = false;
        }
    }


    /////////////////////////////////////////////////
    // MAIN METHODS
    /////////////////////////////////////////////////

    /**
     * Checks for GD library availability
     * @return bool
     */

    function isGDAvailable()
    {
        if (!function_exists('ImageCreate')) {
            $this->recordError('GD library is not available.');
            return 0;
        }

        return 1;
    }


    /**
     * Checks to see if the specified directory exists
     * @return bool
     */

    function isImageDirAvailable()
    {
        if (!is_dir($this->image_dir)) {
            $this->recordError('The image directory (' . $this->image_dir . ') could not be found.');
            return 0;
        }

        return 1;
    }


    /**
     * Determine new image path based on public variables
     * @return string
     */

    function getNewImagePath()
    {
        $path = $this->image_dir . '/' . $this->image_name;
        $path = str_replace('//' , '/', $path);
        return $path;
    }


    /**
     * Creates the image
     */

    function createImage()
    {

        if (!$this->isGDAvailable()) {
            return;
        }

        if (!$this->isImageDirAvailable()) {
            return;
        }

        // remove existing file if found
        if (file_exists($this->getNewImagePath())) {
            unlink($this->getNewImagePath());
        }

        // get original image dimensions
        list($width, $height, $type) = @getimagesize($this->image_src);


        if ($this->crop == true) {


            // SCALE AND CROP

            $new_width  = $this->max_width;
            $new_height = $this->max_height;

            if ($width < $height) {
                $new_height = ($this->max_width / $width) * $height;
            } else {
                $new_width  = ($this->max_height / $height) * $width;
            }

            if ($new_width < $this->max_width) {
                // if the width is smaller than the specified size
                $new_width  = $this->max_width;
                $new_height = ($this->max_width / $width) * $height;
            }

            if ($new_height < $this->max_height) {
                // if the height is smaller than the specified size
                $new_height = $this->max_height;
                $new_width  = ($this->max_height / $height) * $width;
            }

            // create scaled image
            switch ($type) {
                case IMAGETYPE_GIF:
                    $image = @imagecreatefromgif($this->image_src);
                    break;
                case IMAGETYPE_JPEG:
                    $image = @imagecreatefromjpeg($this->image_src);
                    break;
                case IMAGETYPE_PNG:
                    $image = @imagecreatefrompng($this->image_src);
                    break;
                default:
                    return false;
            }

            //$image = @imagecreatefromjpeg($this->image_src);
            $scaled_image = @imagecreatetruecolor($new_width, $new_height);
            @imagecopyresampled($scaled_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

            if (!$scaled_image) {
                $this->recordError('The server could not create the scaled image.');
                return;
            }

            // create cropped image
            $cropped_image = imagecreatetruecolor($this->max_width, $this->max_height);
            $w1 = ($new_width / 2) - ($this->max_width / 2);
            $h1 = ($new_height / 2) - ($this->max_height / 2);
            @imagecopyresampled($cropped_image, $scaled_image, 0, 0, $w1, $h1, $this->max_width, $this->max_height ,$this->max_width, $this->max_height);

            if (!$cropped_image) {
                $this->recordError('The server could not create the cropped image.');
                return;
            }

            // save copy and free resources
            @imagejpeg($cropped_image, $this->getNewImagePath(), $this->image_quality);
            @ImageDestroy($cropped_image);


        } else {


            // SCALE ONLY

            if (($width > $this->max_width) || ($height > $this->max_height)) {
                $aspect_ratio = $width / $height;                           // aspect ratio of source image
                $max_aspect_ratio = $this->max_width / $this->max_height;   // aspect ratio of max dimensions

                if ($aspect_ratio > $max_aspect_ratio) {
                    $new_width  = $this->max_width;                         // size by width
                    $new_height = $new_width / $aspect_ratio;
                } else {
                    $new_height = $this->max_height;                        // size by height
                    $new_width  = $new_height * $aspect_ratio;
                }
            } else {
                $new_width  = $width;
                $new_height = $height;
            }

            // resample image
            $image = @imagecreatefromjpeg($this->image_src);
            $scaled_image = @imagecreatetruecolor($new_width, $new_height);
            @imagecopyresampled($scaled_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

            if (!$scaled_image) {
                $this->recordError('The server could not create the scaled image.');
                return;
            }

            // save copy and free resources
            @imagejpeg($scaled_image, $this->getNewImagePath(), $this->image_quality);
            @ImageDestroy($scaled_image);


        }

        return (count($this->_errors) > 0) ? false : true;
    }


    /**
     * Add an error to the array
     * @return void
     */

    function recordError($message)
    {
        $this->_errors[] = $message;
    }


    /**
     * Send errors to the browser for debugging
     * @return void
     */

    function parseErrors()
    {
        echo "\n\n";

        foreach($this->_errors AS $error) {
            echo "<!-- DYNAMIC IMAGE ERROR: $error -->\n";
        }

        echo "\n\n";
    }


    /**
     * This function can be used for testing purposes.
     * @return void
     */

    function parseTest()
    {
        print('<p>GD Support: '            . $this->isGDAvailable() . '</p>');
        print('<p>Image Source: '          . $this->image_src . '</p>');
        print('<p>Image Directory: '       . $this->image_dir . '</p>');
        print('<p>Image Name: '            . $this->image_name . '</p>');
        print('<p>getNewImagePath(): '     . $this->getNewImagePath() . '</p>');
        print('<p>isImageDirAvailable(): ' . $this->isImageDirAvailable() . '</p>');
    }
}
