<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = ['document_number_id', 'tracking_number', 'title', 'description', 'created_by', 'current_route_id'];

    protected function casts(): array
    {
        return ['document_number_id' => 'integer', 'created_by' => 'integer', 'current_route_id' => 'integer'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currentRoute(): BelongsTo
    {
        return $this->belongsTo(DocumentRoute::class, 'current_route_id');
    }

    public function routes(): HasMany
    {
        return $this->hasMany(DocumentRoute::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(DocumentEvent::class)->orderBy('created_at')->orderBy('id');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->when(! $user->isAdmin(), fn (Builder $query) => $query->where(fn (Builder $query) => $query
            ->where('created_by', $user->id)
            ->orWhereHas('routes', fn (Builder $route) => $route->where('from_user_id', $user->id)->orWhere('to_user_id', $user->id))));
    }

    public function scopeAssignedTo(Builder $query, User $user): Builder
    {
        return $query->whereHas('currentRoute', fn (Builder $route) => $route->where('to_user_id', $user->id)->whereIn('status', ['pending', 'received']));
    }
}
