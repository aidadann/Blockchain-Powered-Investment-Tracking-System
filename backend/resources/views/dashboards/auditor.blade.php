@extends('layouts.app')

@section('content')
<!-- Header -->
<header class="bg-cardbg border-b border-gray-700 px-6 py-4 flex justify-between items-center sticky top-0 z-50 shadow-md">
    <div class="flex items-center space-x-3">
        <div class="text-xl font-bold tracking-wider text-white">InvestmentTracker</div>
        <span class="px-2 py-1 text-xs bg-emerald-500/20 text-emerald-400 rounded-md border border-emerald-500/30">Auditor Mode</span>
    </div>
    <div class="flex items-center space-x-6">
        <a href="/" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Logout
        </a>
        <div class="w-9 h-9 bg-emerald-600 rounded-full flex items-center justify-center text-white font-bold shadow-lg">AU</div>
    </div>
</header>

<div class="p-6 md:p-8 max-w-7xl mx-auto space-y-6">
    <div class="flex justify-between items-end pb-4 border-b border-gray-700/50 flex-col md:flex-row gap-4 md:gap-0">
        <div>
            <h1 class="text-3xl font-extrabold pb-1">Welcome back, Auditor</h1>
            <p class="text-gray-400 text-sm">System integrity checks, compliance logs, and reporting center.</p>
        </div>
        <div class="flex space-x-3 w-full md:w-auto">
            <button class="bg-gray-700 border border-gray-600 hover:bg-gray-600 px-4 py-2.5 rounded-lg text-sm font-semibold shadow transition flex items-center justify-center space-x-2 flex-1 md:flex-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Export PDF</span>
            </button>
            <button class="bg-gray-700 border border-gray-600 hover:bg-gray-600 px-4 py-2.5 rounded-lg text-sm font-semibold shadow transition flex items-center justify-center space-x-2 flex-1 md:flex-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Export CSV</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        
        <!-- Left: User list -->
        <div class="col-span-1 lg:col-span-2 border border-gray-700 bg-cardbg rounded-xl shadow-md h-[500px] flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700 bg-gray-800/50">
                <h3 class="font-semibold text-gray-200">User Trace</h3>
            </div>
            <div class="p-2 overflow-y-auto flex-1">
                <div class="space-y-1">
                    <div class="flex items-center p-3 hover:bg-gray-800/60 rounded cursor-pointer bg-gray-800/30">
                        <div class="w-8 h-8 rounded-full bg-gray-600 mr-3"></div>
                        <span class="text-sm font-medium">Kristin Watson</span>
                    </div>
                    <div class="flex items-center p-3 hover:bg-gray-800/60 rounded cursor-pointer">
                        <div class="w-8 h-8 rounded-full bg-gray-600 mr-3"></div>
                        <span class="text-sm font-medium">Darlene Robertson</span>
                    </div>
                    <div class="flex items-center p-3 hover:bg-gray-800/60 rounded cursor-pointer">
                        <div class="w-8 h-8 rounded-full bg-gray-600 mr-3"></div>
                        <span class="text-sm font-medium">Jenny Wilson</span>
                    </div>
                    <div class="flex items-center p-3 hover:bg-gray-800/60 rounded cursor-pointer">
                        <div class="w-8 h-8 rounded-full bg-gray-600 mr-3"></div>
                        <span class="text-sm font-medium">John Doe</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Audit Logs -->
        <div class="col-span-1 lg:col-span-3 border border-gray-700 bg-cardbg rounded-xl shadow-md h-[500px] flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700 bg-gray-800/50 flex justify-between items-center">
                <h3 class="font-semibold text-gray-200">System Log Trail</h3>
                <span class="text-xs text-gray-400 font-mono flex items-center"><div class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></div> Live View</span>
            </div>
            <div class="p-0 overflow-y-auto flex-1">
                <table class="w-full text-xs text-left font-mono">
                    <tbody>
                        <tr class="border-b border-gray-800 hover:bg-gray-800/30">
                            <td class="px-5 py-3 text-gray-500 w-32 border-r border-gray-800">May 21, 14:32</td>
                            <td class="px-5 py-3 text-blue-400">Jenny Wilson invested $50.0K in Iskandar.</td>
                        </tr>
                        <tr class="border-b border-gray-800 hover:bg-gray-800/30">
                            <td class="px-5 py-3 text-gray-500 w-32 border-r border-gray-800">May 21, 12:15</td>
                            <td class="px-5 py-3 text-red-400">Admin [AD-01] rejected transaction TX-891.</td>
                        </tr>
                        <tr class="border-b border-gray-800 hover:bg-gray-800/30">
                            <td class="px-5 py-3 text-gray-500 w-32 border-r border-gray-800">May 21, 10:05</td>
                            <td class="px-5 py-3 text-emerald-400">Smart Contract verified for wallet 0xabc...123.</td>
                        </tr>
                        <tr class="border-b border-gray-800 hover:bg-gray-800/30">
                            <td class="px-5 py-3 text-gray-500 w-32 border-r border-gray-800">May 20, 18:44</td>
                            <td class="px-5 py-3 text-gray-300">Kristin Watson changed account password.</td>
                        </tr>
                        <tr class="border-b border-gray-800 hover:bg-gray-800/30 bg-gray-800/20">
                            <td class="px-5 py-3 text-gray-500 w-32 border-r border-gray-800">May 20, 15:20</td>
                            <td class="px-5 py-3 text-red-500">FAILED: Investment submitted with missing timestamp. Flagged.</td>
                        </tr>
                         <tr class="border-b border-gray-800 hover:bg-gray-800/30">
                            <td class="px-5 py-3 text-gray-500 w-32 border-r border-gray-800">May 20, 09:12</td>
                            <td class="px-5 py-3 text-yellow-400">Darlene Robertson created investment. Pending.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>
@endsection
