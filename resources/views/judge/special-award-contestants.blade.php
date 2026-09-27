@extends('layouts.app')

@section('title', $award->name . ' — ' . $event->name)

@section('content')

    <div class="max-w-6xl mx-auto" data-candidate-filter-group>

        <div class="mb-6">

            <a href="{{ route('judge.special-awards', $event->id) }}" class="text-sm text-violet-600 hover:underline">
                &larr; Back to Special Awards
            </a>

            <div class="mt-4">

                <p class="text-sm font-medium text-violet-600">
                    {{ $event->name }}
                </p>

                <h1 class="text-3xl font-bold text-slate-900">
                    {{ $award->name }}
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Select a contestant to enter your scores.
                </p>

            </div>

        </div>

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-200">

                <h2 class="text-lg font-semibold text-slate-900">
                    Contestants
                </h2>

                <div class="mt-3 inline-flex rounded-lg bg-slate-100 p-1" role="group"
                    aria-label="Filter contestants by gender">
                    <button type="button" data-candidate-filter="all" aria-pressed="true"
                        class="rounded-md bg-slate-900 px-3 py-1.5 text-xs font-medium text-white shadow-sm transition">
                        All
                    </button>
                    <button type="button" data-candidate-filter="female" aria-pressed="false"
                        class="rounded-md px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-white">
                        Female
                    </button>
                    <button type="button" data-candidate-filter="male" aria-pressed="false"
                        class="rounded-md px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-white">
                        Male
                    </button>
                </div>

            </div>

        </div>


        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">

            @forelse ($contestants as $contestant)

                    @php
                        $status = $scoreStatus[$contestant->id] ?? [
                            'complete' => false,
                            'actual' => 0,
                            'expected' => $award->criteria->count(),
                        ];

                        $percentage = $status['expected'] > 0
                            ? ($status['actual'] / $status['expected']) * 100
                            : 0;
                    @endphp

                    <a href="{{ route('judge.special-award.score', [
                    'event' => $event->id,
                    'award' => $award->id,
                    'contestant' => $contestant->id,
                ]) }}" data-candidate-gender="{{ strtolower($contestant->candidateGender() ?? 'other') }}" class="group bg-white rounded-2xl border border-slate-200
                                           overflow-hidden hover:border-violet-300 hover:shadow-lg
                                           transition duration-200">

                        {{-- Contestant Image --}}
                        <div class="relative aspect-[4/5] bg-slate-100 overflow-hidden">

                            @if ($contestant->photo_path)

                                <img src="{{ Storage::url($contestant->photo_path) }}" alt="{{ $contestant->name }}" class="w-full h-full object-cover
                                                               group-hover:scale-105 transition duration-300">

                            @else

                                <div class="w-full h-full flex items-center justify-center text-slate-400">
                                    <div class="text-center">

                                        <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M5.121 17.804A9 9 0 1118.88 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z" />

                                        </svg>

                                        <p class="text-xs">
                                            No photo
                                        </p>

                                    </div>
                                </div>

                            @endif

                            {{-- Contestant Number --}}
                            <div class="absolute top-3 left-3">

                                <span class="inline-flex items-center justify-center
                                                         min-w-10 h-10 px-3 rounded-full
                                                         bg-white/95 backdrop-blur-sm
                                                         text-slate-800 font-bold shadow-sm">

                                    #{{ $contestant->number }}

                                </span>

                            </div>

                            {{-- Scoring Status --}}
                            <div class="absolute top-3 right-3">

                                @if ($status['complete'])

                                    <span class="inline-flex items-center gap-1
                                                                     rounded-full bg-emerald-500
                                                                     text-white text-xs font-medium
                                                                     px-3 py-1.5 shadow-sm">

                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />

                                        </svg>

                                        Complete

                                    </span>

                                @else

                                    <span class="inline-flex items-center
                                                                     rounded-full bg-white/95
                                                                     backdrop-blur-sm
                                                                     text-slate-600 text-xs font-medium
                                                                     px-3 py-1.5 shadow-sm">

                                        {{ $status['actual'] }}/{{ $status['expected'] }}

                                    </span>

                                @endif

                            </div>

                        </div>

                        {{-- Contestant Information --}}
                        <div class="p-4">

                            <p class="text-lg font-semibold text-slate-800
                                                  group-hover:text-violet-600 transition">

                                {{ $contestant->name }}

                            </p>

                            @if ($contestant->candidateGender())

                                <p
                                    class="mt-1 text-xs font-medium {{ $contestant->candidateGender() === 'Female' ? 'text-rose-700' : 'text-sky-700' }}">
                                    {{ $contestant->candidateGender() }} candidate
                                </p>

                            @endif

                            {{-- Representing --}}
                            @if ($contestant->representing)

                                <p class="mt-1 text-sm text-slate-500">

                                    <span class="font-medium text-slate-600">
                                        Representing:
                                    </span>

                                    {{ $contestant->representing }}

                                </p>

                            @else

                                <p class="mt-1 text-sm text-slate-400 italic">
                                    No representation specified
                                </p>

                            @endif

                            {{-- Score Progress --}}
                            <div class="mt-4">

                                <div class="flex items-center justify-between
                                                        text-xs mb-1.5">

                                    <span class="text-slate-500">
                                        Scoring progress
                                    </span>

                                    <span class="font-medium text-slate-700">

                                        {{ $status['actual'] }}/{{ $status['expected'] }}

                                    </span>

                                </div>

                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">

                                    <div class="h-full rounded-full transition-all
                                                    {{ $status['complete']
                    ? 'bg-emerald-500'
                    : 'bg-violet-500' }}" style="width: {{ $percentage }}%">

                                    </div>

                                </div>

                            </div>

                            {{-- Score Button --}}
                            <div class="mt-4 flex items-center justify-between">

                                <span class="text-xs font-medium
                                                {{ $status['complete']
                    ? 'text-emerald-600'
                    : 'text-violet-600' }}">

                                    {{ $status['complete']
                    ? 'Scoring completed'
                    : 'Click to score' }}

                                </span>

                                <svg class="w-5 h-5 text-slate-400
                                                        group-hover:text-violet-600
                                                        group-hover:translate-x-1
                                                        transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />

                                </svg>

                            </div>

                        </div>

                    </a>

            @empty

                <div class="col-span-full text-center py-12">

                    <div class="text-slate-400 mb-3">

                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />

                        </svg>

                    </div>

                    <p class="text-slate-500">
                        No active contestants have been added to this event.
                    </p>

                </div>

            @endforelse

        </div>

    </div>

@endsection