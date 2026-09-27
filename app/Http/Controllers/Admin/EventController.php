<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_date' => ['nullable', 'date'],
            'ranking_method' => ['required', 'in:points,rank'],
        ]);

        $event = Event::create($data);

        return redirect()->route('admin.events.show', $event)
            ->with('status', 'Event created. Now set up segments and criteria.');
    }

    public function show(Event $event)
{
    $event->load([
        'segments.criteria',
        'contestants' => fn($q) => $q->orderedByNumber(),
        'judges',
        'specialAwards.criteria',
        'specialAwards.winner',
        'specialAwards.photos.contestant',
    ]);

    return view(
        'admin.events.show',
        compact('event')
    );
}
    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_date' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,active,closed'],
            'ranking_method' => ['required', 'in:points,rank'],
        ]);

        $event->update($data);

        return redirect()->route('admin.events.show', $event)->with('status', 'Event updated.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.dashboard')->with('status', 'Event deleted.');
    }

    public function toggleLock(Event $event): RedirectResponse
    {
        $event->update(['scoring_locked' => ! $event->scoring_locked]);

        return back()->with('status', $event->scoring_locked ? 'Scoring locked for all judges.' : 'Scoring re-opened.');
    }
}
