<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class JudgeController extends Controller
{
    public function index()
    {
        $judges = User::where('role', 'judge')->withCount('scores')->latest()->get();

        return view('admin.judges.index', compact('judges'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        $tempPassword = Str::password(10);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($tempPassword),
            'role' => 'judge',
        ]);

        return back()->with('status', "Judge account created for {$data['name']}. Temporary password: {$tempPassword} (share this with the judge; they should change it after first login).");
    }

    public function destroy(User $judge): RedirectResponse
    {
        abort_unless($judge->role === 'judge', 404);
        $judge->delete();

        return back()->with('status', 'Judge account removed.');
    }

    public function assign(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'judge_id' => ['required', 'exists:users,id'],
        ]);

        $event->judges()->syncWithoutDetaching([
            $data['judge_id'] => ['access_code' => (string) random_int(100000, 999999)],
        ]);

        return back()->with('status', 'Judge assigned to event.');
    }

    public function unassign(Event $event, User $judge): RedirectResponse
    {
        $event->judges()->detach($judge->id);

        return back()->with('status', 'Judge removed from event.');
    }
}
