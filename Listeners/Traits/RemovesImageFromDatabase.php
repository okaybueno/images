<?php

namespace OkayBueno\Images\Listeners\Traits;

use OkayBueno\Images\Models\Image;

/**
 * Trait RemovesImageFromDatabase
 * @package OkayBueno\Images\Listeners\Traits
 */
trait RemovesImageFromDatabase
{
    
    /**
     * @param Image $image
     */
    public function removeImageFromDatabase( Image $image )
    {
        $image->delete();
    }
}