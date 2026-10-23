<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @return RedirectResponse
     */
    public function __invoke(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false).'?verified=1');
        }

        $user = $request->user();
        // The Verified event requires the MustVerifyEmail contract, which App\Models\User doesn't implement (yet)
        if ($user->markEmailAsVerified() && $user instanceof MustVerifyEmail) {
            event(new Verified($user));
        }

        return redirect()->intended(route('home', absolute: false).'?verified=1');
    }
}
