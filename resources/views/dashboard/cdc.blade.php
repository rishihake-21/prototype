<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            CDC Incharge Dashboard
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 xl:grid-cols-[280px,minmax(0,1fr)] gap-6 xl:gap-8 items-start">
            <div>
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-5 xl:p-6 sticky top-8 space-y-5">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">CDC Workflow</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Monitor the curriculum pipeline, then jump into programme and scheme management.
                        </p>
                    </div>

                    <div class="space-y-2.5">
                        <a href="#workflow-pipeline" class="block rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-medium text-indigo-700 hover:bg-indigo-100 transition">
                            Workflow Pipeline
                        </a>
                        <a href="{{ route('cdc.programmes.index') }}" class="block rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            All Programmes
                        </a>
                        <a href="{{ route('cdc.schemes.index') }}" class="block rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            All Schemes
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
                                <span class="text-gray-500">Under review</span>
                                <span class="font-semibold text-blue-600">{{ $stats['under_review'] }}</span>
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
                            <p><span class="font-semibold text-gray-900">1.</span> Define programme and scheme.</p>
                            <p><span class="font-semibold text-gray-900">2.</span> Let HOD assign and review from CDC-defined data.</p>
                            <p><span class="font-semibold text-gray-900">3.</span> Follow up on delayed or returned work.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="min-w-0 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Programmes</div>
                        <div class="text-3xl font-bold text-gray-900">{{ $stats['total_programmes'] }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Active Curricula</div>
                        <div class="text-3xl font-bold text-green-600">{{ $stats['active_programmes'] }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Courses Defined</div>
                        <div class="text-3xl font-bold text-gray-900">{{ $stats['total_courses'] }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <div class="text-gray-500 text-sm">Unread HOD Updates</div>
                        <div class="text-3xl font-bold text-amber-600">{{ $stats['unread_handoffs'] }}</div>
                    </div>
                </div>

                <div id="workflow-pipeline" class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-200 flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Workflow Pipeline</h3>
                            <p class="text-sm text-gray-500 mt-1">Main operational board for CDC.</p>
                        </div>
                        <a href="{{ route('cdc.programmes.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 transition">
                            New Programme
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        @if($workflowAssignments->count() > 0)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Faculty</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deadline</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($workflowAssignments as $assignment)
                                        @php
                                            $badge = match($assignment->status) {
                                                \App\Models\CourseAssignment::STATUS_PENDING => 'bg-amber-100 text-amber-800',
                                                \App\Models\CourseAssignment::STATUS_IN_PROGRESS => 'bg-sky-100 text-sky-800',
                                                \App\Models\CourseAssignment::STATUS_SUBMITTED => 'bg-indigo-100 text-indigo-800',
                                                \App\Models\CourseAssignment::STATUS_UNDER_REVIEW => 'bg-blue-100 text-blue-800',
                                                \App\Models\CourseAssignment::STATUS_CHANGES_REQUESTED => 'bg-orange-100 text-orange-800',
                                                \App\Models\CourseAssignment::STATUS_REJECTED => 'bg-rose-100 text-rose-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                            $isOverdue = $assignment->deadline && $assignment->deadline->isPast();
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-bold text-gray-900">{{ $assignment->course->course_code }}</div>
                                                <div class="text-xs text-gray-500">{{ $assignment->course->course_title }}</div>
                                                <div class="text-[10px] text-indigo-600 uppercase mt-1">{{ $assignment->course->programme->code }} / {{ $assignment->course->level->level_code }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">{{ $assignment->department->name }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-600">{{ $assignment->faculty->name }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                {{ $assignment->deadline?->format('d M Y') ?? 'Not set' }}
                                                @if($isOverdue)
                                                    <div class="text-[10px] font-semibold text-red-600 mt-1">Overdue</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                                    {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="px-5 sm:px-6 py-10">
                                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6">
                                    <h4 class="text-sm font-semibold text-gray-900">No active workflow items yet.</h4>
                                    <p class="mt-2 text-sm text-gray-500">
                                        The CDC side is ready, but no course has moved into HOD assignment yet. The next useful view is the course pool waiting to enter the cycle.
                                    </p>

                                    @if($coursesReadyForHod->count() > 0)
                                        <div class="mt-5 overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                <thead class="bg-white">
                                                    <tr>
                                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Course</th>
                                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Programme</th>
                                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Level</th>
                                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 bg-white">
                                                    @foreach($coursesReadyForHod as $course)
                                                        <tr class="hover:bg-gray-50">
                                                            <td class="px-4 py-3">
                                                                <div class="font-medium text-gray-900">{{ $course->course_code }}</div>
                                                                <div class="text-xs text-gray-500">{{ $course->course_title }}</div>
                                                            </td>
                                                            <td class="px-4 py-3 text-gray-600">{{ $course->programme->name }}</td>
                                                            <td class="px-4 py-3 text-gray-600">{{ $course->level->level_code }}</td>
                                                            <td class="px-4 py-3">
                                                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->course_type === \App\Models\Course::TYPE_ELECTIVE ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                                                    {{ ucfirst($course->course_type) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl">
                        <div class="px-5 sm:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Recent Programmes</h3>
                            <p class="text-sm text-gray-500 mt-1">Recently created or updated structures.</p>
                        </div>
                        <div class="overflow-x-auto">
                            @if($recentProgrammes->count() > 0)
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Programme</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($recentProgrammes as $prog)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-bold text-gray-900">{{ $prog->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $prog->code }}</div>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-600">{{ $prog->academic_year }}</td>
                                                <td class="px-6 py-4">
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $prog->isActive() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                                        {{ strtoupper($prog->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="px-6 py-12 text-center text-gray-500">
                                    No programmes created yet.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div id="cdc-handoffs" class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-xl">
                        <div class="px-5 sm:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">CDC and HOD Handoffs</h3>
                            <p class="text-sm text-gray-500 mt-1">Latest notifications between CDC and HOD.</p>
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
    </div>
</x-app-layout>
