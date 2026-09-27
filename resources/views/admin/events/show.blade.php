@extends('layouts.app')
@section('title', $event->name)
@section('content')

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $event->name }}</h1>
            <p class="text-sm text-slate-500">{{ $event->event_date?->format('M j, Y') }} &middot;
                {{ ucfirst($event->status) }} &middot; Ranking:
                {{ $event->ranking_method === 'rank' ? 'Average of ranks' : 'Weighted points' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.tabulation.show', $event) }}"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">View
                Results</a>
            <a href="{{ route('admin.events.edit', $event) }}"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Edit
                Event</a>
            <form method="POST" action="{{ route('admin.events.toggle-lock', $event) }}">
                @csrf
                <button
                    class="rounded-lg px-4 py-2 text-sm font-semibold {{ $event->scoring_locked ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    {{ $event->scoring_locked ? '🔒 Scoring Locked (unlock)' : '🔓 Lock Scoring' }}
                </button>
            </form>
        </div>
    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-2">

        {{-- SEGMENTS & CRITERIA --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">Segments & Criteria</h2>
                <span class="text-xs text-slate-500">Segment weights total:
                    {{ number_format($event->totalSegmentWeight(), 1) }}%</span>
            </div>

            <div class="mt-4 space-y-4">
                @foreach($event->segments as $segment)
                    <div class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium">{{ $segment->name }}</span>
                                <span class="ml-2 text-xs text-slate-500">{{ $segment->weight }}% of overall &middot; criteria
                                    total {{ number_format($segment->totalCriteriaWeight(), 1) }}%</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('admin.segments.toggle-lock', [$event, $segment]) }}">
                                    @csrf
                                    <button
                                        class="text-xs font-medium {{ $segment->is_locked ? 'text-amber-700' : 'text-slate-500' }} hover:underline">
                                        {{ $segment->is_locked ? 'Locked' : 'Lock' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.segments.destroy', [$event, $segment]) }}"
                                    onsubmit="return confirm('Delete this segment and all its criteria/scores?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-medium text-rose-600 hover:underline">Delete</button>
                                </form>
                            </div>
                        </div>

                        <ul class="mt-3 space-y-1">
                            @foreach($segment->criteria as $criterion)
                                @php
                                    $isSpecialAwardCriterion = $criterion->isSpecialAward();
                                @endphp
                                <li class="flex items-center justify-between text-sm text-slate-600">
                                    <span>
                                        {{ $criterion->name }}
                                        <span class="text-slate-400">
                                            (weight {{ $criterion->weight }}%, max
                                            {{ rtrim(rtrim($criterion->max_score, '0'), '.') }})
                                        </span>
                                        @if($isSpecialAwardCriterion)
                                            <span
                                                class="ml-1 inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-medium text-indigo-700">
                                                🏆 Auto from Special Awards
                                            </span>
                                        @endif
                                    </span>
                                    <form method="POST"
                                        action="{{ route('admin.criteria.destroy', [$event, $segment, $criterion]) }}"
                                        onsubmit="return confirm('Delete this criterion?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-rose-500 hover:underline">remove</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>

                        <details class="mt-3">
                            <summary class="cursor-pointer text-xs font-medium text-indigo-600">+ Add criterion</summary>
                            <form method="POST" action="{{ route('admin.criteria.store', [$event, $segment]) }}"
                                class="mt-2 grid grid-cols-2 gap-2">
                                @csrf
                                <input type="text" name="name" placeholder="Criterion name" required
                                    class="col-span-2 rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                                <input type="number" step="0.01" name="weight" placeholder="Weight %" required
                                    class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                                <input type="number" step="0.01" name="max_score" placeholder="Max score" value="100" required
                                    class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                                <button
                                    class="col-span-2 rounded-md bg-slate-800 px-2 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Add</button>
                            </form>
                        </details>
                    </div>
                @endforeach
            </div>

            <details class="mt-4">
                <summary class="cursor-pointer text-sm font-medium text-indigo-600">+ Add segment</summary>
                <form method="POST" action="{{ route('admin.segments.store', $event) }}"
                    class="mt-2 grid grid-cols-2 gap-2">
                    @csrf
                    <input type="text" name="name" placeholder="Segment name (e.g. Evening Gown)" required
                        class="col-span-2 rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                    <input type="number" step="0.01" name="weight" placeholder="Weight % of overall" required
                        class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                    <input type="number" name="order" placeholder="Order (0,1,2...)"
                        class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                    <button
                        class="col-span-2 rounded-md bg-slate-800 px-2 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Add
                        segment</button>
                </form>
            </details>
        </div>

        {{-- CONTESTANTS --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" data-candidate-filter-group>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold">Contestants</h2>
                <div class="inline-flex rounded-lg bg-slate-100 p-1" role="group" aria-label="Filter contestants by gender">
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
            <div class="mt-4 space-y-2">
                @foreach($event->contestants as $contestant)
                    <div class="rounded-lg border border-slate-200 px-3 py-2 text-sm"
                        data-candidate-gender="{{ strtolower($contestant->candidateGender() ?? 'other') }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                @if($contestant->photo_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($contestant->photo_path) }}"
                                        class="h-8 w-8 rounded-full object-cover">
                                @else
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-xs">
                                        {{ $contestant->number }}
                                    </div>
                                @endif
                                <div>
                                    <span class="font-medium">#{{ $contestant->number }} {{ $contestant->name }}</span>
                                    {{-- @if($contestant->candidateGender())
                                        <span
                                            class="ml-1 rounded-full px-2 py-0.5 text-xs font-medium {{ $contestant->candidateGender() === 'Female' ? 'bg-rose-50 text-rose-700' : 'bg-sky-50 text-sky-700' }}">
                                            {{ $contestant->candidateGender() }} candidate
                                        </span>
                                    @endif --}}
                                    @if($contestant->representing)<span class="text-slate-400"> —
                                    {{ $contestant->representing }}</span>@endif
                                    @unless($contestant->is_active)<span class="ml-1 text-rose-500">(inactive)</span>@endunless
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="toggleContestantEdit({{ $contestant->id }})"
                                    class="text-xs font-medium text-indigo-600 hover:underline">
                                    edit
                                </button>
                                <form method="POST" action="{{ route('admin.contestants.destroy', [$event, $contestant]) }}"
                                    onsubmit="return confirm('Remove this contestant?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-rose-500 hover:underline">remove</button>
                                </form>
                            </div>
                        </div>

                        {{-- Inline Edit Form --}}
                        <div id="contestant-edit-{{ $contestant->id }}"
                            class="hidden mt-3 rounded-lg border border-indigo-200 bg-indigo-50/60 p-4">
                            <form method="POST" action="{{ route('admin.contestants.update', [$event, $contestant]) }}"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 mb-1">No.</label>
                                        <input type="text" name="number" value="{{ old('number', $contestant->number) }}"
                                            required class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Full Name</label>
                                        <input type="text" name="name" value="{{ old('name', $contestant->name) }}" required
                                            class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Representing</label>
                                        <input type="text" name="representing"
                                            value="{{ old('representing', $contestant->representing) }}"
                                            class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                                            placeholder="Optional">
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Bio / Description</label>
                                        <textarea name="bio" rows="2"
                                            class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
                                            placeholder="Optional">{{ old('bio', $contestant->bio) }}</textarea>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-medium text-slate-700 mb-1">Photo</label>
                                        <div class="flex items-center gap-3 flex-wrap">
                                            @if($contestant->photo_path)
                                                <img src="{{ \Illuminate\Support\Facades\Storage::url($contestant->photo_path) }}"
                                                    class="h-12 w-12 rounded-full object-cover border border-slate-200">
                                            @endif
                                            <input type="file" name="photo" accept="image/*" class="text-xs max-w-[280px]">
                                            <span class="text-xs text-slate-400">
                                                Leave empty to keep current photo.
                                            </span>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="flex cursor-pointer items-center gap-2">
                                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $contestant->is_active)) class="rounded border-slate-300">
                                            <span class="text-xs font-medium text-slate-700">
                                                Active (included in tabulation and scoring)
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap justify-end gap-2">
                                    <button type="button" onclick="toggleContestantEdit({{ $contestant->id }})"
                                        class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <details class="mt-4">
                <summary class="cursor-pointer text-sm font-medium text-indigo-600">+ Add contestant</summary>
                <form method="POST" action="{{ route('admin.contestants.store', $event) }}" enctype="multipart/form-data"
                    class="mt-2 grid grid-cols-2 gap-2">
                    @csrf
                    <input type="text" name="number" placeholder="No." required
                        class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                    <input type="text" name="name" placeholder="Full name" required
                        class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                    <input type="text" name="representing" placeholder="Representing (optional)"
                        class="col-span-2 rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                    <input type="file" name="photo" accept="image/*" class="col-span-2 text-sm">
                    <button
                        class="col-span-2 rounded-md bg-slate-800 px-2 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Add
                        contestant</button>
                </form>
            </details>
        </div>

        {{-- SPECIAL AWARDS --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">

            {{-- HEADER --}}
            <div class="flex flex-wrap items-start justify-between gap-3">

                <div>
                    <h2 class="text-lg font-semibold text-slate-800">
                        Special Awards
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Create independent awards with their own criteria,
                        scoring, and optional photo submissions.
                    </p>
                </div>

                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-600">
                    {{ $event->specialAwards->count() }} award(s)
                </span>

                @if($event->specialAwards->count())
                    <a href="{{ route('admin.print.special-awards', $event) }}" target="_blank"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        🖨️ Print Results
                    </a>

                    <a href="{{ route('admin.print.special-awards.breakdown', $event) }}" target="_blank"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        📊 Print Breakdown
                    </a>
                @endif

            </div>


            {{-- VALIDATION ERRORS --}}
            @if($errors->any())
                <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-4">

                    <div class="text-sm font-semibold text-rose-700">
                        Please check the following:
                    </div>

                    <ul class="mt-2 list-disc pl-5 text-xs text-rose-600">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                </div>
            @endif


            {{-- EXISTING AWARDS --}}
            <div class="mt-5 space-y-4">

                @forelse($event->specialAwards as $award)

                    <div class="rounded-xl border border-slate-200 overflow-hidden">

                        {{-- AWARD HEADER --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 bg-slate-50 px-4 py-3">

                            <div>

                                <div class="flex flex-wrap items-center gap-2">

                                    <h3 class="font-semibold text-slate-800">
                                        {{ $award->name }}
                                    </h3>

                                    <span
                                        class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-medium text-indigo-700">
                                        {{ ucfirst($award->award_type) }}
                                    </span>

                                    @if(!$award->has_criteria)

                                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-medium text-slate-700">
                                            No Criteria
                                        </span>

                                    @endif

                                    @if($award->allow_multiple_photos)

                                        <span
                                            class="rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-medium text-purple-700">
                                            Multiple Photos
                                        </span>

                                    @endif

                                </div>

                                @if($award->description)

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $award->description }}
                                    </p>

                                @endif

                            </div>


                            {{-- DELETE --}}
                            <form method="POST" action="{{ route('admin.special-awards.destroy', [$event, $award]) }}"
                                onsubmit="return confirm('Delete this special award, its criteria, and uploaded photos?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="text-xs font-medium text-rose-500 hover:underline">
                                    Delete
                                </button>
                            </form>

                        </div>


                        {{-- AWARD BODY --}}
                        <div class="grid gap-6 p-4 lg:grid-cols-2">


                            {{-- CRITERIA --}}
                            <div>

                                <div class="flex items-center justify-between">

                                    <h4 class="text-sm font-semibold text-slate-700">
                                        Criteria
                                    </h4>

                                    @if($award->has_criteria)
                                        <span class="text-xs text-slate-400">
                                            {{ number_format($award->criteria->sum('weight'), 2) }}%
                                        </span>
                                    @endif

                                </div>


                                <div class="mt-3 space-y-2">

                                    @forelse($award->criteria as $criterion)

                                        <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2">

                                            <div>
                                                <div class="text-sm font-medium text-slate-700">
                                                    {{ $criterion->name }}
                                                </div>

                                                <div class="text-[11px] text-slate-400">
                                                    Max score:
                                                    {{ rtrim(rtrim($criterion->max_score)) }}
                                                </div>
                                            </div>

                                            <span class="text-xs font-semibold text-indigo-600">
                                                {{ rtrim(rtrim($criterion->weight)) }}%
                                            </span>

                                        </div>

                                    @empty

                                        <div
                                            class="rounded-lg border border-dashed border-slate-300 p-4 text-center text-xs text-slate-400">
                                            @if($award->has_criteria)
                                                No criteria configured.
                                            @else
                                                This award has no scoring criteria — winner is selected manually.
                                            @endif
                                        </div>

                                    @endforelse

                                </div>

                            </div>


                            {{-- WINNER --}}
                            <div>

                                <h4 class="text-sm font-semibold text-slate-700">
                                    Winner
                                </h4>


                                @if($award->winner)

                                    <div
                                        class="mt-3 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-3">

                                        @if($award->winner->photo_path)

                                            <img src="{{ Storage::url($award->winner->photo_path) }}"
                                                class="h-12 w-12 rounded-full object-cover">

                                        @else

                                            <div
                                                class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold">
                                                {{ $award->winner->number }}
                                            </div>

                                        @endif

                                        <div>

                                            <div class="text-[10px] font-semibold uppercase tracking-wide text-emerald-600">
                                                Winner
                                            </div>

                                            <div class="text-sm font-semibold text-emerald-800">
                                                #{{ $award->winner->number }}
                                                {{ $award->winner->name }}
                                            </div>

                                        </div>

                                    </div>

                                @else

                                    <div class="mt-3 rounded-lg border border-dashed border-slate-300 p-4 text-center">

                                        <div class="text-xs text-slate-400">
                                            No winner selected
                                        </div>

                                    </div>

                                @endif


                                {{-- SELECT WINNER --}}
                                <details class="mt-3">

                                    <summary class="cursor-pointer text-xs font-medium text-indigo-600">
                                        {{ $award->winner ? 'Change Winner' : 'Select Winner' }}
                                    </summary>

                                    <form method="POST" action="{{ route('admin.special-awards.winner', [$event, $award]) }}"
                                        class="mt-3">

                                        @csrf

                                        <select name="winner_contestant_id" required
                                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">

                                            <option value="">
                                                Select contestant...
                                            </option>

                                            @foreach($event->contestants->where('is_active', true) as $contestant)

                                                <option value="{{ $contestant->id }}" @selected(
                                                    $award->winner_contestant_id == $contestant->id
                                                )>
                                                    #{{ $contestant->number }}
                                                    {{ $contestant->name }}
                                                </option>

                                            @endforeach

                                        </select>

                                        <button type="submit"
                                            class="mt-2 w-full rounded-md bg-slate-800 px-3 py-2 text-xs font-medium text-white hover:bg-slate-700">
                                            Save Winner
                                        </button>

                                    </form>

                                </details>

                            </div>

                        </div>


                        {{-- PHOTOS --}}
                        @if($award->allow_multiple_photos)

                            <div class="border-t border-slate-200 bg-slate-50 p-4">

                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700">
                                        Photo Submissions
                                    </h4>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Upload multiple photos for contestants
                                        participating in this award.
                                    </p>
                                </div>


                                <div class="mt-4 space-y-4">

                                    @foreach($event->contestants->where('is_active', true) as $contestant)

                                        @php
                                            $contestantPhotos = $award->photos
                                                ->where('contestant_id', $contestant->id);
                                        @endphp


                                        <div class="rounded-lg border border-slate-200 bg-white p-4">

                                            <div class="flex flex-wrap items-center justify-between gap-3">

                                                <div class="flex items-center gap-3">

                                                    @if($contestant->photo_path)

                                                        <img src="{{ Storage::url($contestant->photo_path) }}"
                                                            class="h-10 w-10 rounded-full object-cover">

                                                    @else

                                                        <div
                                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-xs">
                                                            {{ $contestant->number }}
                                                        </div>

                                                    @endif

                                                    <div>

                                                        <div class="text-sm font-medium text-slate-700">
                                                            #{{ $contestant->number }}
                                                            {{ $contestant->name }}
                                                        </div>

                                                        <div class="text-[11px] text-slate-400">
                                                            {{ $contestantPhotos->count() }} photo(s)
                                                        </div>

                                                    </div>

                                                </div>


                                                {{-- UPLOAD --}}
                                                <form method="POST"
                                                    action="{{ route('admin.special-awards.photos.store', [$event, $award, $contestant]) }}"
                                                    enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">

                                                    @csrf

                                                    <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp"
                                                        class="max-w-[220px] text-xs" required>

                                                    <button type="submit"
                                                        class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500">
                                                        Upload
                                                    </button>

                                                </form>

                                            </div>


                                            {{-- PHOTO GRID --}}
                                            @if($contestantPhotos->count())

                                                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">

                                                    @foreach($contestantPhotos as $photo)

                                                        <div class="group relative overflow-hidden rounded-lg border border-slate-200">

                                                            <img src="{{ Storage::url($photo->photo_path) }}"
                                                                class="aspect-square w-full object-cover">

                                                            <form method="POST"
                                                                action="{{ route('admin.special-awards.photos.destroy', [$event, $award, $photo->id]) }}"
                                                                onsubmit="return confirm('Delete this photo?')"
                                                                class="absolute right-1 top-1 opacity-0 transition group-hover:opacity-100">

                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="submit"
                                                                    class="rounded-full bg-rose-600 px-2 py-1 text-[10px] font-medium text-white shadow">
                                                                    ×
                                                                </button>

                                                            </form>

                                                        </div>

                                                    @endforeach

                                                </div>

                                            @endif

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        @endif

                    </div>

                @empty

                    <div class="rounded-lg border border-dashed border-slate-300 p-8 text-center">

                        <div class="text-sm font-medium text-slate-600">
                            No special awards created yet.
                        </div>

                        <div class="mt-1 text-xs text-slate-400">
                            Create awards such as Photogenic,
                            Best in School Uniform, Best Smile, etc.
                        </div>

                    </div>

                @endforelse

            </div>


            {{-- CREATE SPECIAL AWARD --}}
            <details class="mt-5">

                <summary class="cursor-pointer text-sm font-medium text-indigo-600">
                    + Create Special Award
                </summary>


                <form method="POST" action="{{ route('admin.special-awards.store', $event) }}" class="mt-4 space-y-5">

                    @csrf


                    {{-- AWARD INFORMATION --}}
                    <div class="rounded-lg border border-slate-200 p-4">

                        <h3 class="text-sm font-semibold text-slate-800">
                            Award Information
                        </h3>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">

                            <div class="sm:col-span-2">

                                <label class="block text-xs font-medium text-slate-700">
                                    Award Name
                                </label>

                                <input type="text" name="name" placeholder="e.g. Photogenic" value="{{ old('name') }}"
                                    required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm">

                            </div>


                            <div class="sm:col-span-2">

                                <label class="block text-xs font-medium text-slate-700">
                                    Description
                                </label>

                                <textarea name="description" rows="2" placeholder="Describe this special award"
                                    class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('description') }}</textarea>

                            </div>


                            <div>

                                <label class="block text-xs font-medium text-slate-700">
                                    Award Type
                                </label>

                                <select name="award_type"
                                    class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm">

                                    <option value="special" @selected(old('award_type', 'special') === 'special')>
                                        Special Award
                                    </option>

                                    <option value="minor" @selected(old('award_type') === 'minor')>
                                        Minor Award
                                    </option>

                                    <option value="recognition" @selected(old('award_type') === 'recognition')>
                                        Recognition
                                    </option>

                                </select>

                            </div>


                            <div>

                                <label class="block text-xs font-medium text-slate-700">
                                    Display Order
                                </label>

                                <input type="number" name="order" value="{{ old('order', 0) }}" min="0"
                                    class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm">

                            </div>

                        </div>

                    </div>


                    {{-- PHOTO SETTING --}}
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">

                        <label class="flex cursor-pointer items-start gap-3">

                            <input type="checkbox" name="allow_multiple_photos" value="1"
                                @checked(old('allow_multiple_photos')) class="mt-1 rounded border-slate-300">

                            <div>

                                <div class="text-sm font-semibold text-slate-700">
                                    Allow Multiple Photos
                                </div>

                                <p class="mt-1 text-xs text-slate-500">
                                    Enable this for awards such as
                                    <strong>Photogenic</strong> where each contestant
                                    can submit multiple photos.
                                </p>

                            </div>

                        </label>

                    </div>


                    {{-- CRITERIA TOGGLE --}}
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">

                        <label class="flex cursor-pointer items-start gap-3">

                            <input type="checkbox" name="has_criteria" id="has_criteria" value="1"
                                @checked(old('has_criteria', true)) onchange="toggleCriteriaSection()"
                                class="mt-1 rounded border-slate-300">

                            <div>

                                <div class="text-sm font-semibold text-slate-700">
                                    Score this award using criteria
                                </div>

                                <p class="mt-1 text-xs text-slate-500">
                                    Leave unchecked for awards with no rubric —
                                    you'll pick the winner manually instead
                                    (e.g. a popularity or sponsor's award).
                                </p>

                            </div>

                        </label>

                    </div>


                    {{-- CRITERIA --}}
                    <div id="criteria-section" class="rounded-lg border border-slate-200 p-4">

                        <div class="flex flex-wrap items-center justify-between gap-2">

                            <div>

                                <h3 class="text-sm font-semibold text-slate-800">
                                    Award Criteria
                                </h3>

                                <p class="mt-1 text-xs text-slate-500">
                                    Criteria weights must total exactly 100%.
                                </p>

                            </div>

                            <div id="criteria-total"
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                Total: 0%
                            </div>

                        </div>


                        <div id="special-award-criteria" class="mt-4 space-y-2">

                            <div class="criteria-row grid grid-cols-12 gap-2">

                                <input type="text" name="criteria[0][name]" placeholder="Criterion name"
                                    value="{{ old('criteria.0.name') }}" required
                                    class="col-span-5 rounded-md border border-slate-300 px-2 py-2 text-sm">

                                <input type="number" name="criteria[0][weight]" placeholder="Weight %"
                                    value="{{ old('criteria.0.weight') }}" min="0" max="100" step="0.01" required
                                    oninput="updateCriteriaTotal()"
                                    class="col-span-3 rounded-md border border-slate-300 px-2 py-2 text-sm">

                                <input type="number" name="criteria[0][max_score]" placeholder="Max"
                                    value="{{ old('criteria.0.max_score', 100) }}" min="1" step="0.01" required
                                    class="col-span-3 rounded-md border border-slate-300 px-2 py-2 text-sm">

                                <button type="button" onclick="removeCriteria(this)"
                                    class="col-span-1 rounded-md text-rose-500 hover:bg-rose-50">
                                    ×
                                </button>

                            </div>

                        </div>


                        <button type="button" onclick="addSpecialAwardCriterion()"
                            class="mt-3 text-xs font-medium text-indigo-600 hover:underline">
                            + Add Criterion
                        </button>

                    </div>


                    <button type="submit"
                        class="w-full rounded-md bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                        Create Special Award
                    </button>

                </form>

            </details>

        </div>

        {{-- JUDGES --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">Judges assigned to this event</h2>
                <a href="{{ route('admin.judges.index') }}"
                    class="text-xs font-medium text-indigo-600 hover:underline">Manage judge accounts &rarr;</a>
            </div>

            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                @foreach($event->judges as $judge)
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <div>
                            <span class="font-medium">{{ $judge->name }}</span>
                            <span class="text-slate-400"> — {{ $judge->email }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.print.scoresheet', [$event, $judge]) }}" target="_blank"
                                class="text-xs font-medium text-slate-600 hover:underline">print scoresheet</a>
                            <form method="POST" action="{{ route('admin.judges.unassign', [$event, $judge]) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs text-rose-500 hover:underline">remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('admin.judges.assign', $event) }}"
                class="mt-4 flex flex-wrap items-end gap-2">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-700">Assign existing judge</label>
                    <select name="judge_id" required class="mt-1 rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                        <option value="">Select a judge...</option>
                        @foreach(\App\Models\User::where('role', 'judge')->get() as $judge)
                            <option value="{{ $judge->id }}">{{ $judge->name }} ({{ $judge->email }})</option>
                        @endforeach
                    </select>
                </div>
                <button
                    class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Assign</button>
            </form>
        </div>
    </div>

    <script>
        let specialAwardCriterionIndex = 1;

        function addSpecialAwardCriterion() {

            const container = document.getElementById(
                'special-award-criteria'
            );

            const row = document.createElement('div');

            row.className =
                'criteria-row grid grid-cols-12 gap-2';

            row.innerHTML = `
                <input
                    type="text"
                    name="criteria[${specialAwardCriterionIndex}][name]"
                    placeholder="Criterion name"
                    required
                    class="col-span-5 rounded-md border border-slate-300 px-2 py-2 text-sm"
                >

                <input
                    type="number"
                    name="criteria[${specialAwardCriterionIndex}][weight]"
                    placeholder="Weight %"
                    min="0"
                    max="100"
                    step="0.01"
                    required
                    oninput="updateCriteriaTotal()"
                    class="col-span-3 rounded-md border border-slate-300 px-2 py-2 text-sm"
                >

                <input
                    type="number"
                    name="criteria[${specialAwardCriterionIndex}][max_score]"
                    placeholder="Max"
                    value="100"
                    min="1"
                    step="0.01"
                    required
                    class="col-span-3 rounded-md border border-slate-300 px-2 py-2 text-sm"
                >

                <button
                    type="button"
                    onclick="removeCriteria(this)"
                    class="col-span-1 rounded-md text-rose-500 hover:bg-rose-50"
                >
                    ×
                </button>
            `;

            container.appendChild(row);

            specialAwardCriterionIndex++;

            toggleCriteriaSection();
            updateCriteriaTotal();
        }


        function removeCriteria(button) {

            const rows = document.querySelectorAll(
                '#special-award-criteria .criteria-row'
            );

            if (rows.length <= 1) {
                alert('At least one criterion is required.');
                return;
            }

            button.closest('.criteria-row').remove();

            updateCriteriaTotal();
        }


        function updateCriteriaTotal() {

            let total = 0;

            document
                .querySelectorAll(
                    '#special-award-criteria input[name$="[weight]"]'
                )
                .forEach(input => {

                    total += parseFloat(input.value) || 0;

                });

            const totalElement =
                document.getElementById('criteria-total');

            totalElement.textContent =
                'Total: ' + total.toFixed(2) + '%';

            if (Math.abs(total - 100) < 0.01) {

                totalElement.className =
                    'rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700';

            } else if (total > 100) {

                totalElement.className =
                    'rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700';

            } else {

                totalElement.className =
                    'rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700';

            }
        }


        function toggleCriteriaSection() {

            const checkbox = document.getElementById('has_criteria');
            const section = document.getElementById('criteria-section');
            const inputs = section.querySelectorAll('input[required]');

            if (checkbox.checked) {

                section.classList.remove('opacity-40', 'pointer-events-none');
                inputs.forEach(input => input.setAttribute('required', 'required'));

            } else {

                section.classList.add('opacity-40', 'pointer-events-none');
                inputs.forEach(input => input.removeAttribute('required'));

            }
        }


        function toggleContestantEdit(contestantId) {
            const form = document.getElementById('contestant-edit-' + contestantId);
            if (form) {
                form.classList.toggle('hidden');
            }
        }

        document.addEventListener(
            'DOMContentLoaded',
            () => {
                updateCriteriaTotal();
                toggleCriteriaSection();
       }
        );
    </script>
@endsection
