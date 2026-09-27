@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">Events</h1>
    <a href="{{ route('admin.events.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
        + New Event
    </a>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($events as $event)
    <a href="{{ route('admin.events.show', $event) }}" class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
        <div class="flex items-start justify-between">
            <h2 class="font-semibold text-slate-900">{{ $event->name }}</h2>
            <span @class([
                'rounded-full px-2 py-0.5 text-xs font-medium',
                'bg-slate-100 text-slate-600' => $event->status === 'draft',
                'bg-emerald-100 text-emerald-700' => $event->status === 'active',
                'bg-slate-800 text-white' => $event->status === 'closed',
            ])>{{ ucfirst($event->status) }}</span>
        </div>
        @if($event->event_date)
        <p class="mt-1 text-sm text-slate-500">{{ $event->event_date->format('M j, Y') }}</p>
        @endif
        <div class="mt-4 flex gap-4 text-sm text-slate-600">
            <span>{{ $event->segments_count }} segments</span>
            <span>{{ $event->contestants_count }} contestants</span>
            <span>{{ $event->judges_count }} judges</span>
        </div>
    </a>
    @empty
    <p class="col-span-full text-sm text-slate-500">No events yet. Create your first one to get started.</p>
    @endforelse
</div>
@endsection
