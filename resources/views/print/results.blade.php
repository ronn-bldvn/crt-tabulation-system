<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Results — {{ $event->name }}</title>
    <style>
        @page { margin: 24px 32px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #0A2668; font-size: 12px; }

        /* ===== Header ===== */
        table.header { width: auto; margin: 0 auto 18px; border-collapse: collapse; }
        table.header td { border: none; padding: 0; vertical-align: middle; }
        td.header-logo { width: 95px; text-align: center; padding-right: 14px; }
        td.header-logo img { width: 80px; height: auto; }
        td.header-text { border-left: 2px solid #0A2668; padding-left: 18px; text-align: left; }
        .event-title { font-size: 24px; font-weight: bold; color: #0A2668; margin: 0 0 4px; }
        .event-sub { font-size: 14px; color: #0A2668; margin: 0 0 3px; }
        .event-date { font-size: 10px; color: #64748b; margin: 0; }

        /* ===== Results table ===== */
        table.results { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.results th, table.results td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        table.results th { background: #f1f5f9; font-size: 11px; text-transform: uppercase; letter-spacing: 0.03em; }
        td.num { text-align: right; }
        tr.top3 { background: #fffbeb; }
        .place { font-weight: bold; font-size: 14px; }

        /* ===== Signature ===== */
        .sig-row { width: 100%; margin-top: 60px; text-align: center; }
        .sig-box { display: inline-block; width: 35%; text-align: center; }
        .sig-line { border-top: 1px solid #1e293b; margin-top: 40px; padding-top: 4px; font-size: 10px; }

        .generated { margin-top: 30px; font-size: 9px; color: #94a3b8; text-align: right; }
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
                <div class="event-sub">Official Results</div>
                @if($event->event_date)
                    <div class="event-date">
    {{ $event->event_date->timezone('Asia/Manila')->format('F j, Y') }}
</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="results">
        <thead>
            <tr>
                <th>Place</th>
                <th>No.</th>
                <th>Contestant</th>
                <th>Representing</th>
                <th class="num">Overall Score</th>
                <th class="num">Rank Avg.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ranking as $row)
            <tr @class(['top3' => $row['place'] <= 3])>
                <td class="place">{{ $row['place'] }}</td>
                <td>{{ $row['contestant']->number }}</td>
                <td>{{ $row['contestant']->name }}</td>
                <td>{{ $row['contestant']->representing }}</td>
                <td class="num">{{ number_format($row['points_score'], 2) }}</td>
                <td class="num">{{ number_format($row['rank_average']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="sig-row">
        <div class="sig-box">
            <div class="sig-line">Tabulation Head</div>
        </div>
    </div>

    <div class="generated">Generated {{ now()->format('F j, Y g:i A') }}</div>
</body>
</html>