<?php

namespace App\Exceptions;

use Exception;

class ActiveGameSessionExistsException extends Exception
{
    protected $message = 'Anda masih memiliki permainan aktif. Selesaikan atau lanjutkan permainan tersebut terlebih dahulu.';

    protected $code = 422;
}
