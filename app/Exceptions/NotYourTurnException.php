<?php

namespace App\Exceptions;

use Exception;

class NotYourTurnException extends Exception
{
    protected $message = 'Bukan giliran Anda.';

    protected $code = 403;
}
