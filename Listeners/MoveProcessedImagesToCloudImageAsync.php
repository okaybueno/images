<?php

namespace OkayBueno\Images\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use OkayBueno\Images\Events\ImageEvent;
use OkayBueno\Images\Listeners\Traits\MovesProcessedImageToCloud;

/**
 * Class MoveProcessedImagesToCloudImageAsync
 * @package OkayBueno\Images\Listeners
 */
class MoveProcessedImagesToCloudImageAsync implements ShouldQueue
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
