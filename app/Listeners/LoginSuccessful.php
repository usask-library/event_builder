<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LoginSuccessful
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        // Revoke all previous tokens
        $event->user->tokens()->delete();
        // Generate a new API token and add it to the session data
        $token = $event->user->createToken('event-admin')->plainTextToken;
        session()->put('api_token', $token);
    }
}
