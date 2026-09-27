<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Segment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SegmentController extends Controller
{
    public function store(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $event->segments()->create($data);

        return back()->with('status', 'Segment added.');
    }

    public function update(Request $request, Event $event, Segment $segment): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $segment->update($data);

        return back()->with('status', 'Segment updated.');
    }

    public function destroy(Event $event, Segment $segment): RedirectResponse
    {
        $segment->delete();

        return back()->with('status', 'Segment removed.');
    }

    public function toggleLock(Event $event, Segment $segment): RedirectResponse
    {
        $segment->update(['is_locked' => ! $segment->is_locked]);

        return back()->with('status', $segment->is_locked ? 'Segment locked.' : 'Segment unlocked.');
    }
}
