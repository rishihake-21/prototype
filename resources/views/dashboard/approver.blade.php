<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Approver Dashboard
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <section class="rounded-3xl border border-blue-200 bg-gradient-to-r from-blue-950 via-slate-900 to-cyan-900 px-8 py-8 text-white shadow-xl">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-200">HOD Review Desk</p>
                        <h1 class="mt-3 text-3xl font-bold tracking-tight">Pick up submissions, review clearly, and keep faculty unblocked.</h1>
                        <p class="mt-3 text-sm leading-6 text-slate-200">
                            Use the review queue as your primary board. Start review when you take ownership, then approve, reject, or request changes from the syllabus screen.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:min-w-[34rem]">
                        <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-300">Pending</div>
                            <div class="mt-2 text-2xl font-bold">{{ $stats['pending'] }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-300">Approved</div>
                            <div class="mt-2 text-2xl font-bold">{{ $stats['approved_this_month'] }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-300">Rejected</div>
                            <div class="mt-2 text-2xl font-bold">{{ $stats['rejected_this_month'] }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-300">CDC Updates</div>
                            <div class="mt-2 text-2xl font-bold">{{ $stats['unread_handoffs'] }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1.8fr,1fr]">
                <div class="space-y-6">
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Review Queue</h3>
                                <p class="mt-1 text-sm text-slate-500">These are the syllabi currently waiting for HOD attention.</p>
                            </div>
                            <a href="{{ route('hod.assignments.index') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                                Manage Subject Assignments
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            @if($reviewQueue->count() > 0)
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Course</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Faculty</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Departments</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Stage</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Submitted</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach($reviewQueue as $syllabus)
                                            @php
                                                $badge = match($syllabus->status) {
                                                    \App\Models\Syllabus::STATUS_SUBMITTED => 'bg-indigo-100 text-indigo-700',
                                                    \App\Models\Syllabus::STATUS_UNDER_REVIEW => 'bg-blue-100 text-blue-700',
                                                    \App\Models\Syllabus::STATUS_CHANGES_REQUESTED => 'bg-orange-100 text-orange-700',
                                                    default => 'bg-slate-100 text-slate-700',
                                                };
                                            @endphp
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-semibold text-slate-900">{{ $syllabus->course_code }}</div>
                                                    <div class="text-xs text-slate-500">{{ $syllabus->title }}</div>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-slate-600">{{ $syllabus->creator?->name ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 text-sm text-slate-600">{{ $syllabus->departments->pluck('name')->join(', ') ?: 'N/A' }}</td>
                                                <td class="px-6 py-4">
                                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
                                                        {{ ucfirst(str_replace('_', ' ', $syllabus->status)) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-slate-600">{{ optional($syllabus->submitted_at)->diffForHumans() ?? '-' }}</td>
                                                <td class="px-6 py-4 text-sm">
                                                    <a href="{{ route('syllabi.show', $syllabus) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Open</a>
                                                    @if($syllabus->isSubmitted())
                                                        <form action="{{ route('syllabi.start-review', $syllabus) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="ml-4 font-semibold text-slate-700 hover:text-slate-900">
                                                                Start review
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="ml-4 font-semibold text-slate-500">Continue review</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="border-t border-slate-100 px-6 py-4">
                                    {{ $reviewQueue->links() }}
                                </div>
                            @else
                                <div class="px-6 py-12 text-center">
                                    <p class="text-lg font-semibold text-slate-700">No syllabi are currently waiting for review.</p>
                                    <p class="mt-2 text-sm text-slate-500">New submissions from faculty will appear here automatically.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-900">Review Flow</h3>
                        <div class="mt-5 space-y-4">
                            <div class="flex gap-4">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">1</div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Start review</p>
                                    <p class="text-xs text-slate-500">Claim the submission first so the stage is visible to faculty and CDC.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">2</div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Check against CDC definition</p>
                                    <p class="text-xs text-slate-500">Review inherited course and scheme data together with faculty content.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-500 text-xs font-bold text-white">3</div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Close the loop</p>
                                    <p class="text-xs text-slate-500">Approve, reject, or request changes with clear feedback.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-slate-900">CDC Handoffs</h3>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
                                {{ $handoffs->count() }} Items
                            </span>
                        </div>
                        <div class="mt-5 space-y-3">
                            @forelse($handoffs as $note)
                                <div class="rounded-2xl border p-4 {{ $note->is_read ? 'border-slate-200 bg-white' : 'border-amber-200 bg-amber-50/70' }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ $note->title }}</p>
                                            <p class="mt-1 text-xs leading-5 text-slate-600">{{ $note->message }}</p>
                                        </div>
                                        @if(!$note->is_read)
                                            <button type="button"
                                                    onclick="fetch('{{ route('notifications.read', $note) }}', {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}}).then(() => window.location.reload())"
                                                    class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-amber-700">
                                                Mark read
                                            </button>
                                        @endif
                                    </div>
                                    <div class="mt-2 text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ $note->created_at->diffForHumans() }}</div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                    No CDC/HOD handoff messages yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
