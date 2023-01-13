<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Enum\AlertLevelEnum;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Trip\StoreTripRequest;
use App\Http\Requests\Trip\UpdateTripRequest;

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('trip.index')->with(
            'tripsByYear',
            Trip::orderBy('start_date')->get()
                ->groupBy([
                    function ($val) {
                        return $val->start_date->format('Y');
                    },
                ])
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // TODO import données depuis évenement programme existant (titre, dates, description ?)
        return view('trip.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Trip\StoreTripRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTripRequest $request)
    {
        $trip = new Trip();
        $trip->title = $request->title;
        $trip->description = $request->description;
        $trip->start_date = $request->start_date;
        $trip->end_date = $request->end_date;
        $trip->uuid = $request->uuid;
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
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

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Sortie créée avec succès.');

        return redirect()->route('trips.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function show(Trip $trip)
    {
        return view('trip.show')->with('trip', Trip::findOrFail($trip->id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function edit(Trip $trip)
    {
        return view('trip.edit')->with('trip', $trip->load('boats'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Trip\UpdateTripRequest  $request
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTripRequest $request, Trip $trip)
    {
        $trip = Trip::findOrFail($trip->id);
        $trip->title = $request->title;
        $trip->description = $request->description;
        $trip->start_date = $request->start_date;
        $trip->end_date = $request->end_date;
        $trip->uuid = $request->uuid;
        // Suppression de l'image actuelle si demandée, ou si une autre image a été uploadée
        if (($request->remove_image || $request->hasFile('image')) && $trip->imagePath) {
            $filePath = 'public/trips/' . basename($trip->imagePath);
            if (Storage::delete($filePath)) {
                $trip->imagePath = null;
            }
        }
        // Si une nouvelle image a été uploadée, on la sauvegarde
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
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
            foreach ($request->boats as $boat) {
                if (isset($boat['id'])) {
                    $boat['id'] = (int) $boat['id'];
                }

                $trip->boats()->updateOrCreate(
                    ['id' => $boat['id']],
                    ['name' => $boat['name'], 'type' => $boat['type'], 'year' => $boat['year'], 'builder' => $boat['builder'], 'renter' => $boat['renter'], 'city' => $boat['city'], 'crew' => $boat['crew']]
                );
            }
            // Suppression des bateaux supprimés
            $trip->boats()->whereNotIn('id', array_column($request->boats, 'id'))->delete();
        } else if ($trip->boats()->count() > 0) {
            // Si aucun bateau n'a été envoyé, on supprime tous les bateaux de la sortie
            $trip->boats()->delete();
        }

        $trip->save();

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Sortie modifiée avec succès.');

        return redirect()->route('trips.show', $trip);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function destroy(Trip $trip)
    {
        $trip = Trip::findOrFail($trip->id);
        // Remove image
        if ($trip->imagePath) {
            $filePath = 'public/trips/' . basename($trip->imagePath);
            Storage::delete($filePath);
        }
        // Remove albums
        $trip->albums()->detach();
        // Remove users
        $trip->users()->detach();
        // Remove trip
        $trip->delete();

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Sortie supprimée avec succès.');

        return redirect()->route('trips.index');
    }
}
