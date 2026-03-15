@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Course Assignments') }} - {{ $department->name }}
    </h2>
@endsection

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
            <div class="bg-white shadow sm:rounded-lg p-6 sticky top-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Assign New Subject</h3>
                <p class="mb-4 text-xs text-gray-500">
                    Elective courses come here as a pool from CDC. Only the elective courses selected by HOD for the current academic year move forward in the syllabus cycle.
                </p>

                <form action="{{ route('hod.assignments.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Course / Subject</label>
                        <select name="course_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">
                                    [{{ $course->course_code }}] {{ $course->course_title }}
                                    ({{ $course->programme->code }} - {{ $course->level->level_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Faculty Member</label>
                        <select name="faculty_user_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Select Faculty --</option>
                            @foreach($faculty as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Academic Year</label>
                        <input type="text" name="academic_year" value="{{ date('Y') }}-{{ date('y') + 1 }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Submission Deadline (Optional)</label>
                        <input type="date" name="deadline"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Assign Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Active Assignments</h3>
                        <p class="text-xs text-gray-500 mt-1">Assignments stay linked through drafting, review, feedback, and approval.</p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        {{ $assignments->count() }} Total
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Course</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Faculty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Deadline</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($assignments as $assign)
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
                                    $isLocked = $latestSyllabus && !$latestSyllabus->isDraft();
                                    $isOverdue = $assign->deadline && $assign->deadline->isPast() && $assign->status !== \App\Models\CourseAssignment::STATUS_COMPLETED;
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $assign->course->course_code }}</div>
                                        <div class="text-xs text-gray-500">{{ $assign->course->course_title }}</div>
                                        <div class="text-[10px] text-indigo-600 uppercase mt-1">{{ $assign->course->programme->name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $assign->faculty->name }}</div>
                                        <div class="text-xs text-gray-400">Assigned by {{ $assign->assigner->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $assign->academic_year }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $assign->deadline?->format('d M Y') ?? 'Not set' }}
                                        @if($isOverdue)
                                            <div class="text-[10px] font-semibold text-red-600 mt-1">Overdue</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                            {{ ucfirst(str_replace('_', ' ', $assign->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($latestSyllabus)
                                            <a href="{{ route('syllabi.show', $latestSyllabus) }}" class="text-indigo-600 hover:text-indigo-900 text-xs font-bold mr-3">
                                                View Syllabus
                                            </a>
                                        @endif

                                        @if(!$isLocked)
                                            <form action="{{ route('hod.assignments.destroy', $assign) }}" method="POST" class="inline" onsubmit="return confirm('Revoke this assignment? Any draft linked to it will be removed.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-xs font-bold">
                                                    Revoke
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">
                                                Locked after submission
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No courses assigned yet. Use the form on the left to start.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
