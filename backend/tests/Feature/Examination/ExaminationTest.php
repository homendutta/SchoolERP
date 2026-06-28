<?php

declare(strict_types=1);

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\Room;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Academic\Models\Subject;
use App\Modules\Academic\Models\Term;
use App\Modules\Administration\Models\School;
use App\Modules\Examination\Models\ExamGrade;
use App\Modules\Examination\Models\ExamMark;
use App\Modules\Examination\Models\ExamResult;
use App\Modules\Examination\Services\ResultProcessingService;
use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Modules\Timetable\Models\TimetablePeriod;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    $this->year = AcademicYear::create(['school_id' => $this->school->id, 'name' => '2025-2026', 'slug' => '2025-2026', 'start_date' => '2025-04-01', 'end_date' => '2026-03-31', 'status' => 'active']);
    $this->term = Term::create(['academic_year_id' => $this->year->id, 'name' => 'Term 1', 'start_date' => '2025-04-01', 'end_date' => '2025-09-30', 'status' => 'active']);
    $this->class = SchoolClass::create(['school_id' => $this->school->id, 'code' => 'C1', 'name' => 'Grade 1', 'slug' => 'grade-1', 'status' => 'active']);
    $this->section = Section::create(['class_id' => $this->class->id, 'name' => 'A', 'capacity' => 40, 'status' => 'active']);
    $this->math = Subject::create(['school_id' => $this->school->id, 'code' => 'MATH', 'name' => 'Mathematics', 'slug' => 'math', 'status' => 'active']);
    $this->science = Subject::create(['school_id' => $this->school->id, 'code' => 'SCI', 'name' => 'Science', 'slug' => 'science', 'status' => 'active']);
    $this->music = Subject::create(['school_id' => $this->school->id, 'code' => 'MUS', 'name' => 'Music', 'slug' => 'music', 'status' => 'active']);
    $this->room = Room::create(['school_id' => $this->school->id, 'code' => 'R1', 'name' => 'Hall 1', 'capacity' => 1, 'status' => 'active']);
    $this->period = TimetablePeriod::create(['school_id' => $this->school->id, 'name' => 'Slot 1', 'code' => 'S1', 'sort_order' => 1]);
    $this->invigilator = Staff::create(['school_id' => $this->school->id, 'employee_number' => 'T1', 'name' => 'Mr Rao', 'status' => 'active', 'is_teaching' => true]);

    $this->makeStudent = function (string $adm, string $name): Student {
        $student = Student::create(['school_id' => $this->school->id, 'admission_number' => $adm, 'name' => $name, 'status' => 'active']);
        StudentAcademicRecord::create([
            'school_id' => $this->school->id, 'student_id' => $student->id, 'academic_year_id' => $this->year->id,
            'class_id' => $this->class->id, 'section_id' => $this->section->id, 'status' => 'active', 'is_current' => true, 'started_on' => now()->toDateString(),
        ]);

        return $student->refresh();
    };

    $this->s1 = ($this->makeStudent)('1001', 'Asha');
    $this->s2 = ($this->makeStudent)('1002', 'Bina');

    // Configurable grade scale.
    foreach ([
        ['code' => 'A', 'min' => 75, 'max' => 100, 'gp' => 4, 'fail' => false],
        ['code' => 'B', 'min' => 33, 'max' => 74.99, 'gp' => 3, 'fail' => false],
        ['code' => 'F', 'min' => 0, 'max' => 32.99, 'gp' => 0, 'fail' => true],
    ] as $g) {
        ExamGrade::create([
            'school_id' => $this->school->id, 'code' => $g['code'], 'min_percentage' => $g['min'], 'max_percentage' => $g['max'],
            'grade_point' => $g['gp'], 'is_failing' => $g['fail'], 'status' => 'active',
        ]);
    }

    actingAsSuperAdmin();

    // An exam type + session reused across tests.
    $this->examType = $this->postJson('/api/v1/examinations/types', [
        'school_id' => $this->school->id, 'name' => 'Half Yearly', 'code' => 'HY',
    ])->json('data.id');

    $this->sessionId = $this->postJson('/api/v1/examinations/sessions', [
        'school_id' => $this->school->id, 'academic_year_id' => $this->year->id, 'term_id' => $this->term->id,
        'exam_type_id' => $this->examType, 'name' => 'Half Yearly 2025', 'ranking_method' => 'competition',
    ])->json('data.id');
});

