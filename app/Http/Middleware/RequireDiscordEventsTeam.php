<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireDiscordEventsTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('discord_user')) {
            return $next($request);
        }

        if ($request->is('api/*')) {
            return response()->json(['error' => 'Discord login required'], 401);
        }

        return redirect()->guest(route('discord.login'));
    }
}
