<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Scoresheet — {{ $judge->name }} — {{ $event->name }}</title>
    <style>
        @page {
            margin: 24px 32px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            font-size: 11px;
        }

        h1 {
            font-size: 18px;
            margin: 0 0 2px;
        }

        /* ===== Header ===== */
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

        .segment-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 22px;
            margin-bottom: 4px;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 5px 7px;
            text-align: left;
        }

        th {
            background: #f1f5f9;
            font-size: 10px;
            text-transform: uppercase;
        }

        td.num,
        th.num {
            text-align: right;
        }

        .sig-line {
            border-top: 1px solid #1e293b;
            margin-top: 50px;
            width: 260px;
            padding-top: 4px;
            font-size: 10px;
        }

        .page-break {
            page-break-before: always;
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
                <div class="meta">Judge Scoresheet &middot; Judge: <strong>{{ $judge->name }}</strong>
                    @if($event->event_date)
                    &middot; {{ $event->event_date->format('F j, Y') }} @endif
                </div>
            </td>
        </tr>
    </table>


    @php
        $totalSpecialAwards = $event->specialAwards->count();
        $specialAwardsByContestant = [];
        foreach ($event->specialAwards as $award) {
            if ($award->winner_contestant_id) {
                $specialAwardsByContestant[$award->winner_contestant_id] =
                    ($specialAwardsByContestant[$award->winner_contestant_id] ?? 0) + 1;
            }
        }
    @endphp

    @foreach($event->segments as $segment)
        <div class="segment-title">{{ $segment->name }} <span style="font-weight:normal;font-size:11px;">(worth
                {{ $segment->weight }}% of overall)</span></div>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Contestant</th>
                    @foreach($segment->criteria as $criterion)
                        @php
                            $isAuto = $criterion->isSpecialAward();
                            $suffix = '';
                            if ($isAuto) {
                                $suffix = '<br><span style="font-weight:normal;color:#4f46e5;">[AUTO]</span>';
                            }
                        @endphp
                        <th class="num">{{ $criterion->name }}<br><span style="font-weight:normal;">max
                                {{ rtrim(rtrim($criterion->max_score, '0'), '.') }} / wt {{ $criterion->weight }}%</span>{!! $suffix !!}</th>
                    @endforeach
                    <th class="num">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($event->contestants as $contestant)
                    <tr>
                        <td>{{ $contestant->number }}</td>
                        <td>{{ $contestant->name }}</td>
                        @php $rowTotal = 0;
                        $anyScore = false; @endphp
                        @foreach($segment->criteria as $criterion)
                            @php
                                $isAuto = $criterion->isSpecialAward();
                                $displayValue = '';
                                $scoreValue = null;

                                if ($isAuto) {
                                    $won = $specialAwardsByContestant[$contestant->id] ?? 0;
                                    $ratio = $totalSpecialAwards > 0 ? ($won / $totalSpecialAwards) : 0;
                                    $scoreValue = round($ratio * (float) $criterion->max_score, 2);
                                    $displayValue = number_format($scoreValue, 2);
                                    $anyScore = true;
                                    $rowTotal += (float) $scoreValue;
                                } else {
                                    $s = $scores->first(fn($row) => $row->criterion_id === $criterion->id && $row->contestant_id === $contestant->id);
                                    if ($s) {
                                        $anyScore = true;
                                        $scoreValue = (float) $s->score;
                                        $rowTotal += $scoreValue;
                                        $displayValue = number_format($scoreValue, 2);
                                    }
                                }
                            @endphp
                            <td class="num" style="{!! $isAuto ? 'color:#4f46e5;' : '' !!}">{{ $displayValue }}</td>
                        @endforeach
                        <td class="num">{{ $anyScore ? number_format($rowTotal, 2) : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="sig-line">Judge's Signature over Printed Name</div>
</body>

</html>