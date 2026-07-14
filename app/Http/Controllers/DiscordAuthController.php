<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DiscordAuthController extends Controller
{
    private const DISCORD_API_BASE = 'https://discord.com/api/v10';

    public function login(Request $request): View
    {
        return view('auth.discord-login', [
            'error' => $request->session()->pull('discord_auth_error'),
            'isConfigured' => $this->isConfigured(),
        ]);
    }

    public function redirect(Request $request): RedirectResponse
    {
        if (! $this->isConfigured()) {
            return $this->deny($request, 'Discord login is not configured yet.');
        }

        $state = Str::random(40);
        $request->session()->put('discord_oauth_state', $state);

        $query = http_build_query([
            'client_id' => config('services.discord.client_id'),
            'redirect_uri' => config('services.discord.redirect'),
            'response_type' => 'code',
            'scope' => 'identify guilds.members.read',
            'state' => $state,
            'prompt' => 'consent',
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away('https://discord.com/oauth2/authorize?'.$query);
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! $this->isConfigured()) {
            return $this->deny($request, 'Discord login is not configured yet.');
        }

        if ($request->query('error')) {
            return $this->deny($request, 'Discord did not authorize this login.');
        }

        $expectedState = (string) $request->session()->pull('discord_oauth_state', '');
        $state = (string) $request->query('state', '');
        $code = (string) $request->query('code', '');

        if ($expectedState === '' || ! hash_equals($expectedState, $state) || $code === '') {
            return $this->deny($request, 'Discord login state could not be verified.');
        }

        try {
            $tokenResponse = $this->exchangeCode($code);
        } catch (ConnectionException $exception) {
            return $this->deny($request, 'Discord token exchange failed: '.$exception->getMessage());
        }

        if (! $tokenResponse->successful()) {
            return $this->deny($request, 'Discord token exchange failed: '.$this->discordError($tokenResponse));
        }

        $token = $tokenResponse->json();
        $accessToken = (string) ($token['access_token'] ?? '');

        if ($accessToken === '') {
            return $this->deny($request, 'Discord did not return an access token.');
        }

        try {
            $userResponse = $this->fetchDiscordUser($accessToken);
        } catch (ConnectionException $exception) {
            return $this->deny($request, 'Discord profile lookup failed: '.$exception->getMessage());
        }

        if (! $userResponse->successful()) {
            return $this->deny($request, 'Discord profile lookup failed: '.$this->discordError($userResponse));
        }

        try {
            $memberResponse = $this->fetchGuildMember($accessToken);
        } catch (ConnectionException $exception) {
            return $this->deny($request, 'Discord server membership lookup failed: '.$exception->getMessage());
        }

        if (! $memberResponse->successful()) {
            return $this->deny($request, 'Discord server membership lookup failed: '.$this->discordError($memberResponse));
        }

        $user = $userResponse->json();
        $member = $memberResponse->json();
        $roles = array_map('strval', $member['roles'] ?? []);
        $requiredRoleId = (string) config('services.discord.events_role_id');

        if (! in_array($requiredRoleId, $roles, true)) {
            return $this->deny($request, 'You need the Events Team role to access this portal.');
        }

        $displayName = $member['nick']
            ?? $user['global_name']
            ?? $user['username']
            ?? 'Events Team';

        $request->session()->regenerate();
        $request->session()->put('discord_user', [
            'id' => (string) ($user['id'] ?? ''),
            'username' => (string) ($user['username'] ?? $displayName),
            'display_name' => (string) $displayName,
            'avatar' => $this->avatarUrl($user),
            'roles' => $roles,
            'guild_id' => (string) config('services.discord.guild_id'),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['discord_user', 'discord_oauth_state']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('discord.login');
    }

    private function exchangeCode(string $code): HttpResponse
    {
        return $this->discordHttp()->asForm()
            ->withBasicAuth((string) config('services.discord.client_id'), (string) config('services.discord.client_secret'))
            ->post(self::DISCORD_API_BASE.'/oauth2/token', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => config('services.discord.redirect'),
            ]);
    }

    private function fetchDiscordUser(string $accessToken): HttpResponse
    {
        return $this->discordHttp()->withToken($accessToken)
            ->get(self::DISCORD_API_BASE.'/users/@me');
    }

    private function fetchGuildMember(string $accessToken): HttpResponse
    {
        $guildId = (string) config('services.discord.guild_id');

        return $this->discordHttp()->withToken($accessToken)
            ->get(self::DISCORD_API_BASE."/users/@me/guilds/{$guildId}/member");
    }

    private function discordHttp(): PendingRequest
    {
        $verify = config('services.discord.verify_ssl');
        $caBundle = (string) config('services.discord.ca_bundle', '');

        if ($verify && $caBundle !== '' && file_exists($caBundle)) {
            $verify = $caBundle;
        }

        return Http::withOptions(['verify' => $verify]);
    }

    private function discordError(HttpResponse $response): string
    {
        $payload = $response->json();
        $message = $payload['error_description']
            ?? $payload['message']
            ?? $payload['error']
            ?? 'unknown Discord API error';

        return $response->status().' '.$message;
    }

    private function avatarUrl(array $user): ?string
    {
        $userId = (string) ($user['id'] ?? '');
        $avatar = (string) ($user['avatar'] ?? '');

        if ($userId === '' || $avatar === '') {
            return null;
        }

        $extension = str_starts_with($avatar, 'a_') ? 'gif' : 'png';

        return "https://cdn.discordapp.com/avatars/{$userId}/{$avatar}.{$extension}?size=128";
    }

    private function isConfigured(): bool
    {
        return filled(config('services.discord.client_id'))
            && filled(config('services.discord.client_secret'))
            && filled(config('services.discord.redirect'))
            && filled(config('services.discord.guild_id'))
            && filled(config('services.discord.events_role_id'));
    }

    private function deny(Request $request, string $message): RedirectResponse
    {
        $request->session()->forget('discord_user');
        $request->session()->flash('discord_auth_error', $message);

        return redirect()->route('discord.login');
    }
}
