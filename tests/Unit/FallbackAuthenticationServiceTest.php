<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FallbackAuthenticationService;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class FallbackAuthenticationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new FallbackAuthenticationService();
    }

    public function test_successful_authentication_clears_rate_limit()
    {
        $employee = Employee::factory()->create([
            'email' => 'test@example.com',
            'pin_code' => Hash::make('1234')
        ]);

        // Removed Request mocks

        $result = $this->authService->attempt('test@example.com', '1234', true);

        $this->assertTrue($result['success']);
        $this->assertEquals($employee->id, $result['employee']->id);
    }

    public function test_failed_authentication_triggers_rate_limit_lockout()
    {
        $employee = Employee::factory()->create([
            'email' => 'locked@example.com',
            'pin_code' => Hash::make('1234')
        ]);

        // Removed Request mocks

        // Simulate 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $result = $this->authService->attempt('locked@example.com', 'wrong_pin', true);
            $this->assertFalse($result['success']);
        }

        // 6th attempt should be locked
        $result = $this->authService->attempt('locked@example.com', 'wrong_pin', true);
        $this->assertTrue($result['locked']);
        $this->assertStringContainsString('Too many attempts', $result['message']);
    }
}
