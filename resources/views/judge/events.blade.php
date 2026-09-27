@extends('layouts.app')
@section('title', 'My Events')
@section('content')
<h1 class="text-2xl font-bold">My Events</h1>
<p class="mt-1 text-sm text-slate-500">Select an event to begin scoring.</p>

<div class="mt-6 grid gap-4 sm:grid-cols-2">
    @forelse($events as $event)
    <a href="{{ route('judge.segments', $event) }}" class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300 hover:shadow-md">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold">{{ $event->name }}</h2>
            @if($event->scoring_locked)
                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Locked</span>
            @endif
        </div>
        <p class="mt-1 text-sm text-slate-500">{{ $event->contestants_count }} contestants</p>
    </a>
    @empty
    <p class="text-sm text-slate-500">You haven't been assigned to any events yet. Contact the administrator.</p>
    @endforelse
</div>
@endsection
