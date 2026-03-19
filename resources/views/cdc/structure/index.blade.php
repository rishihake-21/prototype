@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8"
     x-data="schemeForm()" x-init="init()">

    {{-- Breadcrumb --}}
    <div class="mb-5 text-sm text-gray-500 flex items-center gap-1">
        <a href="{{ route('cdc.programmes.index') }}" class="hover:text-indigo-600">Programmes</a>
        <span>/</span>
        <a href="{{ route('cdc.programmes.show', $programme) }}" class="hover:text-indigo-600">{{ $programme->name }}</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">Scheme at a Glance</span>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Scheme at a Glance</h1>
            <p class="text-sm text-gray-500 mt-1">
                Programme: <strong>{{ $programme->name }}</strong> ({{ $programme->academic_year }})
            </p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
        {{ session('success') }}
    </div>
    @endif
    @if(session('structure_errors'))
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        <div class="font-semibold mb-2">Please fix these issues:</div>
        <ul class="list-disc list-inside space-y-1">
            @foreach((array) session('structure_errors') as $msg)
                <li>{{ $msg }}</li>
            @endforeach
        </ul>
    </div>
    @elseif(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-600">
        {{ session('error') }}
    </div>
    @endif

    {{-- Flash warnings from Alpine --}}
    <div x-show="warnings.length > 0" class="mb-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
        <ul class="list-disc list-inside space-y-1">
            <template x-for="w in warnings" :key="w">
                <li x-text="w"></li>
            </template>
        </ul>
    </div>

    <form method="POST" action="{{ route('cdc.programmes.structure.update', $programme) }}" @submit.prevent="submitForm">
        @csrf @method('PUT')

        <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-x-auto">
            <table class="min-w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-indigo-700 text-white text-xs uppercase tracking-wide">
                        <th rowspan="2" class="px-3 py-3 text-center align-middle border border-indigo-600 w-24">Level</th>
                        <th rowspan="2" class="px-3 py-3 text-left align-middle border border-indigo-600">Level Name</th>
                        <th rowspan="2" class="px-3 py-3 text-center align-middle border border-indigo-600">Total Courses<br>Offered</th>
                        <th rowspan="2" class="px-3 py-3 text-center align-middle border border-indigo-600">Courses to<br>Complete</th>
                        <th rowspan="2" class="px-3 py-3 text-center align-middle border border-indigo-600 text-xs">Compulsory<br>Courses</th>
                        <th rowspan="2" class="px-3 py-3 text-center align-middle border border-indigo-600 text-xs">Elective<br>Offered</th>
                        <th rowspan="2" class="px-3 py-3 text-center align-middle border border-indigo-600 text-xs">Elective To<br>Complete</th>
                        <th colspan="4" class="px-3 py-2 text-center border border-indigo-600">Teaching Scheme</th>
                        <th rowspan="2" class="px-3 py-3 text-center align-middle border border-indigo-600">Total<br>Credits</th>
                        <th rowspan="2" class="px-3 py-3 text-center align-middle border border-indigo-600">Total<br>Marks</th>
                        <th rowspan="2" class="px-3 py-3 text-left align-middle border border-indigo-600">Notes</th>
                    </tr>
                    <tr class="bg-indigo-600 text-white text-xs uppercase tracking-wide">
                        <th class="px-3 py-2 text-center border border-indigo-600">TH</th>
                        <th class="px-3 py-2 text-center border border-indigo-600">TU</th>
                        <th class="px-3 py-2 text-center border border-indigo-600">PR</th>
                        <th class="px-3 py-2 text-center border border-indigo-600 font-bold">Total Hrs</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($programme->levels as $index => $level)
                    @php
                        $s = $level->structure;
                        $rowBg = $level->isAudit() ? 'bg-amber-50' : ($index % 2 === 0 ? 'bg-white' : 'bg-gray-50');
                    @endphp
                    <tr class="{{ $rowBg }}"
                        x-data="{
                            levelId: {{ $level->id }},
                            th: {{ $s?->th_hours ?? 0 }},
                            tu: {{ $s?->tu_hours ?? 0 }},
                            pr: {{ $s?->pr_hours ?? 0 }},
                            offered: {{ $s?->total_courses_offered ?? 0 }},
                            toComplete: {{ $s?->courses_to_complete ?? 0 }},
                            comp: {{ $s?->compulsory_count ?? 0 }},
                            elecOffered: {{ $s?->elective_offered_count ?? $s?->elective_count ?? 0 }},
                            elecToComplete: {{ $s?->elective_count ?? 0 }},
                            get totalHrs() { return this.th + this.tu + this.pr; },
                            get warning() {
                                return this.toComplete > this.offered
                                    ? 'Cannot exceed courses offered'
                                    : '';
                            }
                        }"
                        x-init="registerRow($data)">

                        {{-- Level code (read-only) --}}
                        <td class="px-3 py-2 text-center font-mono text-xs font-semibold text-gray-700 border border-gray-200">
                            {{ $level->level_code }}
                        </td>

                        {{-- Level name (editable) --}}
                        <td class="px-3 py-2 border border-gray-200">
                            <input type="text" name="rows[{{ $level->id }}][level_name]"
                                   value="{{ $s?->level?->level_name ?? $level->level_name }}"
                                   class="w-full bg-transparent border-none text-sm text-gray-800 focus:outline-none focus:ring-1 focus:ring-indigo-400 rounded px-1">
                        </td>

                        {{-- Total Courses Offered --}}
                        <td class="px-3 py-2 border border-gray-200">
                            <input type="number" name="rows[{{ $level->id }}][total_courses_offered]"
                                   x-model.number="offered" min="0"
                                   class="w-16 text-center rounded border border-gray-200 text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        </td>

                        {{-- Courses to Complete --}}
                        <td class="px-3 py-2 border border-gray-200">
                            <div class="relative">
                                <input type="number" name="rows[{{ $level->id }}][courses_to_complete]"
                                       x-model.number="toComplete" min="0"
                                       :class="warning ? 'border-red-400' : 'border-gray-200'"
                                       class="w-16 text-center rounded border text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                <p x-show="warning" x-text="warning" class="absolute -bottom-5 left-0 text-xs text-red-500 whitespace-nowrap z-10"></p>
                            </div>
                        </td>

                        {{-- Compulsory Courses --}}
                        <td class="px-3 py-2 border border-gray-200">
                            @if($level->isAudit())
                                <span class="text-[10px] text-gray-400 italic bg-gray-100 px-2 py-1 rounded">N/A</span>
                                <input type="hidden" name="rows[{{ $level->id }}][compulsory_count]" value="0">
                            @else
                                <input type="number" name="rows[{{ $level->id }}][compulsory_count]"
                                       x-model.number="comp" min="0"
                                       class="w-14 text-center rounded border border-gray-200 text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-gray-50">
                            @endif
                        </td>

                        {{-- Elective Courses --}}
                        <td class="px-3 py-2 border border-gray-200">
                            @if($level->isAudit())
                                <span class="text-[10px] text-gray-400 italic bg-gray-100 px-2 py-1 rounded">N/A</span>
                                <input type="hidden" name="rows[{{ $level->id }}][elective_offered_count]" value="0">
                            @else
                                <input type="number" name="rows[{{ $level->id }}][elective_offered_count]"
                                       x-model.number="elecOffered" min="0"
                                       class="w-14 text-center rounded border border-gray-200 text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-gray-50">
                            @endif
                        </td>

                        {{-- Elective Courses To Complete --}}
                        <td class="px-3 py-2 border border-gray-200">
                            @if($level->isAudit())
                                <span class="text-[10px] text-gray-400 italic bg-gray-100 px-2 py-1 rounded">N/A</span>
                                <input type="hidden" name="rows[{{ $level->id }}][elective_count]" value="0">
                            @else
                                <input type="number" name="rows[{{ $level->id }}][elective_count]"
                                       x-model.number="elecToComplete" min="0"
                                       class="w-14 text-center rounded border border-gray-200 text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-gray-50">
                            @endif
                        </td>

                        {{-- TH --}}
                        <td class="px-3 py-2 border border-gray-200">
                            <input type="number" name="rows[{{ $level->id }}][th_hours]"
                                   x-model.number="th" min="0"
                                   class="w-14 text-center rounded border border-gray-200 text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        </td>

                        {{-- TU --}}
                        <td class="px-3 py-2 border border-gray-200">
                            <input type="number" name="rows[{{ $level->id }}][tu_hours]"
                                   x-model.number="tu" min="0"
                                   class="w-14 text-center rounded border border-gray-200 text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        </td>

                        {{-- PR --}}
                        <td class="px-3 py-2 border border-gray-200">
                            <input type="number" name="rows[{{ $level->id }}][pr_hours]"
                                   x-model.number="pr" min="0"
                                   class="w-14 text-center rounded border border-gray-200 text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        </td>

                        {{-- Total Hours (auto-calculated, disabled) --}}
                        <td class="px-3 py-2 border border-gray-200 text-center font-semibold text-indigo-700">
                            <span x-text="totalHrs"></span>
                            <input type="hidden" name="rows[{{ $level->id }}][total_hours]" :value="totalHrs">
                        </td>

                        {{-- Total Credits --}}
                        <td class="px-3 py-2 border border-gray-200 text-center">
                            @if($level->isAudit())
                                <span class="text-[10px] text-gray-400 italic bg-gray-100 px-2 py-1 rounded">N/A</span>
                                <input type="hidden" name="rows[{{ $level->id }}][total_credits]" value="0">
                            @else
                                <input type="number" name="rows[{{ $level->id }}][total_credits]"
                                       value="{{ $s?->total_credits ?? 0 }}" min="0" step="0.5"
                                       class="w-16 text-center rounded border border-gray-200 text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            @endif
                        </td>

                        {{-- Total Marks --}}
                        <td class="px-3 py-2 border border-gray-200 text-center">
                            @if($level->isAudit())
                                <span class="text-[10px] text-gray-400 italic bg-gray-100 px-2 py-1 rounded">N/A</span>
                                <input type="hidden" name="rows[{{ $level->id }}][total_marks]" value="0">
                            @else
                                <input type="number" name="rows[{{ $level->id }}][total_marks]"
                                       value="{{ $s?->total_marks ?? 0 }}" min="0"
                                       class="w-16 text-center rounded border border-gray-200 text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            @endif
                        </td>

                        {{-- Notes --}}
                        <td class="px-3 py-2 border border-gray-200">
                            <input type="text" name="rows[{{ $level->id }}][notes]"
                                   value="{{ $s?->notes }}" placeholder="Optional notes..."
                                   class="w-full bg-transparent border-none text-xs text-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-400 rounded px-1">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-indigo-50 font-semibold text-sm">
                        <td colspan="2" class="px-3 py-3 text-right border border-gray-200 text-gray-700">TOTAL</td>
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.offered"></td>
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.toComplete"></td>
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.comp"></td>
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.elecOffered"></td>
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.elecToComplete"></td>
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.th"></td>
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.tu"></td>
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.pr"></td>
                        <td class="px-3 py-3 text-center border border-gray-200 text-indigo-700" x-text="totals.hours"></td>
                        <td class="px-3 py-3 text-center border border-gray-200" colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="flex gap-3 mt-5">
            <button type="submit"
                    class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                Save & Next: Define Courses
            </button>
            <a href="{{ route('cdc.programmes.show', $programme) }}"
               class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
        </div>
    </form>

    {{-- Abbreviations footnote --}}
    <p class="mt-4 text-xs text-gray-500">
        <strong>Abbreviations:</strong> TH - Theory, TU - Tutorial, PR - Practical
    </p>
