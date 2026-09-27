@extends('layouts.app')
@section('title', 'Edit Event')
@section('content')
<div class="mx-auto max-w-xl">
    <h1 class="text-2xl font-bold">Edit Event</h1>
    <form method="POST" action="{{ route('admin.events.update', $event) }}" class="mt-6 space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-slate-700">Event name</label>
            <input type="text" name="name" value="{{ old('name', $event->name) }}" required
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Description</label>
            <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('description', $event->description) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Event date</label>
            <input type="date" name="event_date" value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}"
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                @foreach(['draft', 'active', 'closed'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $event->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Ranking method</label>
            <select name="ranking_method" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="points" @selected(old('ranking_method', $event->ranking_method) === 'points')>Weighted average score (points system)</option>
                <option value="rank" @selected(old('ranking_method', $event->ranking_method) === 'rank')>Average of ranks (classic pageant method)</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-500">
                Save changes
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Delete this event permanently? This removes all segments, criteria, contestants and scores.')" class="mt-4">
        @csrf @method('DELETE')
        <button class="w-full rounded-lg border border-rose-300 bg-rose-50 px-4 py-2.5 font-semibold text-rose-700 hover:bg-rose-100">
            Delete event
        </button>
    </form>
</div>
@endsection
