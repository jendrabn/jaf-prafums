<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\Api\ResetPasswordRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Tests\TestCase;

class AuthResetPasswordPutTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function reset_password_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(AuthController::class, 'resetPassword', ResetPasswordRequest::class);
  }

  /** @test */
  public function reset_password_request_has_the_correct_rules()
  {
    $this->assertValidationRules(
      [
        'email' => [
          'required',
          'string',
          'email',
          Rule::exists('users', 'email')
        ],
        'token' => [
          'required',
          'string'
        ],
        'password' => [
          'required',
          'string',
          PasswordRule::min(8)->mixedCase()->numbers(),
          'max:30',
          'confirmed'
        ],
      ],
      (new ResetPasswordRequest())->rules()
    );
  }

  /** @test */
  public function can_reset_password()
  {
    $user = $this->createUser();
    $token = Password::createToken($user);
    $newPassword = 'newPassword123';

    $response = $this->putJson('/api/auth/reset_password', [
      'email' => $user->email,
      'token' => $token,
      'password' => $newPassword,
      'password_confirmation' => $newPassword,
    ]);

    $response->assertOk()
      ->assertExactJson(['data' => true]);

    $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    $this->assertDatabaseMissing('password_reset_tokens', [
      'email' => $user->email,
      'token' => $user->token
    ]);
  }

  /** @test */
  public function returns_validation_error_if_all_fields_are_invalid()
  {
    $response = $this->putJson('/api/auth/reset_password', [
      'email' => 'not-email',
      'token' => '',
      'password' => 'new-password',
      'password_confirmation' => 'password',
    ]);

    $response->assertUnprocessable()
      ->assertJsonValidationErrors(['email', 'token', 'password',]);
  }
}
