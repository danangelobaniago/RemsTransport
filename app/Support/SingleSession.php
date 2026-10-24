<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * One account, one browser at a time. Every completed login issues a new
 * token, stored on the user and in that browser's session; any other
 * browser still holding an older token is logged out on its next request.
 */
class SingleSession
{
    public const KEY = 'single_session_token';

    // Call right after Auth::login() once the user is fully verified.
    public static function start(User $user): void
    {
        $token = Str::random(64);

        DB::table('users')->where('id', $user->id)->update(['session_token' => $token]);
        $user->session_token = $token;

        session([self::KEY => $token]);
    }

    public static function isCurrent(User $user): bool
    {
        // Accounts that have not logged in since this was added have no token yet.
        if ($user->session_token === null) {
            return true;
        }

        return hash_equals($user->session_token, (string) session(self::KEY));
    }
}
