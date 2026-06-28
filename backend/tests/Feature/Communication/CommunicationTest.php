<?php

declare(strict_types=1);

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Administration\Models\School;
use App\Modules\Administration\Models\User;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Models\CommunicationMessage;
use App\Modules\Communication\Models\CommunicationTemplate;
use App\Modules\Communication\Services\CommunicationHooks;
use App\Modules\Communication\Services\PreferenceService;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    $this->year = AcademicYear::create(['school_id' => $this->school->id, 'name' => '2025-2026', 'slug' => '2025-2026', 'start_date' => '2025-04-01', 'end_date' => '2026-03-31', 'status' => 'active']);
    $this->class = SchoolClass::create(['school_id' => $this->school->id, 'code' => 'C1', 'name' => 'Grade 1', 'slug' => 'grade-1', 'status' => 'active']);
    $this->section = Section::create(['class_id' => $this->class->id, 'name' => 'A', 'capacity' => 40, 'status' => 'active']);

    $this->user = User::create(['school_id' => $this->school->id, 'name' => 'Asha User', 'email' => 'asha.user@test', 'username' => 'asha', 'password' => 'Password@123', 'status' => 'active']);

    $this->s1 = Student::create(['school_id' => $this->school->id, 'admission_number' => '1001', 'name' => 'Asha', 'email' => 'asha@test', 'phone' => '9000000001', 'user_id' => $this->user->id, 'status' => 'active']);
    $this->s2 = Student::create(['school_id' => $this->school->id, 'admission_number' => '1002', 'name' => 'Bina', 'email' => 'bina@test', 'phone' => '9000000002', 'status' => 'active']);
    foreach ([$this->s1, $this->s2] as $s) {
        StudentAcademicRecord::create(['school_id' => $this->school->id, 'student_id' => $s->id, 'academic_year_id' => $this->year->id, 'class_id' => $this->class->id, 'section_id' => $this->section->id, 'status' => 'active', 'is_current' => true, 'started_on' => now()->toDateString()]);
    }

    $this->guardian = Guardian::create(['school_id' => $this->school->id, 'name' => 'Parent', 'relation' => 'father', 'email' => 'parent@test', 'phone' => '9111111111']);
    $this->s1->guardians()->attach($this->guardian->id, ['is_primary' => true]);

    actingAsSuperAdmin();
});

function publishTo(string $audience, string $channel = 'in_app', array $extra = []): array
{
    return test()->postJson('/api/v1/communication/messages', array_merge([
        'school_id' => test()->school->id,
        'channel' => $channel,
        'audience_type' => $audience,
        'subject' => 'Hello {{recipient_name}}',
        'body' => 'Dear {{recipient_name}}, welcome.',
    ], $extra))->assertCreated()->json('data');
}

// ---------------- Templates ----------------
it('manages reusable templates with variables', function (): void {
    $this->postJson('/api/v1/communication/templates', [
        'school_id' => $this->school->id, 'name' => 'Fee Due', 'code' => 'fee_due', 'channel' => 'email',
        'subject' => 'Fee due for {{student_name}}', 'body' => 'Amount {{amount}} due on {{due_date}}',
        'variables' => ['student_name', 'amount', 'due_date'],
    ])->assertCreated()->assertJsonPath('data.code', 'fee_due');

    $this->getJson("/api/v1/communication/templates?filter[school_id]={$this->school->id}")->assertOk()->assertJsonCount(1, 'data');
});

// ---------------- Publish → engine resolves recipients + renders ----------------
it('publishes through the engine, resolving recipients and rendering variables', function (): void {
    $batch = publishTo('students');
    expect($batch['total_recipients'])->toBe(2);

    $messages = CommunicationMessage::where('batch_id', $batch['id'])->get();
    expect($messages)->toHaveCount(2);
    expect($messages->first()->body)->toContain('Dear Asha'); // variable rendered

    // Every message tracked from creation.
    $this->assertDatabaseHas('communication_delivery_logs', ['event' => 'created']);
    $this->assertDatabaseHas('activity_logs', ['action' => 'communication.published']);
});

// ---------------- Preferences ----------------
it('respects user preferences unless the message is mandatory', function (): void {
    // Asha (user) opts out of in-app.
    app(PreferenceService::class)->set($this->user->id, CommunicationChannel::InApp, false);

    $batch = publishTo('students');
    $ashaMsg = CommunicationMessage::where('batch_id', $batch['id'])->where('recipient_id', $this->s1->id)->first();
    expect($ashaMsg->status->value)->toBe('cancelled'); // preference respected

    // Mandatory overrides the preference.
    $batch2 = publishTo('students', 'in_app', ['is_mandatory' => true]);
    $ashaMsg2 = CommunicationMessage::where('batch_id', $batch2['id'])->where('recipient_id', $this->s1->id)->first();
    expect($ashaMsg2->status->value)->toBe('pending');
});

