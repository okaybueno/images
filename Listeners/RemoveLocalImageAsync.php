<?php

namespace OkayBueno\Images\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use OkayBueno\Images\Events\ImageEvent;
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
     * @param ImageEvent $event
     */
    public function handle( ImageEvent $event )
    {
        $this->removeLocalImage( $event->image );

        $this->removeImageFromDatabase( $event->image );
    }
}
