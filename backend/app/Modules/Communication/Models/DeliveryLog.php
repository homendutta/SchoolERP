<?php

declare(strict_types=1);

namespace App\Modules\Communication\Models;

use Illuminate\Database\Eloquent\Model;

/** Immutable delivery-history entry for a message. */
class DeliveryLog extends Model
{
    public $timestamps = false;

    protected $table = 'communication_delivery_logs';

    protected $fillable = ['message_id', 'event', 'detail', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
