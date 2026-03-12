<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Approver Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Pending Reviews</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['pending'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Approved This Month</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['approved_this_month'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Rejected This Month</div>
                    <div class="text-3xl font-bold text-red-600">{{ $stats['rejected_this_month'] }}</div>
                </div>
            </div>

            <!-- Subject Assignments Quick Link -->
            <div class="mb-8">
                <a href="{{ route('hod.assignments.index') }}" class="flex items-center justify-between bg-indigo-600 p-4 rounded-lg shadow-sm text-white hover:bg-indigo-700 transition">
                    <div>
                        <h3 class="text-lg font-bold">Manage Subject Assignments</h3>
                        <p class="text-indigo-100 text-sm">Assign subjects defined by CDC to your departmental faculty members.</p>
                    </div>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>

            <!-- Review Queue -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">Syllabi Pending Your Review</h3>
                    @if($reviewQueue->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creator</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departments</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted At</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($reviewQueue as $syllabus)
                                    <tr>
                                        <td class="px-6 py-4">{{ $syllabus->title }}</td>
                                        <td class="px-6 py-4">{{ $syllabus->course_code }}</td>
                                        <td class="px-6 py-4">{{ $syllabus->creator?->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">
                                            @if($syllabus->departments->count())
                                                <span class="text-sm text-gray-700">
                                                    {{ $syllabus->departments->pluck('name')->join(', ') }}
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ optional($syllabus->submitted_at)->diffForHumans() ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 space-x-2">
                                            <a href="{{ route('syllabi.show', $syllabus) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                View
                                            </a>
                                            <a href="{{ route('syllabi.edit', $syllabus) }}" class="text-blue-600 hover:text-blue-900 text-sm">
                                                Review
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-4">
                            {{ $reviewQueue->links() }}
                        </div>
                    @else
                        <p class="text-gray-500">No syllabi are currently pending your review.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

