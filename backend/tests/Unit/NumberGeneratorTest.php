<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Administration\Models\NumberSequence;
use App\Modules\Administration\Services\NumberGeneratorService;
use App\Platform\Enums\ResetPolicy;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private function service(): NumberGeneratorService
    {
        return app(NumberGeneratorService::class);
    }

    public function test_generates_sequential_padded_numbers(): void
    {
        NumberSequence::create([
            'key' => 'admission_number', 'initial_number' => 1, 'current_number' => 0,
            'padding' => 6, 'format' => '{number}',
        ]);

        $this->assertSame('000001', $this->service()->next('admission_number'));
        $this->assertSame('000002', $this->service()->next('admission_number'));
        $this->assertSame('000003', $this->service()->next('admission_number'));
    }

    public function test_applies_prefix_format_and_records_registry(): void
    {
        NumberSequence::create([
            'key' => 'receipt_number', 'initial_number' => 1, 'current_number' => 0,
            'prefix' => 'RCP-', 'padding' => 4, 'format' => '{prefix}{number}',
        ]);

        $this->assertSame('RCP-0001', $this->service()->next('receipt_number'));
        $this->assertDatabaseHas('business_number_registry', [
            'type' => 'receipt_number', 'number' => 'RCP-0001',
        ]);
    }

    public function test_creates_a_default_sequence_on_demand(): void
    {
        $number = $this->service()->next('enquiry_number');

        $this->assertNotEmpty($number);
        $this->assertDatabaseHas('number_sequences', ['key' => 'enquiry_number']);
    }

    public function test_peek_does_not_consume_the_number(): void
    {
        NumberSequence::create([
            'key' => 'adm', 'initial_number' => 5, 'current_number' => 0, 'padding' => 3, 'format' => '{number}',
        ]);

        $this->assertSame('005', $this->service()->peek('adm'));
        $this->assertSame('005', $this->service()->next('adm'));
    }

    public function test_reset_returns_sequence_to_initial(): void
    {
        NumberSequence::create([
            'key' => 'inv', 'initial_number' => 100, 'current_number' => 0, 'format' => '{number}',
        ]);

        $this->assertSame('100', $this->service()->next('inv'));
        $this->assertSame('101', $this->service()->next('inv'));
        $this->service()->reset('inv');
        $this->assertSame('100', $this->service()->next('inv'));
    }

    public function test_throws_when_maximum_is_exceeded(): void
    {
        NumberSequence::create([
            'key' => 'cap', 'initial_number' => 1, 'current_number' => 0,
            'maximum_number' => 1, 'format' => '{number}',
        ]);

        $this->assertSame('1', $this->service()->next('cap'));
        $this->expectException(BusinessRuleException::class);
        $this->service()->next('cap');
    }

    public function test_increment_step_is_respected(): void
    {
        NumberSequence::create([
            'key' => 'step', 'initial_number' => 10, 'current_number' => 0, 'increment' => 5, 'format' => '{number}',
        ]);

        $this->assertSame('10', $this->service()->next('step'));
        $this->assertSame('15', $this->service()->next('step'));
        $this->assertSame('20', $this->service()->next('step'));
    }

    public function test_reset_policy_enum_exposes_period(): void
    {
        $this->assertNull(ResetPolicy::None->currentPeriod());
        $this->assertNotNull(ResetPolicy::Yearly->currentPeriod());
    }
}
