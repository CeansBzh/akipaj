<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Copies réduites, en WebP, de l'image de couverture des sorties : le navigateur choisit la plus petite
     * qui remplit l'emplacement, au lieu de télécharger l'image d'origine. Elles sont rangées par largeur.
     */
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->json('image_sizes')->nullable()->after('imagePath');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('image_sizes');
        });
    }
};
