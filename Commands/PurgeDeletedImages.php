<?php

namespace OkayBueno\Images\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use OkayBueno\Images\Models\Image;
use OkayBueno\Images\Services\ImageServiceInterface;

/**
 * Class PurgeDeletedImages
 * @package OkayBueno\Images\Commands
 */
class PurgeDeletedImages extends Command
{
    protected $signature = 'images:purge-deleted-images {--days=30}';
    protected $description = 'Removes the images that were deleted more than the number of days specified in the command. ';

    protected $imageService;

    /**
     * @param ImageServiceInterface $imageServiceInterface
     */
    public function __construct(
        ImageServiceInterface $imageServiceInterface
    )
    {
        parent::__construct();
        $this->imageService = $imageServiceInterface;
    }


    /**
     *
     */
    public function fire()
    {
        $numberOfDays = (int)$this->option('days');
        $numberOfDays = $numberOfDays <= 0 ? 1 : $numberOfDays;

        $minDate = Carbon::now()->subDays( $numberOfDays );

        // get all the images that were removed at least $numberOfDays ago.
        $counter = -1;
        foreach( Image::where('deleted_at', '<=', $minDate )->withTrashed()->cursor() as $counter => $deletedImage  )
        {
            // Destroy image.
            $this->imageService->destroyImage( $deletedImage );
            $this->info("Image #$deletedImage->id was destroyed from disk and database.");
        }

        $counter++;

        $this->info("A total amount of $counter images were destroyed.");
    }

}