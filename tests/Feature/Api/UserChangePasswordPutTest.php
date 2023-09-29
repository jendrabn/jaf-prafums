<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\UserController;
use App\Http\Requests\Api\UpdatePasswordRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

class UserChangePasswordPutTest extends TestCase
{
  use RefreshDatabase;

  public string $changePasswordRoute = '/api/user/change_password';

  /** @test */
  public function update_password_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(UserController::class, 'updatePassword', UpdatePasswordRequest::class);
  }

  /** @test */
  public function update_password_request_has_the_correct_rules()
  {
    $this->assertValidationRules(
      [
        'current_password' => [
          'required',
          'string',
          'current_password',
        ],
        'password' => [
          'required',
          'string', Password::min(8)->mixedCase()->numbers(),
          'max:30',
          'confirmed',
        ],
      ],
      (new UpdatePasswordRequest())->rules()
    );
  }

  /** @test */
  public function returns_unauthenticated_error_if_user_is_not_authenticated()
  {
    $response = $this->putJson($this->changePasswordRoute);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function can_update_password()
  {
    $user = $this->createUser([
      'password' => $password = 'OldPassword123',
    ]);
    $newPassword = 'NewPassword123';
    $data = [
      'current_password' => $password,
      'password' => $newPassword,
      'password_confirmation' => $newPassword,
    ];

    $response = $this->putJson(
      $this->changePasswordRoute,
      $data,
      $this->authBearerToken($user)
    );

    $response->assertOk()
      ->assertExactJson(['data' => true]);

    $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
  }

  /** @test */
  public function returns_validation_error_if_all_fields_are_invalid()
  {
    $user = $this->createUser([
      'password' => 'OldPassword123'
    ]);
    $data = [
      'current_password' => 'WrongPassword',
      'password' => 'NewPassword',
      'password_confirmation' => 'New',
    ];

    $response = $this->putJson($this->changePasswordRoute, $data, $this->authBearerToken($user));

    $response->assertUnprocessable()
      ->assertJsonValidationErrors(['current_password', 'password']);
  }
}