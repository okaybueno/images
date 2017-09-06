<?php

namespace OkayBueno\Images\Listeners;

use OkayBueno\Images\Events\ImageWasMovedToCloud;
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
     * @param ImageWasMovedToCloud $event
     */
    public function handle( ImageWasMovedToCloud $event )
    {
        $this->removeLocalImage( $event->image );

        $this->removeImageFromDatabase( $event->image );
    }
}
