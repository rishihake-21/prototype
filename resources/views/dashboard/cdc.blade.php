<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            CDC Incharge Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border border-gray-100 transition hover:shadow-md">
                    <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Total Programmes</div>
                    <div class="text-4xl font-black text-indigo-600">{{ $stats['total_programmes'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border border-gray-100 transition hover:shadow-md">
                    <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Active Curricula</div>
                    <div class="text-4xl font-black text-emerald-600">{{ $stats['active_programmes'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border border-gray-100 transition hover:shadow-md">
                    <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Total Courses Defined</div>
                    <div class="text-4xl font-black text-teal-600">{{ $stats['total_courses'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Programme Management -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden border border-gray-100">
                        <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Recent Programmes</h3>
                            <a href="{{ route('cdc.programmes.create') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                                + New Programme
                            </a>
                        </div>
                        <div class="p-0">
                            @if($recentProgrammes->count() > 0)
                                <table class="min-w-full divide-y divide-gray-100">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none">Programme Name</th>
                                            <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none text-center">Year</th>
                                            <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none text-center">Status</th>
                                            <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @foreach($recentProgrammes as $prog)
                                            <tr class="hover:bg-indigo-50/30 transition">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-bold text-gray-900 leading-none">{{ $prog->name }}</div>
                                                    <div class="text-[10px] text-gray-400 mt-1 font-mono uppercase">{{ $prog->code }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="text-xs font-medium text-gray-600">{{ $prog->academic_year }}</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full border
                                                        {{ $prog->isActive() ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-gray-50 text-gray-600 border-gray-100' }}">
                                                        {{ strtoupper($prog->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="{{ route('cdc.programmes.show', $prog) }}" class="text-indigo-600 hover:text-indigo-900 font-bold transition">Manage</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="px-6 py-10 text-center">
                                    <p class="text-sm text-gray-500">No programmes created yet.</p>
                                    <a href="{{ route('cdc.programmes.create') }}" class="inline-block mt-4 text-xs font-bold text-indigo-600 border-b border-indigo-200 pb-0.5">Start by creating your first programme</a>
                                </div>
                            @endif
                        </div>
                        <div class="px-6 py-3 bg-gray-50/30 border-t border-gray-50 text-right">
                            <a href="{{ route('cdc.programmes.index') }}" class="text-[10px] font-bold text-gray-400 hover:text-indigo-600 transition uppercase tracking-widest">View All Programmes →</a>
                        </div>
                    </div>
                </div>

                <!-- Guidance / Sidebar -->
                <div class="space-y-6">
                    <div class="bg-indigo-600 rounded-xl p-6 text-white shadow-lg shadow-indigo-100">
                        <h4 class="text-lg font-black leading-tight mb-2">Curriculum Workflow</h4>
                        <p class="text-indigo-100 text-xs leading-relaxed mb-4">Follow these steps carefully to ensure a consistent programme structure.</p>
                        
                        <div class="space-y-4">
                            <div class="flex gap-3">
                                <div class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center text-[10px] font-black shrink-0">1</div>
                                <div>
                                    <div class="text-xs font-bold leading-none">Programme Definition</div>
                                    <div class="text-[10px] text-indigo-200 mt-1">Set code, name and academic year.</div>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center text-[10px] font-black shrink-0">2</div>
                                <div>
                                    <div class="text-xs font-bold leading-none">Scheme at a Glance</div>
                                    <div class="text-[10px] text-indigo-200 mt-1">Define credits and hours budget per level.</div>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center text-[10px] font-black shrink-0">3</div>
                                <div>
                                    <div class="text-xs font-bold leading-none">Course Entry</div>
                                    <div class="text-[10px] text-indigo-200 mt-1">Populate levels with 6-digit courses.</div>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center text-[10px] font-black shrink-0">4</div>
                                <div>
                                    <div class="text-xs font-bold leading-none">Mapping & Sequence</div>
                                    <div class="text-[10px] text-indigo-200 mt-1">Assign to terms and Award of Class.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-gray-100">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">CDC Resources</h4>
                        <ul class="space-y-3">
                            <li>
                                <a href="{{ route('cdc.programmes.index') }}" class="group flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-700 group-hover:text-indigo-600 transition">Manage All Structures</span>
                                    <svg class="w-3 h-3 text-gray-300 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                            </li>
                             <li>
                                <a href="{{ route('cdc.schemes.index') }}" class="group flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-700 group-hover:text-indigo-600 transition">Manage All Schemes</span>
                                    <svg class="w-3 h-3 text-gray-300 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                            </li>
                            <li class="border-t border-gray-50 pt-3">
                                <a href="#" class="group flex items-center justify-between opacity-50 cursor-not-allowed">
                                    <span class="text-xs font-bold text-gray-700">Course Code Directory</span>
                                    <span class="text-[8px] bg-gray-100 px-1 rounded">SOON</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
