<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            HOD Dashboard
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 xl:grid-cols-[280px,minmax(0,1fr)] gap-6 xl:gap-8 items-start">
            <div>
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-5 xl:p-6 sticky top-8 space-y-5">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">HOD Workflow</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Start with department assignments, move electives forward, then track syllabus review decisions.
                        </p>
                    </div>

                    <div class="space-y-2.5">
                        <a href="#selected-assignments" class="block rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-medium text-indigo-700 hover:bg-indigo-100 transition">
                            Active Assignments
                        </a>
                        <a href="#elective-pool" class="block rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Elective Selection
                        </a>
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
                                <span class="text-gray-500">Active assignments</span>
                                <span class="font-semibold text-gray-900">{{ $stats['active_assignments'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Pending reviews</span>
                                <span class="font-semibold text-gray-900">{{ $stats['pending'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Electives selected</span>
                                <span class="font-semibold text-indigo-600">{{ $stats['selected_electives'] ?? 0 }}</span>
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
                            <p><span class="font-semibold text-gray-900">1.</span> Review active assignments and faculty ownership.</p>
                            <p><span class="font-semibold text-gray-900">2.</span> Select electives from the pool for the current academic year.</p>
                            <p><span class="font-semibold text-gray-900">3.</span> Process submitted syllabi from the review queue.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="min-w-0 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Active Assignments</div>
                        <div class="text-3xl font-bold text-gray-900">{{ $stats['active_assignments'] }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Pending Reviews</div>
                        <div class="text-3xl font-bold text-gray-900">{{ $stats['pending'] }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Selected Electives</div>
                        <div class="text-3xl font-bold text-indigo-600">{{ $stats['selected_electives'] ?? 0 }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Approved This Month</div>
                        <div class="text-3xl font-bold text-green-600">{{ $stats['approved_this_month'] }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Rejected This Month</div>
                        <div class="text-3xl font-bold text-red-600">{{ $stats['rejected_this_month'] }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Unread CDC Updates</div>
                        <div class="text-3xl font-bold text-amber-600">{{ $stats['unread_handoffs'] }}</div>
                    </div>
                </div>

                <div id="elective-pool" class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Elective Selection Pool</h3>
                        <p class="text-sm text-gray-500 mt-1">Select electives separately here. Once selected, they move forward into the normal syllabus workflow and are visible to CDC.</p>
                    </div>

                    <div class="p-5 sm:p-6 space-y-6">
                        @if($department)
                            @if($electivePoolCourses->count() > 0)
                                <div class="grid grid-cols-1 2xl:grid-cols-2 gap-4">
                                    @foreach($electivePoolCourses as $course)
                                        @php
                                            $limit = (int) ($course->level?->structure?->elective_count ?? 0);
                                            $alreadySelected = $selectedElectives
                                                ->where('academic_year', $currentAcademicYear)
                                                ->filter(fn ($assignment) => $assignment->course && $assignment->course->level_id === $course->level_id)
                                                ->count();
                                            $remainingForLevel = max($limit - $alreadySelected, 0);
                                        @endphp
                                        <div class="rounded-xl border border-gray-200 p-4 sm:p-5">
                                            <div class="flex flex-col gap-4">
                                                <div>
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">{{ $course->course_code }}</span>
                                                        <span class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-semibold text-purple-700">{{ $course->elective_group ?: 'Elective' }}</span>
                                                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">{{ $course->level->level_code }}</span>
                                                    </div>
                                                    <h4 class="mt-3 text-base font-semibold text-gray-900">{{ $course->course_title }}</h4>
                                                    <p class="mt-1 text-sm text-gray-500">{{ $course->programme->name }}</p>
                                                    <p class="mt-2 text-xs text-gray-500">
                                                        {{ $remainingForLevel }} of {{ $limit }} elective slot(s) still open for {{ $course->level->level_code }} in {{ $currentAcademicYear }}.
                                                    </p>
                                                </div>

                                                @if($remainingForLevel > 0)
                                                    <form action="{{ route('hod.assignments.store') }}" method="POST" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                                        @csrf
                                                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                                                        <input type="hidden" name="academic_year" value="{{ $currentAcademicYear }}">
                                                        <div class="sm:col-span-2">
                                                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Faculty</label>
                                                            <select name="faculty_user_id" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                                <option value="">Select faculty</option>
                                                                @foreach($faculty as $member)
                                                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Deadline</label>
                                                            <input type="date" name="deadline" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 transition">
                                                                Select Elective And Pass Forward
                                                            </button>
                                                        </div>
                                                    </form>
                                                @else
                                                    <div class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-700">
                                                        The elective quota for this level is already filled for {{ $currentAcademicYear }}.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-5 py-8 text-center text-gray-500">
                                    No elective is waiting in the HOD pool right now for {{ $currentAcademicYear }}.
                                </div>
                            @endif

                            <div class="rounded-xl border border-gray-200">
                                <div class="px-5 py-4 border-b border-gray-200">
                                    <h4 class="text-base font-semibold text-gray-900">Selected Electives Already Passed Forward</h4>
                                    <p class="mt-1 text-sm text-gray-500">These electives are now part of the active workflow and visible downstream.</p>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Faculty</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @forelse($selectedElectives as $assign)
                                                @php
                                                    $badge = match($assign->status) {
                                                        \App\Models\CourseAssignment::STATUS_PENDING => 'bg-amber-100 text-amber-800',
                                                        \App\Models\CourseAssignment::STATUS_IN_PROGRESS => 'bg-sky-100 text-sky-800',
                                                        \App\Models\CourseAssignment::STATUS_SUBMITTED => 'bg-indigo-100 text-indigo-800',
                                                        \App\Models\CourseAssignment::STATUS_UNDER_REVIEW => 'bg-blue-100 text-blue-800',
                                                        \App\Models\CourseAssignment::STATUS_CHANGES_REQUESTED => 'bg-orange-100 text-orange-800',
                                                        \App\Models\CourseAssignment::STATUS_REJECTED => 'bg-rose-100 text-rose-800',
                                                        \App\Models\CourseAssignment::STATUS_COMPLETED => 'bg-green-100 text-green-800',
                                                        default => 'bg-gray-100 text-gray-800',
                                                    };
                                                    $latestSyllabus = $assign->syllabi->first();
                                                @endphp
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4">
                                                        <div class="text-sm font-bold text-gray-900">{{ $assign->course->course_code }}</div>
                                                        <div class="text-xs text-gray-500">{{ $assign->course->course_title }}</div>
                                                        <div class="text-[10px] uppercase text-purple-600 mt-1">{{ $assign->course->elective_group ?: 'Elective' }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $assign->faculty->name ?? 'Not assigned' }}</td>
                                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $assign->academic_year }}</td>
                                                    <td class="px-6 py-4">
                                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                                            {{ ucfirst(str_replace('_', ' ', $assign->status)) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm">
                                                        @if($latestSyllabus)
                                                            <a href="{{ route('syllabi.show', $latestSyllabus) }}" class="font-medium text-indigo-600 hover:text-indigo-900">Open Syllabus</a>
                                                        @else
                                                            <span class="text-gray-400">Waiting for faculty</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                                        No electives have been selected yet.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-5 py-8 text-center text-gray-500">
                                No department is linked to this HOD account yet, so elective selection is unavailable.
                            </div>
                        @endif
                    </div>
                </div>

                <div id="selected-assignments" class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Active Assignments</h3>
                        <p class="text-sm text-gray-500 mt-1">Department assignments already moved into the working syllabus flow.</p>
                    </div>
                    <div class="p-5 sm:p-6">
                        @if($regularAssignments->count() > 0)
                            <div class="space-y-4">
                                @foreach($regularAssignments as $assign)
                                    @php
                                        $badge = match($assign->status) {
                                            \App\Models\CourseAssignment::STATUS_PENDING => 'bg-amber-100 text-amber-800',
                                            \App\Models\CourseAssignment::STATUS_IN_PROGRESS => 'bg-sky-100 text-sky-800',
                                            \App\Models\CourseAssignment::STATUS_SUBMITTED => 'bg-indigo-100 text-indigo-800',
                                            \App\Models\CourseAssignment::STATUS_UNDER_REVIEW => 'bg-blue-100 text-blue-800',
                                            \App\Models\CourseAssignment::STATUS_CHANGES_REQUESTED => 'bg-orange-100 text-orange-800',
                                            \App\Models\CourseAssignment::STATUS_REJECTED => 'bg-rose-100 text-rose-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        $typeBadge = $assign->course->course_type === \App\Models\Course::TYPE_ELECTIVE
                                            ? 'bg-purple-100 text-purple-700'
                                            : ($assign->course->course_type === \App\Models\Course::TYPE_AUDIT
                                                ? 'bg-amber-100 text-amber-700'
                                                : 'bg-gray-100 text-gray-700');
                                    @endphp
                                    <div class="rounded-xl border border-gray-200 p-4 sm:p-5">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                            <div class="min-w-0">
                                                <div class="text-sm font-bold text-gray-900">{{ $assign->course->course_code }} - {{ $assign->course->course_title }}</div>
                                                <div class="text-xs text-gray-500 mt-1">{{ $assign->course->programme->code }} / {{ $assign->course->level->level_code }} / {{ $assign->academic_year }}</div>
                                                <div class="text-xs text-gray-500 mt-1">Faculty: {{ $assign->faculty->name ?? 'Not assigned' }} | Department: {{ $assign->department->name ?? 'N/A' }}</div>
                                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $typeBadge }}">
                                                        {{ ucfirst($assign->course->course_type) }}
                                                    </span>
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                                        {{ ucfirst(str_replace('_', ' ', $assign->status)) }}
                                                    </span>
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                        Deadline: {{ $assign->deadline?->format('d M Y') ?? 'Not set' }}
                                                    </span>
                                                </div>
                                            </div>
                                            @php
                                                $latestSyllabus = $assign->syllabi->first();
                                            @endphp
                                            @if($latestSyllabus)
                                                <a href="{{ route('syllabi.show', $latestSyllabus) }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 transition xl:self-start">
                                                    Open Latest Syllabus
                                                </a>
                                            @else
                                                <a href="{{ route('hod.assignments.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition xl:self-start">
                                                    Manage Assignment
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-12 text-center text-gray-500">
                                No regular assignments are active yet.
                            </div>
                        @endif
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
