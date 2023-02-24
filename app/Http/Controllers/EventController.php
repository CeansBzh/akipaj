<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Enums\AlertLevelEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Event::class, 'event');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('event.index')->with('events', Event::where('start_time', '>=', date('Y-m-d'))->orderBy('start_time')->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('event.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'required|string|max:140',
            'start_time' => 'required|date|after_or_equal:today',
            'end_time' => 'required|date|after_or_equal:start_time',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|mimes:png,jpg,jpeg,gif|max:10000|dimensions:max_width=2560,max_height=1600',
        ]);

        $event = new Event();
        $event->name = $request->name;
        $event->description = $request->description;
        $event->start_time = $request->start_time;
        $event->end_time = $request->end_time;
        $event->location = $request->location;
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $path = $request->file('image')->storeAs('public/events', $imageName);
            $event->imagePath = Storage::url($path);
        }
        $event->save();

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Événement créé avec succès.');

        return redirect()->route('events.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  Event $event
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
        return view('event.show')->with('event', Event::findOrFail($event->id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Event $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        return view('event.edit')->with('event', Event::findOrFail($event->id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Event $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        $request->request->add(['remove_image' => filter_var($request->remove_image, FILTER_VALIDATE_BOOLEAN)]);
        $request->validateWithBag('updateEvent', [
            'name' => 'required|string|max:50',
            'description' => 'required|string|max:140',
            'start_time' => 'required|date|after_or_equal:now',
            'end_time' => 'required|date|after_or_equal:start_time',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|mimes:png,jpg,jpeg,gif|max:10000|dimensions:max_width=2560,max_height=1600',
            'remove_image' => 'required|boolean',
        ]);

        $event = Event::findOrFail($event->id);
        $event->name = $request->name;
        $event->description = $request->description;
        $event->start_time = $request->start_time;
        $event->end_time = $request->end_time;
        $event->location = $request->location;

        // Suppression de l'image actuelle si demandée, ou si une autre image a été uploadée
        if (($request->remove_image || $request->hasFile('image')) && $event->imagePath) {
            $filePath = 'public/events/' . basename($event->imagePath);
            if (Storage::delete($filePath)) {
                $event->imagePath = null;
            }
        }
        // Si une nouvelle image a été uploadée, on la sauvegarde
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $path = $request->file('image')->storeAs('public/events', $imageName);
            $event->imagePath = Storage::url($path);
        }
        $event->save();

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Événement modifié avec succès.');

        return redirect()->route('events.show', $event);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Event $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        $event = Event::findOrFail($event->id);
        if ($event->imagePath) {
            $filePath = 'public/events/' . basename($event->imagePath);
            Storage::delete($filePath);
        }
        $event->delete();

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Événement supprimé avec succès.');

        return redirect()->route('events.index');
    }
}
