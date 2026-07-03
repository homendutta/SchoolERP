<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Controllers;

use App\Modules\Cms\Http\Requests\PublicEnquiryRequest;
use App\Modules\Cms\Http\Requests\PublicSubmissionRequest;
use App\Modules\Cms\Services\EnquiryService;
use App\Modules\Cms\Services\PublicContentService;
use App\Modules\Cms\Services\SubmissionService;
use App\Platform\Shared\Http\Controllers\BaseController;
use App\Platform\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The public website's read-only content API + public form intake. No auth: the
 * GET endpoints expose only PUBLISHED content; the POST endpoints capture a
 * contact submission / admission enquiry into the ERP (Communication notifies
 * staff). Admission enquiries never auto-create an admission.
 */
class PublicController extends BaseController
{
    public function __construct(
        private readonly PublicContentService $content,
        private readonly SubmissionService $submissions,
        private readonly EnquiryService $enquiries,
    ) {}

    private function schoolId(Request $request): int
    {
        return $request->integer('school_id') ?: 1;
    }

    public function homepage(Request $request): JsonResponse
    {
        return $this->ok($this->content->homepage($this->schoolId($request)));
    }

    public function settings(Request $request): JsonResponse
    {
        return $this->ok($this->content->settings($this->schoolId($request)));
    }

    public function menus(Request $request): JsonResponse
    {
        return $this->ok($this->content->menus($this->schoolId($request)));
    }

    public function notices(Request $request): JsonResponse
    {
        return $this->ok($this->content->notices($this->schoolId($request), $request->integer('limit') ?: 20));
    }

    public function news(Request $request): JsonResponse
    {
        return $this->ok($this->content->news($this->schoolId($request), $request->integer('limit') ?: 20));
    }

    public function events(Request $request): JsonResponse
    {
        return $this->ok($this->content->events($this->schoolId($request), $request->integer('limit') ?: 20));
    }

    public function gallery(Request $request): JsonResponse
    {
        return $this->ok($this->content->galleries($this->schoolId($request), $request->integer('limit') ?: 50));
    }

    public function videos(Request $request): JsonResponse
    {
        return $this->ok($this->content->videos($this->schoolId($request), $request->integer('limit') ?: 50));
    }

    public function downloads(Request $request): JsonResponse
    {
        return $this->ok($this->content->downloads($this->schoolId($request), $request->integer('limit') ?: 100));
    }

    public function staff(Request $request): JsonResponse
    {
        return $this->ok($this->content->staffDirectory($this->schoolId($request)));
    }

    public function page(Request $request, string $slug): JsonResponse
    {
        $page = $this->content->page($this->schoolId($request), $slug);
        if ($page === null) {
            return ApiResponse::error('Page not found.', 404, 'NOT_FOUND');
        }

        return $this->ok($page);
    }

    /** Public contact / general-enquiry submission. */
    public function submitForm(PublicSubmissionRequest $request): JsonResponse
    {
        $submission = $this->submissions->capture($request->validated());

        return $this->ok(['id' => $submission->id], 'Thank you — your message has been received.', 201);
    }

    /** Public admission enquiry (enquiry only; no admission is created). */
    public function submitEnquiry(PublicEnquiryRequest $request): JsonResponse
    {
        $enquiry = $this->enquiries->capture($request->validated());

        return $this->ok(['id' => $enquiry->id], 'Thank you — your enquiry has been received.', 201);
    }
}
