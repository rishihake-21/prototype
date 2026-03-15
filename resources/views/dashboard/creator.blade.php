<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Creator Dashboard
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 xl:grid-cols-[280px,minmax(0,1fr)] gap-6 xl:gap-8 items-start">
            <div>
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-5 xl:p-6 sticky top-8 space-y-5">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Faculty Workflow</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Use the assignment list first, then continue drafts and track review results.
                        </p>
                    </div>

                    <div class="space-y-2.5">
                        <a href="#active-assignments" class="block rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-medium text-indigo-700 hover:bg-indigo-100 transition">
                            Active Assignments
                        </a>
                        <a href="#recent-syllabi" class="block rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Recent Syllabi
                        </a>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Quick Summary</h4>
                        <div class="mt-3 space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Open assignments</span>
                                <span class="font-semibold text-gray-900">{{ $stats['active_assignments'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Pending review</span>
                                <span class="font-semibold text-indigo-600">{{ $stats['pending'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Overdue</span>
                                <span class="font-semibold text-red-600">{{ $stats['overdue_assignments'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Flow</h4>
                        <div class="mt-3 space-y-3 text-sm text-gray-600">
                            <p><span class="font-semibold text-gray-900">1.</span> Open the assigned course.</p>
                            <p><span class="font-semibold text-gray-900">2.</span> Draft and update the syllabus.</p>
                            <p><span class="font-semibold text-gray-900">3.</span> Watch for approval, rejection, or changes requested.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="min-w-0 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Total Syllabi</div>
                        <div class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Drafts</div>
                        <div class="text-3xl font-bold text-gray-900">{{ $stats['drafts'] }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Approved</div>
                        <div class="text-3xl font-bold text-green-600">{{ $stats['approved'] }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Rejected</div>
                        <div class="text-3xl font-bold text-red-600">{{ $stats['rejected'] }}</div>
                    </div>
                </div>

                <div id="active-assignments" class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-200 flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Active Assignments</h3>
                            <p class="text-sm text-gray-500 mt-1">This is the main working queue for faculty.</p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $assignments->count() }} Total
                        </span>
                    </div>

                    <div class="p-5 sm:p-6">
                        @if($assignments->count() > 0)
                            <div class="space-y-4">
                                @foreach($assignments as $assign)
                                    @php
                                        $facultySyllabus = $assign->syllabi->firstWhere('submitted_by', auth()->id());
                                        $actionRoute = route('syllabi.create', ['assignment' => $assign->id]);
                                        $actionLabel = 'Start Syllabus';

                                        if ($facultySyllabus) {
                                            $actionRoute = $facultySyllabus->canBeEdited()
                                                ? route('syllabi.edit', $facultySyllabus)
                                                : route('syllabi.show', $facultySyllabus);
                                            $actionLabel = $facultySyllabus->canBeEdited() ? 'Continue Work' : 'View Submission';
                                        }

                                        $badge = match($assign->status) {
                                            \App\Models\CourseAssignment::STATUS_PENDING => 'bg-amber-100 text-amber-800',
                                            \App\Models\CourseAssignment::STATUS_IN_PROGRESS => 'bg-sky-100 text-sky-800',
                                            \App\Models\CourseAssignment::STATUS_SUBMITTED => 'bg-indigo-100 text-indigo-800',
                                            \App\Models\CourseAssignment::STATUS_UNDER_REVIEW => 'bg-blue-100 text-blue-800',
                                            \App\Models\CourseAssignment::STATUS_CHANGES_REQUESTED => 'bg-orange-100 text-orange-800',
                                            \App\Models\CourseAssignment::STATUS_REJECTED => 'bg-rose-100 text-rose-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        $isOverdue = $assign->deadline && $assign->deadline->isPast();
                                    @endphp
                                    <div class="rounded-xl border border-gray-200 p-4 sm:p-5">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                            <div class="min-w-0">
                                                <div class="text-sm font-bold text-gray-900">{{ $assign->course->course_code }} - {{ $assign->course->course_title }}</div>
                                                <div class="text-xs text-gray-500 mt-1">{{ $assign->course->programme->code }} / {{ $assign->course->level->level_code }} / {{ $assign->academic_year }}</div>
                                                <div class="text-xs text-gray-500 mt-1">Assigned by {{ $assign->assigner->name }}</div>
                                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                                        {{ ucfirst(str_replace('_', ' ', $assign->status)) }}
                                                    </span>
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                        Deadline: {{ $assign->deadline?->format('d M Y') ?? 'Not set' }}
                                                    </span>
                                                    @if($isOverdue)
                                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                            Overdue
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <a href="{{ $actionRoute }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 transition xl:self-start">
                                                {{ $actionLabel }}
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-12 text-center text-gray-500">
                                No active assignments right now.
                            </div>
                        @endif
                    </div>
                </div>

                <div id="recent-syllabi" class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Recent Syllabi</h3>
                        <p class="text-sm text-gray-500 mt-1">Latest syllabus records and their current state.</p>
                    </div>
                    <div class="overflow-x-auto">
                        @if($recentSyllabi->count() > 0)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($recentSyllabi as $syllabus)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $syllabus->title }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-600">{{ $syllabus->course_code }}</td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                                    @if($syllabus->isDraft()) bg-gray-100 text-gray-800
                                                    @elseif($syllabus->isSubmitted()) bg-indigo-100 text-indigo-800
                                                    @elseif($syllabus->isApproved()) bg-green-100 text-green-800
                                                    @elseif($syllabus->isRejected()) bg-red-100 text-red-800
                                                    @elseif($syllabus->isChangesRequested()) bg-orange-100 text-orange-800
                                                    @elseif($syllabus->isUnderReview()) bg-blue-100 text-blue-800
                                                    @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $syllabus->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                <a href="{{ route('syllabi.show', $syllabus) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">View</a>
                                                @if($syllabus->canBeEdited())
                                                    <a href="{{ route('syllabi.edit', $syllabus) }}" class="ml-4 text-gray-700 hover:text-gray-900 font-medium">Edit</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="px-6 py-12 text-center text-gray-500">
                                No syllabi created yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
