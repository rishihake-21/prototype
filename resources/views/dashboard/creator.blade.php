<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Creator Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Total Syllabi</div>
                    <div class="text-3xl font-bold">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Drafts</div>
                    <div class="text-3xl font-bold text-gray-600">{{ $stats['drafts'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Pending</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['pending'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Approved</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['approved'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Rejected</div>
                    <div class="text-3xl font-bold text-red-600">{{ $stats['rejected'] }}</div>
                </div>
            </div>

            <!-- Assigned Courses -->
            @if($assignments->count() > 0)
                <div class="bg-indigo-50 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8 border border-indigo-200">
                    <h3 class="text-lg font-bold text-indigo-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        Your Assigned Courses
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($assignments as $assign)
                            <div class="bg-white p-4 rounded-md shadow-sm border border-indigo-100 flex justify-between items-center">
                                <div>
                                    <div class="font-bold text-gray-900">{{ $assign->course->course_code }}: {{ $assign->course->course_title }}</div>
                                    <div class="text-xs text-gray-500">{{ $assign->academic_year }} • Assigned by {{ $assign->assigner->name }}</div>
                                </div>
                                <a href="{{ route('syllabi.create', ['assignment' => $assign->id]) }}" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded hover:bg-indigo-700">
                                    Start Syllabus
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8">
                <h3 class="text-lg font-medium mb-4">Quick Actions</h3>
                <div class="flex gap-4">
                    <a href="{{ route('syllabi.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-black">
                        + Generic Syllabus
                    </a>
                </div>
            </div>

            <!-- Recent Syllabi -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">Recent Syllabi</h3>
                    @if($recentSyllabi->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($recentSyllabi as $syllabus)
                                    <tr>
                                        <td class="px-6 py-4">{{ $syllabus->title }}</td>
                                        <td class="px-6 py-4">{{ $syllabus->course_code }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs rounded-full
                                                @if($syllabus->isDraft()) bg-gray-100 text-gray-800
                                                @elseif($syllabus->isSubmitted()) bg-blue-100 text-blue-800
                                                @elseif($syllabus->isApproved()) bg-green-100 text-green-800
                                                @elseif($syllabus->isRejected()) bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $syllabus->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('syllabi.show', $syllabus) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            @if($syllabus->canBeEdited())
                                                <a href="{{ route('syllabi.edit', $syllabus) }}" class="ml-2 text-indigo-600 hover:text-indigo-900">Edit</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">No syllabi created yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
