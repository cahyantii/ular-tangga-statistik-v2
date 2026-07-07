<?php

namespace App\Exceptions;

use Exception;

class QuestionExpiredException extends Exception
{
    protected $message = 'Soal sudah kedaluwarsa atau sudah dijawab sebelumnya.';

    protected $code = 409;
}