/** Map a subject to the session (core subjects auto-assign current students). */
function mapSubject(int $subjectId, bool $elective = false, float $max = 100, float $pass = 33): int
{
    return test()->postJson('/api/v1/examinations/subjects', [
        'school_id' => test()->school->id, 'exam_session_id' => test()->sessionId,
        'class_id' => test()->class->id, 'section_id' => test()->section->id,
        'subject_id' => $subjectId, 'is_elective' => $elective, 'max_marks' => $max, 'passing_marks' => $pass,
    ])->assertCreated()->json('data.id');
}

// ---------------- Types / sessions ----------------
it('configures exam types and sessions (nothing hardcoded)', function (): void {
    $this->getJson("/api/v1/examinations/types?filter[school_id]={$this->school->id}")->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/examinations/sessions?filter[school_id]={$this->school->id}")
        ->assertOk()->assertJsonPath('data.0.exam_type', 'Half Yearly');
});

// ---------------- Subject mapping + auto-assign ----------------
it('auto-assigns core subjects to the current students of the class', function (): void {
    $mathId = mapSubject($this->math->id);

    $this->getJson("/api/v1/examinations/subjects/{$mathId}/students")
        ->assertOk()->assertJsonCount(2, 'data');
});

// ---------------- MANDATORY: optional / elective handling ----------------
it('never auto-assigns electives and never fails a student for a subject they did not take', function (): void {
    $mathId = mapSubject($this->math->id);
    $musicId = mapSubject($this->music->id, elective: true);

    // Elective is not auto-assigned to anyone.
    $this->getJson("/api/v1/examinations/subjects/{$musicId}/students")->assertOk()->assertJsonCount(0, 'data');

    // Only student 1 opts into Music.
    $this->postJson("/api/v1/examinations/subjects/{$musicId}/assign-student", ['student_id' => $this->s1->id])->assertOk();
    $this->getJson("/api/v1/examinations/subjects/{$musicId}/students")->assertOk()->assertJsonCount(1, 'data');

    // Give everyone Math marks; give Music marks only to student 1.
    $this->postJson('/api/v1/examinations/marks', ['exam_subject_id' => $mathId, 'entries' => [
        ['student_id' => $this->s1->id, 'marks_obtained' => 80],
        ['student_id' => $this->s2->id, 'marks_obtained' => 80],
    ]])->assertOk();
    $this->postJson('/api/v1/examinations/marks', ['exam_subject_id' => $musicId, 'entries' => [
        ['student_id' => $this->s1->id, 'marks_obtained' => 90],
    ]])->assertOk();

    $this->postJson("/api/v1/examinations/sessions/{$this->sessionId}/process")->assertOk();

    // Student 2 (did NOT take Music) is graded only on Math — never failed for Music.
    $breakdown = app(ResultProcessingService::class)->subjectResults($this->sessionId, $this->s2->id);
    expect(collect($breakdown)->pluck('subject'))->not->toContain('Music');
    expect(collect($breakdown)->where('passed', false)->count())->toBe(0);

    $r2 = ExamResult::where('exam_session_id', $this->sessionId)->where('student_id', $this->s2->id)->first();
    expect($r2->result_status->value)->toBe('pass');
    expect($r2->subjects_count)->toBe(1); // Math only

    // Student 1 is graded on Math + Music (both assigned).
    $r1 = ExamResult::where('exam_session_id', $this->sessionId)->where('student_id', $this->s1->id)->first();
    expect($r1->subjects_count)->toBe(2);
});

// ---------------- Marks entry + validation ----------------
it('only accepts marks for assigned students and validates maximum marks', function (): void {
    $mathId = mapSubject($this->math->id);
    $musicId = mapSubject($this->music->id, elective: true); // nobody assigned

    // Both students are assigned Math → both saved.
    $this->postJson('/api/v1/examinations/marks', ['exam_subject_id' => $mathId, 'entries' => [
        ['student_id' => $this->s1->id, 'marks_obtained' => 40],
        ['student_id' => $this->s2->id, 'marks_obtained' => 200], // exceeds max → skipped
    ]])->assertOk()->assertJsonPath('data.saved', 1)->assertJsonPath('data.skipped', 1);

    // No student is assigned Music → all skipped.
    $this->postJson('/api/v1/examinations/marks', ['exam_subject_id' => $musicId, 'entries' => [
        ['student_id' => $this->s1->id, 'marks_obtained' => 50],
    ]])->assertOk()->assertJsonPath('data.saved', 0)->assertJsonPath('data.skipped', 1);

    expect(ExamMark::count())->toBe(1);
});

