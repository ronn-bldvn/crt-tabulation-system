@extends('layouts.app')
@section('title', 'Judges')
@section('content')
<h1 class="text-2xl font-bold">Judge Accounts</h1>
<p class="mt-1 text-sm text-slate-500">Create login accounts for judges here, then assign them to specific events from the event page.</p>

<div class="mt-6 grid gap-8 lg:grid-cols-3">
    <div class="lg:col-span-2 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold">All judges</h2>
        <div class="mt-4 space-y-2">
            @forelse($judges as $judge)
            <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <div>
                    <span class="font-medium">{{ $judge->name }}</span>
                    <span class="text-slate-400"> — {{ $judge->email }}</span>
                    <span class="ml-2 text-xs text-slate-400">{{ $judge->scores_count }} scores submitted</span>
                </div>
                <form method="POST" action="{{ route('admin.judges.destroy', $judge) }}" onsubmit="return confirm('Delete this judge account? All scores this judge has submitted will be permanently deleted too.')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-rose-500 hover:underline">delete</button>
                </form>
            </div>
            @empty
            <p class="text-sm text-slate-500">No judge accounts yet.</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold">Add a judge</h2>
        <form method="POST" action="{{ route('admin.judges.store') }}" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-700">Full name</label>
                <input type="text" name="name" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700">Email</label>
                <input type="email" name="email" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                Create account
            </button>
            <p class="text-xs text-slate-500">A temporary password is generated and shown once after creation — share it with the judge for their first login, on their own tablet/device.</p>
        </form>
    </div>
</div>
@endsection
