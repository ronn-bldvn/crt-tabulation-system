<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criterion;
use App\Models\Event;
use App\Models\Segment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CriterionController extends Controller
{
    public function store(Request $request, Event $event, Segment $segment): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_score' => ['required', 'numeric', 'min:1'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $segment->criteria()->create($data);

        return back()->with('status', 'Criterion added.');
    }

    public function update(Request $request, Event $event, Segment $segment, Criterion $criterion): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_score' => ['required', 'numeric', 'min:1'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $criterion->update($data);

        return back()->with('status', 'Criterion updated.');
    }

    public function destroy(Event $event, Segment $segment, Criterion $criterion): RedirectResponse
    {
        $criterion->delete();

        return back()->with('status', 'Criterion removed.');
    }
}
