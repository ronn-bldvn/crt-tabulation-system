@extends('layouts.app')

@section('title', 'Change Password')

@section('content')

<div class="mx-auto max-w-lg">

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="bg-[#0A2668] px-6 py-5">
            <h2 class="text-xl font-bold text-white">
                Change Password
            </h2>
            <p class="mt-1 text-sm text-[#768BC1]">
                Update your account password for <strong class="text-white">{{ auth()->user()->email }}</strong>
            </p>
        </div>

        {{-- Form --}}
        <form
            method="POST"
            action="{{ route('password.update') }}"
            class="space-y-5 p-6"
        >

            @csrf
            @method('PUT')

            {{-- Current Password --}}
            <div>
                <label
                    for="current_password"
                    class="block text-sm font-semibold text-slate-700"
                >
                    Current Password
                </label>

                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    required
                    autocomplete="current-password"
                    class="mt-1.5 w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-slate-900 outline-none transition focus:border-[#04309A] focus:bg-white focus:ring-2 focus:ring-[#768BC1]/30"
                >

                @error('current_password')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- New Password --}}
            <div>
                <label
                    for="password"
                    class="block text-sm font-semibold text-slate-700"
                >
                    New Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="mt-1.5 w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-slate-900 outline-none transition focus:border-[#04309A] focus:bg-white focus:ring-2 focus:ring-[#768BC1]/30"
                >

                @error('password')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @else
                    <p class="mt-1 text-xs text-slate-500">
                        At least 8 characters with uppercase, lowercase, number, and symbol.
                    </p>
                @enderror
            </div>

            {{-- Confirm New Password --}}
            <div>
                <label
                    for="password_confirmation"
                    class="block text-sm font-semibold text-slate-700"
                >
                    Confirm New Password
                </label>

                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="mt-1.5 w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-slate-900 outline-none transition focus:border-[#04309A] focus:bg-white focus:ring-2 focus:ring-[#768BC1]/30"
                >
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">

                <a
                    href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('judge.events') }}"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-[#04309A] px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-[#0A2668] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#768BC1] focus:ring-offset-2"
                >
                    Update Password
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
