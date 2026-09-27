<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Breakdown — {{ $event->name }}</title>
    <style>
        @page { margin: 20px 28px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #1e293b; font-size: 10px; }

        /* ===== Header ===== */
        table.header { width: auto; margin: 0 auto 18px; border-collapse: collapse; }
        table.header td { border: none; padding: 0; vertical-align: middle; }
        td.header-logo { width: 95px; text-align: center; padding-right: 14px; }
        td.header-logo img { width: 80px; height: auto; }
        td.header-text { border-left: 2px solid #0A2668; padding-left: 18px; text-align: left; }
        .event-title { font-size: 24px; font-weight: bold; color: #0A2668; margin: 0 0 4px; }
        .event-sub { font-size: 14px; color: #0A2668; margin: 0 0 3px; }
        .event-date { font-size: 10px; color: #64748b; margin: 0; }


        h1 { font-size: 18px; margin: 0 0 2px; text-align: center; }
        .subtitle { text-align: center; color: #64748b; font-size: 10px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; }
        th { background: #f1f5f9; font-size: 9px; text-transform: uppercase; }
        td.num { text-align: right; }
        .generated { margin-top: 20px; font-size: 8px; color: #94a3b8; text-align: right; }
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
                <div class="event-sub">Full Segment Breakdown — All Judges</div>
                @if($event->event_date)
                    <div class="event-date">
    {{ $event->event_date->timezone('Asia/Manila')->format('F j, Y') }}
</div>
                @endif
            </td>
        </tr>
    </table>
    {{-- <div class="subtitle">Full Segment Breakdown — All Judges</div> --}}

        <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Contestant</th>
                @foreach($results['segments'] as $segment)
                <th class="num">{{ $segment->name }} ({{ $segment->weight }}%)</th>
                @endforeach
                <th class="num">Special Award Pts</th>
                <th class="num">Overall</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results['contestants'] as $row)
            <tr>
                <td>{{ $row['contestant']->number }}</td>
                <td>{{ $row['contestant']->name }}</td>
                @foreach($results['segments'] as $segment)
                <td class="num">{{ number_format($row['segments'][$segment->id]['average'], 2) }}</td>
                @endforeach
                <td class="num">{{ $row['special_award_points'] > 0 ? '+'.number_format($row['special_award_points'], 2) : '—' }}</td>
                <td class="num"><strong>{{ number_format($row['points_score'], 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($event->specialAwards->isNotEmpty())
    <h3 style="margin-top:20px;">Special Awards</h3>
    <table>
        <thead>
            <tr>
                <th>Award</th>
                <th>Winner</th>
                {{-- <th class="num">Points Awarded</th> --}}
            </tr>
        </thead>
        <tbody>
            @php $pointsPerAward = $event->specialAwards->count() > 0 ? round($event->special_award_pool_points / $event->specialAwards->count(), 2) : 0; @endphp
            @foreach($event->specialAwards as $award)
            <tr>
                <td>{{ $award->name }}</td>
                <td>
                    @if($award->winner)
                        #{{ $award->winner->number }} {{ $award->winner->name }}
                    @else
                        <span style="color:#94a3b8;">No winner selected</span>
                    @endif
                </td>
                {{-- <td class="num">{{ $award->winner ? '+'.number_format($pointsPerAward, 2) : '—' }}</td> --}}
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- @foreach($results['segments'] as $segment)
    <h3 style="margin-top:20px;">{{ $segment->name }} — per-judge scores</h3> --}}

    @foreach($results['segments'] as $segment)
    <h3 style="margin-top:20px;">{{ $segment->name }} — per-judge scores</h3>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Contestant</th>
                @foreach($event->judges as $judge)
                <th class="num">{{ $judge->name }}</th>
                @endforeach
                <th class="num">Average</th>
                <th class="num">Rank</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results['contestants'] as $row)
            <tr>
                <td>{{ $row['contestant']->number }}</td>
                <td>{{ $row['contestant']->name }}</td>
                @foreach($event->judges as $judge)
                <td class="num">{{ isset($row['segments'][$segment->id]['per_judge'][$judge->id]) ? number_format($row['segments'][$segment->id]['per_judge'][$judge->id], 2) : '—' }}</td>
                @endforeach
                <td class="num">{{ number_format($row['segments'][$segment->id]['average'], 2) }}</td>
                <td class="num">{{ $row['segments'][$segment->id]['rank'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach

    <div class="generated">Generated {{ now()->format('F j, Y g:i A') }}</div>
</body>
</html>
