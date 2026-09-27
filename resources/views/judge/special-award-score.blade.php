@extends('layouts.app')

@section('title', $award->name . ' — ' . $contestant->name)

@section('content')

<div class="max-w-4xl mx-auto">

    {{-- Back to contestants --}}
    <div class="mb-4">
        <a
            href="{{ route('judge.special-award.contestants', [
                'event' => $event->id,
                'award' => $award->id,
            ]) }}"
            class="text-sm text-violet-600 hover:underline"
        >
            &larr; Back to Contestants
        </a>
    </div>

    {{-- Award Header --}}
    <div class="mb-6">
        <p class="text-sm font-medium text-violet-600">
            Special Award
        </p>

        <h1 class="text-2xl font-bold text-slate-900">
            {{ $award->name }}
        </h1>

        <p class="text-lg font-semibold text-slate-800 mt-2">
            {{ $contestant->name }}
        </p>

        <p class="text-sm text-slate-500 mt-1">
            #{{ $contestant->number }}

            @if($contestant->representing)
                &middot; {{ $contestant->representing }}
            @endif
        </p>

        @if($award->description)
            <p class="text-sm text-slate-500 mt-2">
                {{ $award->description }}
            </p>
        @endif
    </div>

    {{-- Locked Notice --}}
    @if($event->scoring_locked)

        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            🔒 Scoring is locked for this event.
            You can review the scores below, but cannot make changes.
        </div>

    @endif

    {{-- Photos --}}
    @if($award->photos->count())

        <div class="mb-6 rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">
                    Photos
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Photos submitted for {{ $contestant->name }}.
                </p>
            </div>

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                @foreach($award->photos->where('contestant_id', $contestant->id) as $photo)

                    <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50">
                        <img
                            src="{{ Storage::url($photo->photo_path) }}"
                            alt="{{ $contestant->name }}"
                            class="w-full h-64 object-cover"
                        >
                    </div>

                @endforeach

            </div>

        </div>

    @endif

    {{-- Score Form --}}
    <form
        method="POST"
        action="{{ route('judge.special-award.score.store', [
            'event' => $event->id,
            'award' => $award->id,
            'contestant' => $contestant->id,
        ]) }}"
        id="specialAwardScoreForm"
    >

        @csrf

        {{-- Criteria Card --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-5 border-b border-slate-200">

                <h2 class="text-lg font-semibold text-slate-900">
                    Scoring Criteria
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Enter your score for each criterion.
                </p>

            </div>

            {{-- Criteria --}}
            <div class="divide-y divide-slate-100">

                @foreach($award->criteria as $criterion)

                    @php
                        $score = $existing->get($criterion->id);
                    @endphp

                    <div class="px-6 py-5">

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                            {{-- Criterion Information --}}
                            <div>

                                <h3 class="font-medium text-slate-800">
                                    {{ $criterion->name }}
                                </h3>

                                <p class="text-xs text-slate-400 mt-1">

                                    Maximum:
                                    {{ rtrim(rtrim(number_format($criterion->max_score, 2), '0'), '.') }}

                                    &middot;

                                    Weight:
                                    {{ rtrim(rtrim(number_format($criterion->weight, 2), '0'), '.') }}%

                                </p>

                            </div>

                            {{-- Score Input --}}
                            <div class="flex items-center gap-2">

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="{{ $criterion->max_score }}"
                                    name="scores[{{ $criterion->id }}]"
                                    value="{{ old(
                                        'scores.' . $criterion->id,
                                        $score->score ?? ''
                                    ) }}"
                                    {{ $event->scoring_locked ? 'disabled' : 'required' }}
                                    class="w-28 rounded-lg border border-slate-300 px-3 py-2 text-right text-lg font-semibold focus:border-violet-400 focus:ring-violet-400"
                                    placeholder="0"
                                >

                                <span class="text-sm text-slate-400">
                                    /
                                    {{ rtrim(rtrim(number_format($criterion->max_score, 2), '0'), '.') }}
                                </span>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

            {{-- Remarks --}}
            <div class="px-6 py-5 border-t border-slate-200">

                <label
                    for="remarks"
                    class="block text-sm font-medium text-slate-700 mb-2"
                >
                    Remarks
                </label>

                <textarea
                    name="remarks"
                    id="remarks"
                    rows="3"
                    {{ $event->scoring_locked ? 'disabled' : '' }}
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-violet-400 focus:ring-violet-400"
                    placeholder="Optional remarks..."
                >{{ old('remarks', optional($existing->first())->remarks) }}</textarea>

            </div>

        </div>

        {{-- Navigation --}}
        <div class="mt-6 flex flex-col sm:flex-row gap-3">

            {{-- Back --}}
            <a
                href="{{ route('judge.special-award.contestants', [
                    'event' => $event->id,
                    'award' => $award->id,
                ]) }}"
                class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                &larr;
                Contestants
            </a>

            {{-- Save --}}
            @unless($event->scoring_locked)

                <button
                    type="submit"
                    id="saveScoreButton"
                    class="flex-1 rounded-lg bg-violet-600 px-5 py-3 text-sm font-semibold text-white hover:bg-violet-500"
                >
                    Save Score
                </button>

            @endunless

        </div>

    </form>

</div>


{{-- ========================================================= --}}
{{-- JAVASCRIPT --}}
{{-- ========================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('specialAwardScoreForm');
    const button = document.getElementById('saveScoreButton');

    if (!form || !button) {
        return;
    }

    form.addEventListener('submit', async function (event) {

        event.preventDefault();

        /*
        |--------------------------------------------------------------------------
        | Browser validation
        |--------------------------------------------------------------------------
        */

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Disable button
        |--------------------------------------------------------------------------
        */

        button.disabled = true;
        button.innerText = 'Saving...';

        /*
        |--------------------------------------------------------------------------
        | Prepare form data
        |--------------------------------------------------------------------------
        */

        const formData = new FormData(form);

        try {

            const response = await fetch(form.action, {

                method: 'POST',

                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },

                body: formData

            });

            /*
            |--------------------------------------------------------------------------
            | Get response
            |--------------------------------------------------------------------------
            */

            const data = await response.json();

            console.log('Special Award Save Response:', data);

            /*
            |--------------------------------------------------------------------------
            | Handle validation/server errors
            |--------------------------------------------------------------------------
            */

            if (!response.ok) {

                let message = data.message || 'Unable to save score.';

                if (data.errors) {

                    const errors = Object.values(data.errors)
                        .flat()
                        .join('\n');

                    if (errors) {
                        message = errors;
                    }
                }

                throw new Error(message);
            }

            /*
            |--------------------------------------------------------------------------
            | Successful save
            |--------------------------------------------------------------------------
            */

            if (data.success === true) {

                alert(
                    data.message ||
                    'Special Award score saved successfully.'
                );

                if (data.redirect) {

                    window.location.href = data.redirect;

                } else {

                    window.location.href =
                        "{{ route('judge.special-award.contestants', [
                            'event' => $event->id,
                            'award' => $award->id,
                        ]) }}";

                }

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Unexpected response
            |--------------------------------------------------------------------------
            */

            throw new Error(
                data.message ||
                'The score could not be saved.'
            );

        } catch (error) {

            console.error(
                'Special Award score save error:',
                error
            );

            alert(error.message);

            button.disabled = false;
            button.innerText = 'Save Score';

        }

    });

});

</script>

@endsection