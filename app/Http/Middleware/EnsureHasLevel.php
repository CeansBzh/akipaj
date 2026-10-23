<?php

namespace App\Http\Middleware;

use App\Enums\UserLevelEnum;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnsureHasLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next, int $level)
    {
        // Try to convert the string to a UserLevelEnum value, if it fails then use the GUEST level
        $level = UserLevelEnum::tryFrom($level) ?? UserLevelEnum::GUEST;

        if ($request->user()->level->value >= $level->value) {
            return $next($request);
        }

        // If the user isn't of the required level AND is a guest, then redirect to the profile page to show a message about account validation by the admin
        if ($request->user()->level == UserLevelEnum::GUEST) {
            return redirect()->route('profile.show', $request->user()->name);
        }

        abort(403);
    }
}
