<?php

namespace OkayBueno\Images\Events;

use Illuminate\Queue\SerializesModels;
use OkayBueno\Images\Models\Image;

/**
 * Class ImageWasDeleted
 * @package OkayBueno\Images\Events
 */
class ImageWasDeleted extends ImageEvent
{
    use SerializesModels;

    protected $image;

    /**
     * ImageWasDeleted constructor.
     * @param Image $image
     */
    public function __construct( Image $image  )
    {
        $this->image = $image;
    }

}
