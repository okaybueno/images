<?php

namespace OkayBueno\Images\Events;

use Illuminate\Queue\SerializesModels;
use OkayBueno\Images\Models\Image;

/**
 * Class ImageWasCreated
 * @package OkayBueno\Images\Events
 */
class ImageWasCreated extends Event
{
    use SerializesModels;

    protected $image;
    protected $options;

    /**
     * @param Image $image
     * @param array $options
     */
    public function __construct( Image $image, array $options = [] )
    {
        $this->image = $image;
        $this->options = $options;
    }

}
