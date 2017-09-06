<?php

namespace OkayBueno\Images\Listeners;

use OkayBueno\Images\Events\ImageWasProcessed;
use OkayBueno\Images\Listeners\Traits\MovesProcessedImageToCloud;

/**
 * Class MoveProcessedImagesToCloudImageSync
 * @package OkayBueno\Images\Listeners
 */
class MoveProcessedImagesToCloudImageSync
{
    use MovesProcessedImageToCloud;

    /**
     * @param ImageWasProcessed $event
     */
    public function handle( ImageWasProcessed $event )
    {
        $this->moveProcessedImageToCloud( $event->image );
    }
}
