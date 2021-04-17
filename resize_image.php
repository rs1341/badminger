<?php
/**
 * Resize image - preserve ratio of width and height.
 * @param string $sourceImage path to source JPEG image
 * @param string $targetImage path to final JPEG image file
 * @param int $maxWidth maximum width of final image (value 0 - width is optional)
 * @param int $maxHeight maximum height of final image (value 0 - height is optional)
 * @param int $quality quality of final image (0-100)
 * @return bool
 */
function resizeImage($sourceImage, $targetImage, $maxWidth, $maxHeight, $quality = 80)
{
    list($origWidth, $origHeight, $type) = getimagesize($sourceImage);

	if ($type == 1)
	{
		header ('Content-Type: image/gif');
		$image = imagecreatefromgif($sourceImage);
	}
	elseif ($type == 2)
	{
		header ('Content-Type: image/jpeg');
		$image = imagecreatefromjpeg($sourceImage);
	}
	elseif ($type == 3)
	{
		header ('Content-Type: image/png');
		$image = imagecreatefrompng($sourceImage);
	}
	else
	{
		header ('Content-Type: image/x-ms-bmp');
		$image = imagecreatefromwbmp($sourceImage);
	}

	if ($maxWidth == 0)
	{
		$maxWidth  = $origWidth;
	}

	if ($maxHeight == 0)
	{
		$maxHeight = $origHeight;
	}

	// Calculate ratio of desired maximum sizes and original sizes.
	$widthRatio = $maxWidth / $origWidth;
	$heightRatio = $maxHeight / $origHeight;

	// Ratio used for calculating new image dimensions.
	$ratio = min($widthRatio, $heightRatio);

	// Calculate new image dimensions.
	$newWidth  = (int)$origWidth  * $ratio;
	$newHeight = (int)$origHeight * $ratio;

	// Create final image with new dimensions.

	// if($type==1 or $type==3)
	// {
	//    $newImage = imagefill($newImage,0,0,0x7fff0000);
	// }

	$newImage = imagecreatetruecolor($newWidth, $newHeight);

	$transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
	imagefill($newImage, 0, 0, $transparent);
	imagesavealpha($newImage, true);


	imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
	imagepng($newImage, $targetImage);

	// Free up the memory.
	imagedestroy($image);
	imagedestroy($newImage);

	return true;
}

/**
 * Example
 * resizeImage('image.jpg', 'resized.jpg', 200, 200);
 */
 ?>