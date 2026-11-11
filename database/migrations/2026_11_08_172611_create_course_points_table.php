<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée les points du parcours d'une sortie : des lieux nommés, dans l'ordre, chacun rattaché à un jour de la sortie.
     */
    public function up(): void
    {
        Schema::create('course_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->unsignedSmallInteger('day');
            $table->string('name', 100);
            $table->decimal('latitude', 8, 6);
            $table->decimal('longitude', 9, 6);
            $table->timestamps();

            $table->index(['trip_id', 'position']);
        });
    }

    /**
     * Supprime les points du parcours.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_points');
    }
};
