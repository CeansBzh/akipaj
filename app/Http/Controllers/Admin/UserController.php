<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AlertLevelEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.user.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        return view('admin.user.edit')->with('user', User::findOrFail($user->id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $user->fill($request->validated());

        // Suppression de la photo de profil actuelle si demandée, ou si une autre photo de profil a été uploadée
        if (($request->remove_image || $request->hasFile('profile_picture')) && $user->profile_picture_path) {
            $filePath = 'public/profile_pictures/'.basename($user->profile_picture_path);
            if (Storage::delete($filePath)) {
                $user->profile_picture_path = null;
            }
        }
        // Si une nouvelle photo de profil a été uploadée, on la sauvegarde
        if ($request->hasFile('profile_picture')) {
            $imageName = time().'.'.$request->profile_picture->extension();
            $path = $request->file('profile_picture')->storeAs('public/profile_pictures', $imageName);
            $user->profile_picture_path = Storage::url($path);
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        session()->flash('alert-'.AlertLevelEnum::SUCCESS->name, 'Modifications sauvegardées.');

        return Redirect::route('admin.users.edit', $user)->with('status', 'user-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        session()->flash('alert-'.AlertLevelEnum::SUCCESS->name, 'Le compte a bien été supprimé.');

        return Redirect::route('admin.users.index');
    }
}
