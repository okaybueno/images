# IMPORTANT ❗
**This package has been discontinued so it won't receive any other update. If you're using it please consider migrating to another solution. There are many other great solutions out there (ie. "medialibrary") that this package does not provide any benefit whatsoever anymore.**

--- 
# Image

A package that provides an easy way to work with images attached to models. 

[![Latest Version on Packagist](https://img.shields.io/packagist/v/okaybueno/images.svg?style=flat-square)](https://packagist.org/packages/okaybueno/images)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Quality Score](https://img.shields.io/scrutinizer/g/okaybueno/images.svg?style=flat-square)](https://scrutinizer-ci.com/g/okaybueno/images)
[![Total Downloads](https://img.shields.io/packagist/dt/okaybueno/images.svg?style=flat-square)](https://packagist.org/packages/okaybueno/images)

## Disclaimer

This package was originally released [here](https://github.com/followloop/imaging), but since the future 
of that package is not clear, it has been forked and re-worked under this repository.

## Goal

Pretty often we have to use images attached to models (like profile pictures) and the process to implement this is always
pretty much the same: upload image, store it, resize it, move thumbnails and original to cloud storage and so on.
Sometimes things get even a bit more complex and we have to re-size the existing images, remove the old ones and so on.

So the goal of this package is to provide a neat and clean way to work with these kind of images attached to models,
in a way that allows us to store them (both locally and remotely) and work with them (resize them, remove old ones, etc),
both synchronously and asynchronously, reducing the amount of work that we have to do to integrate this in our projects.

## Installation

1. Install this package by adding it to your `composer.json` or by running `composer require okaybueno/images` in your project's folder.
2. For Laravel 5.5 the Service provider is automatically registered, but if you're using Laravel 5.4, then you must add the 
provider to your `config/app.php` file: `OkayBueno\Images\ImageServiceProvider::class`
3. Publish the configuration file by running `php artisan vendor:publish --provider="OkayBueno\Images\ImageServiceProvider"`
4. Open the configuration file (`config/images.php`) and configure the settings according to your needs. Keep reading to know what the parameters mean.
5. Ready to go!


## Usage

The package provides 5 different things:
- **Image Model:** You can establish relations in your entities by using this model.
- **Configuration file:** It contains the settings for disks, image drivers to use and default settings for image processing.  
- **Image Service:** It allows you to upload, process, and remove the images.
- **Events/Listeners**: Allow you to handle the operations with the images (process, move them to CDN, etc).
- **Commands**: Allow you to purge deleted images (destroy from disk) and resize existing ones.

### Image Model

The model is located at `OkayBueno\Images\Models\Image`. You should include this model as a relation in all your models that
may contain images. This model contains 2 main functions that just return some values:

**+ thumbnails( $size = NULL, $onlyPath = FALSE):** Calling this will return the thumbnails generated for this image.
By default, the function returns the full URL for ALL thumbnails. You can also select just one size (1st param) or
return just the path to the image (2nd param).

**+ url():** Returns the full URL to the original image.

### Configuration file

It contains the parameters that need to be configured to make the package work properly.

```
    /*
    |--------------------------------------------------------------------------
    | Driver for image processing
    |--------------------------------------------------------------------------
    | The driver that you want to use to process the images.
    |
    | Accepted values: 'gd', 'imagick'
    |
    */
    'driver' => 'gd',

    /*
    |--------------------------------------------------------------------------
    | Default processing settings
    |--------------------------------------------------------------------------
    | When processing an image we can apply different settings. Although for
    | each image we can apply custom settings, we still need default ones for
    | for those images where no settings are applied.
    |
    */
    'processing_settings' => [
        'maintain_aspect_ratio'     => TRUE, // accepts TRUE or FALSE.
        'prevent_upsizing'          => TRUE, // accepts TRUE or FALSE.
        'crop'                      => FALSE, // accepts TRUE or FALSE.
        'extension'                 => 'png', // accepts jpg, png, gif, bmp, etc.
        'quality'                   => 90 // accepts any integer between 0 and 100.
    ],

    /*
    |--------------------------------------------------------------------------
    | Local disk name
    |--------------------------------------------------------------------------
    |
    | All images are first uploaded/stored in your local disk, so please
    | specific the name of disk you want to use for this.
    |
    | You can add more disks in config/filesystems.php
    |
    */
    'local_disk_name' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Local disk URL
    |--------------------------------------------------------------------------
    |
    | When the image is not moved to the cloud yet, it still needs to be
    | served, so please specify here the full URL to the base folder
    | where you store the uploaded images in your local disk.
    */
    'local_disk_url' => 'http://temporal-url',

    /*
    |--------------------------------------------------------------------------
    | Cloud disk URL
    |--------------------------------------------------------------------------
    |
    | After one image is uploaded and processed, it can be moved to the cloud.
    | To do so, besides wiring up the events and listeners, you have to
    | specify a cloud disk here. If you do not want to use a cloud disk,
    | just leave this empty.
    |
    | You can add more disks in config/filesystems.php
    |
    */
    'cloud_disk_name' => '',

    /*
    |--------------------------------------------------------------------------
    | Cloud disk URL
    |--------------------------------------------------------------------------
    |
    | Same as local disk URL, but for the cloud. This URL will be used
    | once (and if) the images have been moved to the cloud.
    |
    */
    'cloud_disk_url' => '',
```

### Image Service

The Image Service is bind to an interface located at `OkayBueno\Images\Services\ImageServiceInterface`.The service contains
the next functions:

```php
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
    public function createImage( $imageB64OrUploadedFile, array $options = [] );
    
    /**
     * Deletes (soft deletes) an image from the database
     *
     * @param int $imageId Id of the image to delete.
     * @param bool|true $skipValidation If this is set to true, the validation will be skipped and the function won't
     * check if the entity exists in the DB.
     * @return mixed true if success, array with error otherwise.
     */
    public function deleteImage( $imageId, $skipValidation = TRUE);
    
    /**
     * Processes an image stored in the local disk, and resizes it to the given sizes.
     *
     * @param mixed $imageIdOrImage ID of the image, or instance of the image to process.
     * @param array $sizes associative array with the sizes that the image needs to be resized to.
     * @return mixed Image instance.
     */
    public function processImage( $imageIdOrImage, array $sizes = [] );
    
    /**
     * Destroy an image (and its thumbs) from the disks and from the DB. It cannot be reverted.
     *
     * @param mixed $imageIdOrImage ID or instance of the image to destroy.
     * @return mixed true if success, array with errors otherwise.
     */
    public function destroyImage( $imageIdOrImage );

```


### Events / Listeners

There are some events and listeners included:

#### EVENTS:

**OkayBueno\Images\Events\ImageWasCreated:** Triggered when a new image was successfully uploaded and created in the DB/local disk.  
**OkayBueno\Images\Events\ImageWasProcessed:** Triggered when the image is processed and the thumbnails were generated (locally).  
**OkayBueno\Images\Events\ImageWasMovedToCloud:** Triggered when the image (and all its thumbnails) were moved to the cloud disk.  
**OkayBueno\Images\Events\ImageWasDeleted:** Triggered when an image gets deleted (soft deleted).  
 
===========

#### LISTENERS:

**OkayBueno\Images\Listeners\ProcessImageAsync:** Processes the received image asynchronously (queued). It can subscribe to the ImageWasCreated event.  
**OkayBueno\Images\Listeners\ProcessImageSync:** Processes the received image synchronously (not queued). It can subscribe to the ImageWasCreated event.  
**OkayBueno\Images\Listeners\MoveProcessedImagesToCloudImageAsync:** Moves the processed image and its thumbnail to the cloud disk asynchronously (queued). It can subscribe to the ImageWasProcessed event.  
**OkayBueno\Images\Listeners\MoveProcessedImagesToCloudImageSync:** Moves the processed image and its thumbnail to the cloud disk synchronously (not queued). It can subscribe to the ImageWasProcessed event.  
**OkayBueno\Images\Listeners\RemoveLocalImageAsync:** Deletes the local files for the received image asynchronously (queued). It can subscribe to the ImageWasMovedToCloud event.  
**OkayBueno\Images\Listeners\RemoveLocalImageSync:** Deletes the local files for the received image synchronously (not queued). It can subscribe to the ImageWasMovedToCloud event.  
  
To configure them just add them to your EventServiceProvider located at `app/Providers/EventServiceProvider.php`

As you can see, there are Async and Sync events. Subscribe just to one of them, according to your needs.


### Commands

There are 2 commands included, that help you maintain the images.

##### *php artisan images:purge-deleted-images {--days=30}:*

Takes all the images deleted `--days` or more days ago (30 by default) and destroys them from the DB and from the local and remote disks.

##### *php artisan images:resize {--image-id=} {--image-type=} {--sizes=}*
 
 Resizes the given image (by `-image-id`) or the group of given images (by `--image-type`) to the new sizes provided via `--sizes`.
 
 NOTE: `--sizes` should contain ALL the sizes, not only the new ones. All the other versions of the pic that do not exist in this parameter will be removed.
 This parameter should have this structure: "big:500x500,small:100x100,medium:x250".


## Practical Example

Let's suppose you have a user entity with a profile pic. You should include the relation with the Image entity like this:

```php

public function profile_picture()
{
    return $this->hasOne( \OkayBueno\Images\Models\Image::class, 'id', 'profile_picture_id' );
}

```

Whenever you need to update the image, you need to use the Image Service provided. We recommended that you handle the
image creation and old images removal at once, so you can keep images updated and synchronized. An example of function
would be the next one:

```php

protected function createAndSaveProfilePictureForUser( $image, User $user )
{
    if ( $image )
    {
        $prefix = substr( $user->uuid , 0, 3);
        $options = [
            'sizes' => [
                'large' => '600x600',
                'medium' => '300x300',
                'small' => '100x100'
            ],
            'path' => sprintf( 'users/%s/%s/%s/', $prefix, $user->uuid, (string)date('dmY') ),
            'crop' => TRUE,
            'extension' => 'png'
        ];

        $imageResult = $this->imageService->createImage( $image , $options );

        if ( $imageResult instanceof Image )
        {
            $saveData = [
                'picture_id' => $imageResult->id
            ];

            // Remove other image from post.
            $userPictureId = $user->picture_id;
            if ( $userPictureId ) $this->imageService->deleteImage( $userPictureId, TRUE );

            $this->usersRepository->updateBy( $saveData, $user->id );

            $user = $this->usersRepository->findOneBy( $user->id );

            return $user;
        }

        return $imageResult;
    }

    return $user;
}

```

`$image` can be anything that can create an instance of an Intervention Image object. 

Then, whenever you call your function to create or update your user, you can include this method to update the image:

```php
public function updateUser( User $user, array $newUserData )
{
    ... 
    
    ...
    
    if ( array_key_exists( 'profile_picture', $newUserData ) )
    {
        $user = $this->createAndSaveProfilePictureForUser( $newUserData['profile_picture'], $user );
        unset( $newUserData['profile_picture'] );
    }
    
    ... 
        
    ...
    
    return $user;
}

```

In order to make all this work automatically, your EventServiceProvider should wire up the events and listeners:

```php
    protected $listen = [
        'OkayBueno\Images\Events\ImageWasCreated' => [
            'OkayBueno\Images\Listeners\ProcessImageSync'
        ],
        'OkayBueno\Images\Events\ImageWasProcessed' => [
            'OkayBueno\Images\Listeners\MoveProcessedImagesToCloudImageSync'
        ],
        'OkayBueno\Images\Events\ImageWasMovedToCloud' => [
            'OkayBueno\Images\Listeners\RemoveLocalImageSync'
        ]
    ];
```

This way, we are wiring up all the events to the listeners in order to:

- Once the image is created (E) --> Process the image asynchronously (L).
- Once that it's processed (E) --> Move it asynchronously to the cloud (L).
- Once that is moved to the cloud (E) --> Delete it from the local disk (L).

You can of course set this listeners to be processed automatically (by replacing 'Async' by 'Sync' in the name of the
listener).

You can also decide if you use cloud disks, or if just store files just locally, etc... For example, you could choose not to move
the files to the cloud and process them asynchronously, in which case you would have just something like this:

```php
    protected $listen = [
        'OkayBueno\Images\Events\ImageWasCreated' => [
            'OkayBueno\Images\Listeners\ProcessImageAsync'
        ]
    ];
```

That's all! Simply put: the way you configure your events and listeners wil define the way that the files will be stored.

## Changelog

-- No official version released yet --

## Credits

- [okay bueno - A fully transparent digital products studio](http://www.okaybueno.com)
- [Jesús Espejo](https://github.com/jespejoh) ([Twitter](https://twitter.com/jespejo89))

## Bugs & contributing

* Found a bug? That's good (and bad). Let me know using the Issues on Github.
* Need a feature or have something interesting to contribute with? Great! Open a pull request.

## To-dos

- Automated tests: Although this package is already used on production environments, there are no automated tests in place...

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
