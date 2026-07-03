<?php

declare(strict_types=1);

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Subject;
use App\Modules\Academic\Models\TeacherSubjectAssignment;
use App\Modules\Administration\Models\School;
use App\Modules\Administration\Models\User;
use App\Modules\Lms\Models\Homework;
use App\Modules\Lms\Models\Submission;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    $this->year = AcademicYear::create(['school_id' => $this->school->id, 'name' => '2025-2026', 'slug' => '2025-2026', 'start_date' => '2025-04-01', 'end_date' => '2026-03-31', 'status' => 'active']);
    $this->class = SchoolClass::create(['school_id' => $this->school->id, 'code' => 'C1', 'name' => 'Class 1', 'slug' => 'class-1']);
    $this->subject = Subject::create(['school_id' => $this->school->id, 'code' => 'MATH', 'name' => 'Mathematics', 'slug' => 'mathematics']);

    // A teacher assigned to Mathematics for Class 1.
    $this->teacher = User::create(['name' => 'Teacher', 'email' => 't@x.test', 'username' => 'teach1', 'password' => 'Password@123', 'status' => 'active', 'school_id' => $this->school->id]);
    TeacherSubjectAssignment::create(['academic_year_id' => $this->year->id, 'class_id' => $this->class->id, 'subject_id' => $this->subject->id, 'teacher_id' => $this->teacher->id, 'status' => 'active']);

    // A user with no teaching assignment.
    $this->stranger = User::create(['name' => 'Stranger', 'email' => 'x@x.test', 'username' => 'stranger', 'password' => 'Password@123', 'status' => 'active', 'school_id' => $this->school->id]);

    // A student (self-service) + an unrelated student.
    $this->studentUser = User::create(['name' => 'Stud', 'email' => 's@x.test', 'username' => 'stud1', 'password' => 'Password@123', 'status' => 'active', 'school_id' => $this->school->id]);
    $this->student = Student::create(['school_id' => $this->school->id, 'user_id' => $this->studentUser->id, 'admission_number' => 'A1', 'name' => 'Pupil', 'status' => 'active']);
    $this->other = Student::create(['school_id' => $this->school->id, 'admission_number' => 'A2', 'name' => 'Other', 'status' => 'active']);

    // A parent of the student.
    $this->parentUser = User::create(['name' => 'Parent', 'email' => 'p@x.test', 'username' => 'parent1', 'password' => 'Password@123', 'status' => 'active', 'school_id' => $this->school->id]);
    $this->guardian = Guardian::create(['school_id' => $this->school->id, 'user_id' => $this->parentUser->id, 'name' => 'Parent', 'relation' => 'father']);
    DB::table('student_guardian')->insert(['student_id' => $this->student->id, 'guardian_id' => $this->guardian->id, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()]);
});

function makeHomework(array $overrides = []): int
{
    Sanctum::actingAs(test()->teacher);

    return test()->postJson('/api/v1/lms/homework', array_merge([
        'school_id' => test()->school->id, 'subject_id' => test()->subject->id, 'class_id' => test()->class->id,
        'title' => 'Algebra sheet', 'status' => 'published',
    ], $overrides))->assertCreated()->json('data.id');
}

// ---------------- Teacher authorization ----------------
it('lets an assigned teacher publish homework and notifies via Communication', function (): void {
    $id = makeHomework();

    expect(Homework::find($id)->teacher_id)->toBe($this->teacher->id);
    $this->assertDatabaseHas('activity_logs', ['action' => 'lms.homework_published']);
    $this->assertDatabaseHas('communication_batches', ['event' => 'lms.homework_assigned']);
});

it('blocks a non-assigned user from creating content for a subject', function (): void {
    Sanctum::actingAs($this->stranger);

    $this->postJson('/api/v1/lms/homework', [
        'school_id' => $this->school->id, 'subject_id' => $this->subject->id, 'class_id' => $this->class->id, 'title' => 'X',
    ])->assertStatus(403);
});

// ---------------- Student submissions (immutable version history) ----------------
it('records immutable submission versions', function (): void {
    $hwId = makeHomework();
    Sanctum::actingAs($this->studentUser);

    $this->postJson('/api/v1/lms/submissions', ['type' => 'homework', 'submittable_id' => $hwId, 'student_id' => $this->student->id, 'content' => 'v1'])
        ->assertCreated()->assertJsonPath('data.version', 1);
    $this->postJson('/api/v1/lms/submissions', ['type' => 'homework', 'submittable_id' => $hwId, 'student_id' => $this->student->id, 'content' => 'v2'])
        ->assertCreated()->assertJsonPath('data.version', 2);

    // Both versions kept (immutable history).
    expect(Submission::where('submittable_id', $hwId)->where('student_id', $this->student->id)->count())->toBe(2);
    $this->getJson("/api/v1/lms/submissions?type=homework&submittable_id={$hwId}&student_id={$this->student->id}")
        ->assertOk()->assertJsonCount(2, 'data');
    $this->assertDatabaseHas('communication_batches', ['event' => 'lms.submission_received']);
});

