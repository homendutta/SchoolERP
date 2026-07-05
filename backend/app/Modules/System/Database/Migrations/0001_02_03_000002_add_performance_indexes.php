<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive composite indexes for hot query paths (Sprint 23 — DB optimization).
 * These do NOT change any business data or behaviour — they only speed up common
 * filtered reads (fee-collection reports, portal fee history, audit reports).
 * Guarded so a fresh install and an upgrade both apply cleanly.
 */
return new class extends Migration
{
    /** @var array<int, array{table:string, columns:array<int,string>, name:string}> */
    private array $indexes = [
        ['table' => 'payments', 'columns' => ['school_id', 'paid_on'], 'name' => 'payments_school_paidon_idx'],
        ['table' => 'activity_logs', 'columns' => ['school_id', 'log_name'], 'name' => 'activity_logs_school_log_idx'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $ix) {
            if (! Schema::hasTable($ix['table'])) {
                continue;
            }
            Schema::table($ix['table'], function (Blueprint $table) use ($ix): void {
                $table->index($ix['columns'], $ix['name']);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $ix) {
            if (! Schema::hasTable($ix['table'])) {
                continue;
            }
            Schema::table($ix['table'], function (Blueprint $table) use ($ix): void {
                $table->dropIndex($ix['name']);
            });
        }
    }
};
