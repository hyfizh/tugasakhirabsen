<x-admin-layout>
    <div class="space-y-6 pb-6" x-data="{ searchQuery: '', selectedEvent: '' }">
        
        <!-- Header Title & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Log Audit Sistem</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Monitor comprehensive security events, authentication trails, and IoT sensor activities across the campus network.</p>
            </div>

            <!-- Export CSV Button -->
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-sm transition-all whitespace-nowrap">
                <i class="fa-solid fa-download mr-2 text-xs text-indigo-600"></i> Export CSV
            </button>
        </div>

        <!-- Search & Filter Bar Card -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                <!-- Search Input Box -->
                <div class="relative w-full sm:w-72">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" x-model="searchQuery" placeholder="Search actors or actions..." 
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none">
                </div>

                <!-- Event Type Filter -->
                <div class="w-full sm:w-auto">
                    <select x-model="selectedEvent" class="w-full sm:w-48 px-4 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                        <option value="">All Events</option>
                        <option value="alert">Alerts Only</option>
                        <option value="warning">Warnings Only</option>
                        <option value="info">Info Logs</option>
                    </select>
                </div>
            </div>

            <!-- Date Range Filter -->
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-medium">
                <div class="flex items-center space-x-2 bg-slate-50/50 border border-slate-200 rounded-xl px-3 py-2">
                    <i class="fa-regular fa-calendar text-slate-400"></i>
                    <span class="font-mono text-slate-700">Oct 26, 2023</span>
                </div>
                <span>-</span>
                <div class="flex items-center space-x-2 bg-slate-50/50 border border-slate-200 rounded-xl px-3 py-2">
                    <i class="fa-regular fa-calendar text-slate-400"></i>
                    <span class="font-mono text-slate-700">Oct 27, 2023</span>
                </div>
            </div>

        </div>

        <!-- Audit Log Table Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">TIMESTAMP</th>
                            <th class="py-4 px-6">USER / DEVICE ACTOR</th>
                            <th class="py-4 px-6">ACTION EXECUTED</th>
                            <th class="py-4 px-6">CONTEXT</th>
                            <th class="py-4 px-6 text-center">SEVERITY</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        
                        @if ($logs->isNotEmpty())
                            @foreach ($logs as $log)
                                @php
                                    $isAlert = str_contains(strtoupper($log->tipe_log), 'DENIED') || str_contains(strtoupper($log->tipe_log), 'ALERT');
                                    $isWarning = str_contains(strtoupper($log->tipe_log), 'WARN') || str_contains(strtoupper($log->tipe_log), 'FAIL');
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors"
                                    x-show="(searchQuery === '' || '{{ strtolower($log->deskripsi . ' ' . $log->tipe_log . ' ' . $log->ip_address) }}'.includes(searchQuery.toLowerCase()))">
                                    
                                    <!-- Timestamp -->
                                    <td class="py-4 px-6 font-mono text-slate-500 text-xs whitespace-nowrap">
                                        {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '2023-10-27 15:42:11' }}
                                    </td>

                                    <!-- User / Device Actor -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-3">
                                            @if ($isAlert)
                                                <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xs shadow-sm">
                                                    <i class="fa-solid fa-ban"></i>
                                                </div>
                                            @elseif ($isWarning)
                                                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xs shadow-sm">
                                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                                </div>
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center text-xs shadow-sm">
                                                    <i class="fa-solid fa-microchip"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <span class="font-extrabold text-slate-900 text-xs block leading-tight">{{ $log->tipe_log }}</span>
                                                <span class="text-[10px] text-slate-400 font-mono block mt-0.5">IP: {{ $log->ip_address ?: '192.168.1.104' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Action Executed -->
                                    <td class="py-4 px-6 font-extrabold text-slate-800 text-xs">
                                        {{ $log->deskripsi }}
                                    </td>

                                    <!-- Context -->
                                    <td class="py-4 px-6 text-slate-500 text-xs">
                                        <span class="block">Node: IoT Gate Sensor</span>
                                        <span class="text-[10px] text-slate-400 font-mono block">Port 22 SSH</span>
                                    </td>

                                    <!-- Severity Badge -->
                                    <td class="py-4 px-6 text-center">
                                        @if ($isAlert)
                                            <span class="px-3 py-1 bg-rose-50 text-rose-600 border border-rose-200/80 font-extrabold text-[10px] rounded-full inline-flex items-center">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span> Alert
                                            </span>
                                        @elseif ($isWarning)
                                            <span class="px-3 py-1 bg-amber-100/70 text-amber-700 font-bold text-[10px] rounded-full inline-flex items-center">
                                                Warning
                                            </span>
                                        @else
                                            <span class="px-3 py-1 bg-indigo-50 text-indigo-600 font-bold text-[10px] rounded-full inline-flex items-center">
                                                Info
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="py-12 px-6 text-center text-slate-400">
                                    <i class="fa-solid fa-folder-open text-4xl mb-2 text-slate-300 block"></i>
                                    Belum ada data log aktivitas tersimpan di MySQL database.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Table Footer & Pagination -->
            <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500 font-medium">
                <div>
                    Menampilkan {{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} log audit
                </div>

                <!-- Real Laravel Pagination Links -->
                <div>
                    {{ $logs->links() }}
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
