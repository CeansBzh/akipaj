<?php

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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('location')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('imagePath')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        File::cleanDirectory(storage_path('app/public/events'));
        Schema::dropIfExists('events');
    }
};
