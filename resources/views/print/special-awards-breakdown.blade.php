{{-- <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        h1 { text-align: center; margin-bottom: 4px; }
        h2 { margin-top: 28px; margin-bottom: 4px; border-bottom: 2px solid #333; padding-bottom: 4px; }
        h3 { margin-top: 16px; margin-bottom: 6px; font-size: 11px; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: center; }
        th { background: #eee; }
        td.name { text-align: left; }
        td.incomplete { color: #b45309; font-style: italic; }
        td.total { font-weight: bold; }
        .no-data { font-style: italic; color: #777; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <h1>{{ $event->name }}</h1>
    <p style="text-align:center;">Special Awards — Score Breakdown per Judge</p>

    @foreach ($event->specialAwards as $award)
        <h2>{{ $award->name }}</h2>

        @php $judges = $breakdowns->get($award->id); @endphp

        @if (!$judges || $judges->isEmpty())
            <p class="no-data">No scores submitted yet.</p>
        @else
            @foreach ($judges as $judgeId => $row)
                <h3>Judge: {{ $row['judge']->name ?? 'Unknown' }}</h3>

                <table>
                    <thead>
                        <tr>
                            <th style="text-align:left;">Contestant</th>
                            @foreach ($award->criteria as $criterion)
                                <th>{{ $criterion->name }}<br><span style="font-weight:normal;">(max {{ rtrim(rtrim($criterion->max_score, '0'), '.') }}, {{ rtrim(rtrim($criterion->weight, '0'), '.') }}%)</span></th>
                            @endforeach
                            <th>Weighted Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($row['contestants'] as $contestantId => $data)
                            <tr>
                                <td class="name">#{{ $data['contestant']->number ?? '?' }} {{ $data['contestant']->name ?? 'Unknown' }}</td>
                                @foreach ($award->criteria as $criterion)
                                    @php $s = $data['scores'][$criterion->id] ?? null; @endphp
                                    <td class="{{ is_null($s) ? 'incomplete' : '' }}">
                                        {{ is_null($s) ? '—' : number_format($s, 2) }}
                                    </td>
                                @endforeach
                                <td class="total">
                                    @if ($data['complete'])
                                        {{ number_format($data['total'], 2) }}
                                    @else
                                        <span class="incomplete">incomplete</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endif
    @endforeach
</body>
</html> --}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        h1 { text-align: center; margin-bottom: 4px; }
        h2 { margin-top: 28px; margin-bottom: 4px; border-bottom: 2px solid #333; padding-bottom: 4px; }
        h3 { margin-top: 16px; margin-bottom: 6px; font-size: 11px; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: center; }
        th { background: #eee; }
        td.name { text-align: left; }
        td.incomplete { color: #b45309; font-style: italic; }
        td.total { font-weight: bold; }
        .no-data { font-style: italic; color: #777; }
        .rank-1 { background: #fef3c7; font-weight: bold; }
        .ranking-table th { background: #dbeafe; }
        .sig-row { width: 100%; margin-top: 60px; text-align: center; }
        .sig-box { display: inline-block; width: 35%; text-align: center; }
        .sig-line { border-top: 1px solid #1e293b; margin-top: 40px; padding-top: 4px; font-size: 10px; }
                table.header {
            width: auto;
            margin: 0 auto 18px;
            border-collapse: collapse;
        }

        table.header td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        td.header-logo {
            width: 95px;
            text-align: center;
            padding-right: 14px;
        }

        td.header-logo img {
            width: 80px;
            height: auto;
        }

        td.header-text {
            border-left: 2px solid #0A2668;
            padding-left: 18px;
            text-align: left;
        }

        .event-title {
            font-size: 24px;
            font-weight: bold;
            color: #0A2668;
            margin: 0 0 4px;
        }

        .event-sub {
            font-size: 14px;
            color: #0A2668;
            margin: 0 0 3px;
        }

        .event-date {
            font-size: 10px;
            color: #64748b;
            margin: 0;
        }

        .meta {
            color: #64748b;
            font-size: 11px;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('uploads/favicon.png') }}" alt="CRT San Jose">
            </td>
            <td class="header-text">
                <div class="event-title">{{ $event->name }}</div>
                <div class="meta">Special Awards — Ranking & Score Breakdown per Judge</strong>
                    @if($event->event_date)
                    &middot; {{ $event->event_date->format('F j, Y') }} @endif
                </div>
            </td>
        </tr>
    </table>
    {{-- <h1>{{ $event->name }}</h1> --}}
    {{-- <p style="text-align:center;"></p> --}}

    @foreach ($event->specialAwards as $award)
        <h2>{{ $award->name }}</h2>

        @php
            $ranking = $rankings->get($award->id);
            $judges = $breakdowns->get($award->id);
        @endphp

        {{-- RANKING SUMMARY --}}
        <h3>Ranking</h3>

        @if (!$ranking || $ranking->isEmpty())
            <p class="no-data">No completed scores yet — ranking not available.</p>
        @else
            <table class="ranking-table">
                <thead>
                    <tr>
                        <th style="width:60px;">Rank</th>
                        <th style="text-align:left;">Contestant</th>
                        <th style="width:120px;">Weighted Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ranking as $row)
                        <tr class="{{ $row['rank'] === 1 ? 'rank-1' : '' }}">
                            <td>{{ $row['rank'] }}</td>
                            <td class="name">#{{ $row['contestant']->number ?? '?' }} {{ $row['contestant']->name ?? 'Unknown' }}</td>
                            <td>{{ number_format($row['score'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- PER-JUDGE BREAKDOWN --}}
        <h3>Breakdown per Judge</h3>

        @if (!$judges || $judges->isEmpty())
            <p class="no-data">No scores submitted yet.</p>
        @else
            @foreach ($judges as $judgeId => $row)
                <h3 style="color:#666; font-weight:normal;">Judge: {{ $row['judge']->name ?? 'Unknown' }}</h3>

                <table>
                    <thead>
                        <tr>
                            <th style="text-align:left;">Contestant</th>
                            @foreach ($award->criteria as $criterion)
                                <th>{{ $criterion->name }}<br><span style="font-weight:normal;">(max {{ rtrim(rtrim($criterion->max_score, '0'), '.') }}, {{ rtrim(rtrim($criterion->weight, '0'), '.') }}%)</span></th>
                            @endforeach
                            <th>Weighted Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($row['contestants'] as $contestantId => $data)
                            <tr>
                                <td class="name">#{{ $data['contestant']->number ?? '?' }} {{ $data['contestant']->name ?? 'Unknown' }}</td>
                                @foreach ($award->criteria as $criterion)
                                    @php $s = $data['scores'][$criterion->id] ?? null; @endphp
                                    <td class="{{ is_null($s) ? 'incomplete' : '' }}">
                                        {{ is_null($s) ? '—' : number_format($s, 2) }}
                                    </td>
                                @endforeach
                                <td class="total">
                                    @if ($data['complete'])
                                        {{ number_format($data['total'], 2) }}
                                    @else
                                        <span class="incomplete">incomplete</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endif
    @endforeach

    <div class="sig-row">
        <div class="sig-box">
            <div class="sig-line">Tabulation Head</div>
        </div>
    </div>
</body>
</html>