<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Library\Enums\BorrowStatus;
use App\Modules\Library\Enums\CopyStatus;
use App\Modules\Library\Models\Book;
use App\Modules\Library\Models\Borrowing;
use App\Modules\Library\Models\Copy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class LibraryDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(?int $schoolId): array
    {
        $copies = fn (): Builder => Copy::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));
        $countByStatus = fn (CopyStatus $s): int => (clone $copies())->where('status', $s->value)->count();

        $borrowings = fn (): Builder => Borrowing::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));

        return [
            'widgets' => [
                'total_titles' => Book::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->count(),
                'total_copies' => (clone $copies())->count(),
                'borrowed' => $countByStatus(CopyStatus::Borrowed),
                'available' => $countByStatus(CopyStatus::Available),
                'reserved' => $countByStatus(CopyStatus::Reserved),
                'overdue' => (clone $borrowings())->where('status', BorrowStatus::Overdue->value)->count(),
                'lost' => $countByStatus(CopyStatus::Lost),
                'damaged' => $countByStatus(CopyStatus::Damaged),
            ],
            'charts' => [
                'borrowing_trend' => $this->trend((clone $borrowings())),
                'popular_books' => $this->popular((clone $borrowings())),
                'category_distribution' => $this->categories($schoolId),
                'overdue_trend' => $this->trend((clone $borrowings())->where('status', BorrowStatus::Overdue->value)),
            ],
        ];
    }

    /**
     * @return array<int, array{label:string, count:int}>
     */
    private function trend(Builder $query): array
    {
        return $query->get(['borrow_date'])
            ->groupBy(fn ($b) => Carbon::parse($b->borrow_date)->format('Y-m-d'))
            ->map(fn ($g, $period) => ['label' => $period, 'count' => $g->count()])
            ->sortKeys()->values()->take(-14)->all();
    }

    /**
     * @return array<int, array{book_id:int, count:int}>
     */
    private function popular(Builder $query): array
    {
        return $query->get(['book_id'])
            ->groupBy('book_id')
            ->map(fn ($g, $bookId) => ['book_id' => (int) $bookId, 'count' => $g->count()])
            ->sortByDesc('count')->values()->take(10)->all();
    }

    /**
     * @return array<int, array{category_id:int|null, count:int}>
     */
    private function categories(?int $schoolId): array
    {
        return Book::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->get(['category_id'])
            ->groupBy('category_id')
            ->map(fn ($g, $categoryId) => ['category_id' => $categoryId !== '' ? (int) $categoryId : null, 'count' => $g->count()])
            ->values()->all();
    }
}
