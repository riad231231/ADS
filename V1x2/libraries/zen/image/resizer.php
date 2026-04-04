<?php
/**
 * @package		Zen Library
 * @subpackage	Zen Library
 * @author		Joomla Bamboo - design@joomlabamboo.com
 * @copyright 	Copyright (c) 2013 Joomla Bamboo. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @version		1.0.2
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class ZenImageResizer
{
	private static $cacheGroup = '';

	public static function setCacheGroup($group = '')
	{
		if (!empty($group))
		{
			self::$cacheGroup = $group;
		}
	}

	public static function getResizedImage($image, $newWidth, $newHeight, $option='crop', $quality='90')
	{
		if (empty($image))
		{
			return '';
		}

		// Import libraries
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// Windows or linux, set variables
		$full_site_dir = JURI::base();
		$site_dir = JURI::base(true).'/';
		$jpath = str_replace('\\', '/', JPATH_ROOT);
		$jpath_win = str_replace('/', '\\', JPATH_ROOT);
		$replace = array($full_site_dir, (($site_dir=='/') ? '' : $site_dir), (($jpath=='/') ? '' : $jpath), (($jpath_win=='\\') ? '' : $jpath_win));
		$image = str_replace($replace, '', $image);
		$cache_dir = $site_dir.'cache/';
		$local_image = str_replace('\\'.'\\', '\\', str_replace('//', '/', str_replace('\\', '/', $site_dir.$image)));

		if (strtolower(substr(PHP_OS, 0, 3)) === 'win')
		{// Windows
			$site_root = $jpath_win.'\\';
			$cache_root = $site_root.'cache\\';
			$image = str_replace('\\'.'\\', '\\', str_replace('//', '/', $site_root.str_replace('/', '\\', $image)));
		}
		else
		{ // Linux
			$site_root = $jpath.'/';
			$cache_root = $site_root.'cache/';
			$image = str_replace('\\'.'\\', '\\', str_replace('//', '/', $site_root.str_replace('\\', '/', $image)));
		}

		if (!empty(self::$cacheGroup))
		{
			$cache_root .= self::$cacheGroup . '/';
			$cache_dir .= self::$cacheGroup . '/';
		}

		if (!JFile::exists($image))
		{
			return '';
		}

		$lastmod = filemtime($image);
		$image_file = JFile::getName($image);
		$extension = '.'.JFile::getExt($image_file);

		// Open the image
		switch($extension)
		{
			case '.jpg':
			case '.jpeg':
				$img = imagecreatefromjpeg($image);
				break;

			case '.JPG':
			case '.JPEG':
				$img = imagecreatefromJPEG($image);
				break;

			case '.gif':
			case '.GIF':
				$img = imagecreatefromgif ($image);
				break;

			case '.png':
			case '.PNG':
				$img = imagecreatefrompng($image);
				break;

			default:
				$img = false;
				break;
		}

		$extension = strtolower($extension);

		// Retrieve its width and Height
		$width  = imagesx($img);
		$height = imagesy($img);

		// Name for our new image & path to save to
		$new_image = md5($image.'-'.$newWidth.'x'.$newHeight.'-'.$option.'-'.$lastmod);
		$savePath = $cache_root . $new_image . $extension;

		// If the original image is smaller than specified we just return the original
		if (($width<$newWidth)&&($height<$newHeight))
		{
			return $local_image;
		}

		// If we have already created the image once at the same size we just return that one
		if (file_exists($cache_root. $new_image . $extension))
		{
			return $cache_dir. $new_image . $extension;
		}

		// Make sure the cache exists. If it doesn't, then create it
		if (!JFolder::exists($cache_root))
		{
			JFolder::create($cache_root, 0755);
		}

		// Set permissions if they are not correct
		if ((JFolder::exists($cache_root))&&(JPath::setPermissions($cache_root)!='0755'))
		{
			JPath::setPermissions($cache_root, $filemode= '0755', $foldermode= '0755');
		}

		// Get optimal width and height - based on $option
		$optionArray = self::getDimensions($newWidth, $newHeight, $width, $height, $option);
		$optimalWidth  = $optionArray['optimalWidth'];
		$optimalHeight = $optionArray['optimalHeight'];

		// Resample - create image canvas of x, y size
		$imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);

		if ($extension === '.png')
		{
			self::setPngTransparency($imageResized, $img);
		}
		else if ($extension === 'gif')
		{
			self::setGifTransparency($imageResized, $img);
		}

		imagecopyresampled($imageResized, $img, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $width, $height);

		// If option is 'crop', then crop too
		if ($option == 'crop')
		{
			//Find center - this will be used for the crop
			$cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
			$cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );
			$crop = $imageResized;
			//Now crop from center to exact requested size
			$imageResized = imagecreatetruecolor($newWidth , $newHeight);

			if ($extension === '.png')
			{

			}
			else if ($extension === 'gif')
			{
				self::setGifTransparency($imageResized, $img);
			}

			imagecopyresampled($imageResized, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
		}

		// If option is 'topleft', then crop w/o resize
		if ($option == 'topleft')
		{
			$crop = $img;
			//Now crop from top left to exact requested size
			$imageResized = imagecreatetruecolor($newWidth , $newHeight);

			if ($extension === '.png')
			{

			}
			else if ($extension === 'gif')
			{
				self::setGifTransparency($imageResized, $img);
			}

			imagecopyresampled($imageResized, $crop, 0, 0, 0, 0, $newWidth, $newHeight , $newWidth, $newHeight);
		}

		// If option is 'topleft', then crop w/o resize
		if ($option == 'center')
		{
			//Find center - this will be used for the crop
			$cropStartX = ( $width / 2)  - ( $optimalWidth / 2);
			$cropStartY = ( $height / 2) - ( $optimalHeight/ 2);
			$cropEndX = $cropStartX + $optimalWidth;
			$cropEndY = $cropStartY + $optimalHeight;
			$crop = $img;
			//Now crop from center to exact requested size
			$imageResized = imagecreatetruecolor($newWidth, $newHeight);

			if ($extension === '.png')
			{

			}
			else if ($extension === 'gif')
			{
				self::setGifTransparency($imageResized, $img);
			}

			imagecopyresampled($imageResized, $crop, 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
		}

		switch ($extension)
		{
			case '.jpg':
			case '.jpeg':
				// Sharpen the image before we save it
				$sharpness = self::findSharp($width, $optimalWidth);
				$sharpenMatrix = array(
					array(-1, -2, -1),
					array(-2, $sharpness + 12, -2),
					array(-1, -2, -1)
				);
				$divisor = $sharpness;
				$offset = 0;

				if (function_exists('imageconvolution'))
				{
					imageconvolution($imageResized, $sharpenMatrix, $divisor, $offset);
				}
				else
				{
					JLoader::import('zen.image.convolution', ZEN_LIBRARY_PATH);
					ZenImageConvolution::imageConvolution($imageResized, $sharpenMatrix, $divisor, $offset);
				}

				if (imagetypes() & IMG_JPG)
				{
					imagejpeg($imageResized, $savePath, $quality);
				}
				break;

			case '.gif':
				if (imagetypes() & IMG_GIF)
				{
					imagegif ($imageResized, $savePath);
				}
				break;

			case '.png':
				// Scale quality from 0-100 to 0-9
				$scaleQuality = round(($quality/100) * 9);
				// Invert quality setting as 0 is best, not 9
				$invertScaleQuality = 9 - $scaleQuality;
				if (imagetypes() & IMG_PNG)
				{
					 imagepng($imageResized, $savePath, $invertScaleQuality);
				}
				break;

			default:
				break;
		}
		imagedestroy($imageResized);
		return $cache_dir . $new_image . $extension;
	}

	private static function getDimensions($newWidth, $newHeight, $width, $height, $option="crop")
	{
		switch ($option)
		{
			case 'exact':
				$optimalWidth = $newWidth;
				$optimalHeight = $newHeight;
				break;

			case 'portrait':
				$optimalWidth = self::getSizeByFixedHeight($newHeight, $width, $height);
				$optimalHeight = $newHeight;
				break;

			case 'landscape':
				$optimalWidth = $newWidth;
				$optimalHeight = self::getSizeByFixedWidth($newWidth, $width, $height);
				break;

			case 'auto':
				$optionArray = self::getSizeByAuto($newWidth, $newHeight, $width, $height);
				$optimalWidth = $optionArray['optimalWidth'];
				$optimalHeight = $optionArray['optimalHeight'];
				break;

			case 'crop':
				$optionArray = self::getOptimalCrop($newWidth, $newHeight, $width, $height);
				$optimalWidth = $optionArray['optimalWidth'];
				$optimalHeight = $optionArray['optimalHeight'];
				break;

			case 'topleft':
				$optimalWidth = $newWidth;
				$optimalHeight = $newHeight;
				break;

			case 'center':
				$optimalWidth = $newWidth;
				$optimalHeight = $newHeight;
				break;

			default:
				$optionArray = self::getOptimalCrop($newWidth, $newHeight, $width, $height);
				$optimalWidth = $optionArray['optimalWidth'];
				$optimalHeight = $optionArray['optimalHeight'];
				break;

		}

		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}

	private static function getSizeByFixedHeight($newHeight, $width, $height)
	{
		$ratio = $width / $height;
		$newWidth = $newHeight * $ratio;
		return $newWidth;
	}

	private static function getSizeByFixedWidth($newWidth, $width, $height)
	{
		$ratio = $height / $width;
		$newHeight = $newWidth * $ratio;
		return $newHeight;
	}

	private static function getSizeByAuto($newWidth, $newHeight, $width, $height)
	{
		// Image to be resized is wider (landscape)
		if ($height < $width)
		{
			$optimalWidth = $newWidth;
			$optimalHeight= self::getSizeByFixedWidth($newWidth, $width, $height);
		}
		elseif ($height > $width) // Image to be resized is taller (portrait)
		{
			$optimalWidth = self::getSizeByFixedHeight($newHeight, $width, $height);
			$optimalHeight= $newHeight;
		}
		else // Image to be resizerd is a square
		{
			if ($newHeight < $newWidth)
			{
				$optimalWidth = $newWidth;
				$optimalHeight= self::getSizeByFixedWidth($newWidth, $width, $height);
			}
			elseif ($newHeight > $newWidth)
			{
				$optimalWidth = self::getSizeByFixedHeight($newHeight, $width, $height);
				$optimalHeight= $newHeight;
			}
			else
			{
				//Sqaure being resized to a square
				$optimalWidth = $newWidth;
				$optimalHeight= $newHeight;
			}
		}

		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}

	private static function getOptimalCrop($newWidth, $newHeight, $width, $height)
	{
		$heightRatio = $height / $newHeight;
		$widthRatio  = $width /  $newWidth;

		if ($heightRatio < $widthRatio)
		{
			$optimalRatio = $heightRatio;
		}
		else
		{
			$optimalRatio = $widthRatio;
		}

		$optimalHeight = $height / $optimalRatio;
		$optimalWidth  = $width  / $optimalRatio;

		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}

	// Function from Ryan Rud (http://adryrun.com)
	private static function findSharp($orig, $final)
	{
		$final	= $final * (750.0 / $orig);
		$a		= 52;
		$b		= -0.27810650887573124;
		$c		= .00047337278106508946;

		$result = $a + $b * $final + $c * $final * $final;

		return max(round($result), 0);
	}

	private static function setGifTransparency($new_image, $image_source)
	{
		$transparencyIndex = imagecolortransparent($image_source);
		$palletSize = imagecolorstotal($image_source);

		$transparencyColor = array('red' => 255, 'green' => 255, 'blue' => 255);

		if ($transparencyIndex >= 0 && $transparencyIndex < $palletSize)
		{
			$transparencyColor = imagecolorsforindex($image_source, $transparencyIndex);
		}

		$transparencyIndex = imagecolorallocate($new_image, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);

		imagefill($new_image, 0, 0, $transparencyIndex);
		imagecolortransparent($new_image, $transparencyIndex);
	}

	private static function setPngTransparency($new_image, $image_source)
	{
		imagealphablending($new_image, false);
		imagesavealpha($new_image, true);

		$transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
		imagefilledrectangle($new_image, 0, 0, imagesx($new_image), imagesy($new_image), $transparent);
	}
}
