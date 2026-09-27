<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate — {{ $contestant->name }}</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            color: #1e293b;
            text-align: center;
            padding: 60px 80px;
        }
        .border {
            border: 3px double #b45309;
            padding: 50px 40px;
            height: 460px;
        }
        .kicker { letter-spacing: 4px; font-size: 12px; color: #b45309; text-transform: uppercase; margin-bottom: 10px; }
        h1 { font-size: 34px; margin: 0 0 30px; color: #1e293b; }
        .presented { font-size: 13px; color: #64748b; }
        .name { font-size: 30px; font-weight: bold; margin: 14px 0 10px; border-bottom: 1px solid #cbd5e1; display: inline-block; padding-bottom: 8px; }
        .placement { font-size: 20px; color: #b45309; font-weight: bold; margin-top: 6px; }
        .desc { margin-top: 20px; font-size: 13px; color: #475569; max-width: 480px; margin-left: auto; margin-right: auto; }
        .sigs { margin-top: 50px; width: 100%; }
        .sig-box { display: inline-block; width: 200px; margin: 0 30px; }
        .sig-line { border-top: 1px solid #1e293b; padding-top: 6px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="border">
        <div class="kicker">Certificate of Recognition</div>
        <h1>{{ $event->name }}</h1>

        <div class="presented">This certificate is proudly presented to</div>
        <div class="name">{{ $contestant->name }}</div>
        @if($contestant->representing)
        <div style="font-size:12px;color:#94a3b8;">representing {{ $contestant->representing }}</div>
        @endif

        @if($placement)
        <div class="placement">
            @php
                $ord = match($placement['place']) { 1 => '1st Place / Winner', 2 => '2nd Place', 3 => '3rd Place', default => $placement['place'].'th Place' };
            @endphp
            {{ $ord }}
        </div>
        @endif

        <div class="desc">
            In recognition of outstanding participation and performance in {{ $event->name }}@if($event->event_date), held {{ $event->event_date->format('F j, Y') }}@endif.
        </div>

        <div class="sigs">
            <div class="sig-box"><div class="sig-line">Event Coordinator</div></div>
            <div class="sig-box"><div class="sig-line">Board of Judges Chair</div></div>
        </div>
    </div>
</body>
</html>
