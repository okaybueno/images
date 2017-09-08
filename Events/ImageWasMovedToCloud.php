<?php

namespace OkayBueno\Images\Events;

use Illuminate\Queue\SerializesModels;
use OkayBueno\Images\Models\Image;

/**
 * Class ImageWasMovedToCloud
 * @package OkayBueno\Images\Events
 */
class ImageWasMovedToCloud extends ImageEvent
{
    use SerializesModels;

    protected $image;

    /**
     * @param Image $image
     */
    public function __construct( Image $image )
    {
        $this->image = $image;
    }

}
