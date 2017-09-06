<?php

namespace OkayBueno\Images\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use OkayBueno\Images\Events\ImageWasProcessed;
use OkayBueno\Images\Listeners\Traits\MovesProcessedImageToCloud;

/**
 * Class MoveProcessedImagesToCloudImageAsync
 * @package OkayBueno\Images\Listeners
 */
class MoveProcessedImagesToCloudImageAsync implements ShouldQueue
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
