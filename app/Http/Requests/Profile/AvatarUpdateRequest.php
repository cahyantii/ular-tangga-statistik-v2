<?php

namespace App\Http\Requests\Profile;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AvatarUpdateRequest extends FormRequest
{
    protected $errorBag = 'avatar';

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'preset' => ['nullable', 'string', Rule::in(User::PRESET_AVATARS)],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('preset') && ! $this->hasFile('avatar')) {
                $validator->errors()->add('avatar', 'Pilih salah satu avatar bawaan atau unggah foto terlebih dahulu.');
            }
        });
    }
}
