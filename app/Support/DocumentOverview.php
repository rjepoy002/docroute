<?php

namespace App\Support;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DocumentOverview
{
    /** Completed = closed documents created by this user OR closed by this user, counted once. */
    public function forUser(User $user): array
    {
        $row = DB::table('documents as d')->join('document_routes as r', 'r.id', '=', 'd.current_route_id')
            ->where(fn ($query) => $query->where('d.created_by', $user->id)->orWhere('r.to_user_id', $user->id))
            ->selectRaw("COALESCE(SUM(CASE WHEN r.to_user_id = ? AND r.status IN ('pending', 'received') THEN 1 ELSE 0 END), 0) AS incoming", [$user->id])
            ->selectRaw('COALESCE(SUM(CASE WHEN d.created_by = ? THEN 1 ELSE 0 END), 0) AS outgoing', [$user->id])
            ->selectRaw("COALESCE(SUM(CASE WHEN r.to_user_id = ? AND r.status = 'pending' THEN 1 ELSE 0 END), 0) AS pending", [$user->id])
            ->selectRaw("COALESCE(SUM(CASE WHEN r.to_user_id = ? AND r.status IN ('pending', 'received') AND r.seen_at IS NULL THEN 1 ELSE 0 END), 0) AS unseen", [$user->id])
            ->selectRaw("COALESCE(SUM(CASE WHEN r.status = 'closed' AND (r.to_user_id = ? OR d.created_by = ?) THEN 1 ELSE 0 END), 0) AS completed", [$user->id, $user->id])->first();

        return array_map('intval', (array) $row);
    }

    public function recentForUser(User $user)
    {
        return Document::where(fn ($query) => $query->where('created_by', $user->id)
            ->orWhereHas('routes', fn ($route) => $route->where('from_user_id', $user->id)->orWhere('to_user_id', $user->id)))
            ->with(['currentRoute.recipient', 'currentRoute.toDepartment'])->latest('updated_at')->orderByDesc('id')->limit(5)->get();
    }
}
