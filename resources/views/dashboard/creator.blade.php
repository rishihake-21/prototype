<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Creator Dashboard
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <section class="rounded-3xl border border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-900 px-8 py-8 text-white shadow-xl">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-indigo-200">Faculty Workflow</p>
                        <h1 class="mt-3 text-3xl font-bold tracking-tight">Work on assigned syllabi without losing the review trail.</h1>
                        <p class="mt-3 text-sm leading-6 text-slate-200">
                            Start from the active assignment list, continue drafts, and track what is under HOD review or waiting for revision.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:min-w-[30rem]">
                        <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-300">Active</div>
                            <div class="mt-2 text-2xl font-bold">{{ $stats['active_assignments'] }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-300">Drafts</div>
                            <div class="mt-2 text-2xl font-bold">{{ $stats['drafts'] }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-300">Pending</div>
                            <div class="mt-2 text-2xl font-bold">{{ $stats['pending'] }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                            <div class="text-[11px] uppercase tracking-[0.2em] text-slate-300">Overdue</div>
                            <div class="mt-2 text-2xl font-bold">{{ $stats['overdue_assignments'] }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1.7fr,1fr]">
                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Active Assignments</h3>
                            <p class="mt-1 text-sm text-slate-500">This is your working queue. Start here before opening anything else.</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
                            {{ $assignments->count() }} Open
                        </span>
                    </div>

                    <div class="p-6">
                        @if($assignments->count() > 0)
                            <div class="space-y-4">
                                @foreach($assignments as $assign)
                                    @php
                                        $facultySyllabus = $assign->syllabi->firstWhere('submitted_by', auth()->id());
                                        $actionRoute = route('syllabi.create', ['assignment' => $assign->id]);
                                        $actionLabel = 'Start syllabus';

                                        if ($facultySyllabus) {
                                            $actionRoute = $facultySyllabus->canBeEdited()
                                                ? route('syllabi.edit', $facultySyllabus)
                                                : route('syllabi.show', $facultySyllabus);
                                            $actionLabel = $facultySyllabus->canBeEdited() ? 'Continue work' : 'View submission';
                                        }

                                        $badge = match($assign->status) {
                                            \App\Models\CourseAssignment::STATUS_PENDING => 'bg-amber-100 text-amber-800 border-amber-200',
                                            \App\Models\CourseAssignment::STATUS_IN_PROGRESS => 'bg-sky-100 text-sky-800 border-sky-200',
                                            \App\Models\CourseAssignment::STATUS_SUBMITTED => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                            \App\Models\CourseAssignment::STATUS_UNDER_REVIEW => 'bg-blue-100 text-blue-800 border-blue-200',
                                            \App\Models\CourseAssignment::STATUS_CHANGES_REQUESTED => 'bg-orange-100 text-orange-800 border-orange-200',
                                            \App\Models\CourseAssignment::STATUS_REJECTED => 'bg-rose-100 text-rose-800 border-rose-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                        $isOverdue = $assign->deadline && $assign->deadline->isPast();
                                    @endphp
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <div class="space-y-3">
                                                <div>
                                                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ $assign->course->programme->code }} / {{ $assign->course->level->level_code }}</div>
                                                    <h4 class="mt-1 text-lg font-semibold text-slate-900">{{ $assign->course->course_code }} - {{ $assign->course->course_title }}</h4>
                                                    <p class="mt-1 text-sm text-slate-500">Assigned by {{ $assign->assigner->name }} for {{ $assign->academic_year }}</p>
                                                </div>
                                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                                    <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $badge }}">
                                                        {{ ucfirst(str_replace('_', ' ', $assign->status)) }}
                                                    </span>
                                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 border border-slate-200">
                                                        Deadline: {{ $assign->deadline?->format('d M Y') ?? 'Not set' }}
                                                    </span>
                                                    @if($isOverdue)
                                                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 border border-red-200">
                                                            Overdue
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <a href="{{ $actionRoute }}" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                                                {{ $actionLabel }}
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                                <p class="text-lg font-semibold text-slate-700">No active assignments right now.</p>
                                <p class="mt-2 text-sm text-slate-500">When HOD assigns a course to you, it will appear here as the first step in your workflow.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-900">Your Process</h3>
                        <div class="mt-5 space-y-4">
                            <div class="flex gap-4">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">1</div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Open assigned course</p>
                                    <p class="text-xs text-slate-500">Start only from the assignment queue so scheme and course data stay linked.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">2</div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Draft and refine</p>
                                    <p class="text-xs text-slate-500">Continue work from the same assignment card until you are ready to submit.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-white">3</div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Watch review status</p>
                                    <p class="text-xs text-slate-500">Track submitted, under-review, and changes-requested stages from one place.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Approved</div>
                                <div class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['approved'] }}</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Rejected</div>
                                <div class="mt-2 text-3xl font-bold text-rose-600">{{ $stats['rejected'] }}</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total</div>
                                <div class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total'] }}</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pending</div>
                                <div class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['pending'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Recent Syllabi</h3>
                        <p class="mt-1 text-sm text-slate-500">Your recent work and its current review state.</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    @if($recentSyllabi->count() > 0)
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Course Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($recentSyllabi as $syllabus)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $syllabus->title }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ $syllabus->course_code }}</td>
                                        <td class="px-6 py-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold
                                                @if($syllabus->isDraft()) bg-slate-100 text-slate-700
                                                @elseif($syllabus->isSubmitted()) bg-indigo-100 text-indigo-700
                                                @elseif($syllabus->isApproved()) bg-emerald-100 text-emerald-700
                                                @elseif($syllabus->isRejected()) bg-rose-100 text-rose-700
                                                @elseif($syllabus->isChangesRequested()) bg-orange-100 text-orange-700
                                                @elseif($syllabus->isUnderReview()) bg-blue-100 text-blue-700
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $syllabus->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <a href="{{ route('syllabi.show', $syllabus) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">View</a>
                                            @if($syllabus->canBeEdited())
                                                <a href="{{ route('syllabi.edit', $syllabus) }}" class="ml-4 font-semibold text-slate-700 hover:text-slate-900">Edit</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="px-6 py-12 text-center text-sm text-slate-500">
                            No syllabi created yet.
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
