<?php

namespace App\Http\Controllers\Auth;

use App\Enum\AlertLevelEnum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ChangePasswordController extends Controller
{
    /**
     * Change the user's password to the new one.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $request->validate([
            'oldPassword' => 'required|string|current_password',
            'newPassword' => 'required|string|confirmed|min:8',
        ]);
        if (!Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->oldPassword,
        ])) {
            throw ValidationException::withMessages([
                'oldPassword' => __('auth.password'),
            ]);
        }

        $request->user()->forceFill([
            'password' => bcrypt($request->newPassword),
        ])->save();

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Votre mot de passe a bien été changé.');

        return redirect()->route('profile');
    }
}
