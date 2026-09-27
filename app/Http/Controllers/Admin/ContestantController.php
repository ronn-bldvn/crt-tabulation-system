<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contestant;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContestantController extends Controller
{
    public function store(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'number' => ['required', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:255'],
            'representing' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:20480'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('contestants', 'public');
        }
        unset($data['photo']);

        $event->contestants()->create($data);

        return back()->with('status', 'Contestant added.');
    }

    public function update(Request $request, Event $event, Contestant $contestant): RedirectResponse
    {
        $data = $request->validate([
            'number' => ['required', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:255'],
            'representing' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('contestants', 'public');
        }
        unset($data['photo']);
        $data['is_active'] = $request->boolean('is_active');

        $contestant->update($data);

        return back()->with('status', 'Contestant updated.');
    }

    public function destroy(Event $event, Contestant $contestant): RedirectResponse
    {
        $contestant->delete();

        return back()->with('status', 'Contestant removed.');
    }
}
