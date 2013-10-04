<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Utilities;


use stdClass;
use Molajo\Utilities\Api\ImageInterface;
use Molajo\Utilities\Exception\ImageException;

/**
 * Image
 *
 * @package     Molajo
 * @subpackage  Service
 * @since       1.0
 */
class Image // implements ImageInterface
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var integer $this ->size
     *
     * thumbnail; configuration option, defaults to 50 x 50
     * small; configuration option, defaults to 75 x 75
     * medium; configuration option, defaults to 150 x 150
     * large; configuration option, defaults to 300 x 300
     * original
     */
    protected $size;

    /**
     * @var integer $fileName
     */
    protected $fileName;

    /**
     * @var integer $fileNameOriginal
     */
    protected $fileNameOriginal;

    /**
     * @var integer $fileNameNew
     */
    protected $fileNameNew;

    /**
     * @var integer $image
     */
    protected $image;

    /**
     * @var integer $type
     */
    protected $type;

    /**
     * @var integer $width
     */
    protected $width;

    /**
     * @var integer $height
     */
    protected $height;

    /**
     * @var integer $imageResized
     */
    protected $imageResized;

    /**
     * initialise
     *
     * @return boolean
     * @since  1.0
     */
    public function initialise()
    {
    }

    /**
     * getImage
     *
     * Build an SQL query to select an image.
     *
     * @return
     * @since    1.0
     */
    public function getImage($id, $size = 0, $type = 'crop')
    {
        /** initialise  */
        $this->id   = (int)$id;
        $this->size = (int)$this->size;
        if ($this->size == 'thumbnail'
            || $this->size == 'small'
            || $this->size == 'medium'
            || $this->size == 'large'
        ) {
        } else {
            $this->size = 'large';
        }
        if ($this->type == 'exact'
            || $this->type == 'portrait'
            || $this->type == 'landscape'
            || $this->type == 'auto'
        ) {
        } else {
            $this->type = 'crop';
        }

        /** retrieve filename and perform Permissions Verification */
//		$results = $this->getImage();
//		if ($results === false) {
//			return false;
//		}

        /** return original size, if selected */
        if ($this->size == 0) {
            return $this->fileNameOriginal;
        }

        /** return resized image */
        $results = $this->getResizedImage();
        if ($results === false) {
        } else {
            return $this->fileNameNew;
        }

        /** resize file */
        $results = $this->createResizedImage();
        if ($results === false) {
            return false;
        } else {
            return $this->fileNameNew;
        }

        $db    = Services::DB();
        $query = $db->getQuery(true);

        $date = Services::Date()
            ->format('Y-m-d-H-i-s');

        $now       = $date->toSql();
        $null_date = $db->getNullDate();

        $query->select($db->qn('path'));
        $query->from($db->qn('#__content') . 'as a');
        $query->where('a.' . $db->qn('status') . ' = 1');
        $query->where(
            '(a.' . $db->qn('start_publishing_datetime') . ' = ' . $db->q($null_date) .
            ' OR a.' . $db->qn('start_publishing_datetime') . ' <= ' . $db->q($now) . ')'
        );
        $query->where(
            '(a.' . $db->qn('stop_publishing_datetime') . ' = ' . $db->q($null_date) .
            ' OR a.' . $db->qn('stop_publishing_datetime') . ' >= ' . $db->q($now) . ')'
        );
        $query->where('a.id = ' . (int)$this->id);

        $query->from($db->qn('#__catalog') . 'as b');
        $query->where('b.' . $db->qn('source_id') . ' = ' . $db->qn('id'));
        $query->where('b.' . $db->qn('catalog_type_id') . ' = ' . $db->qn('catalog_type_id'));

        $db->setQuery($query->__toString());

        $this->filename = $db->loadResult();
        if ($this->filename === false) {
            return false;
        }

        /** retrieve image folder for original images */
        $images = $this->application->get('system_media_folder', 'media/images');

        /** folders */
        if (is_dir(SITE_BASE_PATH . '/' . $images)) {
        } else {
            mkdir(SITE_BASE_PATH . '/' . $images, 0700);
        }

        /** make certain original image exists */
        $this->fileNameOriginal = SITE_BASE_PATH . '/' . $images . '/' . $this->filename;
        if (file_exists($this->fileNameOriginal)) {
            return $this->fileNameOriginal;
        } else {
            return false;
        }
    }

    /**
     * getResizedImage
     *
     * @return string
     */
    private function getResizedImage()
    {
        /** retrieve image folder for resized images */
        $images = $this->application->get('image_thumb_folder', '/media/images/thumbs');

        /** folders */
        if (is_dir(SITE_BASE_PATH . '/' . $images)) {
        } else {
            mkdir(SITE_BASE_PATH . '/' . $images, 0700);
        }

        /** if resized image already exists, return it */
        $this->fileNameNew = SITE_BASE_PATH . '/' . $images . '/' . 's' . $this->size . '_' . 't' . '_' . $this->type . $this->filename;
        if (file_exists($this->fileNameNew)) {
            return true;
        }

        return false;
    }

    /**
     * resizeImage
     *
     * @return string
     */
    protected function createResizedImage()
    {
        /** Options: exact, portrait, landscape, auto, crop and size */
        if ($this->size == 'thumbnail') {
            $width  = $this->application->get('image_thumbnail_width', 50);
            $height = $this->application->get('image_thumbnail_height', 50);

        } elseif ($this->size == 'small') {
            $width  = $this->application->get('image_small_width', 100);
            $height = $this->application->get('image_small_height', 100);

        } elseif ($this->size == 'medium') {
            $width  = $this->application->get('image_medium_width', 300);
            $height = $this->application->get('image_medium_height', 300);

        } elseif ($this->size == 'large') {
            $width  = $this->application->get('image_large_width', 500);
            $height = $this->application->get('image_large_height', 500);

        } else {
            $this->width  = imagesx($this->image);
            $this->height = imagesy($this->image);
        }

        /** 1. open the original file */
        $this->createImageObject();

        /** 2. set existing dimensions */
        $this->width  = imagesx($this->image);
        $this->height = imagesy($this->image);

        /** 3. resize Image */
        $this->resizeImage($width, $height);

        /** 4. Save image */

        return $this->saveImage(100);
    }

    /**
     * createImageObject
     *
     * @param $file
     *
     * @return bool|resource
     */
    protected function createImageObject()
    {
        $ext = strtolower(strrchr($this->fileNameOriginal, '.'));

        switch ($ext) {
            case '.jpg':
            case '.jpeg':
                $this->image = imagecreatefromjpeg($this->fileNameOriginal);
                break;

            case '.gif':
                $this->image = imagecreatefromgif($this->fileNameOriginal);
                break;

            case '.png':
                $this->image = imagecreatefrompng($this->fileNameOriginal);
                break;

            default:
                $this->image = false;
                break;
        }
    }

    /**
     * resizeImage
     *
     * @param        $newWidth
     * @param        $newHeight
     * @param string $this ->type
     *
     * @return void
     */
    public function resizeImage($width, $height)
    {
        /** Get optimal dimensions based on type */
        $newWidth        = $width;
        $newHeight       = $height;
        $this->typeArray = $this->getDimensions($newWidth, $newHeight, $this->type);

        $optimalWidth  = $this->typeArray['optimalWidth'];
        $optimalHeight = $this->typeArray['optimalHeight'];

        /** resample */
        $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
        imagecopyresampled(
            $this->imageResized,
            $this->image,
            0,
            0,
            0,
            0,
            $optimalWidth,
            $optimalHeight,
            $this->width,
            $this->height
        );

        if ($this->type == 'crop') {
            $this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
        }
    }

    /**
     * getDimensions
     *
     * @param  $newWidth
     * @param  $newHeight
     *
     * @return array
     * @since  1.0
     */
    protected function getDimensions($newWidth, $newHeight)
    {
        switch ($this->type) {
            case 'exact':
                $optimalWidth  = $newWidth;
                $optimalHeight = $newHeight;
                break;

            case 'portrait':
                $optimalWidth  = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
                break;

            case 'landscape':
                $optimalWidth  = $newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($newWidth);
                break;

            case 'auto':
                $this->typeArray = $this->getSizeByAuto($newWidth, $newHeight);
                $optimalWidth    = $this->typeArray['optimalWidth'];
                $optimalHeight   = $this->typeArray['optimalHeight'];
                break;

            case 'crop':
                $this->typeArray = $this->getOptimalCrop($newWidth, $newHeight);
                $optimalWidth    = $this->typeArray['optimalWidth'];
                $optimalHeight   = $this->typeArray['optimalHeight'];
                break;
        }

        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    /**
     * getSizeByFixedHeight
     *
     * @param $newHeight
     *
     * @return
     * @since  1.0
     */
    protected function getSizeByFixedHeight($newHeight)
    {
        $ratio    = $this->width / $this->height;
        $newWidth = $newHeight * $ratio;

        return $newWidth;
    }

    /**
     * getSizeByFixedWidth
     *
     * @param  $newWidth
     *
     * @return
     * @since  1.0
     */
    protected function getSizeByFixedWidth($newWidth)
    {
        $ratio     = $this->height / $this->width;
        $newHeight = $newWidth * $ratio;

        return $newHeight;
    }

    /**
     * getSizeByAuto
     *
     * @param $newWidth
     * @param $newHeight
     *
     * @return array
     * @since  1.0
     */
    protected function getSizeByAuto($newWidth, $newHeight)
    {
        if ($this->height < $this->width) {

            // *** Image to be resized is wider (landscape)
            $optimalWidth  = $newWidth;
            $optimalHeight = $this->getSizeByFixedWidth($newWidth);

        } elseif ($this->height > $this->width) {

            // *** Image to be resized is taller (portrait)
            $optimalWidth  = $this->getSizeByFixedHeight($newHeight);
            $optimalHeight = $newHeight;

        } else {

            // *** Image to be resized is a square
            if ($newHeight < $newWidth) {
                $optimalWidth  = $newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($newWidth);

            } elseif ($newHeight > $newWidth) {
                $optimalWidth  = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;

            } else {
                // *** Square resized to a square
                $optimalWidth  = $newWidth;
                $optimalHeight = $newHeight;
            }
        }

        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    /**
     * getOptimalCrop
     *
     * @param $newWidth
     * @param $newHeight
     *
     * @return array
     */
    protected function getOptimalCrop($newWidth, $newHeight)
    {
        $heightRatio = $this->height / $newHeight;
        $widthRatio  = $this->width / $newWidth;

        if ($heightRatio < $widthRatio) {
            $optimalRatio = $heightRatio;
        } else {
            $optimalRatio = $widthRatio;
        }

        $optimalHeight = $this->height / $optimalRatio;
        $optimalWidth  = $this->width / $optimalRatio;

        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    /**
     * crop
     *
     * @param $optimalWidth
     * @param $optimalHeight
     * @param $newWidth
     * @param $newHeight
     *
     * @return void
     */
    protected function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight)
    {
        // *** Find center - this will be used for the crop
        $cropStartX = ($optimalWidth / 2) - ($newWidth / 2);
        $cropStartY = ($optimalHeight / 2) - ($newHeight / 2);

        $crop = $this->imageResized;

        // *** Now crop from center to exact requested size
        $this->imageResized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled(
            $this->imageResized,
            $crop,
            0,
            0,
            $cropStartX,
            $cropStartY,
            $newWidth,
            $newHeight,
            $newWidth,
            $newHeight
        );
    }

    /**
     * saveImage
     *
     * @param string $imageQuality
     *
     * @return boolean
     * @since  1.0
     */
    public function saveImage($imageQuality = "100")
    {
        // *** Get extension
        $ext = strrchr($this->fileNameNew, '.');
        $ext = strtolower($ext);

        switch ($ext) {
            case '.jpg':
            case '.jpeg':
                if (imagetypes() & IMG_JPG) {
                    imagejpeg($this->imageResized, $this->fileNameNew, $imageQuality);
                }
                $results = true;
                break;

            case '.gif':
                if (imagetypes() & IMG_GIF) {
                    imagegif($this->imageResized, $this->fileNameNew);
                }
                $results = true;
                break;

            case '.png':
                // *** Scale quality from 0-100 to 0-9
                $scaleQuality = round(($imageQuality / 100) * 9);

                // *** Invert quality setting as 0 is best, not 9
                $invertScaleQuality = 9 - $scaleQuality;

                if (imagetypes() & IMG_PNG) {
                    imagepng($this->imageResized, $this->fileNameNew, $invertScaleQuality);
                }
                $results = true;
                break;

            default:
                $results = false;
                break;
        }

        imagedestroy($this->imageResized);

        return $results;
    }

    /**
     * getPlaceHolderImage
     *
     * @static
     *
     * @param       $width
     * @param       $height
     * @param array $options
     *
     * @return mixed
     * @since 1.0
     */
    public function getPlaceHolderImage($width, $height, $options = array())
    {

        $services_class = array(
            'placehold'   => 'PlaceholdImage',
            'lorem_pixel' => 'LoremPixelImage'
        );

        $service = $options['service_name'];
        $service = isset($service) ? $service : 'placehold';

        $service_class_name = $services_class[$service];
        if (class_exists($service_class_name)) {
            $service = new $service_class_name($width, $height, $options);

            return $service->url();
        } else {
            render_error("No placeholder image service called #{$service} exists!");
        }
    }
}
