<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class JoinRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_room' => ['required', 'string', 'size:6'],
        ];
    }
}
