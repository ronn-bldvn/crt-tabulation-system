<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">


<title>@yield('title', 'CRT SAN JOSE - Tabulation System')</title>

<link rel="icon" type="image/x-icon" href="{{ asset('uploads/fav.ico') }}">

<meta name="csrf-token" content="{{ csrf_token() }}">

@vite(['resources/css/app.css', 'resources/js/app.js'])


</head>

<body class="min-h-screen text-slate-900 antialiased">


{{-- Background Image --}}
<div
    class="fixed inset-0 -z-20 bg-cover bg-center bg-no-repeat"
    style="background-image: url('{{ asset('uploads/CRT.jpg') }}');"
></div>

{{-- Background Overlay --}}
<div class="fixed inset-0 -z-10 bg-[#0A2668]/25"></div>

@auth
<nav class="bg-[#0A2668] text-white shadow-lg">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="flex h-16 items-center justify-between">

            <div class="flex items-center gap-8">

                <a
                    href="{{ auth()->user()->isAdmin()
                        ? route('admin.dashboard')
                        : route('judge.events') }}"
                    class="text-lg font-bold tracking-tight"
                >
                    <span class="text-[#EFF232]">🏆</span>
                    CRT SAN JOSE
                    <span class="font-normal text-[#768BC1]">
                        | Tabulation System
                    </span>
                </a>

                @if(auth()->user()->isAdmin())
                <div class="hidden gap-6 text-sm font-medium text-[#768BC1] sm:flex">

                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="transition hover:text-[#EFF232]"
                    >
                        Events
                    </a>

                    <a
                        href="{{ route('admin.judges.index') }}"
                        class="transition hover:text-[#EFF232]"
                    >
                        Judges
                    </a>

                </div>
                @endif

            </div>

            <div class="flex items-center gap-4 text-sm">

                <span class="text-[#768BC1]">
                    {{ auth()->user()->name }}

                    <span class="text-[#EFF232]">
                        ({{ auth()->user()->role }})
                    </span>
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        class="rounded-lg bg-[#04309A] px-3 py-1.5 font-medium transition hover:bg-[#900504]"
                    >
                        Log out
                    </button>
                </form>

            </div>

        </div>

    </div>
</nav>
@endauth

<main class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    {{-- Success Message --}}
    @if (session('status'))
        <div
            class="mb-6 rounded-lg border border-[#C6C72D] bg-[#EFF232]/90 px-4 py-3 text-sm text-[#0A2668] shadow"
        >
            {{ session('status') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div
            class="mb-6 rounded-lg border border-[#900504]/30 bg-white/95 px-4 py-3 text-sm text-[#900504] shadow"
        >
            <ul class="list-inside list-disc">

                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>
        </div>
    @endif

    @yield('content')

</main>


</body>
</html>
