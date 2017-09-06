<?php

namespace OkayBueno\Images;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;
use OkayBueno\Images\Services\ImageServiceInterface;
use OkayBueno\Images\Services\ImageProcessingServiceInterface;
use OkayBueno\Images\Services\src\ImageProcessingService;
use OkayBueno\Images\Services\src\ImageService;
use OkayBueno\Images\Services\Validation\ImageValidatorInterface;
use OkayBueno\Images\Services\Validation\src\ImageValidatorLaravel;

/**
 * Class ImagingServiceProvider
 * @package OkayBueno\Images
 */
class ImageServiceProvider extends ServiceProvider
{

    private $configFileName = 'images.php';
    private $configPath = '/config/';


    /**
     *
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.$this->configPath.$this->configFileName => config_path( $this->configFileName),
        ], 'images');

        $this->loadMigrationsFrom( __DIR__.'/migrations' );
    }


    /**
     *
     */
    public function register()
    {
        // merge default config
        $this->mergeConfigFrom(
            __DIR__.$this->configPath , 'images'
        );

        // Bindings.
        $this->bindValidators();
        $this->bindImageProcessor();
        $this->bindImageService();

        $this->loadHelpers();

        // And commands.
        $this->registerPurgeImagesCommand();
        $this->registerResizeImageCommand();
    }


    /**
     *
     */
    private function bindImageProcessor()
    {
        // Bind the image processing service.
        $this->app->bind( ImageProcessingServiceInterface::class, function ( $app )
        {
            // create an image manager instance with favored driver
            $config = [
                'driver' => config('images.driver', 'gd')
            ];

            $imageManager = new ImageManager( $config );

            return new ImageProcessingService( $imageManager );
        });
    }

    /**
     *
     */
    private function bindValidators()
    {
        // Bind the image service.
        $this->app->bind( ImageValidatorInterface::class, function ( $app )
        {
            return $app->make( ImageValidatorLaravel::class );
        });
    }

    /**
     *
     */
    private function bindImageService()
    {
        // Bind the image service.
        $this->app->bind( ImageServiceInterface::class, function ( $app )
        {
            return $app->make( ImageService::class );
        });
    }

    /**
     *
     */
    private function loadHelpers()
    {
        foreach (glob(__DIR__.'/Helpers/*.php') as $filename) require_once( $filename );
    }


    /**
     *
     */
    private function registerPurgeImagesCommand()
    {
        $this->app->singleton('command.images.purge-deleted-images', function ($app)
        {
            return $app[ \OkayBueno\Images\Commands\PurgeDeletedImages::class ];
        });

        $this->commands('command.images.purge-deleted-images');
    }

    /**
     *
     */
    private function registerResizeImageCommand()
    {
        $this->app->singleton('command.images.resize-image', function ($app)
        {
            return $app[ \OkayBueno\Images\Commands\ResizeImage::class ];
        });

        $this->commands('command.images.resize-image');
    }


}