// ---------------- Queue processing + delivery tracking ----------------
it('processes the queue and tracks delivery', function (): void {
    $batch = publishTo('students');

    $this->postJson('/api/v1/communication/queue/process', ['school_id' => $this->school->id])
        ->assertOk()->assertJsonPath('data.processed', 2);

    $message = CommunicationMessage::where('batch_id', $batch['id'])->first();
    expect($message->status->value)->toBe('delivered');
    expect($message->sent_at)->not->toBeNull();
    $this->assertDatabaseHas('communication_delivery_logs', ['message_id' => $message->id, 'event' => 'delivered']);
});

// ---------------- Retry / backoff (never lose messages) ----------------
it('retries a failed message instead of losing it', function (): void {
    // Email channel to a custom recipient with NO email address → provider fails.
    $batch = $this->postJson('/api/v1/communication/messages', [
        'school_id' => $this->school->id, 'channel' => 'email', 'audience_type' => 'custom',
        'subject' => 'Hi', 'body' => 'Body',
        'recipients' => [['recipient_name' => 'No Address', 'recipient_type' => 'custom']],
    ])->assertCreated()->json('data');

    $this->postJson('/api/v1/communication/queue/process', ['school_id' => $this->school->id])->assertOk();

    $message = CommunicationMessage::where('batch_id', $batch['id'])->first();
    expect($message->status->value)->toBe('pending'); // back in queue, not lost
    expect($message->attempts)->toBe(1);
    expect($message->next_attempt_at)->not->toBeNull(); // backoff scheduled
    $this->assertDatabaseHas('communication_delivery_logs', ['message_id' => $message->id, 'event' => 'retried']);
});

// ---------------- Scheduling ----------------
it('queues scheduled messages without sending them immediately', function (): void {
    $future = now()->addDay()->toIso8601String();
    $batch = publishTo('students', 'in_app', ['scheduled_at' => $future]);

    $this->postJson('/api/v1/communication/queue/process', ['school_id' => $this->school->id])
        ->assertOk()->assertJsonPath('data.processed', 0); // not due yet

    $this->getJson("/api/v1/communication/messages/scheduled?school_id={$this->school->id}")
        ->assertOk()->assertJsonCount(2, 'data');
    expect(CommunicationMessage::where('batch_id', $batch['id'])->first()->status->value)->toBe('pending');
});

// ---------------- Notification hook (modules publish, never send) ----------------
it('lets a business module publish via a hook', function (): void {
    CommunicationTemplate::create(['school_id' => $this->school->id, 'name' => 'Fee Due', 'code' => 'fee_due', 'channel' => 'email', 'subject' => 'Fee due', 'body' => 'Please pay {{amount}}']);

    $batch = app(CommunicationHooks::class)->feeDue($this->school->id, ['amount' => '5000'], ['channel' => CommunicationChannel::Email]);

    expect($batch->event)->toBe('finance.fee_due');
    expect($batch->is_mandatory)->toBeTrue();
    expect(CommunicationMessage::where('batch_id', $batch->id)->where('channel', 'email')->count())->toBeGreaterThan(0);
});

// ---------------- Channels + preferences API ----------------
it('configures channels and exposes the active provider registry', function (): void {
    $this->postJson('/api/v1/communication/channels', [
        'school_id' => $this->school->id, 'channel' => 'sms', 'is_enabled' => true, 'max_attempts' => 5, 'backoff' => 'linear',
    ])->assertOk()->assertJsonPath('data.max_attempts', 5);

    $this->getJson("/api/v1/communication/channels?filter[school_id]={$this->school->id}")
        ->assertOk()
        ->assertJsonPath('data.active_providers', ['email', 'sms', 'push', 'in_app']);
});

it('reads and updates user communication preferences', function (): void {
    $this->putJson('/api/v1/communication/preferences', [
        'user_id' => $this->user->id,
        'preferences' => [['channel' => 'sms', 'is_enabled' => false]],
    ])->assertOk();

    $this->getJson("/api/v1/communication/preferences?user_id={$this->user->id}")
        ->assertOk()->assertJsonPath('data.user_id', $this->user->id);
});

// ---------------- Announcements + circulars (via the engine) ----------------
it('creates an announcement that goes out through the engine', function (): void {
    $this->postJson('/api/v1/communication/announcements', [
        'school_id' => $this->school->id, 'title' => 'Holiday', 'body' => 'School closed Friday', 'audience_type' => 'students',
    ])->assertCreated()->assertJsonPath('data.title', 'Holiday');

    // The announcement fanned out to messages via the engine.
    expect(CommunicationMessage::where('school_id', $this->school->id)->where('channel', 'in_app')->count())->toBe(2);
});

it('creates a circular with a media attachment reference', function (): void {
    $this->postJson('/api/v1/communication/circulars', [
        'school_id' => $this->school->id, 'title' => 'Annual Day', 'body' => 'See attached', 'audience_type' => 'school',
    ])->assertCreated()->assertJsonPath('data.title', 'Annual Day');
});

// ---------------- Dashboard ----------------
it('returns the communication dashboard widgets and charts', function (): void {
    publishTo('students');

    $this->getJson("/api/v1/communication/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['messages_sent', 'failed', 'pending', 'scheduled', 'delivery_rate'],
            'charts' => ['daily_messages', 'channel_usage', 'delivery_success', 'failure_trend'],
        ]]);
});
