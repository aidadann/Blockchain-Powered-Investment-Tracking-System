@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-darkbg p-4">
    <div class="bg-cardbg p-8 rounded-2xl shadow-2xl border border-gray-700 max-w-md w-full">
        <h2 class="text-3xl font-bold mb-2 text-white text-center">System Login</h2>
        <p class="text-center text-gray-400 mb-8 text-sm">Secure Blockchain Tracking Prototype</p>
        
        <form class="space-y-5" onsubmit="event.preventDefault();">
            <div>
                <label class="block text-sm font-medium text-gray-300">Email Address</label>
                <input type="email" placeholder="name@example.com" class="w-full mt-1.5 bg-gray-800 text-white rounded-lg p-3 border border-gray-600 focus:ring-2 focus:ring-purple-500 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300">Password</label>
                <input type="password" placeholder="••••••••" class="w-full mt-1.5 bg-gray-800 text-white rounded-lg p-3 border border-gray-600 focus:ring-2 focus:ring-purple-500 focus:outline-none transition">
            </div>
            <button type="submit" class="w-full py-3 bg-gray-600 cursor-not-allowed opacity-50 rounded-lg text-white font-semibold mt-4">Login (Disabled)</button>
        </form>

        <div class="mt-8 border-t border-gray-700 pt-6">
            <p class="text-xs text-gray-400 text-center uppercase tracking-wider mb-4 font-semibold">Mock Access / Bypass Auth</p>
            <div class="grid grid-cols-1 gap-3">
                <a href="/dashboard/investor" class="block w-full py-2.5 bg-blue-600 hover:bg-blue-700 rounded-lg text-center font-semibold transition shadow-lg shadow-blue-500/20 text-sm">Log in as Investor</a>
                <a href="/dashboard/admin" class="block w-full py-2.5 bg-purple-600 hover:bg-purple-700 rounded-lg text-center font-semibold transition shadow-lg shadow-purple-500/20 text-sm">Log in as Admin</a>
                <a href="/dashboard/auditor" class="block w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 rounded-lg text-center font-semibold transition shadow-lg shadow-emerald-500/20 text-sm">Log in as Auditor</a>
            </div>
        </div>
    </div>
</div>
@endsection
