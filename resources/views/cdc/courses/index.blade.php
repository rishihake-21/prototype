@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    {{-- Breadcrumb --}}
    <div class="mb-5 text-sm text-gray-500 flex items-center gap-1">
        <a href="{{ route('cdc.programmes.index') }}" class="hover:text-indigo-600">Programmes</a>
        <span>/</span>
        <a href="{{ route('cdc.programmes.show', $programme) }}" class="hover:text-indigo-600">{{ $programme->name }}</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">Courses</span>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Level-wise Course Definitions</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $programme->name }} — {{ $programme->academic_year }}</p>
        </div>
        <a href="{{ route('cdc.courses.create', $programme) }}"
           class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Course
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Level filter --}}
    <form method="GET" class="mb-4 flex gap-3 items-center">
        <label class="text-sm font-medium text-gray-700">Filter by Level:</label>
        <select name="level_id" onchange="this.form.submit()"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Levels</option>
            @foreach($programme->levels as $lv)
                <option value="{{ $lv->id }}" {{ request('level_id') == $lv->id ? 'selected' : '' }}>
                    {{ $lv->level_code }} – {{ $lv->level_name }}
                </option>
            @endforeach
        </select>
    </form>

    @php
        $grouped = $courses->getCollection()->groupBy(fn($c) => $c->level_id);
    @endphp

    @if($courses->isEmpty())
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 py-14 text-center">
            <p class="text-sm text-gray-500">No courses found. Use "Add Course" to define courses for this programme.</p>
        </div>
    @else
        @foreach($programme->levels as $level)
            @if(!$grouped->has($level->id)) @continue @endif
            @php $levelCourses = $grouped[$level->id]; @endphp

            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-700">
                        {{ $level->level_code }} — {{ $level->level_name }}
                    </h2>
                    <a href="{{ route('cdc.courses.bulk-edit', [$programme, $level]) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Bulk Edit Level
                    </a>
                </div>
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-x-auto">
                    <table class="min-w-full text-xs border-collapse">
                        <thead class="bg-indigo-700 text-white">
                            <tr>
                                <th class="px-2.5 py-2.5 text-left border border-indigo-600">Sr.</th>
                                <th class="px-2.5 py-2.5 text-left border border-indigo-600">Code</th>
                                <th class="px-2.5 py-2.5 text-left border border-indigo-600 min-w-[160px]">Course Title</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">Abbr</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">TH</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">TU</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">PR</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">Hrs</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">Cr</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">Th.Ppr</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">Th.Mx</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">Test</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">PR</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">OR</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">TW</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">Total</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">Type</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($levelCourses as $i => $course)
                            <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition">
                                <td class="px-2.5 py-2 border border-gray-200 text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 font-mono font-semibold text-gray-800">{{ $course->course_code }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-gray-800">
                                    {{ $course->course_title }}
                                    @if($course->is_common_course)
                                        <span class="ml-1 inline-block bg-teal-100 text-teal-700 text-[10px] px-1.5 py-0.5 rounded-full font-medium">Common</span>
                                    @endif
                                    @if($course->course_type === 'elective')
                                        <span class="ml-1 inline-block bg-purple-100 text-purple-700 text-[10px] px-1.5 py-0.5 rounded-full font-medium">{{ $course->elective_group }}</span>
                                    @endif
                                </td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center text-gray-600">{{ $course->course_abbr }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->th_hours }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->tu_hours }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->pr_hours }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center font-medium text-indigo-700">{{ $course->total_hours }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->credits }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->theory_paper_hrs ?: '—' }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->theory_max_marks ?: '—' }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->test_max_marks ?: '—' }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->pr_max_marks ?: '—' }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->or_max_marks ?: '—' }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->tw_max_marks ?: '—' }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center font-bold text-gray-800">{{ $course->total_marks ?: '—' }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium
                                        {{ $course->course_type === 'elective' ? 'bg-purple-100 text-purple-700' :
                                           ($course->course_type === 'audit' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                        {{ ucfirst($course->course_type) }}
                                    </span>
                                </td>
                                <td class="px-2.5 py-2 border border-gray-200">
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('cdc.courses.edit', [$programme, $course]) }}"
                                           class="text-blue-600 hover:underline">Edit</a>
                                        <span class="text-gray-300">|</span>
                                        <form method="POST" action="{{ route('cdc.courses.clone', $course) }}"
                                              onsubmit="return confirm('Clone this course?')">
                                            @csrf
                                            <button type="submit" class="text-gray-500 hover:text-gray-800 hover:underline">Clone</button>
                                        </form>
                                        <span class="text-gray-300">|</span>
                                        <form method="POST" action="{{ route('cdc.courses.destroy', [$programme, $course]) }}"
                                              onsubmit="return confirm('Delete this course?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:underline">Del</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        {{-- Level totals --}}
                        <tfoot>
                            <tr class="bg-indigo-50 text-xs font-semibold">
                                <td colspan="4" class="px-2.5 py-2 border border-gray-200 text-right text-gray-700">TOTAL</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $levelCourses->sum('th_hours') }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $levelCourses->sum('tu_hours') }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $levelCourses->sum('pr_hours') }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center text-indigo-700">{{ $levelCourses->sum('total_hours') }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ number_format($levelCourses->sum('credits'), 2) }}</td>
                                <td colspan="6" class="px-2.5 py-2 border border-gray-200"></td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center font-bold">{{ $levelCourses->sum('total_marks') }}</td>
                                <td colspan="2" class="border border-gray-200"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach
    @endif

    <div class="mt-4">{{ $courses->withQueryString()->links() }}</div>
</div>
@endsection
