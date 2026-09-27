@extends('layouts.app')

@section('title', $event->name . ' — Special Awards')

@section('content')

<div class="max-w-3xl mx-auto">

    {{-- Back --}}
    <a href="{{ route('judge.segments', $event) }}"
       class="text-sm text-violet-600 hover:underline">
        ← Back to Segments
    </a>

    {{-- Header --}}
    <div class="mt-2 mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">
            Special Awards
        </h1>

        <p class="text-slate-500 mt-1">
            Select a special award to score.
        </p>
    </div>

    {{-- Locked Notice --}}
    @if ($event->scoring_locked)

        <div class="mb-5 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3">
            Scoring for this event is currently locked by the administrator.
        </div>

    @endif

    {{-- Special Awards --}}
    <div class="space-y-3">

        @forelse ($event->specialAwards as $award)

            <a
                href="{{ route('judge.special-award.contestants', [
                    'event' => $event,
                    'award' => $award,
                ]) }}"
                class="group block rounded-xl border border-slate-200 bg-white px-5 py-5 hover:border-violet-300 hover:shadow-sm transition"
            >

                <div class="flex items-center justify-between gap-4">

                    {{-- Left --}}
                    <div class="flex items-center gap-4">

                        {{-- <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-700 text-xl">
                            
                        </div> --}}

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-100 p-2">
                            <img
                                src="{{ asset('uploads/favicon.png') }}"
                                alt="School Logo"
                                class="h-9 w-9 object-contain"
                            >
                        </div>

                        <div>

                            <p class="font-semibold text-slate-800">
                                {{ $award->name }}
                            </p>

                            @if ($award->description)

                                <p class="text-sm text-slate-500 mt-1">
                                    {{ $award->description }}
                                </p>

                            @endif

                            <div class="flex flex-wrap items-center gap-2 mt-2">

                                <span class="text-xs text-slate-400">
                                    {{ $award->criteria->count() }}
                                    {{ $award->criteria->count() === 1 ? 'criterion' : 'criteria' }}
                                </span>

                                @if ($award->allow_multiple_photos)

                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                        📷 Multiple Photos
                                    </span>

                                @endif

                                @if ($award->award_type)

                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                        {{ ucfirst($award->award_type) }}
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                    {{-- Arrow --}}
                    <div class="flex items-center gap-3 shrink-0">

                        <span class="hidden sm:inline-flex rounded-full bg-violet-50 border border-violet-200 px-3 py-1 text-xs font-semibold text-violet-700">
                            Score
                        </span>

                        <span class="text-xl text-violet-600 group-hover:translate-x-1 transition">
                            →
                        </span>

                    </div>

                </div>

                {{-- Winner --}}
                {{-- @if ($award->winner)

                    <div class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3">

                        <div class="flex items-center gap-3">

                            <div class="text-2xl">
                                🏆
                            </div>

                            <div>

                                <p class="text-[11px] uppercase tracking-wide font-semibold text-emerald-600">
                                    Current Winner
                                </p>

                                <p class="text-sm font-bold text-emerald-800">
                                    {{ $award->winner->name }}
                                </p>

                            </div>

                        </div>

                    </div>

                @else --}}

                    {{-- <div class="mt-4 rounded-lg bg-slate-50 border border-slate-200 px-4 py-3">

                        <p class="text-xs text-slate-500">
                            🕐 Winner will be determined automatically after scoring.
                        </p>

                    </div> --}}

                {{-- @endif --}}

            </a>

        @empty

            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center">

                <div class="text-4xl mb-3">
                    
                </div>

                <h2 class="font-semibold text-slate-800">
                    No Special Awards
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    No special awards have been created for this event yet.
                </p>

            </div>

        @endforelse

    </div>

</div>

@endsection