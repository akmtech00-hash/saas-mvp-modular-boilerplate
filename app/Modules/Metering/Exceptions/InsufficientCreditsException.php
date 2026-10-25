<?php

declare(strict_types=1);

namespace App\Modules\Metering\Exceptions;

use Exception;

class InsufficientCreditsException extends Exception
{
    public function __construct(string $metric = 'ai_credits', int $requested = 1, int $remaining = 0)
    {
        parent::__construct(
            "Insufficient {$metric}. Requested: {$requested}, Remaining: {$remaining}. Please upgrade your subscription plan."
        );
    }
}
