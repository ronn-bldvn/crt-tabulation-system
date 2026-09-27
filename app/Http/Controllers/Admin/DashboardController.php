<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $events = Event::withCount(['contestants', 'segments', 'judges'])
            ->latest()
            ->get();

        return view('admin.dashboard', compact('events'));
    }
}
