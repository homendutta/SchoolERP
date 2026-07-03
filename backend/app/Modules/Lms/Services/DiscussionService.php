<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Lms\Models\Discussion;
use App\Modules\Lms\Models\DiscussionPost;
use App\Modules\Students\Models\Student;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Exceptions\DomainException;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Classroom discussions. Teachers create + moderate topics; students reply. There
 * is NO private messaging. Notifications reuse the Communication Engine.
 */
class DiscussionService extends BaseCrudService
{
    public function __construct(
        private readonly LmsAuthorizationService $auth,
        private readonly ActivityLogger $activity,
        private readonly LmsHooks $hooks,
    ) {}

    protected function model(): string
    {
        return Discussion::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['posts' => fn ($q) => $q->where('status', 'visible')->orderBy('id')]);
    }

    protected function searchable(): array
    {
        return ['title'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'subject_id', 'class_id', 'section_id', 'teacher_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at'];
    }

    /** Post a reply. Author is the student (self) or the teaching User. */
    public function post(User $user, Discussion $discussion, string $body, ?int $studentId = null): DiscussionPost
    {
        if ($discussion->locked) {
            throw new DomainException('This discussion is locked.', 422, 'DISCUSSION_LOCKED');
        }

        if ($this->auth->isTeacher($user)) {
            $authorType = User::class;
            $authorId = (int) $user->id;
        } else {
            $student = $this->auth->authorizeStudent($user, (int) $studentId);
            $authorType = Student::class;
            $authorId = (int) $student->id;
        }

        $post = DiscussionPost::query()->create([
            'school_id' => $discussion->school_id,
            'discussion_id' => $discussion->id,
            'author_type' => $authorType,
            'author_id' => $authorId,
            'body' => $body,
        ]);

        $this->activity->record('lms.discussion_post', 'Discussion reply', $post, [], (int) $discussion->school_id, 'lms');
        $this->hooks->publish((int) $discussion->school_id, 'lms.discussion_post', 'Discussion reply', 'A new reply was posted.');

        return $post;
    }

    /** Teacher moderation: hide a post. */
    public function moderatePost(User $user, DiscussionPost $post): DiscussionPost
    {
        $this->auth->requireTeacher($user);
        $post->update(['status' => 'hidden']);
        $this->activity->record('lms.discussion_moderated', 'Post hidden', $post, [], (int) $post->school_id, 'lms');

        return $post->refresh();
    }
}
