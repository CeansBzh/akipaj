<?php

use App\Models\User;
use App\Models\Album;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Album::class);
            $table->foreignIdFor(User::class);
            $table->string('title');
            $table->string('path');
            $table->string('thumb_path');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('thumb_width')->nullable();
            $table->integer('thumb_height')->nullable();
            $table->text('legend')->nullable();
            $table->double('latitude',)->nullable();
            $table->double('longitude')->nullable();
            $table->timestamp('taken_at')->nullable();
            $table->timestamps();
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
        File::cleanDirectory(storage_path('app/public/photos'));
        File::makeDirectory(storage_path('app/public/photos/thumbs'));
        Schema::dropIfExists('photos');
    }
};
