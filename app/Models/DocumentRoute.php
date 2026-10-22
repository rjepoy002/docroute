<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRoute extends Model
{
    protected $fillable = ['document_id', 'from_user_id', 'to_user_id', 'from_department_id', 'to_department_id', 'status', 'sent_at', 'seen_at', 'received_at', 'declined_at', 'closed_at', 'remarks'];

    protected function casts(): array
    {
        return array_fill_keys(['sent_at', 'seen_at', 'received_at', 'declined_at', 'closed_at'], 'datetime')
            + array_fill_keys(['document_id', 'from_user_id', 'to_user_id', 'from_department_id', 'to_department_id'], 'integer');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }
}
