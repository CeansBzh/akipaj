<?php

namespace App\Http\Controllers;

use App\Enum\AlertLevelEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'latestPayments' => Auth::user()->payments()->latest()->take(5)->get(),
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
