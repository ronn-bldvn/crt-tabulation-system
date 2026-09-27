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

<body class="min-h-screen bg-[#f4f6fb] text-slate-900 antialiased">

    @auth
    <nav class="bg-[#0A2668] text-white shadow-lg">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">

                <div class="flex items-center gap-8">

                    <a href="{{ auth()->user()->isAdmin()
                        ? route('admin.dashboard')
                        : route('judge.events') }}"
                       class="flex items-center gap-2 text-lg font-bold tracking-tight">

                        <div class="mx-auto mb-4 flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-white shadow-lg">
                            <img
                                src="{{ asset('uploads/favicon.png') }}"
                                alt="CRT San Jose Logo"
                                class="h-10 w-10 object-contain"
                            >
                        </div>

                        <span>
                            CRT SAN JOSE
                            <span class="font-normal text-[#768BC1]">
                                | Tabulation
                            </span>
                        </span>

                    </a>

                    @if(auth()->user()->isAdmin())
                    <div class="hidden gap-6 text-sm font-medium text-[#768BC1] sm:flex">

                        <a href="{{ route('admin.dashboard') }}"
                           class="transition hover:text-[#EFF232]">
                            Events
                        </a>

                        <a href="{{ route('admin.judges.index') }}"
                           class="transition hover:text-[#EFF232]">
                            Judges
                        </a>

                    </div>
                    @endif

                </div>

                <div class="flex items-center gap-3 text-sm">

                    <span class="hidden text-[#768BC1] sm:inline">
                        {{ auth()->user()->name }}

                        <span class="text-[#EFF232]">
                            ({{ auth()->user()->role }})
                        </span>
                    </span>

                    <a
                        href="{{ route('password.change') }}"
                        class="hidden sm:inline-flex rounded-lg border border-[#768BC1]/30 bg-[#0A2668] px-3 py-1.5 font-medium text-[#EFF232] transition hover:bg-[#04309A]"
                    >
                        Change Password
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button
                            class="rounded-lg border border-[#768BC1]/30 bg-[#04309A] px-3 py-1.5 font-medium transition hover:bg-[#900504]">
                            Log out
                        </button>
                    </form>

                </div>

            </div>
        </div>
    </nav>
    @endauth

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        @if (session('status'))
            <div class="mb-6 rounded-lg border border-[#C6C72D] bg-[#EFF232]/20 px-4 py-3 text-sm text-[#0A2668]">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-[#900504]/30 bg-red-50 px-4 py-3 text-sm text-[#900504]">
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