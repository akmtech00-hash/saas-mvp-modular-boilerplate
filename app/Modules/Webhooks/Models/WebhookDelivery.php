<?php

declare(strict_types=1);

namespace App\Modules\Webhooks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    use HasFactory;

    protected $table = 'webhook_deliveries';

    protected $fillable = [
        'webhook_endpoint_id',
        'event_type',
        'payload',
        'response_status',
        'response_body',
        'duration_ms',
        'attempt',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
        'response_status' => 'integer',
        'duration_ms' => 'float',
        'attempt' => 'integer',
    ];

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id');
    }
}
