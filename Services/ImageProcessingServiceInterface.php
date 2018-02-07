<?php

namespace OkayBueno\Images\Services;

/**
 * Interface ImageProcessingServiceInterface
 * @package OkayBueno\Images\Services
 */
interface ImageProcessingServiceInterface
{
    public function createImageFromImageDataOrURL( $imageDataOrURL, $destinationFolder, array $settings = [] );
    public function resizeOrCropImageToSizes( $sourceLocalImage, $destinationFolder, array $sizes, array $settings = [] );
}
