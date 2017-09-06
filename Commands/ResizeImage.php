<?php

namespace OkayBueno\Images\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use OkayBueno\Images\Models\Image;
use OkayBueno\Images\Services\ImageServiceInterface;
use OkayBueno\Images\Services\src\ImageProcessingService;

/**
 * Class ResizeImage
 * @package OkayBueno\Images\Commands
 */
class ResizeImage extends Command
{
    protected $signature = 'images:resize {--image-id=} {--image-type=} {--sizes=}';
    protected $description = 'Resizes the image or group of images to the given sizes.';

    protected $imageService;
    protected $imageProcessingService;
    protected $cloudDisk;
    protected $localDisk;
    protected $localDiskBasePath;

    /**
     * @param ImageServiceInterface $imageServiceInterface
     * @param ImageProcessingService $imageProcessingService
     */
    public function __construct(
        ImageServiceInterface $imageServiceInterface,
        ImageProcessingService $imageProcessingService
    )
    {
        parent::__construct();
        $this->imageService = $imageServiceInterface;
        $this->imageProcessingService = $imageProcessingService;

        $this->loadDisks();

        $this->localDiskBasePath = trim( $this->localDisk->getAdapter()->getPathPrefix(), '/' );
    }


    /**
     *
     */
    public function fire()
    {
        $imageId = $this->option('image-id');
        $imageType = $this->option('image-type');

        if ( $imageId || $imageType )
        {
            $sizes = $this->option('sizes');
            $parsedSizes = $this->parseSizesString( $sizes );

            if ( $parsedSizes || empty( $parsedSizes ) )
            {
                // Find images or image type.
                if ( $imageId )
                {
                    $imageToResize = $this->imageService->findImageById( $imageId );

                    if ( !is_array( $imageToResize ) )
                    {
                        // Destroy image.
                        $this->info("Resizing image #$imageToResize->id...");
                        $this->resizeImage( $imageToResize, $parsedSizes );
                        $this->info("Image #$imageToResize->id was resized.");
                    } else
                    {
                        $this->error("Image with $imageId does not exist.");
                    }
                } else if ( $imageType )
                {
                    foreach( Image::where('type', $imageType )->cursor() as $counter => $imageToResize  )
                    {
                        // Destroy image.
                        $this->info("Resizing image #$imageToResize->id...");
                        $this->resizeImage( $imageToResize, $parsedSizes );
                        $this->info("Image #$imageToResize->id was resized.");
                    }
                }
            } else $this->error('Please specify the sizes you want to resize the image to.');
        } else $this->error('Please specify either the image-type that you want to resize or the image-id.');
    }


    /**
     * @param Image $image
     * @param array $newSizes
     */
    protected function resizeImage( Image $image, array $newSizes )
    {
        foreach( $image->thumbnails( NULL, TRUE ) as $thumbKey => $thumb )
        {
            $folder = dirname( $thumb );
            $folderPath = explode('/', $folder );

            if ( is_array( $folderPath ) ) $existingSize = array_pop( $folderPath );
            else $existingSize = NULL;

            $existingSize = str_replace( '_', '', $existingSize );

            $existingSizeKey = array_search( $existingSize, $newSizes );

            if ( $existingSizeKey === FALSE )
            {
                // Remove the existing size.
                $this->removeImageSize( $thumb, $image->cloud );
            }
        }

        // If the image does not exist locally, copy it over.
        if ( $image->cloud )
        {
            if ( !$this->localDisk->exists( $image->path ) )
            {
                $folderName = dirname( $image->path );
                if ( !$this->localDisk->exists( $folderName ) ) $this->localDisk->makeDirectory( $folderName, 0775);

                $fileContents = $this->cloudDisk->get( $image->path );
                $this->localDisk->put( $image->path, $fileContents );
            }
        }

        $finalThumbs = [];

        foreach( $newSizes as $sizeKey => $size )
        {
            // At this point, old images are removed, so we can convert to new sizes.
            $newThumbs = $this->imageProcessingService->resizeOrCropImageToSizes( $image->path, dirname( $image->path ), [ $size ] );

            if ( $newThumbs && !empty( $newThumbs ) )
            {
                $thumb =  array_first( $newThumbs );
                $finalThumbs[ $sizeKey ] = $thumb;
            }

            // Move thumbs to the cloud disk.
            if ( $image->cloud )
            {
                $this->moveThumbsToRemoteDisk( $newThumbs, $image->filename );
            }
        }

        // Update the file.
        $update = [
            'thumbnails' => json_encode( $finalThumbs ),
        ];

        $image->update( $update );

    }


    /**
     * @param $sizes
     * @return array
     */
    protected function parseSizesString( $sizes )
    {
        $parsedSizes = [];

        $explodedSizes = explode( ',', $sizes );

        foreach( $explodedSizes as $sizeString )
        {
            $explodedSizeString = explode( ':', $sizeString );
            if ( is_array( $explodedSizeString ) && count( $explodedSizeString ) >= 2 )
            {
                $parsedSizes[ $explodedSizeString[0] ] = $explodedSizeString[1];
            }
        }

        return $parsedSizes;
    }

    /**
     * @param $pathFromDisk
     * @param bool|false $alsoFromCloud
     */
    protected function removeImageSize( $pathFromDisk, $alsoFromCloud = FALSE  )
    {
        if ( $this->localDisk->exists( $pathFromDisk ) ) $this->localDisk->delete( $pathFromDisk );

        if ( $alsoFromCloud && $this->cloudDisk )
        {
            if ( $this->cloudDisk->exists( $pathFromDisk ) ) $this->cloudDisk->delete( $pathFromDisk );
        }
    }


    /**
     *
     */
    protected function loadDisks()
    {
        $this->localDisk = Storage::disk( config( 'images.local_disk_name', 'local' ) );

        $cloudDiskName = config( 'images.cloud_disk_name', NULL );
        if ( $cloudDiskName ) $this->cloudDisk = Storage::disk( $cloudDiskName );
    }


    /**
     * @param $thumbs
     * @param $filename
     */
    protected function moveThumbsToRemoteDisk( $thumbs, $filename )
    {
        foreach( $thumbs as $thumb )
        {
            if ( !$this->cloudDisk->exists( $thumb ) )
            {
                $folderName = dirname( $thumb );
                // And move them to the cloud.
                $thumbFile = new File( get_path_to( $this->localDiskBasePath, $thumb ) );
                $this->cloudDisk->putFileAs( $folderName, $thumbFile, $filename );

                // Remove from local.
                if ( $this->localDisk->exists( $thumb ) ) $this->localDisk->delete( $thumb );
            }
        }
    }




}