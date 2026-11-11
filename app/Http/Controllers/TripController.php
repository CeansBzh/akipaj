<?php

namespace App\Http\Controllers;

use App\Enums\AlertLevelEnum;
use App\Http\Requests\Trip\StoreTripRequest;
use App\Http\Requests\Trip\UpdateTripRequest;
use App\Models\Trip;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TripController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Trip::class, 'trip');
    }

    /**
     * Affiche les sorties de la plus récente à la plus ancienne, rangées par année, avec leur frise.
     */
    public function index(): View
    {
        $trips = Trip::query()
            ->withCount('users')
            ->with(['albums' => fn ($query) => $query->withCount('photos')])
            ->orderByDesc('start_date')
            ->get();

        return view('trip.index')->with([
            'trips' => $trips,
            'tripsByYear' => $trips->groupBy(fn (Trip $trip): int => $trip->start_date->year),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('trip.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTripRequest $request): RedirectResponse
    {
        $trip = new Trip;
        $trip->title = $request->title;
        $trip->description = $request->description;
        $trip->start_date = $request->start_date;
        $trip->end_date = $request->end_date;
        $trip->location = $request->location;
        $trip->uuid = $request->uuid;
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $path = $request->file('image')->storeAs('public/trips', $imageName);
            $trip->imagePath = Storage::url($path);
        }
        $trip->save();
        // Attach albums
        if ($request->has('albums')) {
            $trip->albums()->attach($request->albums);
        }
        // Attach users
        if ($request->has('users')) {
            $trip->users()->attach($request->users);
        }
        // Attach boats
        if ($request->has('boats') && is_array($request->boats)) {
            $trip->boats()->createMany($request->boats);
        }
        $this->saveCourse($trip, $request);

        session()->flash('alert-'.AlertLevelEnum::SUCCESS->name, 'Sortie créée avec succès.');

        return redirect()->route('trips.index');
    }

    /**
     * Affiche une sortie, avec son parcours, ses bateaux, ses matelots et ses albums, même ceux qui n'ont pas encore de photo.
     */
    public function show(Trip $trip): View
    {
        return view('trip.show')->with('trip', $trip->load([
            'boats',
            'users',
            'albums' => fn ($query) => $query->withCount('photos')->with('oldestPhoto')->orderBy('date'),
            'coursePoints',
        ]));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Trip $trip): View
    {
        return view('trip.edit')->with('trip', $trip->load(['boats', 'coursePoints']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTripRequest $request, Trip $trip): RedirectResponse
    {
        $trip = Trip::findOrFail($trip->id);
        $trip->title = $request->title;
        $trip->description = $request->description;
        $trip->start_date = $request->start_date;
        $trip->end_date = $request->end_date;
        $trip->location = $request->location;
        $trip->uuid = $request->uuid;
        // Suppression de l'image actuelle si demandée, ou si une autre image a été uploadée
        if (($request->remove_image || $request->hasFile('image')) && $trip->imagePath) {
            $filePath = 'public/trips/'.basename($trip->imagePath);
            if (Storage::delete($filePath)) {
                $trip->imagePath = null;
            }
        }
        // Si une nouvelle image a été uploadée, on la sauvegarde
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $path = $request->file('image')->storeAs('public/trips', $imageName);
            $trip->imagePath = Storage::url($path);
        }
        // Modification des albums
        if ($request->has('albums')) {
            $trip->albums()->sync($request->albums);
        }
        // Modification des utilisateurs
        if ($request->has('users')) {
            $trip->users()->sync($request->users);
        }
        // Modification des bateaux
        if ($request->has('boats')) {
            // Mise à jour des bateaux existants et création des nouveaux
            $savedBoatIds = [];
            foreach ($request->boats as $boat) {
                $savedBoatIds[] = $trip->boats()->updateOrCreate(
                    ['id' => isset($boat['id']) ? (int) $boat['id'] : null],
                    ['name' => $boat['name'], 'type' => $boat['type'], 'year' => $boat['year'], 'builder' => $boat['builder'], 'renter' => $boat['renter'], 'city' => $boat['city'], 'crew' => $boat['crew']]
                )->id;
            }
            // Suppression des bateaux supprimés (ceux qui n'ont pas été envoyés)
            $trip->boats()->whereNotIn('id', $savedBoatIds)->delete();
        } elseif ($trip->boats()->count() > 0) {
            // Si aucun bateau n'a été envoyé, on supprime tous les bateaux de la sortie
            $trip->boats()->delete();
        }

        $trip->save();
        $this->saveCourse($trip, $request);

        session()->flash('alert-'.AlertLevelEnum::SUCCESS->name, 'Sortie modifiée avec succès.');

        return redirect()->route('trips.show', $trip);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trip $trip): RedirectResponse
    {
        $trip = Trip::findOrFail($trip->id);
        // Remove image
        if ($trip->imagePath) {
            $filePath = 'public/trips/'.basename($trip->imagePath);
            Storage::delete($filePath);
        }
        // Remove albums
        $trip->albums()->detach();
        // Remove users
        $trip->users()->detach();
        // Remove trip
        $trip->delete();

        session()->flash('alert-'.AlertLevelEnum::SUCCESS->name, 'Sortie supprimée avec succès.');

        return redirect()->route('trips.index');
    }

    /**
     * Remplace le parcours de la sortie par celui du formulaire, quand le formulaire en contient un.
     * Les points sont rangés par jour, sans changer leur ordre au sein d'une même journée.
     */
    private function saveCourse(Trip $trip, StoreTripRequest|UpdateTripRequest $request): void
    {
        if (! $request->has('course')) {
            return;
        }

        /** @var list<array{name: string, day: int|string, latitude: float|string, longitude: float|string}> $points */
        $points = $request->validated('course') ?? [];

        DB::transaction(function () use ($trip, $points): void {
            $trip->coursePoints()->delete();
            $trip->coursePoints()->createMany(
                collect($points)
                    ->sortBy(fn (array $point): int => (int) $point['day'])
                    ->values()
                    ->map(fn (array $point, int $index): array => [
                        'position' => $index + 1,
                        'day' => (int) $point['day'],
                        'name' => $point['name'],
                        'latitude' => $point['latitude'],
                        'longitude' => $point['longitude'],
                    ])
                    ->all()
            );
        });
    }
}
