<?php

namespace App\Exceptions;

use RuntimeException;

class NoAvailableRoomException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No available rooms of this type for the selected dates.');
    }
}
