<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Services\PhoneNumberService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => PhoneNumberService::normalize($this->input('phone')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                'regex:/^62[0-9]{8,18}$/',
                Rule::unique(User::class, 'phone')->ignore($this->user()->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Nomor WhatsApp harus berupa nomor Indonesia dengan format 62...',
            'phone.unique' => 'Nomor WhatsApp ini sudah digunakan oleh akun lain.',
        ];
    }
}
