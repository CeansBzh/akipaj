<?php

namespace App\Http\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserCatalog extends Component
{
    use WithPagination;

    public $searchTerm;
    public $role;
    public $paginate = 10;

    public function updating()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.user-catalog', [
            'users' => User::whereLike(['name', 'email'], $this->searchTerm ?? '')
                ->when($this->role, function ($query, $role) {
                    $query->whereRelation('roles', 'id', $role);
                })->paginate($this->paginate),
        ]);
    }
}
