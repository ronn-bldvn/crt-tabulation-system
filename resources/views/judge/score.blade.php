@extends('layouts.app')

@section('title', $segment->name . ' — ' . $event->name)

@section('content')

<div class="max-w-4xl mx-auto">

    {{-- Back to contestants --}}
    <div class="mb-4">
        <a
            href="{{ route('judge.contestants', [$event->id, $segment->id]) }}"
            class="text-sm text-violet-600 hover:underline"
        >
            &larr; Back to Contestants
        </a>
    </div>

    {{-- Contestant Header --}}
    <div class="mb-6">
        <p class="text-sm font-medium text-violet-600">
            {{ $segment->name }}
        </p>

        <h1 class="text-2xl font-bold text-slate-900">
            {{ $contestant->name }}
        </h1>

        <p class="text-sm text-slate-500 mt-1">
            #{{ $contestant->number }}

            @if($contestant->representing)
                &middot; {{ $contestant->representing }}
            @endif
        </p>
    </div>

    {{-- Locked Notice --}}
    @if($event->scoring_locked || $segment->is_locked)

        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            🔒 Scoring is locked for
            {{ $event->scoring_locked ? 'this event' : 'this segment' }}.

            You can review the scores below, but cannot make changes.
        </div>

    @endif

    {{-- Progress --}}
    @if($currentIndex !== false)

        <div class="mb-6">

            <div class="flex items-center justify-between text-sm mb-2">

                <span class="text-slate-500">
                    Contestant {{ $currentIndex + 1 }}
                    of {{ $contestants->count() }}
                </span>

                <span class="font-medium text-slate-700">
                    {{ $contestant->name }}
                </span>

            </div>

            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">

                <div
                    class="h-full bg-violet-600 rounded-full"
                    style="width: {{ $contestants->count() > 0 ? (($currentIndex + 1) / $contestants->count()) * 100 : 0 }}%"
                ></div>

            </div>

        </div>

    @endif

    {{-- Score Form --}}
    <form
        method="POST"
        action="{{ route('judge.score.store', [
            'event' => $event->id,
            'segment' => $segment->id,
            'contestant' => $contestant->id,
        ]) }}"
        id="scoreForm"
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
                    Enter the score for each criterion.
                </p>

            </div>

            {{-- Criteria --}}
            <div class="divide-y divide-slate-100">

                @foreach($segment->criteria as $criterion)

                    @php
                        $score = $existing->get($criterion->id);
                        $isSpecialAwardCriterion = $criterion->isSpecialAward();
                        $autoScore = null;
                        if ($isSpecialAwardCriterion) {
                            $autoScore = round($specialAwardCriterionScore * (float) $criterion->max_score, 2);
                        }
                    @endphp

                    <div class="px-6 py-5 {{ $isSpecialAwardCriterion ? 'bg-indigo-50/50' : '' }}">

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                            {{-- Criterion Information --}}
                            <div>

                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-medium text-slate-800">
                                        {{ $criterion->name }}
                                    </h3>

                                    @if($isSpecialAwardCriterion)
                                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-medium text-indigo-700">
                                            🏆 Auto-calculated
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs text-slate-400 mt-1">

                                    Maximum:
                                    {{ rtrim(rtrim(number_format($criterion->max_score, 2), '0'), '.') }}

                                    &middot;

                                    Weight:
                                    {{ rtrim(rtrim(number_format($criterion->weight, 2), '0'), '.') }}%

                                    @if($isSpecialAwardCriterion)
                                        &middot;
                                        <span class="text-indigo-600">
                                            Won {{ $wonSpecialAwards }} of {{ $totalSpecialAwards }} special award(s)
                                        </span>
                                    @endif

                                </p>

                            </div>

                            {{-- Score Input --}}
                            <div class="flex items-center gap-2">

                                @if($isSpecialAwardCriterion)

                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="{{ $criterion->max_score }}"
                                        value="{{ $autoScore }}"
                                        disabled
                                        class="w-28 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-right text-lg font-semibold text-indigo-800 cursor-not-allowed"
                                        placeholder="0"
                                        title="This score is auto-calculated from special award wins and cannot be edited."
                                    >

                                @else

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
                                        {{ $event->scoring_locked || $segment->is_locked ? 'disabled' : 'required' }}
                                        class="w-28 rounded-lg border border-slate-300 px-3 py-2 text-right text-lg font-semibold focus:border-violet-400 focus:ring-violet-400"
                                        placeholder="0"
                                    >

                                @endif

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
                    {{ $event->scoring_locked || $segment->is_locked ? 'disabled' : '' }}
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-violet-400 focus:ring-violet-400"
                    placeholder="Optional remarks..."
                >{{ old('remarks', optional($existing->first())->remarks) }}</textarea>

            </div>

        </div>

        {{-- Navigation --}}
        <div class="mt-6 flex flex-col sm:flex-row gap-3">

            {{-- Previous --}}
            @if($prev)

                <a
                    href="{{ route('judge.score', [
                        'event' => $event->id,
                        'segment' => $segment->id,
                        'contestant' => $prev->id,
                    ]) }}"
                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    &larr;
                    Previous
                </a>

            @else

                <a
                    href="{{ route('judge.contestants', [$event->id, $segment->id]) }}"
                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    &larr;
                    Contestants
                </a>

            @endif

            {{-- Save Score --}}
            @unless($event->scoring_locked || $segment->is_locked)

                <button
                    type="submit"
                    class="flex-1 rounded-lg bg-violet-600 px-5 py-3 text-sm font-semibold text-white hover:bg-violet-500"
                >
                    Save Score
                </button>

            @endunless

            {{-- Save & Next --}}
            @if($next && !($event->scoring_locked || $segment->is_locked))

                <button
                    type="button"
                    id="saveNextButton"
                    onclick="saveAndNext(this)"
                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800"
                >
                    Save & Next
                    &rarr;
                </button>

            @endif

        </div>

    </form>

</div>

<script>

async function saveAndNext(button) {

    const form = document.getElementById('scoreForm');

    // Validate required fields
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Disable button while saving
    button.disabled = true;
    button.innerText = 'Saving...';

    const formData = new FormData(form);

    try {

        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector(
                    'input[name="_token"]'
                ).value,

                'Accept': 'application/json'
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error('Unable to save scores.');
        }

        const data = await response.json();

        if (data.status === 'saved') {

            @if($next)

                window.location.href = "{{ route('judge.score', [
                    'event' => $event->id,
                    'segment' => $segment->id,
                    'contestant' => $next->id,
                ]) }}";

            @else

                window.location.href = "{{ route('judge.contestants', [
                    'event' => $event->id,
                    'segment' => $segment->id,
                ]) }}";

            @endif

        } else {

            throw new Error('The scores could not be saved.');

        }

    } catch (error) {

        alert(error.message);

        button.disabled = false;
        button.innerText = 'Save & Next →';

    }

}

</script>

@endsection