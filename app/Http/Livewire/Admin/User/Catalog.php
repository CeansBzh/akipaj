<?php

namespace App\Http\Livewire\Admin\User;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Catalog extends Component
{
    use WithPagination;

    public $searchTerm;
    public $level = -1;
    public $paginate = 10;

    public function updating()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('admin.user.livewire.catalog', [
            'users' => User::whereLike(['name', 'email'], $this->searchTerm ?? '')
                ->when($this->level != -1, function ($query) {
                    $query->where('level', $this->level);
                })
                ->paginate($this->paginate),
        ]);
    }
}
