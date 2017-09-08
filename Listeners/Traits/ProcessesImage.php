<?php

namespace OkayBueno\Images\Listeners\Traits;

use OkayBueno\Images\Models\Image;

/**
 * Class ProcessesImageAndMovesResultsToCloud
 * @package OkayBueno\Images\Listeners\Traits
 */
trait ProcessesImage
{
    /**
     * @param Image $image
     * @param $options
     */
    public function processImage( Image $image, $options )
    {
        $this->imageService->processImage( $image, $options );
    }
}