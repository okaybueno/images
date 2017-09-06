<?php

namespace OkayBueno\Images\Listeners;

use OkayBueno\Images\Listeners\Traits\ProcessesImage;
use OkayBueno\Images\Services\ImageServiceInterface;
use OkayBueno\Images\Events\ImageWasCreated;

/**
 * Class ProcessImageSync
 * @package OkayBueno\Images\Listeners
 */
class ProcessImageSync
{
    use ProcessesImage;

    protected $imageService;

    /**
     * @param ImageServiceInterface $imageServiceInterface
     */
    public function __construct(
        ImageServiceInterface $imageServiceInterface
    )
    {
        $this->imageService = $imageServiceInterface;
    }


    /**
     * @param ImageWasCreated $event
     */
    public function handle( ImageWasCreated $event )
    {
        $this->processImage( $event->image, $event->options );
    }
}