</div>

@push('scripts')
<script>
function schemeForm() {
    return {
        rows: [],
        warnings: [],

        registerRow(rowData) {
            this.rows.push(rowData);
        },

        get totals() {
            return this.rows.reduce((acc, r) => ({
                offered:    acc.offered    + (r.offered    || 0),
                toComplete: acc.toComplete + (r.toComplete || 0),
                comp:       acc.comp       + (r.comp       || 0),
                elecOffered: acc.elecOffered + (r.elecOffered || 0),
                elecToComplete: acc.elecToComplete + (r.elecToComplete || 0),
                th:         acc.th         + (r.th         || 0),
                tu:         acc.tu         + (r.tu         || 0),
                pr:         acc.pr         + (r.pr         || 0),
                hours:      acc.hours      + (r.totalHrs   || 0),
            }), { offered: 0, toComplete: 0, comp: 0, elecOffered: 0, elecToComplete: 0, th: 0, tu: 0, pr: 0, hours: 0 });
        },

        init() { /* rows register themselves via x-init */ },

        submitForm(e) {
            this.warnings = [];
            const invalid = this.rows.filter(r => r.toComplete > r.offered);
            const invalidElectives = this.rows.filter(r => r.elecToComplete > r.elecOffered);
            const invalidOfferedBreakup = this.rows.filter(r => (r.comp + r.elecOffered) > r.offered);
            const invalidToCompleteBreakup = this.rows.filter(r => (r.comp + r.elecToComplete) > r.toComplete);
            if (invalid.length) {
                invalid.forEach(r => {
                    this.warnings.push(`Level ID ${r.levelId}: "Courses to Complete" cannot exceed "Total Courses Offered".`);
                });
            }
            if (invalidElectives.length) {
                invalidElectives.forEach(r => {
                    this.warnings.push(`Level ID ${r.levelId}: "Elective To Complete" cannot exceed "Elective Offered".`);
                });
            }
            if (invalidOfferedBreakup.length) {
                invalidOfferedBreakup.forEach(r => {
                    this.warnings.push(`Level ID ${r.levelId}: compulsory courses plus elective offered cannot exceed total courses offered.`);
                });
            }
            if (invalidToCompleteBreakup.length) {
                invalidToCompleteBreakup.forEach(r => {
                    this.warnings.push(`Level ID ${r.levelId}: compulsory courses plus elective to complete cannot exceed courses to complete.`);
                });
            }
            if (this.warnings.length) return;
            e.target.submit();
        },
    };
}
</script>
@endpush
@endsection
