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
            $table->foreignIdFor(Album::class)->nullable();
            $table->foreignIdFor(User::class);
            $table->string('title');
            $table->string('path');
            $table->text('legend')->nullable();
            $table->date('taken')->nullable();
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
        Schema::dropIfExists('photos');
    }
};
