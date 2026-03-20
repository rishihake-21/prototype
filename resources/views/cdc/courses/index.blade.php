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
            <p class="text-sm text-gray-500 mt-1">{{ $programme->name }} - {{ $programme->academic_year }}</p>
        </div>
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
                    {{ $lv->level_code }} - {{ $lv->level_name }}
                </option>
            @endforeach
        </select>
    </form>

    @php
        $grouped = $courses->getCollection()->groupBy(fn($c) => $c->level_id);
        $leafCols = $programme->scheme?->getCourseAssessmentLeafColumns() ?? [];
    @endphp

    @if($courses->isEmpty())
        <div class="mb-6 bg-white rounded-xl shadow-sm ring-1 ring-gray-200 py-8 text-center">
            <p class="text-sm text-gray-500">No courses have been defined yet. Use the level-wise pending slots below to start filling the programme.</p>
        </div>
    @endif

    @foreach($programme->levels as $level)
            @php
                $levelCourses = $grouped->get($level->id, collect());
                $structure = $level->structure;
                $offered = (int) ($structure?->total_courses_offered ?? $level->courses_limit ?? 0);
                $defined = $levelCourses->count();
                $remaining = max($offered - $defined, 0);
                $compLimit = (int) ($structure?->compulsory_count ?? 0);
                $elecLimit = (int) ($structure?->elective_offered_count ?? $structure?->elective_count ?? 0);
                $elecToComplete = (int) ($structure?->elective_count ?? 0);
                $compDefined = $levelCourses->where('course_type', 'compulsory')->count();
                $elecDefined = $levelCourses->where('course_type', 'elective')->count();
            @endphp

            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-700">
                            {{ $level->level_code }} - {{ $level->level_name }}
                        </h2>
                        <div class="mt-1 flex flex-wrap gap-2 text-[11px] text-gray-600">
                            <span class="rounded-full bg-indigo-50 px-2 py-0.5">Defined {{ $defined }} / {{ $offered }} offered</span>
                            <span class="rounded-full bg-gray-100 px-2 py-0.5">Remaining slots {{ $remaining }}</span>
                            <span class="rounded-full bg-gray-100 px-2 py-0.5">Compulsory {{ $compDefined }} / {{ $compLimit }}</span>
                            <span class="rounded-full bg-gray-100 px-2 py-0.5">Elective pool {{ $elecDefined }} / {{ $elecLimit }}</span>
                            <span class="rounded-full bg-gray-100 px-2 py-0.5">HOD to pick {{ $elecToComplete }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($remaining > 0)
                            <a href="{{ route('cdc.courses.create', [$programme, 'level_id' => $level->id]) }}"
                               class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition">
                                Add Course to {{ $level->level_code }}
                            </a>
                        @else
                            <span class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-500">
                                Level filled
                            </span>
                        @endif
                    </div>
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
                                @if(!empty($leafCols))
                                    @foreach($leafCols as $leaf)
                                        <th class="px-2.5 py-2.5 text-center border border-indigo-600">{{ $leaf['name'] }}</th>
                                    @endforeach
                                @endif
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">Total</th>
                                <th class="px-2.5 py-2.5 text-center border border-indigo-600">Type</th>
                                <th class="px-2.5 py-2.5 text-left border border-indigo-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($levelCourses as $i => $course)
                                @php
                                    $marksByComponent = $course->assessments?->pluck('max_marks', 'component_id') ?? collect();
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-2.5 py-2 border border-gray-200 text-gray-500">{{ $i + 1 }}</td>
                                    <td class="px-2.5 py-2 border border-gray-200 font-mono">{{ $course->course_code }}</td>
                                    <td class="px-2.5 py-2 border border-gray-200 font-medium">{{ $course->course_title }}</td>
                                    <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->course_abbr }}</td>
                                    <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->th_hours }}</td>
                                    <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->tu_hours }}</td>
                                    <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->pr_hours }}</td>
                                    <td class="px-2.5 py-2 border border-gray-200 text-center font-medium text-indigo-700">{{ $course->total_hours }}</td>
                                    <td class="px-2.5 py-2 border border-gray-200 text-center">{{ number_format((float) $course->credits, 2) }}</td>
                                    <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $course->theory_paper_hrs ?: '--' }}</td>
                                    @if(!empty($leafCols))
                                        @foreach($leafCols as $leaf)
                                            @php $v = $marksByComponent->get($leaf['id']); @endphp
                                            <td class="px-2.5 py-2 border border-gray-200 text-center">{{ ($v === null || $v === '') ? '--' : $v }}</td>
                                        @endforeach
                                    @endif
                                    <td class="px-2.5 py-2 border border-gray-200 text-center font-bold text-gray-800">{{ $course->total_marks ?: '--' }}</td>
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
                            @empty
                                <tr>
                                    <td colspan="{{ 13 + count($leafCols) }}" class="px-4 py-6 text-center text-sm text-gray-500">
                                        No courses defined yet for {{ $level->level_code }}. Use the pending slots below to add them.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-indigo-50 text-xs font-semibold">
                                <td colspan="4" class="px-2.5 py-2 border border-gray-200 text-right text-gray-700">TOTAL</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $levelCourses->sum('th_hours') }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $levelCourses->sum('tu_hours') }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ $levelCourses->sum('pr_hours') }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center text-indigo-700">{{ $levelCourses->sum('total_hours') }}</td>
                                <td class="px-2.5 py-2 border border-gray-200 text-center">{{ number_format($levelCourses->sum('credits'), 2) }}</td>
                                <td class="px-2.5 py-2 border border-gray-200"></td>
                                @if(!empty($leafCols))
                                    @foreach($leafCols as $leaf)
                                        <td class="px-2.5 py-2 border border-gray-200"></td>
                                    @endforeach
                                @endif
                                <td class="px-2.5 py-2 border border-gray-200 text-center font-bold">{{ $levelCourses->sum('total_marks') }}</td>
                                <td colspan="2" class="border border-gray-200"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($remaining > 0)
                    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @for($slot = 0; $slot < $remaining; $slot++)
                            <a href="{{ route('cdc.courses.create', [$programme, 'level_id' => $level->id]) }}"
                               class="rounded-xl border border-dashed border-blue-300 bg-blue-50/60 px-4 py-3 text-sm text-blue-700 hover:bg-blue-100 transition">
                                <div class="font-semibold">Pending course slot {{ $defined + $slot + 1 }}</div>
                                <div class="mt-1 text-xs text-blue-600">Create a course for {{ $level->level_code }}.</div>
                            </a>
                        @endfor
                    </div>
                @endif
            </div>
    @endforeach

    <div class="mt-4">{{ $courses->withQueryString()->links() }}</div>
</div>
@endsection
