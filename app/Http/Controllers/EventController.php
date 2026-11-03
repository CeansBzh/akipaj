<?php

namespace App\Http\Controllers;

use App\Enums\AlertLevelEnum;
use App\Models\Event;
use App\Rules\RichTextLength;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * Crée l'instance du contrôleur.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Event::class, 'event');
    }

    /**
     * Affiche la liste des ressources.
     */
    public function index(): View
    {
        return view('event.index')->with('events', Event::where('start_time', '>=', date('Y-m-d'))->orderBy('start_time')->get());
    }

    /**
     * Affiche le formulaire de création d'une ressource.
     */
    public function create(): View
    {
        return view('event.create');
    }

    /**
     * Enregistre une nouvelle ressource.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'description' => ['required', 'string', new RichTextLength(1000)],
            'start_time' => 'required|date|after_or_equal:today',
            'end_time' => 'required|date|after_or_equal:start_time',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|mimes:png,jpg,jpeg,gif|max:10000|dimensions:max_width=2560,max_height=1600',
        ]);

        $event = new Event;
        $event->name = $request->name;
        $event->description = $request->description;
        $event->start_time = $request->start_time;
        $event->end_time = $request->end_time;
        $event->location = $request->location;
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $path = $request->file('image')->storeAs('public/events', $imageName);
            $event->imagePath = Storage::url($path);
        }
        $event->save();

        session()->flash('alert-'.AlertLevelEnum::SUCCESS->name, 'Événement créé avec succès.');

        return redirect()->route('events.index');
    }

    /**
     * Affiche la ressource indiquée.
     */
    public function show(Event $event): View
    {
        return view('event.show')->with('event', Event::findOrFail($event->id));
    }

    /**
     * Affiche le formulaire de modification de la ressource indiquée.
     */
    public function edit(Event $event): View
    {
        return view('event.edit')->with('event', Event::findOrFail($event->id));
    }

    /**
     * Met à jour la ressource indiquée.
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        $request->request->add(['remove_image' => filter_var($request->remove_image, FILTER_VALIDATE_BOOLEAN)]);
        $request->validateWithBag('updateEvent', [
            'name' => 'required|string|max:50',
            'description' => ['required', 'string', new RichTextLength(1000)],
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
            $filePath = 'public/events/'.basename($event->imagePath);
            if (Storage::delete($filePath)) {
                $event->imagePath = null;
            }
        }
        // Si une nouvelle image a été uploadée, on la sauvegarde
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $path = $request->file('image')->storeAs('public/events', $imageName);
            $event->imagePath = Storage::url($path);
        }
        $event->save();

        session()->flash('alert-'.AlertLevelEnum::SUCCESS->name, 'Événement modifié avec succès.');

        return redirect()->route('events.show', $event);
    }

    /**
     * Supprime la ressource indiquée.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $event = Event::findOrFail($event->id);
        if ($event->imagePath) {
            $filePath = 'public/events/'.basename($event->imagePath);
            Storage::delete($filePath);
        }
        $event->delete();

        session()->flash('alert-'.AlertLevelEnum::SUCCESS->name, 'Événement supprimé avec succès.');

        return redirect()->route('events.index');
    }
}
