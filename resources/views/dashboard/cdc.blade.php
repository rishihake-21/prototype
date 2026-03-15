<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            CDC Incharge Dashboard
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <section class="rounded-3xl border border-indigo-200 bg-gradient-to-r from-slate-950 via-indigo-950 to-sky-950 px-8 py-8 text-white shadow-xl">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-200">Curriculum Control Room</p>
                        <h1 class="mt-3 text-3xl font-bold tracking-tight">See where every programme is in the CDC to HOD to faculty cycle.</h1>
                        <p class="mt-3 text-sm leading-6 text-slate-200">
                            Use this dashboard as a process board: check the pipeline first, follow overdue work next, and then go deeper into programmes and handoffs.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:min-w-[36rem]">
                        <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-300">Programmes</div>
                            <div class="mt-2 text-2xl font-bold">{{ $stats['total_programmes'] }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-300">Active</div>
                            <div class="mt-2 text-2xl font-bold">{{ $stats['active_programmes'] }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-300">Courses</div>
                            <div class="mt-2 text-2xl font-bold">{{ $stats['total_courses'] }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-300">Unread HOD</div>
                            <div class="mt-2 text-2xl font-bold">{{ $stats['unread_handoffs'] }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Active Assignments</div>
                    <div class="mt-3 text-3xl font-bold text-slate-900">{{ $stats['active_assignments'] }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Under Review</div>
                    <div class="mt-3 text-3xl font-bold text-blue-600">{{ $stats['under_review'] }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Changes Requested</div>
                    <div class="mt-3 text-3xl font-bold text-orange-600">{{ $stats['changes_requested'] }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Overdue Assignments</div>
                    <div class="mt-3 text-3xl font-bold text-rose-600">{{ $stats['overdue_assignments'] }}</div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1.8fr,1fr]">
                <div class="space-y-6">
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Workflow Pipeline</h3>
                                <p class="mt-1 text-sm text-slate-500">This is the main operational board for CDC.</p>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('cdc.programmes.create') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                                    New Programme
                                </a>
                                <a href="{{ route('cdc.programmes.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                                    View All Programmes
                                </a>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Course</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Department</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Faculty</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Deadline</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse($workflowAssignments as $assignment)
                                        @php
                                            $badge = match($assignment->status) {
                                                \App\Models\CourseAssignment::STATUS_PENDING => 'bg-amber-100 text-amber-700',
                                                \App\Models\CourseAssignment::STATUS_IN_PROGRESS => 'bg-sky-100 text-sky-700',
                                                \App\Models\CourseAssignment::STATUS_SUBMITTED => 'bg-indigo-100 text-indigo-700',
                                                \App\Models\CourseAssignment::STATUS_UNDER_REVIEW => 'bg-blue-100 text-blue-700',
                                                \App\Models\CourseAssignment::STATUS_CHANGES_REQUESTED => 'bg-orange-100 text-orange-700',
                                                \App\Models\CourseAssignment::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
                                                default => 'bg-slate-100 text-slate-700',
                                            };
                                            $isOverdue = $assignment->deadline && $assignment->deadline->isPast();
                                        @endphp
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-semibold text-slate-900">{{ $assignment->course->course_code }}</div>
                                                <div class="text-xs text-slate-500">{{ $assignment->course->course_title }}</div>
                                                <div class="mt-1 text-[11px] font-medium uppercase tracking-wide text-indigo-600">
                                                    {{ $assignment->course->programme->code }} / {{ $assignment->course->level->level_code }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-slate-600">{{ $assignment->department->name }}</td>
                                            <td class="px-6 py-4 text-sm text-slate-600">{{ $assignment->faculty->name }}</td>
                                            <td class="px-6 py-4 text-sm text-slate-600">
                                                {{ $assignment->deadline?->format('d M Y') ?? 'Not set' }}
                                                @if($isOverdue)
                                                    <div class="mt-1 text-[11px] font-semibold text-rose-600">Overdue</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
                                                    {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">No active workflow items yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Recent Programmes</h3>
                                <p class="mt-1 text-sm text-slate-500">Recently created or updated curriculum structures.</p>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            @if($recentProgrammes->count() > 0)
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Programme</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Year</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach($recentProgrammes as $prog)
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-semibold text-slate-900">{{ $prog->name }}</div>
                                                    <div class="text-xs text-slate-500">{{ $prog->code }}</div>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-slate-600">{{ $prog->academic_year }}</td>
                                                <td class="px-6 py-4">
                                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $prog->isActive() ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                                        {{ strtoupper($prog->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm">
                                                    <a href="{{ route('cdc.programmes.show', $prog) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Manage</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="px-6 py-12 text-center text-sm text-slate-500">No programmes created yet.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-900">CDC Process</h3>
                        <div class="mt-5 space-y-4">
                            <div class="flex gap-4">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">1</div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Define programme and scheme</p>
                                    <p class="text-xs text-slate-500">Keep scheme at a glance and course definition aligned first.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">2</div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Watch the handoff</p>
                                    <p class="text-xs text-slate-500">HOD picks the elective pool and assigns faculty from the CDC-defined course set.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-rose-500 text-xs font-bold text-white">3</div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Escalate stalled work</p>
                                    <p class="text-xs text-slate-500">Use overdue and changes-requested counts as your follow-up triggers.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-slate-900">CDC and HOD Handoffs</h3>
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
