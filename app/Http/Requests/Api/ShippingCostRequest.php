<?php

namespace App\Http\Requests\Api;

use App\Models\Shipping;
use Illuminate\Foundation\Http\FormRequest;

class ShippingCostRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
   */
  public function rules(): array
  {
    return [
      'destination' => ['required', 'integer', 'exists:cities,id'],
      'weight' => ['required', 'integer', 'max:' . Shipping::MAX_WEIGHT],
    ];
  }
}