it('blocks a student from submitting for another student', function (): void {
    $hwId = makeHomework();
    Sanctum::actingAs($this->studentUser);

    $this->postJson('/api/v1/lms/submissions', ['type' => 'homework', 'submittable_id' => $hwId, 'student_id' => $this->other->id, 'content' => 'x'])
        ->assertStatus(403);
});

it('rejects a late submission when late submissions are not allowed', function (): void {
    $hwId = makeHomework(['due_date' => now()->subWeek()->toDateString(), 'allow_late' => false]);
    Sanctum::actingAs($this->studentUser);

    $this->postJson('/api/v1/lms/submissions', ['type' => 'homework', 'submittable_id' => $hwId, 'student_id' => $this->student->id, 'content' => 'late'])
        ->assertStatus(422);
});

// ---------------- Teacher review + grading ----------------
it('lets a teacher grade a submission', function (): void {
    $hwId = makeHomework();
    Sanctum::actingAs($this->studentUser);
    $subId = $this->postJson('/api/v1/lms/submissions', ['type' => 'homework', 'submittable_id' => $hwId, 'student_id' => $this->student->id, 'content' => 'v1'])->json('data.id');

    Sanctum::actingAs($this->teacher);
    $this->postJson('/api/v1/lms/reviews', ['submission_id' => $subId, 'subject_id' => $this->subject->id, 'action' => 'grade', 'marks' => 9])
        ->assertCreated();

    expect(Submission::find($subId)->status->value)->toBe('graded');
    expect((float) Submission::find($subId)->marks)->toBe(9.0);
    $this->assertDatabaseHas('communication_batches', ['event' => 'lms.assignment_graded']);
});

// ---------------- Quiz + attempts (auto-graded; attempt limit) ----------------
it('auto-grades a quiz attempt and enforces the attempt limit', function (): void {
    Sanctum::actingAs($this->teacher);
    $quiz = $this->postJson('/api/v1/lms/quizzes', [
        'school_id' => $this->school->id, 'subject_id' => $this->subject->id, 'class_id' => $this->class->id,
        'title' => 'Times tables', 'status' => 'published', 'passing_marks' => 1, 'max_attempts' => 1,
        'questions' => [
            ['type' => 'multiple_choice', 'question' => '2x2?', 'options' => ['3', '4'], 'answer' => ['4'], 'marks' => 2],
        ],
    ])->assertCreated()->json('data');
    $questionId = $quiz['questions'][0]['id'];

    Sanctum::actingAs($this->studentUser);
    $this->postJson('/api/v1/lms/attempts', [
        'quiz_id' => $quiz['id'], 'student_id' => $this->student->id, 'responses' => [(string) $questionId => '4'],
    ])->assertCreated()->assertJsonPath('data.score', '2.00')->assertJsonPath('data.passed', true);

    // Second attempt exceeds the limit.
    $this->postJson('/api/v1/lms/attempts', ['quiz_id' => $quiz['id'], 'student_id' => $this->student->id, 'responses' => []])
        ->assertStatus(422);
});

// ---------------- Discussions ----------------
it('lets a teacher open a discussion and a student reply, and locks work', function (): void {
    Sanctum::actingAs($this->teacher);
    $discussion = $this->postJson('/api/v1/lms/discussions', [
        'school_id' => $this->school->id, 'subject_id' => $this->subject->id, 'class_id' => $this->class->id, 'title' => 'Doubts',
    ])->assertCreated()->json('data');

    Sanctum::actingAs($this->studentUser);
    $this->postJson("/api/v1/lms/discussions/{$discussion['id']}/posts", ['body' => 'My question', 'student_id' => $this->student->id])
        ->assertCreated();
    $this->assertDatabaseHas('lms_discussion_posts', ['discussion_id' => $discussion['id'], 'body' => 'My question']);

    // Lock it (teacher) then a reply is rejected.
    Sanctum::actingAs($this->teacher);
    $this->putJson("/api/v1/lms/discussions/{$discussion['id']}", ['locked' => true])->assertOk();
    Sanctum::actingAs($this->studentUser);
    $this->postJson("/api/v1/lms/discussions/{$discussion['id']}/posts", ['body' => 'again', 'student_id' => $this->student->id])
        ->assertStatus(422);
});

// ---------------- Dashboards + progress + parent isolation ----------------
it('returns role-aware dashboards and parent-scoped progress', function (): void {
    Sanctum::actingAs($this->teacher);
    $this->getJson('/api/v1/lms/dashboard')->assertOk()->assertJsonPath('data.role', 'teacher');

    Sanctum::actingAs($this->parentUser);
    $this->getJson('/api/v1/lms/dashboard')->assertOk()->assertJsonPath('data.role', 'student');
    $this->getJson("/api/v1/lms/progress?student_id={$this->student->id}")
        ->assertOk()->assertJsonStructure(['data' => ['lessons_completed', 'homework_submitted', 'assignments_submitted', 'quizzes_completed', 'average_quiz_score']]);
    // Parent cannot see an unrelated student's progress.
    $this->getJson("/api/v1/lms/progress?student_id={$this->other->id}")->assertStatus(403);
});

// ---------------- Auth required ----------------
it('requires authentication', function (): void {
    $this->getJson('/api/v1/lms/dashboard')->assertStatus(401);
});
