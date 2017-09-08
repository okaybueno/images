<?php

namespace OkayBueno\Images\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Image
 * @package OkayBueno\Images\Models
 */
class Image extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'path', 'filename', 'type', 'thumbnails', 'metadata', 'processed'
    ];


    /*******************************************************************************************************************
     ************************************************** HELPERS / DATA *************************************************
     *******************************************************************************************************************/
    /**
     * @param null $size
     * @param bool|FALSE $onlyPath
     * @return array|null|string
     */
    public function thumbnails( $size = NULL, $onlyPath = FALSE )
    {
        $sizesArray = [];

        if ( is_null( $size ) )
        {
            if ( is_array( $this->thumbnails ) )
            {
                foreach( $this->thumbnails as $sizeKey => $path )
                {
                    $sizesArray[ $sizeKey ] = $onlyPath ? $path : $this->url_to( $path );
                }
            }

            return empty( $sizesArray ) ? NULL : $sizesArray;

        } else
        {
            if ( is_array( $this->thumbnails) )
            {
                foreach( $this->thumbnails as $sizeKey => $path )
                {
                    if( $size == $sizeKey ) return $onlyPath ? $path : $this->url_to( $path );
                }
            }
        }

        return NULL;
    }

    /**
     * @return string
     */
    public function url()
    {
        return $this->url_to( $this->path );
    }

    /*******************************************************************************************************************
     **************************************************** INTERNAL *****************************************************
     *******************************************************************************************************************/

    /**
     * @param $value
     * @return mixed
     */
    public function getThumbnailsAttribute( $value )
    {
        return json_decode( $value, TRUE );
    }

    /**
     * @param $path
     * @return string
     */
    protected function url_to( $path )
    {
        $localUrl = config('images.local_disk_url');
        $remoteUrl = config('images.cloud_disk_url');
        if ( $this->processed ) $cdnBaseUrl = $remoteUrl ? $remoteUrl : $localUrl;
        else $cdnBaseUrl = $localUrl;

        $cdnUrlToImage = rtrim( $cdnBaseUrl, '/') . '/' . trim( $path, '/');

        return $cdnUrlToImage;
    }


}
