<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Lms\Enums\LmsStatus;
use App\Modules\Lms\Models\Quiz;
use App\Modules\Lms\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** LMS quizzes with their questions (learning quizzes, not Examination exams). */
class QuizService extends LmsContentService
{
    protected function model(): string
    {
        return Quiz::class;
    }

    protected function contentType(): string
    {
        return 'quiz';
    }

    protected function withRelations(Builder $query): void
    {
        $query->with('questions');
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

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'title' => ['type' => 'text', 'columns' => ['title']],
            'status' => ['type' => 'enum', 'enum' => LmsStatus::class],
        ];
    }

    public function find(int|string $id): Model
    {
        $query = $this->query();
        $this->withRelations($query);

        return $query->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        $questions = $this->pullQuestions($data);

        return $this->transaction(function () use ($data, $questions): Model {
            $quiz = parent::create($data);
            $this->syncQuestions((int) $quiz->getKey(), $questions ?? []);

            return $quiz->load('questions');
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Model $model, array $data): Model
    {
        $questions = $this->pullQuestions($data);

        return $this->transaction(function () use ($model, $data, $questions): Model {
            $quiz = parent::update($model, $data);
            if ($questions !== null) {
                QuizQuestion::query()->where('quiz_id', $quiz->getKey())->delete();
                $this->syncQuestions((int) $quiz->getKey(), $questions);
            }

            return $quiz->load('questions');
        });
    }

    protected function onPublished(Model $model): void
    {
        $this->hooks->publish((int) $model->getAttribute('school_id'), 'lms.quiz_published', 'Quiz published', 'Quiz: '.$model->getAttribute('title'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>|null
     */
    private function pullQuestions(array &$data): ?array
    {
        if (! array_key_exists('questions', $data)) {
            return null;
        }
        $questions = $data['questions'];
        unset($data['questions']);

        return is_array($questions) ? $questions : [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $questions
     */
    private function syncQuestions(int $quizId, array $questions): void
    {
        foreach (array_values($questions) as $i => $q) {
            QuizQuestion::query()->create([
                'quiz_id' => $quizId,
                'type' => $q['type'],
                'question' => $q['question'],
                'options' => $q['options'] ?? null,
                'answer' => $q['answer'] ?? null,
                'marks' => $q['marks'] ?? 1,
                'sequence' => $q['sequence'] ?? $i,
            ]);
        }
    }
}
