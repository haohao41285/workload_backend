<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			'password' => 'required|
                min:9|
                regex:/[a-z]/|
                regex:/[A-Z]/|
                regex:/[0-9]/|
                regex:/[@$!%*#?&]/',
		];
	}
	public function messages() {
		return [
			'password.required' => 'Password been required',
			'password.min' => 'must be at least 9 characters in length',
			'password.regex' => 'Must contain at least one lowercase letter, one uppercase, one digit, one special character',
		];
	}
}
