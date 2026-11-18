<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index des requêtes de la galerie : photos d'un album dans l'ordre de prise de vue, photos d'un matelot,
     * commentaires d'une photo et albums par date. Ajoute aussi la vignette de la couverture des albums,
     * montrée dans le bandeau de la galerie et sur la page d'une sortie à la place de l'image d'origine.
     */
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->index(['album_id', 'taken_at']);
            $table->index('user_id');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index(['commentable_type', 'commentable_id']);
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->index('date');
            $table->string('cover_thumb_path')->nullable()->after('imagePath');
        });
    }

    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn('cover_thumb_path');
            $table->dropIndex(['date']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['commentable_type', 'commentable_id']);
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['album_id', 'taken_at']);
        });
    }
};
