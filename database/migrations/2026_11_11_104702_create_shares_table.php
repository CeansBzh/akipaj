<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Liens de partage d'une photo ou d'un album, ouverts à qui n'a pas de compte jusqu'à leur expiration.
     */
    public function up(): void
    {
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->morphs('shareable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Durée choisie à la création, gardée pour permettre plus tard une durée propre à chaque lien
            $table->unsignedSmallInteger('expires_in_days');
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shares');
    }
};
