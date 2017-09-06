<?php

namespace OkayBueno\Images\Services;

/**
 * Interface ImageServiceInterface
 * @package OkayBueno\Images\Services
 */
interface ImageServiceInterface
{
    /**
     * Finds an Image via the given ID, and returns an Image instance or an array with the errors.
     *
     * @param int $imageId ID of the image to find.
     * @return mixed Image instance if success, array otherwise.
     */
    public function findImageById( $imageId );

    /**
     * Creates a new Image in the system.
     *
     * @param mixed $imageDataOrImageURL Anything that can create an image on the Intervention's make() function.
     * @param array $options Array of options when creating the image. The options can be:
     *                  - path => Path where the image needs to be stored.
     *                  - sizes => Associative array of sizes that this image needs to be resized to.
     *                  - type => If the image belongs to a certain type, add it here. Useful to segregate.
     *                  - maintain_aspect_ratio => If set to true, it will respect the aspect ratio of the image. Default: TRUE.
     *                  - prevent_upsizing => Id set to true, the image won't be upsized (no quality loss). Default: TRUE.
     *                  - crop => You can crop the image instead of resize it. To do so, set this to true. Default: FALSE.
     *                  - extension => Extension you want to save the processed files with. Default: 'jpg',
     *                  - quality => Quality of the generated images after processing. Default: 90
     * @return mixed array or false in case of error, instance of the Image, in case of success.
     */
    public function createImage( $imageDataOrImageURL, array $options = [] );

    /**
     * Deletes (soft deletes) an image from the database
     *
     * @param int $imageId Id of the image to delete.
     * @param bool|true $skipValidation If this is set to true, the validation will be skipped and the function won't
     * check if the entity exists in the DB.
     * @return mixed true if success, array with error otherwise.
     */
    public function deleteImage( $imageId, $skipValidation = TRUE );

    /**
     * Processes an image stored in the local disk, and resizes it to the given sizes.
     *
     * @param mixed $imageIdOrImage ID of the image, or instance of the image to process.
     * @param array $options this array may contain the same values as the $options param in the createImage() function.
     * @return mixed Image instance.
     */
    public function processImage( $imageIdOrImage, array $options = [] );

    /**
     * Destroy an image (and its thumbs) from the disks and from the DB. It cannot be reverted.
     *
     * @param mixed $imageIdOrImage ID or instance of the image to destroy.
     * @return mixed true if success, array with errors otherwise.
     */
    public function destroyImage( $imageIdOrImage );
}