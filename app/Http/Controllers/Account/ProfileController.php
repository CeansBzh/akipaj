<?php

namespace App\Http\Controllers\Account;

use App\Enums\UserLevelEnum;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Middleware\Authenticate;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware([Authenticate::class, 'level:' . UserLevelEnum::MEMBER->value], ['only' => ['index']]);
    }

    /**
     * Display the user profile.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('profile.index')->with('users', User::where('level', '>=', UserLevelEnum::MEMBER)->get());
    }

    /**
     * Display the user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function show(Request $request, User $user = null)
    {
        // If the user is logged in and not a guest, or if the requested user is the current user, show the profile
        if ($user && ($user == $request->user() || !$request->user()->isGuest())) {
            $user->loadCount('photos');
            $tripsByYear = $user
                ->trips()
                ->orderBy('start_date')
                ->get()
                ->groupBy([
                    function ($val) {
                        return $val->start_date->format('Y');
                    },
                ]);

            return view('profile.show', compact('user', 'tripsByYear'));
        }

        if ($request->user()) {
            return redirect()->route('profile.show', $request->user()->name);
        }

        abort(404);
    }
}
