<?php

namespace App\Http\Requests\Admin;

class UpdateSoalRequest extends StoreSoalRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            '_version' => ['required', 'string'],
        ]);
    }
}
