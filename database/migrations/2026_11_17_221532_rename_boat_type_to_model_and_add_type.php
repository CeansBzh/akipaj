<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Mots qui désignent un catamaran dans le modèle d'un bateau (le type ou la marque).
     *
     * @var list<string>
     */
    private const CATAMARAN_HINTS = ['cata', 'lagoon', 'lucia', 'fountaine', 'pajot', 'nautitech', 'bali', 'leopard', 'excess', 'saona', 'astrea'];

    /**
     * Mots qui désignent un voilier à deux mâts dans le modèle d'un bateau.
     *
     * @var list<string>
     */
    private const KETCH_HINTS = ['ketch', 'yawl', 'goélette', 'goelette'];

    /**
     * Renomme la colonne `type` des bateaux en `model`, puisqu'elle contient le modèle,
     * puis ajoute un vrai type (monocoque, ketch, catamaran) déduit du modèle des bateaux existants.
     */
    public function up(): void
    {
        Schema::table('boats', function (Blueprint $table) {
            $table->renameColumn('type', 'model');
        });

        Schema::table('boats', function (Blueprint $table) {
            $table->string('type', 20)->nullable()->after('name');
        });

        DB::table('boats')->whereNotNull('model')->where('model', '!=', '')->orderBy('id')
            ->each(function (object $boat) {
                $model = Str::lower($boat->model);
                $type = match (true) {
                    Str::contains($model, self::CATAMARAN_HINTS) => 'catamaran',
                    Str::contains($model, self::KETCH_HINTS) => 'ketch',
                    default => 'monohull',
                };

                DB::table('boats')->where('id', $boat->id)->update(['type' => $type]);
            });
    }

    /**
     * Supprime le type et redonne au modèle son ancien nom de colonne.
     */
    public function down(): void
    {
        Schema::table('boats', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('boats', function (Blueprint $table) {
            $table->renameColumn('model', 'type');
        });
    }
};