// ---------------- Result processing + grade + pass/fail + ranking ----------------
it('processes results with configurable grades, pass/fail and ranking', function (): void {
    $mathId = mapSubject($this->math->id);
    $sciId = mapSubject($this->science->id);

    $this->postJson('/api/v1/examinations/marks', ['exam_subject_id' => $mathId, 'entries' => [
        ['student_id' => $this->s1->id, 'marks_obtained' => 80],
        ['student_id' => $this->s2->id, 'marks_obtained' => 20], // fail (<33)
    ]])->assertOk();
    $this->postJson('/api/v1/examinations/marks', ['exam_subject_id' => $sciId, 'entries' => [
        ['student_id' => $this->s1->id, 'marks_obtained' => 70],
        ['student_id' => $this->s2->id, 'marks_obtained' => 60],
    ]])->assertOk();

    $this->postJson("/api/v1/examinations/sessions/{$this->sessionId}/process")
        ->assertOk()->assertJsonPath('data.processed', 2);

    $r1 = ExamResult::where('student_id', $this->s1->id)->first();
    $r2 = ExamResult::where('student_id', $this->s2->id)->first();

    expect($r1->percentage)->toBe(75.0)->and($r1->result_status->value)->toBe('pass')->and($r1->rank)->toBe(1);
    expect($r2->result_status->value)->toBe('fail')->and($r2->failed_count)->toBe(1)->and($r2->rank)->toBe(2);
    expect($r1->grade->code)->toBe('A');
});

// ---------------- Schedule clash ----------------
it('prevents a room clash when scheduling exams', function (): void {
    $mathId = mapSubject($this->math->id);
    $sciId = mapSubject($this->science->id);

    $this->postJson('/api/v1/examinations/schedules', [
        'school_id' => $this->school->id, 'exam_session_id' => $this->sessionId, 'exam_subject_id' => $mathId,
        'exam_date' => '2026-01-10', 'period_id' => $this->period->id, 'room_id' => $this->room->id,
    ])->assertCreated();

    $this->postJson('/api/v1/examinations/schedules', [
        'school_id' => $this->school->id, 'exam_session_id' => $this->sessionId, 'exam_subject_id' => $sciId,
        'exam_date' => '2026-01-10', 'period_id' => $this->period->id, 'room_id' => $this->room->id,
    ])->assertStatus(422)->assertJsonPath('code', 'ROOM_CLASH');
});

// ---------------- Invigilator + seating capacity ----------------
it('assigns invigilators and never exceeds room capacity', function (): void {
    $mathId = mapSubject($this->math->id);
    $scheduleId = $this->postJson('/api/v1/examinations/schedules', [
        'school_id' => $this->school->id, 'exam_session_id' => $this->sessionId, 'exam_subject_id' => $mathId,
        'exam_date' => '2026-01-10', 'period_id' => $this->period->id, 'room_id' => $this->room->id,
    ])->json('data.id');

    $this->postJson('/api/v1/examinations/invigilators', [
        'school_id' => $this->school->id, 'exam_schedule_id' => $scheduleId, 'staff_id' => $this->invigilator->id, 'role' => 'chief',
    ])->assertCreated();

    // Room capacity = 1.
    $this->postJson('/api/v1/examinations/seating', [
        'school_id' => $this->school->id, 'exam_schedule_id' => $scheduleId, 'room_id' => $this->room->id, 'student_id' => $this->s1->id, 'seat_number' => 'A1',
    ])->assertCreated();
    $this->postJson('/api/v1/examinations/seating', [
        'school_id' => $this->school->id, 'exam_schedule_id' => $scheduleId, 'room_id' => $this->room->id, 'student_id' => $this->s2->id, 'seat_number' => 'A2',
    ])->assertStatus(422)->assertJsonPath('code', 'ROOM_CAPACITY_EXCEEDED');
});

// ---------------- Exam attendance (separate) ----------------
it('records exam attendance separately with malpractice / medical statuses', function (): void {
    $mathId = mapSubject($this->math->id);
    $scheduleId = $this->postJson('/api/v1/examinations/schedules', [
        'school_id' => $this->school->id, 'exam_session_id' => $this->sessionId, 'exam_subject_id' => $mathId,
        'exam_date' => '2026-01-10', 'period_id' => $this->period->id, 'room_id' => $this->room->id,
    ])->json('data.id');

    $this->postJson('/api/v1/examinations/attendance', [
        'school_id' => $this->school->id, 'exam_schedule_id' => $scheduleId, 'entries' => [
            ['student_id' => $this->s1->id, 'status' => 'present'],
            ['student_id' => $this->s2->id, 'status' => 'malpractice', 'remarks' => 'Caught copying'],
        ],
    ])->assertOk()->assertJsonPath('data.marked', 2);

    $this->getJson("/api/v1/examinations/attendance?filter[exam_schedule_id]={$scheduleId}")->assertOk()->assertJsonCount(2, 'data');
});

