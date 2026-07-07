<?php

namespace App\Exceptions;

use Exception;

class GameAlreadyFinishedException extends Exception
{
    protected $message = 'Permainan sudah selesai.';

    protected $code = 422;
}
