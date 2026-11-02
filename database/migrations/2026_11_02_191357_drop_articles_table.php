<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('articles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->index()->unique();
            $table->text('summary')->nullable();
            $table->text('body_md');
            $table->text('body_html')->nullable();
            $table->char('online', 1)->default(0);
            $table->string('imagePath')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
        });
    }
};
