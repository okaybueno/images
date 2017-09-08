<?php

namespace OkayBueno\Images\Services\Validation;

use OkayBueno\Validation\ValidatorInterface;

/**
 * Interface ImageValidatorInterface
 * @package OkayBueno\Images\Services\Validation
 */
interface ImageValidatorInterface extends ValidatorInterface
{
    const CREATE = 'create';
    const EXISTS_BY_ID = 'existsById';
    const EXISTS_BY_ID_EVEN_DELETED = 'existsByIdEvenDeleted';

    public function create();
    public function existsById();
    public function existsByIdEvenDeleted();
}