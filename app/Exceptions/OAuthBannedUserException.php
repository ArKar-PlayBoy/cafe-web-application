<?php

namespace App\Exceptions;

use RuntimeException;

class OAuthBannedUserException extends RuntimeException
{
    public function __construct(
        public readonly string $banReason = ''
    ) {
        parent::__construct('User account has been banned.');
    }
}
