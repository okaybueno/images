<?php

namespace OkayBueno\Images\Services\src;

use Illuminate\Support\Facades\Storage;
use OkayBueno\Images\Events\ImageWasCreated;
use OkayBueno\Images\Events\ImageWasProcessed;
use OkayBueno\Images\Events\ImageWasDeleted;
use OkayBueno\Images\Models\Image;
use OkayBueno\Images\Services\ImageProcessingServiceInterface;
use OkayBueno\Images\Services\ImageServiceInterface;
use OkayBueno\Images\Services\Validation\ImageValidatorInterface;

/**
 * Class ImageService
 * @package OkayBueno\Images\Services\src
 */
class ImageService implements ImageServiceInterface
{
    protected $imageValidator;
    protected $imageProcessingService;

    /**
     * @param ImageValidatorInterface $imageValidatorInterface
     * @param ImageProcessingServiceInterface $imageProcessingServiceInterface
     */
    public function __construct(
        ImageValidatorInterface $imageValidatorInterface,
        ImageProcessingServiceInterface $imageProcessingServiceInterface
    )
    {
        $this->imageValidator = $imageValidatorInterface;
        $this->imageProcessingService = $imageProcessingServiceInterface;
    }


    /**
     * @param int $imageId
     * @return array|Image
     */
    public function findImageById( $imageId )
    {
        $data = [
            'id' => $imageId
        ];

        if ( $this->imageValidator->with( $data )->passes( ImageValidatorInterface::EXISTS_BY_ID ) )
        {
            return Image::find( $imageId );
        }

        return $this->imageValidator->errors();
    }


    /**
     * @param mixed $imageDataOrImageURL
     * @param array $options
     * @return array|bool|Image
     */
    public function createImage( $imageDataOrImageURL, array $options = [] )
    {
        $path = @$options['path'];

        $imagePath = $this->imageProcessingService->createImageFromImageDataOrURL( $imageDataOrImageURL, $path );

        if ( $imagePath )
        {
            $filename = basename( $imagePath );

            $data = [
                'path' => $imagePath,
                'filename' => $filename,
                'processed' => FALSE,
                'type' => @$options['type'],
            ];

            if ( $this->imageValidator->with( $data )->passes( ImageValidatorInterface::CREATE ) )
            {
                $image = Image::create( $data );

                event( new ImageWasCreated( $image, $options ) );

                return $image;
            }

            return $this->imageValidator->errors();
        }

        return FALSE;

    }


    /**
     * @param $imageId
     * @param bool|TRUE $skipValidation
     * @return array|bool
     */
    public function deleteImage( $imageId, $skipValidation = TRUE )
    {
        $data = [
            'id' => $imageId
        ];

        if ( $skipValidation || $this->imageValidator->with( $data )->passes( ImageValidatorInterface::EXISTS_BY_ID ) )
        {
            $image = Image::find( $imageId );

            event( new ImageWasDeleted( $image ) );

            return TRUE;
        }

        return $this->imageValidator->errors();
    }


    /**
     * @param mixed $imageIdOrImage
     * @param array $options
     * @return array
     */
    public function processImage( $imageIdOrImage, array $options = [] )
    {
        $image = $this->getInstance( $imageIdOrImage );

        if ( !is_array( $image ) && !$image->processed )
        {
            $sizes = @$options['sizes'];
            $sizes = is_array( $sizes ) ? $sizes : [];

            $finalThumbs = [];
            $destinationPath = dirname( $image->path );
            foreach( $sizes as $sizeKey => $size )
            {
                $thumbs = $this->imageProcessingService->resizeOrCropImageToSizes( $image->path, $destinationPath, [ $size ], $options );

                if ( $thumbs && !empty( $thumbs ) )
                {
                    $thumb =  array_first( $thumbs );
                    $finalThumbs[ $sizeKey ] = $thumb;
                }
            }

            // Update the file.
            $update = [
                'thumbnails' => json_encode( $finalThumbs ),
                'processed' => TRUE
            ];

            $image->update( $update );

            foreach( $update as $key => $value ) $image->{$key} = $value;

            event( new ImageWasProcessed( $image ) );

        }

        return $image;

    }


    /**
     * @param $imageIdOrImage
     * @return array|bool
     */
    public function destroyImage( $imageIdOrImage )
    {
        $isImage = $imageIdOrImage instanceof Image;

        $data = [
            'id' => $isImage ? $imageIdOrImage : $imageIdOrImage->id
        ];

        if ( $isImage || $this->imageValidator->with( $data )->passes( ImageValidatorInterface::EXISTS_BY_ID_EVEN_DELETED ) )
        {
            $image = $isImage ? $imageIdOrImage : Image::find( $imageIdOrImage )->withTrashed();

            // Remove from local disk.
            $this->removeImageFromDisk( $image, config( 'images.local_disk_name', 'local' ) );

            // If it's in the cloud then remove it from the cloud too.
            if ( $image->processed )
            {
                $this->removeImageFromDisk( $image, config( 'images.cloud_disk_name' ) );
            }

            // Destroy the entry.
            $image->forceDelete();

            return TRUE;
        }

        return $this->imageValidator->errors();
    }


    /*******************************************************************************************************************
     *******************************************************************************************************************
     ******************************************************************************************************************/
    /**
     * @param Image $image
     * @param $diskName
     */
    protected function removeImageFromDisk( Image $image, $diskName )
    {
        if ( $diskName )
        {
            $disk = Storage::disk( $diskName );

            // Delete all thumbnails locally.
            foreach( $image->thumbnails( NULL, TRUE ) as $thumb )
            {
                $folderName = dirname( $thumb );
                if ( $disk->exists( $folderName ) ) $disk->deleteDirectory( $folderName );
            }

            // Also remove the main pic.
            if ( $disk->exists( $image->path ) ) $disk->delete( $image->path );
        }
    }

    /**
     * @param $imageOrImageId
     * @return array
     */
    protected function getInstance( $imageOrImageId )
    {
        $isImage = $imageOrImageId instanceof Image;

        return $isImage ? $imageOrImageId : $this->findImageById( $imageOrImageId->id );

    }

}