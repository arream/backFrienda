<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserConfirmRequest extends FormRequest
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
            'phone' => 'required|phone:RU',
            'code' => 'required|integer|digits:4',
        ];
    }
    /**
     *
     * @return array
     */
    public function messages()
    {
        return [
            'phone.required' => 'Номер телефона обязательно для заполнения.',
            'phone.phone' => 'Номер телефона некорректен.',
            'code.required' => 'Код обязателен для заполнения.',
            'code.integer' => 'Код должен содержать только числовые символы.',
            'code.digits' => 'Код должен состоять ровно из 4 цифр.',
        ];
    }
}
