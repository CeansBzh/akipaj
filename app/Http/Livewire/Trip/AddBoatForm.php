<?php

namespace App\Http\Livewire\Trip;

use App\Models\Boat;
use Livewire\Component;

class AddBoatForm extends Component
{
    public $name;
    public $type;
    public $year;
    public $builder;
    public $renter;
    public $city;
    public $crew;
    public $boats = [];
    public $boatsInputData;

    protected $rules = [
        'name' => 'required|max:255',
        'type' => 'nullable|max:255',
        'year' => 'nullable|integer',
        'builder' => 'nullable|max:255',
        'renter' => 'nullable|max:255',
        'city' => 'nullable|max:255',
        'crew' => 'nullable|integer',
    ];

    public function addBoat()
    {
        $this->validate();

        $this->dispatchBrowserEvent('close-modal');

        $boat = new Boat([
            'name' => $this->name,
            'type' => $this->type,
            'year' => $this->year,
            'builder' => $this->builder,
            'renter' => $this->renter,
            'city' => $this->city,
            'crew' => $this->crew,
        ]);

        $this->boats[] = $boat;
        $this->boatsInputData = json_encode($this->boats);
        $this->resetFields();
    }

    /**
     * Function to update the boats input value when a boat is removed.
     */
    public function updatedBoats($newValue)
    {
        $this->boatsInputData = json_encode($newValue);
    }

    public function resetFields()
    {
        $this->name = '';
        $this->type = '';
        $this->year = '';
        $this->builder = '';
        $this->renter = '';
        $this->city = '';
        $this->crew = '';
    }

    public function render()
    {
        return view('trip.livewire.add-boat-form');
    }
}
