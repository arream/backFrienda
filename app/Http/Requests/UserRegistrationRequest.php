<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserRegistrationRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'phone' => 'required|phone:RU|unique:App\Models\User,phone',
        ];
    }

    /**
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Имя обязательно к заполнению.',
            'surname.required' => 'Фамилия обязательна к заполнению.',
            'phone.required' => 'Номер телефона обязателен к заполнению.',
            'phone.phone' => 'Номер телефона некорректен.',
            'phone.unique' => 'Такой номер телефона уже зарегистрирован.',
        ];
    }
}
