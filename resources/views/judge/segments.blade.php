@extends('layouts.app')

@section('title', $event->name . ' — Segments')

@section('content')

<div class="max-w-3xl mx-auto">

    <a href="{{ route('judge.events') }}"
       class="text-sm text-violet-600 hover:underline">
        ← My events
    </a>

    <h1 class="text-2xl font-semibold mt-2 mb-1">
        {{ $event->name }}
    </h1>

    <p class="text-slate-500 mb-6">
        Select a segment or special award to score.
    </p>

    @if ($event->scoring_locked)

        <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3">
            Scoring for this event is currently locked by the administrator.
        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- REGULAR SEGMENTS --}}
    {{-- ========================================================= --}}

    <div class="space-y-3">

        @forelse ($event->segments as $segment)

            @php
                $status = $segmentStatus[$segment->id] ?? [
                    'complete' => false,
                    'actual' => 0,
                    'expected' => 0,
                ];
            @endphp

            <a
                href="{{ route('judge.contestants', [
                    'event' => $event,
                    'segment' => $segment
                ]) }}"
                class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-5 py-4 hover:border-violet-300 hover:shadow-sm transition"
            >

                <div>

                    <p class="font-medium text-slate-800">
                        {{ $segment->name }}
                    </p>

                    <p class="text-sm text-slate-500">
                        {{ $segment->criteria->count() }}
                        criteria
                        ·
                        weight
                        {{ rtrim(rtrim(number_format($segment->weight, 2), '0'), '.') }}%
                    </p>

                </div>

                <div class="text-right">

                    @if ($status['complete'])

                        <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium px-2.5 py-1">
                            Complete
                        </span>

                    @else

                        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 text-xs font-medium px-2.5 py-1">
                            {{ $status['actual'] }}/{{ $status['expected'] }} scored
                        </span>

                    @endif

                    @if ($segment->is_locked)

                        <p class="text-xs text-amber-600 mt-1">
                            Locked
                        </p>

                    @endif

                </div>

            </a>

        @empty

            <p class="text-slate-500">
                No segments have been set up for this event yet.
            </p>

        @endforelse

    </div>


    {{-- ========================================================= --}}
    {{-- SPECIAL AWARDS --}}
    {{-- ========================================================= --}}

    @if ($event->specialAwards->count())

        <div class="mt-8">

            <div class="mb-3">

                <h2 class="text-lg font-semibold text-slate-900">
                    Special Awards
                </h2>

                <p class="text-sm text-slate-500">
                    Select a special award to score.
                </p>

            </div>

            <a
                href="{{ route('judge.special-awards', [
                    'event' => $event
                ]) }}"
                class="group flex items-center justify-between rounded-xl border border-violet-200 bg-violet-50 px-5 py-5 hover:border-violet-400 hover:bg-violet-100 hover:shadow-sm transition"
            >

                <div class="flex items-center gap-4">

                    {{-- Icon --}}
                    {{-- <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xl text-white shadow-sm">
                        🏆
                    </div> --}}
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-100 p-2">
                            <img
                                src="{{ asset('uploads/favicon.png') }}"
                                alt="School Logo"
                                class="h-9 w-9 object-contain"
                            >
                        </div>

                    <div>

                        <p class="font-semibold text-slate-900">
                            Special Awards
                        </p>

                        <p class="text-sm text-slate-600">
                            {{ $event->specialAwards->count() }}
                            {{ $event->specialAwards->count() === 1 ? 'award' : 'awards' }}
                            available for judging
                        </p>

                    </div>

                </div>

                <div class="flex items-center gap-3">

                    <span class="hidden sm:inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-violet-700 border border-violet-200">
                        View Awards
                    </span>

                    <span class="text-xl text-violet-600 group-hover:translate-x-1 transition">
                        →
                    </span>

                </div>

            </a>

        </div>

    @endif

</div>

@endsection