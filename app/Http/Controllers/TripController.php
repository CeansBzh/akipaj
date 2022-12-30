<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Enum\AlertLevelEnum;
use App\Http\Requests\Trip\StoreTripRequest;
use Illuminate\Http\Request;
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('trip.index')->with('trips', Trip::orderBy('start_date')->get());
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
        if ($request->has('boats')) {
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
        return view('trip.edit')->with('trip', Trip::findOrFail($trip->id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Trip $trip)
    {
        $request->request->add(['remove_image' => filter_var($request->remove_image, FILTER_VALIDATE_BOOLEAN)]);
        $request->validateWithBag('updateTrip', [
            'title' => 'required|string|max:50',
            'description' => 'required|string|max:500',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'image' => 'nullable|mimes:png,jpg,jpeg,gif|max:10000|dimensions:max_width=2560,max_height=1600',
            'remove_image' => 'required|boolean',
        ]);

        $trip = Trip::findOrFail($trip->id);
        $trip->title = $request->title;
        $trip->description = $request->description;
        $trip->start_date = $request->start_date;
        $trip->end_date = $request->end_date;

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
