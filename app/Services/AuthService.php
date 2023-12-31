<?php

namespace App\Services;

use App\Http\Requests\Api\{LoginRequest, ResetPasswordRequest};
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
  public function login(LoginRequest $request): User
  {
    $validatedData = $request->validated();

    throw_if(
      !auth()->attempt($validatedData),
      new AuthenticationException('The provided credentials are incorrect.')
    );

    $user = User::where('email', $validatedData['email'])->firstOrFail();

    $user->auth_token = $user->createToken('auth_token')->plainTextToken;

    return $user;
  }

  public function resetPassword(ResetPasswordRequest $request): string
  {
    $status = Password::reset(
      $request->validated(),
      function (User $user, string $password) {
        $user->forceFill(['password' => $password])->setRememberToken(Str::random(60));
        $user->save();

        event(new PasswordReset($user));
      }
    );

    throw_if(
      $status !== Password::PASSWORD_RESET,
      ValidationException::withMessages(['email' => $status])
    );

    return $status;
  }
}
