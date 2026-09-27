@extends('layouts.login')

@section('title', 'CRT SAN JOSE - Tabulation System')

@section('content')

<div class="relative flex min-h-[calc(100vh-4rem)] items-center justify-center overflow-hidden py-12">

{{-- Background Overlay --}}
{{-- <div class="absolute inset-0 bg-[#0A2668]/75"></div> --}}

{{-- Login Card --}}
<div class="relative z-10 w-full max-w-md px-4">

    <div class="overflow-hidden rounded-2xl border border-white/20 bg-white/95 shadow-2xl backdrop-blur-sm">

        {{-- Header --}}
        <div class="bg-[#0A2668] px-8 py-8 text-center">

            {{-- Logo --}}
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-white shadow-lg">

                <img
                    src="{{ asset('uploads/favicon.png') }}"
                    alt="CRT San Jose Logo" 
                    class="h-full w-full object-contain"
                >

            </div>

            <h1 class="text-2xl font-bold text-white">
                CRT SAN JOSE
            </h1>

            <p class="mt-1 text-sm font-medium text-[#768BC1]">
                Tabulation System
            </p>

        </div>

        {{-- Form --}}
        <div class="p-8">

            <div class="mb-6">
                <h2 class="text-xl font-bold text-[#0A2668]">
                    Welcome Back
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Sign in to continue
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('login') }}"
                class="space-y-5"
            >

                @csrf

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-semibold text-[#0A2668]">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="mt-1.5 w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-slate-900 outline-none transition focus:border-[#04309A] focus:bg-white focus:ring-2 focus:ring-[#768BC1]/30"
                    >
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-sm font-semibold text-[#0A2668]">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        required
                        class="mt-1.5 w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-slate-900 outline-none transition focus:border-[#04309A] focus:bg-white focus:ring-2 focus:ring-[#768BC1]/30"
                    >
                </div>

                {{-- Remember Me --}}
                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600">

                    <input
                        type="checkbox"
                        name="remember"
                        class="h-4 w-4 rounded border-slate-300 text-[#04309A] focus:ring-[#768BC1]"
                    >

                    <span>
                        Remember me
                    </span>

                </label>

                {{-- Sign In --}}
                <button
                    type="submit"
                    class="w-full rounded-lg bg-[#04309A] px-4 py-3 font-bold text-white shadow-md transition hover:bg-[#0A2668] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#768BC1] focus:ring-offset-2"
                >
                    Sign in
                </button>

            </form>

        </div>

        {{-- Accent --}}
        <div class="h-1.5 bg-[#EFF232]"></div>

    </div>

    <p class="mt-5 text-center text-xs font-medium text-white/80">
        CRT SAN JOSE • Pageant Tabulation System
    </p>

</div>
```

</div>

@endsection
