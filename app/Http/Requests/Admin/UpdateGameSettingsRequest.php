<?php

namespace App\Http\Requests\Admin;

use App\Enums\SettingType;
use App\Models\GameSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGameSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $settings = GameSetting::query()->whereIn('id', array_keys($this->input('settings', [])))->get()->keyBy('id');
        $rules = [];

        foreach ($this->input('settings', []) as $id => $row) {
            $setting = $settings->get((int) $id);

            if (! $setting) {
                continue;
            }

            $rules["settings.{$id}._version"] = ['required', 'string'];
            $rules["settings.{$id}.value"] = match ($setting->type) {
                SettingType::Integer => ['required', 'integer'],
                SettingType::Boolean => ['required', Rule::in(['0', '1'])],
                SettingType::String => ['required', 'string', 'max:255'],
            };
        }

        return $rules;
    }
}
