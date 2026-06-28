<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Controllers;

use App\Modules\Communication\Actions\PublishMessageAction;
use App\Modules\Communication\Http\Requests\PublishMessageRequest;
use App\Modules\Communication\Http\Resources\BatchResource;
use App\Modules\Communication\Http\Resources\MessageResource;
use App\Modules\Communication\Models\CommunicationMessage;
use App\Modules\Communication\Services\CommunicationEngine;
use App\Modules\Communication\Services\MessageService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The message queue + delivery tracking. Publishing is the ONLY send path — no
 * module sends directly.
 */
class MessageController extends BaseController
{
    public function __construct(
        private readonly MessageService $service,
        private readonly CommunicationEngine $engine,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all());

        return $this->ok(MessageResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new MessageResource($this->service->find($id)->load('logs')));
    }

    /** Publish a communication request (manual or from a business module). */
    public function publish(PublishMessageRequest $request, PublishMessageAction $action): JsonResponse
    {
        return $this->ok(new BatchResource($action->handle($request->validated())), 'Communication published.', 201);
    }

    /** Scheduled (future) messages. */
    public function scheduled(Request $request): JsonResponse
    {
        $schoolId = (int) $request->integer('school_id');
        $messages = $this->service->scheduled($schoolId)->orderBy('scheduled_at')->paginate(25);

        return $this->ok(MessageResource::collection($messages), null, 200, [
            'total' => $messages->total(),
            'per_page' => $messages->perPage(),
            'current_page' => $messages->currentPage(),
            'last_page' => $messages->lastPage(),
        ]);
    }

    /** Process due pending messages (the queue worker). */
    public function process(Request $request): JsonResponse
    {
        $schoolId = $request->has('school_id') ? (int) $request->integer('school_id') : null;

        return $this->ok(['processed' => $this->engine->processDue($schoolId)], 'Queue processed.');
    }

    public function retry(int|string $id): JsonResponse
    {
        return $this->ok(new MessageResource($this->engine->retry(CommunicationMessage::query()->findOrFail($id))), 'Re-queued.');
    }

    public function markRead(int|string $id): JsonResponse
    {
        return $this->ok(new MessageResource($this->engine->markRead(CommunicationMessage::query()->findOrFail($id))), 'Marked read.');
    }

    public function cancel(int|string $id): JsonResponse
    {
        return $this->ok(new MessageResource($this->engine->cancel(CommunicationMessage::query()->findOrFail($id))), 'Cancelled.');
    }
}
