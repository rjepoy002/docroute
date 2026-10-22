<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class DocumentEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['document_id', 'document_route_id', 'actor_id', 'event_type', 'previous_value', 'new_value', 'context', 'remarks', 'created_at'];

    protected function casts(): array
    {
        return ['previous_value' => 'array', 'new_value' => 'array', 'context' => 'array', 'created_at' => 'datetime', 'document_id' => 'integer', 'document_route_id' => 'integer', 'actor_id' => 'integer'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Document events are append-only.'));
        static::deleting(fn () => throw new LogicException('Document events are append-only.'));
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(DocumentRoute::class, 'document_route_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
