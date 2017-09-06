<?php

namespace OkayBueno\Images\Listeners;

use OkayBueno\Images\Events\ImageEvent;
use OkayBueno\Images\Listeners\Traits\RemovesImageFromDatabase;
use OkayBueno\Images\Listeners\Traits\RemovesLocalImage;

/**
 * Class RemoveLocalImageSync
 * @package OkayBueno\Images\Listeners
 */
class RemoveLocalImageSync
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
