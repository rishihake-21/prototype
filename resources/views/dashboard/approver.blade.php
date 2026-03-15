<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Approver Dashboard
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 xl:grid-cols-[280px,minmax(0,1fr)] gap-6 xl:gap-8 items-start">
            <div>
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-5 xl:p-6 sticky top-8 space-y-5">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">HOD Review</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Start review from the queue, then approve, reject, or request changes.
                        </p>
                    </div>

                    <div class="space-y-2.5">
                        <a href="#review-queue" class="block rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-medium text-indigo-700 hover:bg-indigo-100 transition">
                            Review Queue
                        </a>
                        <a href="{{ route('hod.assignments.index') }}" class="block rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Manage Subject Assignments
                        </a>
                        <a href="#cdc-handoffs" class="block rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            CDC Handoffs
                        </a>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Quick Summary</h4>
                        <div class="mt-3 space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Pending reviews</span>
                                <span class="font-semibold text-gray-900">{{ $stats['pending'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Approved</span>
                                <span class="font-semibold text-green-600">{{ $stats['approved_this_month'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Unread CDC updates</span>
                                <span class="font-semibold text-amber-600">{{ $stats['unread_handoffs'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Flow</h4>
                        <div class="mt-3 space-y-3 text-sm text-gray-600">
                            <p><span class="font-semibold text-gray-900">1.</span> Open the submitted syllabus.</p>
                            <p><span class="font-semibold text-gray-900">2.</span> Start review so the stage is visible.</p>
                            <p><span class="font-semibold text-gray-900">3.</span> Approve, reject, or return with changes.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="min-w-0 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Pending Reviews</div>
                        <div class="text-3xl font-bold text-gray-900">{{ $stats['pending'] }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Approved This Month</div>
                        <div class="text-3xl font-bold text-green-600">{{ $stats['approved_this_month'] }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Rejected This Month</div>
                        <div class="text-3xl font-bold text-red-600">{{ $stats['rejected_this_month'] }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Unread CDC Updates</div>
                        <div class="text-3xl font-bold text-amber-600">{{ $stats['unread_handoffs'] }}</div>
                    </div>
                </div>

                <div id="review-queue" class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Review Queue</h3>
                        <p class="text-sm text-gray-500 mt-1">Main queue for submitted and in-review syllabi.</p>
                    </div>
                    <div class="overflow-x-auto">
                        @if($reviewQueue->count() > 0)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Faculty</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departments</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stage</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($reviewQueue as $syllabus)
                                        @php
                                            $badge = match($syllabus->status) {
                                                \App\Models\Syllabus::STATUS_SUBMITTED => 'bg-indigo-100 text-indigo-800',
                                                \App\Models\Syllabus::STATUS_UNDER_REVIEW => 'bg-blue-100 text-blue-800',
                                                \App\Models\Syllabus::STATUS_CHANGES_REQUESTED => 'bg-orange-100 text-orange-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-bold text-gray-900">{{ $syllabus->course_code }}</div>
                                                <div class="text-xs text-gray-500">{{ $syllabus->title }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">{{ $syllabus->creator?->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-600">{{ $syllabus->departments->pluck('name')->join(', ') ?: 'N/A' }}</td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                                    {{ ucfirst(str_replace('_', ' ', $syllabus->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">{{ optional($syllabus->submitted_at)->diffForHumans() ?? '-' }}</td>
                                            <td class="px-6 py-4 text-sm">
                                                <a href="{{ route('syllabi.show', $syllabus) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Open</a>
                                                @if($syllabus->isSubmitted())
                                                    <form action="{{ route('syllabi.start-review', $syllabus) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="ml-4 text-gray-700 hover:text-gray-900 font-medium">
                                                            Start Review
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="ml-4 text-gray-500 font-medium">Continue Review</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="px-6 py-4 border-t border-gray-100">
                                {{ $reviewQueue->links() }}
                            </div>
                        @else
                            <div class="px-6 py-12 text-center text-gray-500">
                                No syllabi are currently waiting for review.
                            </div>
                        @endif
                    </div>
                </div>

                <div id="cdc-handoffs" class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">CDC Handoffs</h3>
                        <p class="text-sm text-gray-500 mt-1">Messages coming from the CDC side of the workflow.</p>
                    </div>
                    <div class="p-5 sm:p-6 space-y-3">
                        @forelse($handoffs as $note)
                            <div class="rounded-xl border p-4 {{ $note->is_read ? 'border-gray-200 bg-white' : 'border-amber-200 bg-amber-50' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $note->title }}</div>
                                        <div class="mt-1 text-xs text-gray-600">{{ $note->message }}</div>
                                    </div>
                                    @if(!$note->is_read)
                                        <button type="button"
                                                onclick="fetch('{{ route('notifications.read', $note) }}', {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}}).then(() => window.location.reload())"
                                                class="text-[10px] font-semibold uppercase tracking-wide text-indigo-600 hover:text-indigo-800">
                                            Mark Read
                                        </button>
                                    @endif
                                </div>
                                <div class="mt-2 text-[11px] text-gray-400 uppercase tracking-wide">{{ $note->created_at->diffForHumans() }}</div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-500">
                                No CDC/HOD handoff messages yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
