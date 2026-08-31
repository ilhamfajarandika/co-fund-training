<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;


class AuthService
{
    public function register(array $data): array
    {

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'backer'
            ]);

            event(new Registered($user));

            DB::commit();

            return [
                'user' => $user
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \RuntimeException('Registration failed: ' . $e->getMessage());
        }
    }

    public function login(array $credentials): array
    {
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new \Illuminate\Auth\AuthenticationException('Email or password incorrect');
        }

        if (!$user->hasVerifiedEmail()) {
            throw new \RuntimeException('Please verify your email first.', 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'data' => $user,
            'token' => $token
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function changePassword(User $user, string $currentPassword, string $newPasswrod): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new ValidationException(
                Validator::make([], []),
                'Current password is incorrect'
            );
        }

        $user->password = Hash::make($newPasswrod);
        $user->save();
    }

    /**
     * Kirim link reset password.
     *
     * @throws RuntimeException Jika gagal mengirim link (email tidak terdaftar, dll).
     */
    public function sendPasswordResetLink(array $data): string
    {
        $status = Password::sendResetLink(
            Arr::only($data, 'email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            throw new RuntimeException(__($status), 400);
        }

        return __($status);
    }

    /**
     * Reset password user menggunakan token.
     *
     * @throws RuntimeException Jika token tidak valid/expired.
     */
    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            Arr::only($data, ['email', 'password', 'password_confirmation', 'token']),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new RuntimeException(__($status), 400);
        }

        return __($status);
    }
}
