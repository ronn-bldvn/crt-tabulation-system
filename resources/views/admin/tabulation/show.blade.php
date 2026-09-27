@extends('layouts.app')
@section('title', 'Results — '.$event->name)
@section('content')

<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold">Results — {{ $event->name }}</h1>
        <p class="text-sm text-slate-500">Ranking method: {{ $event->ranking_method === 'rank' ? 'Average of ranks' : 'Weighted points' }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.print.results', $event) }}" target="_blank" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">🖨 Print Results</a>
        <a href="{{ route('admin.print.breakdown', $event) }}" target="_blank" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">🖨 Print Breakdown</a>
        <a href="{{ route('admin.events.show', $event) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">&larr; Back to event</a>
    </div>
</div>

{{-- Completeness warnings --}}
@php $incomplete = $completeness->filter(fn($c) => !$c['complete']); @endphp
@if($incomplete->isNotEmpty())
<div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
    ⚠️ Scoring is incomplete for {{ $incomplete->count() }} segment(s). Results below are based on scores submitted so far.
</div>
@endif

{{-- Overall ranking --}}
<div class="mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Place</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Contestant</th>
                @foreach($results['segments'] as $segment)
                <th class="px-4 py-3 text-right font-semibold text-slate-600">{{ $segment->name }}</th>
                @endforeach
                <th class="px-4 py-3 text-right font-semibold text-slate-600">Overall Score</th>
                <th class="px-4 py-3 text-right font-semibold text-slate-600">Rank Avg</th>
                <th class="px-4 py-3 text-right font-semibold text-slate-600">Certificate</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($ranking as $row)
            <tr class="{{ $row['place'] <= 3 ? 'bg-amber-50/50' : '' }}">
                <td class="px-4 py-3 font-bold">{{ $row['place'] }}</td>
                <td class="px-4 py-3">#{{ $row['contestant']->number }} {{ $row['contestant']->name }}</td>
                @foreach($results['segments'] as $segment)
                <td class="px-4 py-3 text-right text-slate-600">
                    {{ number_format($row['segments'][$segment->id]['average'], 2) }}
                    <span class="text-xs text-slate-400">(#{{ $row['segments'][$segment->id]['rank'] }})</span>
                </td>
                @endforeach
                <td class="px-4 py-3 text-right font-semibold">{{ number_format($row['points_score'], 2) }}</td>
                <td class="px-4 py-3 text-right text-slate-600">{{ number_format($row['rank_average'], 2) }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.print.certificate', [$event, $row['contestant']]) }}" target="_blank" class="text-xs font-medium text-indigo-600 hover:underline">print</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<p class="mt-3 text-xs text-slate-500">
    Segment columns show the average normalized score (0–100) across judges, with the contestant's rank within that segment in parentheses.
    "Overall Score" is the segment-weighted average (points method). "Rank Avg" is the weighted average of segment ranks (lower is better; classic pageant method).
</p>
@endsection
