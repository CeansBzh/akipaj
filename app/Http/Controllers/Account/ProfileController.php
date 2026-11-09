<?php

namespace App\Http\Controllers\Account;

use App\Enums\UserLevelEnum;
use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Affiche les matelots du club avec le nombre de sorties qu'ils ont faites :
     * le matelot connecté en premier, puis les autres du plus grand au plus petit nombre de sorties.
     */
    public function index(Request $request): View
    {
        $members = User::query()
            ->where('level', '>=', UserLevelEnum::MEMBER)
            ->withCount(['trips' => fn ($query) => $query->started()])
            ->orderByRaw('users.id = ? desc', [$request->user()->id])
            ->orderByDesc('trips_count')
            ->orderBy('name')
            ->get();

        return view('profile.index')->with('members', $members);
    }

    /**
     * Affiche le profil d'un matelot : ses sorties faites, de la plus récente à la plus ancienne et rangées par année,
     * sa prochaine sortie, ses dernières photos et les commentaires laissés sur son profil.
     */
    public function show(Request $request, ?User $user = null): View|RedirectResponse
    {
        $currentUser = $request->user();
        // If the user is logged in and not a guest, or if the requested user is the current user, show the profile
        if ($user && $currentUser && ($user->is($currentUser) || ! $currentUser->isGuest())) {
            $user->loadCount('photos')->load('comments.user');
            $trips = $user->trips()->started()->orderByDesc('start_date')->get();

            return view('profile.show', [
                'user' => $user,
                'isOwnProfile' => $user->is($currentUser),
                'tripsByYear' => $trips->groupBy(fn (Trip $trip): int => $trip->start_date->year),
                'tripCount' => $trips->count(),
                // Une sortie en cours ne compte que ses jours déjà passés
                'daysAboard' => $trips->sum(fn (Trip $trip): int => (int) $trip->start_date->diffInDays($trip->end_date->min(today())) + 1),
                'coverImage' => $trips->first(fn (Trip $trip): bool => $trip->imagePath !== null)?->imagePath,
                'nextTrip' => $user->trips()->notStarted()->orderBy('start_date')->first(),
                'latestPhotos' => $user->photos()->with('album')->latest('taken_at')->latest('id')->take(6)->get(),
            ]);
        }

        if ($currentUser) {
            return redirect()->route('profile.show', $currentUser->name);
        }

        abort(404);
    }
}