// ---------------- Report card respects assigned subjects + identity QR ----------------
it('builds a report card listing only assigned subjects with an identity QR', function (): void {
    $mathId = mapSubject($this->math->id);
    mapSubject($this->music->id, elective: true); // student does NOT take Music

    $this->postJson('/api/v1/examinations/marks', ['exam_subject_id' => $mathId, 'entries' => [
        ['student_id' => $this->s1->id, 'marks_obtained' => 80],
    ]])->assertOk();
    $this->postJson("/api/v1/examinations/sessions/{$this->sessionId}/process")->assertOk();

    $card = $this->getJson("/api/v1/examinations/report-cards?exam_session_id={$this->sessionId}&student_id={$this->s1->id}")
        ->assertOk()->json('data');

    expect(collect($card['subjects'])->pluck('subject'))->toContain('Mathematics')->not->toContain('Music');
    expect($card['identity']['qr_url'])->not->toBeNull();
    expect((float) $card['summary']['percentage'])->toBe(80.0);
});

// ---------------- Tabulation + promotion readiness ----------------
it('produces a tabulation sheet and promotion readiness', function (): void {
    $mathId = mapSubject($this->math->id);

    $this->postJson('/api/v1/examinations/marks', ['exam_subject_id' => $mathId, 'entries' => [
        ['student_id' => $this->s1->id, 'marks_obtained' => 80],
        ['student_id' => $this->s2->id, 'marks_obtained' => 10],
    ]])->assertOk();
    $this->postJson("/api/v1/examinations/sessions/{$this->sessionId}/process")->assertOk();

    $tab = $this->getJson("/api/v1/examinations/tabulation?exam_session_id={$this->sessionId}&class_id={$this->class->id}")
        ->assertOk()->json('data');
    expect($tab['rows'])->toHaveCount(2);

    $promo = $this->getJson("/api/v1/examinations/promotion-readiness?exam_session_id={$this->sessionId}&class_id={$this->class->id}")
        ->assertOk()->json('data');
    expect($promo['summary']['eligible'])->toBe(1);
    expect($promo['summary']['not_eligible'])->toBe(1);
});

// ---------------- Publish ----------------
it('publishes results with audit and student timeline entries', function (): void {
    $mathId = mapSubject($this->math->id);
    $this->postJson('/api/v1/examinations/marks', ['exam_subject_id' => $mathId, 'entries' => [
        ['student_id' => $this->s1->id, 'marks_obtained' => 80],
    ]])->assertOk();
    $this->postJson("/api/v1/examinations/sessions/{$this->sessionId}/process")->assertOk();

    // Both current students have a processed result (s2 has no marks → fail).
    $this->postJson("/api/v1/examinations/sessions/{$this->sessionId}/publish")
        ->assertOk()->assertJsonPath('data.published', 2);

    $this->assertDatabaseHas('activity_logs', ['action' => 'exam.results_published']);
    $this->assertDatabaseHas('student_timelines', ['student_id' => $this->s1->id, 'event_type' => 'exam.result_published']);
    expect(ExamResult::where('student_id', $this->s1->id)->first()->is_published)->toBeTrue();
});

// ---------------- Dashboard ----------------
it('returns the examination dashboard widgets and charts', function (): void {
    $this->getJson("/api/v1/examinations/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['active_exams', 'scheduled_exams', 'completed_exams', 'pending_marks_entry', 'published_results'],
            'charts' => ['pass_percentage', 'grade_distribution', 'subject_performance', 'class_performance'],
        ]]);
});

// ---------------- Grades + components configurable ----------------
it('manages configurable grades and components', function (): void {
    $this->postJson('/api/v1/examinations/components', ['school_id' => $this->school->id, 'name' => 'Theory', 'code' => 'TH'])->assertCreated();
    $this->postJson('/api/v1/examinations/components', ['school_id' => $this->school->id, 'name' => 'Practical', 'code' => 'PR'])->assertCreated();
    $this->getJson("/api/v1/examinations/components?filter[school_id]={$this->school->id}")->assertOk()->assertJsonCount(2, 'data');
    $this->getJson("/api/v1/examinations/grades?filter[school_id]={$this->school->id}")->assertOk()->assertJsonCount(3, 'data');
});
