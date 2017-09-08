<?php

namespace OkayBueno\Images\Events;

/**
 * Class ImageEvent
 * @package OkayBueno\Images\Events
 */
abstract class ImageEvent
{

    /**
     * Magic method to get the private/protected properties from the event class.
     * @param $property
     * @return null
     */
    public function __get( $property )
    {
        if ( property_exists( $this, $property ) )
        {
            return $this->$property;
        }

        return NULL;
    }
}
