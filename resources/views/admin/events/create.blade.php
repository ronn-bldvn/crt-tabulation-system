@extends('layouts.app')
@section('title', 'New Event')
@section('content')
<div class="mx-auto max-w-xl">
    <h1 class="text-2xl font-bold">New Event</h1>
    <form method="POST" action="{{ route('admin.events.store') }}" class="mt-6 space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700">Event name</label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Mutya ng Cabanatuan 2026"
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Description</label>
            <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('description') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Event date</label>
            <input type="date" name="event_date" value="{{ old('event_date') }}"
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Ranking method</label>
            <select name="ranking_method" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="points">Weighted average score (points system)</option>
                <option value="rank">Average of ranks (classic pageant method)</option>
            </select>
            <p class="mt-1 text-xs text-slate-500">You can change this later. "Points" ranks by overall weighted score; "Average of ranks" ranks by each judge's per-segment placing.</p>
        </div>
        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-500">
            Create event
        </button>
    </form>
</div>
@endsection
