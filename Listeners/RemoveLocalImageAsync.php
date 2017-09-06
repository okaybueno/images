<?php

namespace OkayBueno\Images\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use OkayBueno\Images\Events\ImageWasMovedToCloud;
use OkayBueno\Images\Listeners\Traits\RemovesImageFromDatabase;
use OkayBueno\Images\Listeners\Traits\RemovesLocalImage;

/**
 * Class RemoveLocalImageAsync
 * @package OkayBueno\Images\Listeners
 */
class RemoveLocalImageAsync implements ShouldQueue
{
    use RemovesLocalImage;
    use RemovesImageFromDatabase;

    /**
     * @param ImageWasMovedToCloud $event
     */
    public function handle( ImageWasMovedToCloud $event )
    {
        $this->removeLocalImage( $event->image );

        $this->removeImageFromDatabase( $event->image );
    }
}
