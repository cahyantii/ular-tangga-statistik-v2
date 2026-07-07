<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'soal_id' => ['required', 'integer'],
            'jawaban' => ['nullable', 'string', 'in:A,B,C,D,a,b,c,d'],
        ];
    }
}
