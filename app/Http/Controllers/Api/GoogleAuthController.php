<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    protected function frontendBaseUrl(): string
    {
        return rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
    }

    protected function redirectWithError(string $message, int $status = 302)
    {
        $redirectUrl = $this->frontendBaseUrl() . '/google-callback?error=' . urlencode($message);

        return redirect($redirectUrl, $status);
    }

    // Required by task: redirect() and callback() methods
    public function redirect()
    {
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(Request $request)
    {
        try {
            // OAuth must be handled within the stateful web middleware group
            $googleUser = Socialite::driver('google')->user();

            $googleId = (string) $googleUser->getId();
            $email = $googleUser->getEmail();

            if (empty($googleId)) {
                return $this->redirectWithError('Google authentication failed: missing google_id');
            }

            if (empty($email)) {
                return $this->redirectWithError('Google authentication failed: missing email');
            }

            // 1) Requirement: if Google email already exists, log in automatically
            $user = User::query()->where('email', $email)->first();

            // 2) If no email match, try by google_id / provider_id
            if (!$user) {
                $user = User::query()
                    ->where('google_id', $googleId)
                    ->orWhere(function ($q) use ($googleId) {
                        $q->where('provider', 'google')
                            ->where('provider_id', $googleId);
                    })
                    ->first();
            }

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName() ?: 'Google User',
                    'email' => $email,
                    'avatar' => $googleUser->getAvatar(),
                    'google_id' => $googleId,
                    'provider' => 'google',
                    'provider_id' => $googleId,
                    'role' => 'customer',
                    'password' => bcrypt(bin2hex(random_bytes(16))),
                ]);
            } else {
                // Keep user identity, but ensure required social fields are stored
                $user->forceFill([
                    'name' => $googleUser->getName() ?: $user->name,
                    'avatar' => $googleUser->getAvatar() ?: $user->avatar,
                    'google_id' => $user->google_id ?: $googleId,
                    'provider' => 'google',
                    'provider_id' => $user->provider_id ?: $googleId,
                ])->save();
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return redirect($this->frontendBaseUrl() . '/google-callback?token=' . urlencode($token));
        } catch (Throwable $e) {
            Log::error('Google OAuth callback failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->redirectWithError('Google authentication failed');
        }
    }

    // Backward compatibility for existing route imports (if any)
    public function redirectToGoogle()
    {
        return $this->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        return $this->callback($request);
    }
}


