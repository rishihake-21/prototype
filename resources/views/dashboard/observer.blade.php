<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Observer Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 uppercase tracking-wider font-semibold">Approved Syllabi</div>
                        <div class="text-3xl font-bold">{{ $stats['total_approved'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100 text-indigo-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 uppercase tracking-wider font-semibold">Programmes</div>
                        <div class="text-3xl font-bold">{{ $stats['total_programmes'] }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Browse Approved Syllabi</h3>
                        <div class="text-sm text-gray-500">Read-only Access</div>
                    </div>

                    @if($approvedSyllabi->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @foreach($approvedSyllabi as $syllabus)
                                <div class="border rounded-lg p-4 hover:shadow-md transition bg-gray-50">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ $syllabus->course_code }}</span>
                                        <div class="flex space-x-1">
                                            <a href="{{ route('syllabi.download-pdf', $syllabus) }}" title="Download PDF" class="text-red-500 hover:text-red-700">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M13.172 2L18 6.828V18a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2h9.172zM15 18a1 1 0 100-2 1 1 0 000 2zM9 18a1 1 0 100-2 1 1 0 000 2zM5 18a1 1 0 100-2 1 1 0 000 2zM11 6V1h-1v5H1v1h18V6h-8z"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                    <h4 class="font-bold text-gray-900 mb-1 line-clamp-1">{{ $syllabus->title }}</h4>
                                    <p class="text-xs text-gray-500 mb-3">{{ $syllabus->program_name }}</p>
                                    
                                    <div class="flex justify-between items-center mt-auto">
                                        <div class="text-[10px] text-gray-400">Approved: {{ $syllabus->approved_at?->format('M d, Y') }}</div>
                                        <a href="{{ route('syllabi.show', $syllabus) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-900 underline">View Full Syllabus</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-6">
                            {{ $approvedSyllabi->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2m16-11V9a2 2 0 00-2-2H6a2 2 0 00-2-2M6 7V5a2 2 0 012-2h8a2 2 0 012 2v2M7 7h10"></path></svg>
                            <p class="text-gray-500">No approved syllabi found in the system yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notice Box -->
            <div class="mt-8 bg-amber-50 border-l-4 border-amber-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-700">
                            Observers have <strong>read-only</strong> access. You can view and download approved syllabi but cannot create or modify any curriculum data.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
