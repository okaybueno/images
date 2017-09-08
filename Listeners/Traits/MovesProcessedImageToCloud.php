<?php

namespace OkayBueno\Images\Listeners\Traits;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use OkayBueno\Images\Events\ImageWasMovedToCloud;
use OkayBueno\Images\Models\Image;

/**
 * Class MovesProcessedImageToCloud
 * @package OkayBueno\Images\Listeners\Traits
 */
trait MovesProcessedImageToCloud
{
    /**
     * @param Image $image
     */
    public function moveProcessedImageToCloud( Image $image )
    {
        $cloudDiskName = config( 'images.cloud_disk_name', NULL);

        if ( $image->processed && $cloudDiskName )
        {
            $localDisk = Storage::disk( config( 'images.local_disk_name', 'local' ) );
            $cloudDisk = Storage::disk( $cloudDiskName );

            $diskBasePath = trim( $localDisk->getAdapter()->getPathPrefix(), '/' );

            $filePath = get_path_to( $diskBasePath, $image->path );

            $localFile = new File( $filePath );

            // Copy main file to the cloud.
            $cloudDisk->putFileAs( dirname( $image->path ), $localFile, $image->filename );

            foreach( $image->thumbnails( NULL, TRUE ) as $thumb )
            {
                if ( !$cloudDisk->exists( $thumb ) )
                {
                    $folderName = dirname( $thumb );
                    // And move them to the cloud.
                    $thumbFile = new File( get_path_to( $diskBasePath, $thumb ) );
                    $cloudDisk->putFileAs( $folderName, $thumbFile, $image->filename );
                }
            }

            $image->cloud = TRUE;
            $image->save();

            event( new ImageWasMovedToCloud( $image ) );
        }
    }
}