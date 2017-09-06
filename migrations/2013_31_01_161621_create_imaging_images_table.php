<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateImagingImagesTable
 */
class CreateImagingImagesTable extends Migration
{
    const IMAGES_TABLE = "images";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( self::IMAGES_TABLE , function (Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->text('path');
            $table->text('filename');
            $table->string('type')->nullable()->default(NULL);
            $table->boolean('processed')->nullable()->default(FALSE);
            $table->text('thumbnails')->nullable()->default(NULL);
            $table->text('metadata')->nullable()->default(NULL);
            $table->timestampsTz();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop( self::IMAGES_TABLE );
    }
}
