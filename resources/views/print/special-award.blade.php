<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { text-align: center; margin-bottom: 4px; }
        h2 { margin-top: 32px; margin-bottom: 8px; border-bottom: 2px solid #333; padding-bottom: 4px; }
        /* ===== Header ===== */
        table.header { width: auto; margin: 0 auto 18px; border-collapse: collapse; }
        table.header td { border: none; padding: 0; vertical-align: middle; }
        td.header-logo { width: 95px; text-align: center; padding-right: 14px; }
        td.header-logo img { width: 80px; height: auto; }
        td.header-text { border-left: 2px solid #0A2668; padding-left: 18px; text-align: left; }
        .event-title { font-size: 24px; font-weight: bold; color: #0A2668; margin: 0 0 4px; }
        .event-sub { font-size: 14px; color: #0A2668; margin: 0 0 3px; }
        .event-date { font-size: 10px; color: #64748b; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; }
        th { background: #eee; }
        .rank-1 { font-weight: bold; }
        .score { text-align: right; }
        .no-data { font-style: italic; color: #777; }
        .sig-row { width: 100%; margin-top: 60px; text-align: center; }
        .sig-box { display: inline-block; width: 35%; text-align: center; }
        .sig-line { border-top: 1px solid #1e293b; margin-top: 40px; padding-top: 4px; font-size: 10px; }
    </style>
</head>
<body>
    <div style="text-align: center;">
    <table class="header">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('uploads/favicon.png') }}" alt="CRT San Jose">
            </td>
            <td class="header-divider"></td>
            <td class="header-text">
                <div class="event-title">{{ $event->name }}</div>
                <div class="event-sub">Special Awards — Results</div>
                @if($event->event_date)
                    <div class="event-date">{{ $event->event_date->format('F j, Y') }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>
    {{-- <h1>{{ $event->name }}</h1> --}}
    {{-- <p style="text-align:center;">Special Awards — Results</p> --}}

    @foreach ($event->specialAwards as $award)
        <h2>{{ $award->name }}</h2>

        @php $ranking = $rankings->get($award->id); @endphp

        @if ($ranking->isEmpty())
            <p class="no-data">No completed scores yet.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Contestant</th>
                        <th class="score">Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ranking as $row)
                        <tr class="{{ $row['rank'] === 1 ? 'rank-1' : '' }}">
                            <td>{{ $row['rank'] }}</td>
                            <td>{{ $row['contestant']->name }}</td>
                            <td class="score">{{ number_format($row['score'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    <div class="sig-row">
        <div class="sig-box">
            <div class="sig-line">Tabulation Head</div>
        </div>
    </div>
</body>
</html>