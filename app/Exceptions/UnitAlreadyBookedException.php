<?php

namespace App\Exceptions;

use Exception;

class UnitAlreadyBookedException extends Exception
{
    protected $message = 'One or more selected units have already been booked or locked by another transaction.';
}
