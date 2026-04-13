@extends('layouts.app')

@section('content')
<!-- Header -->
<header class="bg-cardbg border-b border-gray-700 px-6 py-4 flex justify-between items-center sticky top-0 z-50 shadow-md">
    <div class="flex items-center space-x-3">
        <div class="text-xl font-bold tracking-wider text-white">InvestmentTracker</div>
        <span class="px-2 py-1 text-xs bg-purple-500/20 text-purple-400 rounded-md border border-purple-500/30">Admin Mode</span>
    </div>
    <div class="flex items-center space-x-6">
        <a href="/" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Logout
        </a>
        <div class="w-9 h-9 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold shadow-lg">AD</div>
    </div>
</header>

<div class="p-6 md:p-8 max-w-7xl mx-auto space-y-6">
    <div class="pb-4 border-b border-gray-700/50">
        <h1 class="text-3xl font-extrabold pb-1">Welcome back, Admin</h1>
        <p class="text-gray-400 text-sm">System oversight, user management, and pending approval queue.</p>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column -->
        <div class="col-span-1 space-y-6">
            <!-- Stats overview mock -->
            <div class="border border-gray-700 rounded-xl bg-cardbg p-6 text-center shadow-md">
                <h3 class="font-semibold text-gray-200 mb-6 border-b border-gray-700 pb-2">Approval Statistics</h3>
                
                <div class="relative w-40 h-40 mx-auto rounded-full border-[16px] border-gray-700 flex items-center justify-center">
                    <!-- Simple CSS circles representation -->
                    <div class="absolute inset-0 rounded-full border-[16px] border-t-yellow-400 border-r-green-500 border-b-red-500 border-l-gray-700" style="transform: rotate(45deg);"></div>
                    <div class="absolute inset-0 m-auto w-24 h-24 bg-cardbg rounded-full flex flex-col justify-center items-center z-10" style="transform: rotate(-45deg);">
                        <span class="text-4xl font-extrabold text-white">64</span>
                        <span class="text-xs text-gray-400 uppercase">Requests</span>
                    </div>
                </div>

                <div class="flex justify-center space-x-4 mt-8 text-xs font-semibold">
                    <span class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-sm bg-green-500"></div> Approved</span>
                    <span class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-sm bg-red-500"></div> Rejected</span>
                    <span class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-sm bg-yellow-400"></div> Pending</span>
                </div>
            </div>

            <!-- Notifications mock -->
            <div class="border border-gray-700 rounded-xl bg-cardbg p-5 shadow-md">
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-700 text-sm">
                    <span class="font-semibold">Recent Alerts</span>
                    <span class="bg-blue-600 text-white text-xs px-2 py-0.5 rounded-full">4 New</span>
                </div>
                <ul class="space-y-4 text-xs text-gray-400">
                    <li class="flex gap-2"><span class="text-orange-400">•</span> Investment on Iskandar Ventures is pending approval.</li>
                    <li class="flex gap-2"><span class="text-orange-400">•</span> Investment on AEON flag raised.</li>
                </ul>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-span-1 lg:col-span-2 space-y-6">
            
            <!-- Pending Approvals -->
            <div class="border border-gray-700 rounded-xl bg-cardbg shadow-md">
                <div class="px-5 py-4 border-b border-gray-700 bg-gray-800/50">
                    <h3 class="font-semibold text-gray-200">Pending Approval Action Required</h3>
                </div>
                <div class="p-2 space-y-1 max-h-[300px] overflow-y-auto">
                    <!-- Item -->
                    <div class="flex items-center justify-between p-3 hover:bg-gray-800/50 rounded-lg transition">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-gray-600 rounded-full"></div>
                            <div>
                                <p class="text-sm font-semibold text-white">Kristin Watson</p>
                                <p class="text-xs text-blue-400">Project: Iskandar Venture</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="bg-green-600 hover:bg-green-500 px-3 py-1.5 rounded text-xs font-semibold text-white transition">Approve</button>
                            <button class="bg-red-600 hover:bg-red-500 px-3 py-1.5 rounded text-xs font-semibold text-white transition">Reject</button>
                        </div>
                    </div>
                    <!-- Item -->
                    <div class="flex items-center justify-between p-3 hover:bg-gray-800/50 rounded-lg transition">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-gray-600 rounded-full"></div>
                            <div>
                                <p class="text-sm font-semibold text-white">Darlene Robertson</p>
                                <p class="text-xs text-blue-400">Project: AEON</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="bg-green-600 hover:bg-green-500 px-3 py-1.5 rounded text-xs font-semibold text-white transition">Approve</button>
                            <button class="bg-red-600 hover:bg-red-500 px-3 py-1.5 rounded text-xs font-semibold text-white transition">Reject</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manage Users -->
            <div class="border border-gray-700 rounded-xl bg-cardbg shadow-md">
                <div class="px-5 py-4 border-b border-gray-700 bg-gray-800/50 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-200">Manage Users</h3>
                    <button class="text-xs text-blue-400 hover:text-blue-300">View All</button>
                </div>
                <div class="p-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div class="flex items-center space-x-3 p-3 bg-gray-800/30 rounded-lg">
                        <div class="w-8 h-8 font-bold bg-gray-700 rounded-full flex items-center justify-center text-xs">JW</div>
                        <span class="text-sm">Jenny Wilson</span>
                    </div>
                    <div class="flex items-center space-x-3 p-3 bg-gray-800/30 rounded-lg">
                        <div class="w-8 h-8 font-bold bg-gray-700 rounded-full flex items-center justify-center text-xs">JD</div>
                        <span class="text-sm">John Doe</span>
                    </div>
                    <div class="flex items-center space-x-3 p-3 bg-gray-800/30 rounded-lg">
                        <div class="w-8 h-8 font-bold bg-gray-700 rounded-full flex items-center justify-center text-xs">MA</div>
                        <span class="text-sm">Mark Admin</span>
                    </div>
                    <div class="flex items-center space-x-3 p-3 bg-gray-800/30 rounded-lg">
                        <div class="w-8 h-8 font-bold bg-gray-700 rounded-full flex items-center justify-center text-xs">KW</div>
                        <span class="text-sm">Kristin Watson</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
