<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Approver Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Unread CDC Updates</div>
                    <div class="text-3xl font-bold text-amber-600">{{ $stats['unread_handoffs'] }}</div>
                </div>
            </div>

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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
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
                                                {{ optional($syllabus->submitted_at)->diffForHumans() ?? '-' }}
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

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">CDC ↔ HOD Handoffs</h3>
                        <div class="space-y-3">
                            @forelse($handoffs as $note)
                                <div class="rounded-lg border {{ $note->is_read ? 'border-gray-200 bg-white' : 'border-amber-200 bg-amber-50/60' }} p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="text-sm font-semibold text-gray-900">{{ $note->title }}</div>
                                        <div class="flex flex-col items-end gap-2">
                                            @if(!$note->is_read)
                                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">NEW</span>
                                                <button type="button"
                                                        onclick="fetch('{{ route('notifications.read', $note) }}', {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}}).then(() => window.location.reload())"
                                                        class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 hover:text-indigo-800">
                                                    Mark Read
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-600">{{ $note->message }}</div>
                                    <div class="mt-2 text-[10px] uppercase tracking-widest text-gray-400">{{ $note->created_at->diffForHumans() }}</div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No CDC/HOD handoff messages yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
