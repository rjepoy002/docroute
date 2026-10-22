<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->department?->is_active;
    }

    public function view(User $user, Document $document): bool
    {
        return $user->is_active && ($user->isAdmin() || $document->created_by === $user->id
            || $document->routes()->where(fn ($query) => $query->where('from_user_id', $user->id)->orWhere('to_user_id', $user->id))->exists());
    }

    public function receive(User $user, Document $document): bool
    {
        return $this->owns($user, $document, 'pending');
    }

    public function decline(User $user, Document $document): bool
    {
        return $this->owns($user, $document, 'pending');
    }

    public function forward(User $user, Document $document): bool
    {
        return $this->owns($user, $document, 'received');
    }

    public function close(User $user, Document $document): bool
    {
        return $this->owns($user, $document, 'received');
    }

    private function owns(User $user, Document $document, string $status): bool
    {
        return $user->is_active && $document->currentRoute?->to_user_id === $user->id
            && $document->currentRoute->status === $status;
    }
}
