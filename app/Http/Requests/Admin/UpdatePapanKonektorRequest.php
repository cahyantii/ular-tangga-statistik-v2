<?php

namespace App\Http\Requests\Admin;

class UpdatePapanKonektorRequest extends StorePapanKonektorRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            '_version' => ['required', 'string'],
        ]);
    }
}
