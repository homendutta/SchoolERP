<?php

declare(strict_types=1);

namespace App\Modules\Documents\Models;

use App\Modules\Documents\Enums\VerificationResult;
use Illuminate\Database\Eloquent\Model;

/** A logged verification attempt against a generated document. */
class Verification extends Model
{
    protected $table = 'document_verifications';

    protected $fillable = ['school_id', 'document_id', 'method', 'result', 'identifier', 'verified_at'];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime', 'result' => VerificationResult::class];
    }
}
