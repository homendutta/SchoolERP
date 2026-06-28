<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Actions;

use App\Modules\Administration\Models\NumberSequence;
use App\Modules\Administration\Models\Role;
use App\Modules\Administration\Models\User;
use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Administration\Services\SettingsService;
use App\Modules\Admissions\DTO\EnrollmentResult;
use App\Modules\Admissions\Enums\ApplicationStatus;
use App\Modules\Admissions\Models\AdmissionApplication;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Foundation\Notifications\NotificationService;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Enroll an approved applicant. Executes every step of admission → enrollment in
 * ONE transaction: Guardian, Student, Academic Record, Admission Number, Student
 * & Parent users, role assignment, links, credentials, notifications, audit log.
 * Any failure rolls the whole thing back. Students are created ONLY here.
 */
class EnrollStudentAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly NumberGeneratorService $numbers,
        private readonly NotificationService $notifications,
        private readonly ActivityLogger $activity,
        private readonly SettingsService $settings,
    ) {}

    public function handle(AdmissionApplication $application): EnrollmentResult
    {
        if ($application->status === ApplicationStatus::Enrolled || $application->enrolled_student_id !== null) {
            throw BusinessRuleException::make('This application is already enrolled.', 'ALREADY_ENROLLED');
        }

        if ($application->status !== ApplicationStatus::Approved) {
            throw BusinessRuleException::make('Only an approved application can be enrolled.', 'NOT_APPROVED');
        }

        return DB::transaction(function () use ($application): EnrollmentResult {
            $schoolId = (int) $application->school_id;
            $config = $this->settings->group('admissions', $schoolId);
            $passwordLength = (int) ($config['password_length'] ?? 10);

            // 4. Generate the official numbers. The Parent ID sequence gets a
            // distinct default prefix so it never collides with the numeric
            // Admission Number in the global, unique username column (the admin
            // can reconfigure either sequence later via the Number Generator).
            NumberSequence::query()->firstOrCreate(
                ['school_id' => $schoolId, 'key' => 'parent_number'],
                ['prefix' => 'P'],
            );
            $admissionNumber = $this->numbers->next('admission_number', $schoolId);
            $parentNumber = $this->numbers->next('parent_number', $schoolId);

            // 1. Guardian.
            $guardian = Guardian::create([
                'school_id' => $schoolId,
                'parent_number' => $parentNumber,
                'name' => $application->guardian_name,
                'relation' => $application->guardian_relation,
                'phone' => $application->guardian_phone,
                'email' => $application->guardian_email,
                'occupation' => $application->guardian_occupation,
                'address' => $application->address,
                'status' => 'active',
            ]);

            // 6. Parent user (username = Parent ID).
            $parentPassword = $this->generatePassword($passwordLength);
            $parentUser = User::create([
                'school_id' => $schoolId,
                'name' => $application->guardian_name,
                'username' => $parentNumber,
                // Login email is synthetic + unique; the real contact email lives
                // on the Guardian record. Username is the Parent ID.
                'email' => "par.{$parentNumber}@{$schoolId}.asylinx.local",
                'phone' => $application->guardian_phone,
                'password' => $parentPassword,
                'status' => 'active',
                'must_change_password' => true,
            ]);
            $guardian->forceFill(['user_id' => $parentUser->id])->save();

            // 2. Student.
            $student = Student::create([
                'school_id' => $schoolId,
                'admission_application_id' => $application->id,
                'admission_number' => $admissionNumber,
                'name' => $application->student_name,
                'gender' => $application->gender,
                'date_of_birth' => $application->date_of_birth,
                'blood_group' => $application->blood_group,
                'address' => $application->address,
                'status' => 'active',
                'enrolled_on' => now()->toDateString(),
            ]);

            // 5. Student user (username = Admission Number).
            $studentPassword = $this->generatePassword($passwordLength);
            $studentUser = User::create([
                'school_id' => $schoolId,
                'name' => $application->student_name,
                'username' => $admissionNumber,
                // Synthetic + unique login email; username is the Admission Number.
                'email' => "stu.{$admissionNumber}@{$schoolId}.asylinx.local",
                'password' => $studentPassword,
                'status' => 'active',
                'must_change_password' => true,
            ]);
            $student->forceFill(['user_id' => $studentUser->id])->save();

            // 3. Student Academic Record (immutable per-year placement).
            StudentAcademicRecord::create([
                'school_id' => $schoolId,
                'student_id' => $student->id,
                'academic_year_id' => $application->academic_year_id,
                'class_id' => $application->class_id,
                'section_id' => $application->section_id,
                'status' => 'active',
                'is_current' => true,
                'started_on' => now()->toDateString(),
            ]);

            // 7. Roles.
            $this->assignRole($studentUser, 'student');
            $this->assignRole($parentUser, 'parent');

            // 8. Link Student ↔ Guardian (relationship details live on the pivot;
            // relationship type is Master Data and set later, not as free text).
            $student->guardians()->attach($guardian->id, [
                'is_primary' => true,
                'emergency_contact' => true,
                'pickup_authorized' => true,
                'financial_responsible' => true,
            ]);

            // 10. Mark the application enrolled.
            $application->forceFill([
                'status' => ApplicationStatus::Enrolled->value,
                'enrolled_student_id' => $student->id,
            ])->save();

            $studentCreds = ['username' => $admissionNumber, 'password' => $studentPassword];
            $parentCreds = ['username' => $parentNumber, 'password' => $parentPassword];

            // 11. Notifications (only if enabled for the school).
            $this->sendNotifications($application, $student, $guardian, $studentCreds, $parentCreds, $config, $schoolId);

            // 12. Activity log.
            $this->activity->record(
                'admission.enrolled',
                "Enrolled {$student->name} (Admission #{$admissionNumber})",
                $application,
                ['student_id' => $student->id, 'admission_number' => $admissionNumber],
                $schoolId,
                'admissions',
            );

            return new EnrollmentResult(
                $application->refresh(),
                $student->refresh(),
                $guardian->refresh(),
                $studentCreds,
                $parentCreds,
            );
        });
    }

    private function assignRole(User $user, string $slug): void
    {
        $role = Role::query()->where('slug', $slug)->first();
        if ($role !== null) {
            $user->assignRole($role);
        }
    }

    private function generatePassword(int $length): string
    {
        return Str::password(max(8, $length), symbols: false);
    }

    /**
     * @param  array{username:string, password:string}  $studentCreds
     * @param  array{username:string, password:string}  $parentCreds
     * @param  array<string, mixed>  $config
     */
    private function sendNotifications(
        AdmissionApplication $application,
        Student $student,
        Guardian $guardian,
        array $studentCreds,
        array $parentCreds,
        array $config,
        int $schoolId,
    ): void {
        $emailEnabled = (bool) ($config['notify_email'] ?? true);
        $smsEnabled = (bool) ($config['notify_sms'] ?? true);

        $studentBody = "Welcome {$student->name}. Your login is {$studentCreds['username']} / {$studentCreds['password']}.";
        $parentBody = "Admission confirmed for {$student->name}. Parent login: {$parentCreds['username']} / {$parentCreds['password']}.";

        if ($application->guardian_email) {
            $this->notifications->email($application->guardian_email, 'Admission Confirmed', $parentBody, $emailEnabled, $schoolId, $guardian);
        }
        if ($application->guardian_phone) {
            $this->notifications->sms($application->guardian_phone, $parentBody, $smsEnabled, $schoolId, $guardian);
        }
        if ($application->guardian_email) {
            $this->notifications->email($application->guardian_email, 'Student Login Credentials', $studentBody, $emailEnabled, $schoolId, $student);
        }
        if ($application->guardian_phone) {
            $this->notifications->sms($application->guardian_phone, $studentBody, $smsEnabled, $schoolId, $student);
        }
    }
}
