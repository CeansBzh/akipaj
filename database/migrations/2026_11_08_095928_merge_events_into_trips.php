<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Fusionne les événements dans les sorties : une sortie à venir tient désormais lieu d'événement.
     *
     * Les événements à venir ou en cours deviennent des sorties, avec leur image. Les événements passés
     * sont supprimés avec leur image : l'historique du club, ce sont les sorties.
     */
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->string('location')->nullable()->after('description');
        });

        foreach (DB::table('events')->orderBy('id')->get() as $event) {
            if (Carbon::parse($event->end_time)->lt(today())) {
                $this->deleteImage($event->imagePath);

                continue;
            }

            DB::table('trips')->insert([
                'title' => $event->name,
                'description' => $event->description,
                'location' => $event->location,
                'start_date' => Carbon::parse($event->start_time)->toDateString(),
                'end_date' => Carbon::parse($event->end_time)->toDateString(),
                'imagePath' => $this->moveImageToTrips($event->imagePath),
                'created_at' => $event->created_at,
                'updated_at' => $event->updated_at,
            ]);
        }

        Schema::dropIfExists('events');
    }

    /**
     * Recrée la table des événements, vide, et retire le lieu des sorties.
     *
     * Les données ne sont pas restaurées : les événements devenus des sorties le restent,
     * les événements passés et le lieu des sorties sont perdus.
     */
    public function down(): void
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

        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }

    /**
     * Déplace l'image d'un événement dans le dossier des images de sortie et donne sa nouvelle adresse.
     *
     * Le préfixe « event- » évite d'écraser une image de sortie du même nom.
     * Une adresse qui ne désigne pas un fichier du dossier des événements est gardée telle quelle.
     */
    private function moveImageToTrips(?string $imagePath): ?string
    {
        if ($imagePath === null || ! Storage::exists('public/events/'.basename($imagePath))) {
            return $imagePath;
        }

        $tripImagePath = 'public/trips/event-'.basename($imagePath);
        Storage::move('public/events/'.basename($imagePath), $tripImagePath);

        return Storage::url($tripImagePath);
    }

    /**
     * Supprime l'image d'un événement, si elle est dans le dossier des événements.
     */
    private function deleteImage(?string $imagePath): void
    {
        if ($imagePath !== null) {
            Storage::delete('public/events/'.basename($imagePath));
        }
    }
};
