@extends('layouts.app')

@section('content')
<!-- Header -->
<header class="bg-cardbg border-b border-gray-700 px-6 py-4 flex justify-between items-center sticky top-0 z-50 shadow-md">
    <div class="flex items-center space-x-3">
        <div class="text-xl font-bold tracking-wider text-white">InvestmentTracker</div>
        <span class="px-2 py-1 text-xs bg-blue-500/20 text-blue-400 rounded-md border border-blue-500/30">Investor Mode</span>
    </div>
    <div class="flex items-center space-x-6">
        <a href="/" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Logout
        </a>
        <div class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold shadow-lg">IV</div>
    </div>
</header>

<div class="p-6 md:p-8 max-w-7xl mx-auto space-y-8">
    <div class="flex justify-between items-end pb-4 border-b border-gray-700/50 flex-col md:flex-row gap-4 md:gap-0">
        <div>
            <h1 class="text-3xl font-extrabold pb-1">Welcome back, Investor</h1>
            <p class="text-gray-400 text-sm">Here is the overview of your active portfolio and pending transactions.</p>
        </div>
        <button class="bg-blue-600 hover:bg-blue-700 px-5 py-2.5 rounded-lg font-semibold shadow-lg shadow-blue-500/20 transition flex items-center space-x-2 text-sm w-full md:w-auto justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            <span>Add Investment</span>
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-cardbg rounded-xl p-6 border border-gray-700 shadow flex justify-between items-center transition hover:border-gray-500">
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-widest font-semibold mb-1">Total Investment</p>
                <p class="text-3xl font-bold text-white">$350.00 M</p>
            </div>
            <div class="text-4xl opacity-80">📈</div>
        </div>
        <div class="bg-cardbg rounded-xl p-6 border border-gray-700 shadow flex justify-between items-center transition hover:border-gray-500">
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-widest font-semibold mb-1">Total Balance</p>
                <p class="text-3xl font-bold text-white">$120.45 M</p>
            </div>
            <div class="text-4xl opacity-80">💎</div>
        </div>
        <div class="bg-cardbg rounded-xl p-6 border border-gray-700 shadow flex justify-between items-center transition hover:border-gray-500">
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-widest font-semibold mb-1">Pending Approval</p>
                <p class="text-3xl font-bold text-blue-400">3</p>
            </div>
            <div class="text-4xl opacity-80">⏳</div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-4">
        
        <!-- Left Col: Active Investments -->
        <div class="col-span-1 lg:col-span-1 border border-gray-700 rounded-xl bg-cardbg overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700 bg-gray-800/50 flex justify-between items-center">
                <h3 class="font-semibold text-gray-200">Active Investments</h3>
            </div>
            <div class="p-0 overflow-y-auto max-h-[350px]">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-400 uppercase bg-gray-800 border-b border-gray-700">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-medium">Name</th>
                            <th scope="col" class="px-5 py-3 font-medium text-right">Total ($K)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-700 hover:bg-gray-800/50">
                            <td class="px-5 py-4 font-medium text-white">Iskandar Ventures</td>
                            <td class="px-5 py-4 text-right">168.71</td>
                        </tr>
                        <tr class="border-b border-gray-700 hover:bg-gray-800/50">
                            <td class="px-5 py-4 font-medium text-white">Puteri Iskandar</td>
                            <td class="px-5 py-4 text-right">233.99</td>
                        </tr>
                        <tr class="border-b border-gray-700 hover:bg-gray-800/50">
                            <td class="px-5 py-4 font-medium text-white">EcoSave</td>
                            <td class="px-5 py-4 text-right">2,545.06</td>
                        </tr>
                        <tr class="hover:bg-gray-800/50">
                            <td class="px-5 py-4 font-medium text-white">Aeon</td>
                            <td class="px-5 py-4 text-right">261.11</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Col: Main Data Table representation -->
        <div class="col-span-1 lg:col-span-2 border border-gray-700 rounded-xl bg-cardbg overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700 bg-gray-800/50 flex justify-between items-center">
                <h3 class="font-semibold text-gray-200">Recent Transactions</h3>
            </div>
            <div class="p-0 overflow-y-auto">
                <table class="w-full text-sm text-left text-gray-300">
                    <thead class="text-xs text-gray-400 uppercase bg-gray-800 border-b border-gray-700">
                        <tr>
                            <th scope="col" class="px-5 py-3">Asset Name</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                            <th scope="col" class="px-5 py-3">Quantity</th>
                            <th scope="col" class="px-5 py-3">Price</th>
                            <th scope="col" class="px-5 py-3 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row 1 -->
                        <tr class="border-b border-gray-700 hover:bg-gray-800/50 bg-gray-800/20">
                            <td class="px-5 py-4 font-medium text-white">Giant</td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-1 text-xs bg-green-500/10 text-green-400 border border-green-500/20 rounded">APPROVED</span>
                            </td>
                            <td class="px-5 py-4">807.766</td>
                            <td class="px-5 py-4">$70.52</td>
                            <td class="px-5 py-4 text-right">06/26/2024</td>
                        </tr>
                        <!-- Row 2 -->
                        <tr class="border-b border-gray-700 hover:bg-gray-800/50">
                            <td class="px-5 py-4 font-medium text-white">Aeon</td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-1 text-xs bg-red-500/10 text-red-400 border border-red-500/20 rounded">REJECTED</span>
                            </td>
                            <td class="px-5 py-4">-</td>
                            <td class="px-5 py-4">-</td>
                            <td class="px-5 py-4 text-right">06/20/2024</td>
                        </tr>
                        <!-- Row 3 -->
                        <tr class="hover:bg-gray-800/50">
                            <td class="px-5 py-4 font-medium text-white">EcoSave</td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-1 text-xs bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 rounded">PENDING</span>
                            </td>
                            <td class="px-5 py-4">0.37360</td>
                            <td class="px-5 py-4">$125.00</td>
                            <td class="px-5 py-4 text-right">06/16/2024</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
