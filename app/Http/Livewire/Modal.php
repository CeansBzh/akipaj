<?php
 
namespace App\Http\Livewire;
 
use Livewire\Component;
 
abstract class Modal extends Component
{
    public $show = false;
 
    protected $listeners = [
        'show' => 'show',
        'create' => 'create',
    ];
 
    public function show()
    {
        $this->show = true;
    }
}