<?php

namespace OkayBueno\Images\Listeners\Traits;

use Illuminate\Support\Facades\Storage;
use OkayBueno\Images\Models\Image;

/**
 * Class RemovesLocalImage
 * @package OkayBueno\Images\Listeners\Traits
 */
trait RemovesLocalImage
{
    
    /**
     * @param Image $image
     */
    public function removeLocalImage( Image $image )
    {
        $localDisk = Storage::disk( config( 'images.local_disk_name', 'local' ) );

        // Delete all thumbnails.
        foreach( $image->thumbnails( NULL, TRUE ) as $thumb )
        {
            $folderName = dirname( $thumb );
            if ( $localDisk->exists( $folderName ) ) $localDisk->deleteDirectory( $folderName );
        }

        // Also remove the main pic.
        $localDisk->delete( $image->path );
    }
}