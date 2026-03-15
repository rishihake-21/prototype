@extends('layouts.app')

@section('content')
@php
    $levelStats = $levelStats ?? [];
    $selectedLevelId = old('level_id', $selectedLevelId ?? $course?->level_id);
@endphp
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6"
     x-data="courseForm()" x-init="init()">

    {{-- Breadcrumb --}}
    <div class="mb-5 text-sm text-gray-500 flex items-center gap-1">
        <a href="{{ route('cdc.programmes.index') }}" class="hover:text-indigo-600">Programmes</a>
        <span>/</span>
        <a href="{{ route('cdc.courses.index', $programme) }}" class="hover:text-indigo-600">Courses</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">{{ $course ? 'Edit: ' . $course->course_code : 'Add Course' }}</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-6">
        {{ $course ? 'Edit Course' : 'Add Course' }} - {{ $programme->name }}
    </h1>

    <div class="mb-5 rounded-xl border border-indigo-200 bg-indigo-50/70 p-4">
        <div class="text-sm font-semibold text-indigo-900">Course Definition is now driven by Scheme at a Glance</div>
        <p class="mt-1 text-sm text-indigo-800">Select a level and define courses only within the offered slots configured for that level.</p>
    </div>

    @if($errors->any())
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        <p class="font-medium mb-1">Please fix the following errors:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ $course
              ? route('cdc.courses.update', [$programme, $course])
              : route('cdc.courses.store', $programme) }}">
        @csrf
        @if($course) @method('PUT') @endif

        {{-- Section: Identification --}}
        <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-5 mb-4">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4">Course Identification</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- Level --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Level <span class="text-red-500">*</span></label>
                    <select name="level_id" required x-model="selectedLevel"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($programme->levels as $lv)
                            <option value="{{ $lv->id }}"
                                {{ $selectedLevelId == $lv->id ? 'selected' : '' }}>
                                {{ $lv->level_code }} - {{ $lv->level_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('level_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Course Code --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        Course Code <span class="text-red-500">*</span>
                        <span class="ml-1 text-gray-400 font-normal">(6 digits: YY L NNN)</span>
                    </label>
                    <input type="text" name="course_code"
                           value="{{ old('course_code', $course?->course_code) }}"
                           placeholder="e.g. 231001" maxlength="6" pattern="\d{6}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('course_code') border-red-400 @enderror"
                           title="6-digit code: first 2 = year, 3rd = level digit, 4th-6th = serial number">
                    @error('course_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Abbreviation --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Abbreviation <span class="text-red-500">*</span></label>
                    <input type="text" name="course_abbr"
                           value="{{ old('course_abbr', $course?->course_abbr) }}"
                           placeholder="e.g. CMS" maxlength="20"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('course_abbr') border-red-400 @enderror">
                    @error('course_abbr')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Course Title --}}
            <div class="mt-4">
                <label class="block text-xs font-medium text-gray-700 mb-1">Course Title <span class="text-red-500">*</span></label>
                <input type="text" name="course_title"
                       value="{{ old('course_title', $course?->course_title) }}"
                       placeholder="e.g. Communication Skills"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('course_title') border-red-400 @enderror">
                @error('course_title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-lg bg-gray-50 px-3 py-2">
                    <div class="text-[11px] uppercase tracking-wide text-gray-500">Offered</div>
                    <div class="text-lg font-semibold text-gray-900" x-text="selectedStats.offered ?? 0"></div>
                </div>
                <div class="rounded-lg bg-gray-50 px-3 py-2">
                    <div class="text-[11px] uppercase tracking-wide text-gray-500">Already Defined</div>
                    <div class="text-lg font-semibold text-gray-900" x-text="selectedStats.defined ?? 0"></div>
                </div>
                <div class="rounded-lg bg-gray-50 px-3 py-2">
                    <div class="text-[11px] uppercase tracking-wide text-gray-500">Remaining Slots</div>
                    <div class="text-lg font-semibold text-indigo-700" x-text="selectedStats.remaining ?? 0"></div>
                </div>
                <div class="rounded-lg bg-gray-50 px-3 py-2">
                    <div class="text-[11px] uppercase tracking-wide text-gray-500">Course Mix</div>
                    <div class="text-sm font-semibold text-gray-900">
                        <span x-text="`Comp ${selectedStats.compulsory_defined ?? 0}/${selectedStats.compulsory_limit ?? 0}`"></span>
                        <span class="mx-1 text-gray-300">|</span>
                        <span x-text="`Elec Pool ${selectedStats.elective_defined ?? 0}/${selectedStats.elective_offered_limit ?? selectedStats.elective_limit ?? 0}`"></span>
                    </div>
                    <div class="mt-1 text-[11px] text-gray-500" x-text="`HOD will pick ${selectedStats.elective_to_complete_limit ?? 0}`"></div>
                </div>
            </div>

            <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-3 text-xs text-gray-700">
                <div class="font-semibold text-gray-800">Level Limits</div>
                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    <div x-text="`TH ${selectedStats.th_used ?? 0} / ${selectedStats.th_limit ?? 0}`"></div>
                    <div x-text="`TU ${selectedStats.tu_used ?? 0} / ${selectedStats.tu_limit ?? 0}`"></div>
                    <div x-text="`PR ${selectedStats.pr_used ?? 0} / ${selectedStats.pr_limit ?? 0}`"></div>
                    <div x-text="`Hours ${selectedStats.hours_used ?? 0} / ${selectedStats.hours_limit ?? 0}`"></div>
                    <div x-text="`Credits ${selectedStats.credits_used ?? 0} / ${selectedStats.credits_limit ?? 0}`"></div>
                    <div x-text="`Marks ${selectedStats.marks_used ?? 0} / ${selectedStats.marks_limit ?? 0}`"></div>
                </div>
            </div>
        </div>

        {{-- Section: Teaching Scheme --}}
        <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-5 mb-4">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4">Teaching Scheme</h2>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">TH (Theory)</label>
                    <input type="number" name="th_hours" x-model.number="th"
                           value="{{ old('th_hours', $course?->th_hours ?? 0) }}" min="0"
                           class="w-full text-center rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">TU (Tutorial)</label>
                    <input type="number" name="tu_hours" x-model.number="tu"
                           value="{{ old('tu_hours', $course?->tu_hours ?? 0) }}" min="0"
                           class="w-full text-center rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">PR (Practical)</label>
                    <input type="number" name="pr_hours" x-model.number="pr"
                           value="{{ old('pr_hours', $course?->pr_hours ?? 0) }}" min="0"
                           class="w-full text-center rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Total Hours</label>
                    <div class="w-full text-center rounded-lg border border-gray-200 bg-indigo-50 px-3 py-2 text-sm font-bold text-indigo-700"
                         x-text="(Number(th) || 0) + (Number(tu) || 0) + (Number(pr) || 0)"></div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Credits <span class="text-red-500">*</span></label>
                    <input type="number" name="credits"
                           value="{{ old('credits', $course?->credits ?? 0) }}" min="0" step="0.5"
                           class="w-full text-center rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('credits') border-red-400 @enderror">
                    @error('credits')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-400">TH - Theory, TU - Tutorial, PR - Practical. Total Hours is auto-calculated.</p>
            <p class="mt-1 text-xs text-gray-500">Validation also checks the level totals saved in Scheme at a Glance.</p>
        </div>

        {{-- Section: Examination Scheme --}}
        <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-5 mb-4">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4">Examination Scheme</h2>

            <div class="mb-5 border-b border-gray-100 pb-5">
                <label class="block text-xs font-medium text-gray-700 mb-1">Theory Paper Hrs</label>
                <input type="number" name="theory_paper_hrs"
                       value="{{ old('theory_paper_hrs', $course?->theory_paper_hrs ?? 0) }}" min="0" step="0.5"
                       class="w-32 text-center rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-3">Assessment Marks Breakdown</h3>

            @php
                $schemeRows = $schemeRows ?? [];
                $leafCols = $leafCols ?? [];
            @endphp

            @if(empty($schemeRows) || empty($leafCols))
                <div class="rounded-lg bg-orange-50 border border-orange-200 p-4 text-sm text-orange-700">
                    <p class="font-medium">No Assessment Columns Defined.</p>
                    <p class="mt-1">Please edit the parent Scheme ({{ $programme->scheme?->name }}) to define assessment columns.</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm mt-4 mb-6">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 text-gray-700">
                            @foreach($schemeRows as $row)
                                <tr>
                                    @foreach($row as $col)
                                        <th colspan="{{ $col['colspan'] }}" rowspan="{{ $col['rowspan'] }}"
                                            class="border-b border-r border-gray-200 px-4 py-2 font-semibold text-center {{ $col['is_leaf'] ? 'bg-indigo-50/30 text-xs text-gray-600' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $col['name'] }}
                                        </th>
                                    @endforeach
                                </tr>
                            @endforeach
                        </thead>
                        <tbody class="bg-white">
                            <tr>
                                @foreach($leafCols as $leaf)
                                    <td class="px-2 py-3 border-r border-gray-200 text-center hover:bg-indigo-50/30 transition">
                                        <input type="number" name="assessment_marks[{{ $leaf['id'] }}]"
                                               x-model.number="marks[{{ $leaf['id'] }}]" min="0" placeholder="0"
                                               class="w-full min-w-[60px] max-w-[80px] text-center rounded border-gray-300 text-xs py-1.5 focus:ring-2 focus:ring-indigo-500 mx-auto">
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end border-t border-gray-100 pt-4">
                    <div class="w-48">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Total Expected Marks</label>
                        <div class="w-full text-center rounded-lg border border-gray-200 bg-green-50 px-3 py-2 text-sm font-bold text-green-700"
                             x-text="totalMarks"></div>
                    </div>
                </div>

                <p class="mt-2 text-xs text-gray-400 text-right">Total marks are calculated automatically from the max-mark leaf values.</p>
                <p class="mt-1 text-xs text-gray-500 text-right">The course will be blocked if this pushes the level above its total marks limit.</p>
            @endif
        </div>

        {{-- Section: Classification --}}
        <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-5 mb-6">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4">Classification</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Course Type --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Course Type <span class="text-red-500">*</span></label>
                    <select name="course_type" x-model="courseType" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($types as $val => $label)
                            <option value="{{ $val }}" {{ old('course_type', $course?->course_type ?? 'compulsory') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Elective Group (conditional) --}}
                <div x-show="courseType === 'elective'" x-transition>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        Elective Group <span class="text-red-500">*</span>
                    </label>
                    <select name="elective_group"
                            :required="courseType === 'elective'"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Select Group --</option>
                        @foreach($electiveGroups as $grp)
                            <option value="{{ $grp }}" {{ old('elective_group', $course?->elective_group) === $grp ? 'selected' : '' }}>
                                {{ $grp }}
                            </option>
                        @endforeach
                    </select>
                    @error('elective_group')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Common Course --}}
            <div class="mt-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_common_course" value="1" x-model="isCommon"
                           {{ old('is_common_course', $course?->is_common_course) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-medium text-gray-700">This course is shared across multiple departments</span>
                </label>
            </div>

            {{-- Departments (conditional on common course) --}}
            <div x-show="isCommon" x-transition class="mt-3">
                <label class="block text-xs font-medium text-gray-700 mb-2">Select Departments <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                    @php $selectedDepts = old('departments', $course?->departments?->pluck('id')->toArray() ?? []); @endphp
                    @foreach($departments as $dept)
                    <label class="flex items-center gap-2 cursor-pointer bg-gray-50 rounded-lg px-3 py-2 text-sm hover:bg-gray-100 transition">
                        <input type="checkbox" name="departments[]" value="{{ $dept->id }}"
                               {{ in_array($dept->id, $selectedDepts) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600">
                        {{ $dept->name }} ({{ $dept->code }})
                    </label>
                    @endforeach
                </div>
                @error('departments')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex gap-3">
            <button type="submit"
                    class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                {{ $course ? 'Update Course' : 'Create Course' }}
            </button>
            <a href="{{ route('cdc.courses.index', $programme) }}"
               class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function courseForm() {
    return {
        th: {{ old('th_hours', $course?->th_hours ?? 0) }},
        tu: {{ old('tu_hours', $course?->tu_hours ?? 0) }},
        pr: {{ old('pr_hours', $course?->pr_hours ?? 0) }},
        marks: @json($marks ?? []),
        levelStats: @json($levelStats),
        selectedLevel: '{{ $selectedLevelId }}',
        courseType: '{{ old('course_type', $course?->course_type ?? 'compulsory') }}',
        isCommon: {{ old('is_common_course', $course?->is_common_course ?? false) ? 'true' : 'false' }},
        get selectedStats() {
            return this.levelStats[this.selectedLevel] || {};
        },
        get totalMarks() {
            return Object.values(this.marks).reduce((acc, val) => acc + (Number(val) || 0), 0);
        },
        init() {
            // Ensure all columns exist in marks object for reactivity
            @foreach(($leafCols ?? []) as $leaf)
                if (this.marks[{{ $leaf['id'] }}] === undefined) {
                    this.marks[{{ $leaf['id'] }}] = null;
                }
            @endforeach
        },
    };
}
</script>
@endpush
@endsection
