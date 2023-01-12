<?php

namespace App\Http\Livewire\Trip;

use Livewire\Component;

class Boats extends Component
{
    public $boats;

    public function addBoat()
    {
        $this->boats[] = ['name' => '', 'type' => '', 'year' => '', 'builder' => '', 'renter' => '', 'city' => '', 'crew' => ''];
    }

    public function deleteBoat($index)
    {
        unset($this->boats[$index]);
        $this->boats = empty($this->boats) ? null : array_values($this->boats);
    }

    public function render()
    {
        return view('trip.livewire.boats');
    }
}
