<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Enum\AlertLevelEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\ProfileUpdateRequest;

class ProfileController extends Controller
{

    /**
     * Display the user profile.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('profile.index')->with([
            'latestPayments' => Payment::where('user_id', Auth::user()->id)->latest()->take(5)->get(),
        ]);
    }

    /**
     * Display the user's profile edit form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     *
     * @param  \App\Http\Requests\ProfileUpdateRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileUpdateRequest $request)
    {
        $request->user()->fill($request->validated());

        // Suppression de la photo de profil actuelle si demandée, ou si une autre photo de profil a été uploadée
        if (($request->remove_image || $request->hasFile('profile_picture')) && $request->user()->profile_picture_path) {
            $filePath = 'public/profile_pictures/' . basename($request->user()->profile_picture_path);
            if (Storage::delete($filePath)) {
                $request->user()->profile_picture_path = null;
            }
        }
        // Si une nouvelle photo de profil a été uploadée, on la sauvegarde
        if ($request->hasFile('profile_picture')) {
            $imageName = time() . '.' . $request->profile_picture->extension();
            $path = $request->file('profile_picture')->storeAs('public/profile_pictures', $imageName);
            $request->user()->profile_picture_path = Storage::url($path);
        }

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Modifications sauvegardées.');

        return Redirect::route('profile.edit')->with('status', $request->status());
    }

    /**
     * Delete the user's account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Votre compte a bien été supprimé.');

        return Redirect::to('/');
    }
}
