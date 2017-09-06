<?php

namespace OkayBueno\Images\Services\Validation\src;

use OkayBueno\Images\Services\Validation\ImageValidatorInterface;
use OkayBueno\Validation\src\LaravelValidator;

/**
 * Class ImageValidatorLaravel
 * @package OkayBueno\Images\Services\Validation\src
 */
class ImageValidatorLaravel extends LaravelValidator implements ImageValidatorInterface
{
    /**
     * @return array
     */
    public function create()
    {
        return [
            'filename' => 'required',
            'path' => 'required',
            'type' => 'sometimes|max:255'
        ];
    }

    /**
     * @return array
     */
    public function existsById()
    {
        return [
            'id' => 'required|exists:images,id,deleted_at,NULL'
        ];
    }

    /**
     * @return array
     */
    public function existsByIdEvenDeleted()
    {
        return [
            'id' => 'required|exists:images,id'
        ];
    }


}