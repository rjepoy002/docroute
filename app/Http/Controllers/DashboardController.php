<?php

namespace App\Http\Controllers;

use App\Support\DocumentOverview;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DocumentOverview $overview): View
    {
        if ($request->user()->isAdmin()) {
            return app(Admin\DashboardController::class)->index($request, $overview);
        }

        return view('dashboard', [
            'documentCounts' => $overview->forUser($request->user()),
            'recentDocuments' => $overview->recentForUser($request->user()),
        ]);
    }
}
