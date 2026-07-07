<?php

namespace App\Exceptions;

use Exception;

class RoomFullException extends Exception
{
    protected $message = 'Room sudah penuh.';

    protected $code = 422;
}
