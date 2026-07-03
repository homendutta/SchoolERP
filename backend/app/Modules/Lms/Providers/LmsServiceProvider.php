<?php

declare(strict_types=1);

namespace App\Modules\Lms\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Learning Management System module (Sprint 19).
 *
 * Digital teaching + learning for ENROLLED students: lesson plans, lessons,
 * learning materials, homework, assignments, immutable student submissions,
 * teacher reviews/grading, reusable classroom resources, moderated classroom
 * discussions, learning quizzes + attempts and operational learning progress.
 *
 * It EXTENDS Academic/Timetable/Examination/Portal without duplicating them:
 * teachers manage only their assigned subjects (Academic teacher-subject
 * assignments), students/parents are isolated via the Portal context, files use
 * the Media Platform, notifications use the Communication Engine, and homework /
 * assignments / quizzes are INDEPENDENT of the Examination module. Every action is
 * audited; the Timeline records publications, submissions, grading, quiz attempts
 * and discussions; submission history is immutable.
 *
 * Designed so Live Classes, Online/Paid Courses, external learners, Zoom/Meet/
 * Teams/BBB/Jitsi, SCORM/Moodle, AI tutoring/grading, learning analytics, digital
 * certificates, offline learning and a PWA (Sprint 20+) require no structural
 * change.
 */
class LmsServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Lms';
}
