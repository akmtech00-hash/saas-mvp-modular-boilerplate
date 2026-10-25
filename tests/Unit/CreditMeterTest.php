<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Metering\Exceptions\InsufficientCreditsException;
use App\Modules\Metering\Models\UsageMeter;
use PHPUnit\Framework\TestCase;

class CreditMeterTest extends TestCase
{
    public function test_remaining_credits_calculation(): void
    {
        $meter = new UsageMeter([
            'allocated_limit' => 100,
            'current_usage' => 45,
        ]);

        $this->assertEquals(55, $meter->remainingCredits());
    }

    public function test_remaining_credits_cannot_be_negative(): void
    {
        $meter = new UsageMeter([
            'allocated_limit' => 50,
            'current_usage' => 70,
        ]);

        $this->assertEquals(0, $meter->remainingCredits());
    }

    public function test_insufficient_credits_exception_message(): void
    {
        $exception = new InsufficientCreditsException('ai_credits', 10, 2);
        $this->assertStringContainsString('Requested: 10, Remaining: 2', $exception->getMessage());
    }
}
