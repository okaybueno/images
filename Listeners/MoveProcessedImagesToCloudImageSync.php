<?php

namespace OkayBueno\Images\Listeners;

use OkayBueno\Images\Events\ImageEvent;
use OkayBueno\Images\Listeners\Traits\MovesProcessedImageToCloud;

/**
 * Class MoveProcessedImagesToCloudImageSync
 * @package OkayBueno\Images\Listeners
 */
class MoveProcessedImagesToCloudImageSync
{
    use MovesProcessedImageToCloud;

    /**
     * @param ImageEvent $event
     */
    public function handle( ImageEvent $event )
    {
        $this->moveProcessedImageToCloud( $event->image );
    }
}
