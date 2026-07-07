<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateAchievementRequest extends StoreAchievementRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['kode'] = ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('achievements', 'kode')->ignore($this->route('achievement'))];

        return $rules;
    }
}
