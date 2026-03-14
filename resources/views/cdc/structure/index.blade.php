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
        <button type="button" @click="calculateFromCourses"
                :disabled="calculating"
                class="inline-flex items-center gap-2 rounded-lg border border-indigo-300 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 transition disabled:opacity-60">
            <svg class="w-4 h-4" :class="{'animate-spin': calculating}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span x-text="calculating ? 'Calculating…' : 'Calculate from Courses'"></span>
        </button>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
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
                        <th rowspan="2" class="px-3 py-3 text-center align-middle border border-indigo-600 text-xs">Elective<br>Courses</th>
                        <th rowspan="2" class="px-3 py-3 text-center align-middle border border-indigo-600 text-xs">Audit<br>Courses</th>
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
                            isAudit: {{ $level->isAudit() ? 'true' : 'false' }},
                            th: {{ $s?->th_hours ?? 0 }},
                            tu: {{ $s?->tu_hours ?? 0 }},
                            pr: {{ $s?->pr_hours ?? 0 }},
                            offered: {{ $s?->total_courses_offered ?? 0 }}, // display-only; computed below
                            toComplete: {{ $s?->courses_to_complete ?? 0 }},
                            comp: {{ $s?->compulsory_count ?? 0 }},
                            elec: {{ $s?->elective_count ?? 0 }},
                            audit: {{ $s?->audit_count ?? 0 }},
                            credits: {{ $level->isAudit() ? 0 : ($s?->total_credits ?? 0) }},
                            marks: {{ $level->isAudit() ? 0 : ($s?->total_marks ?? 0) }},
                            get totalHrs() { return this.th + this.tu + this.pr; },
                            get offeredComputed() { return (this.comp || 0) + (this.elec || 0) + (this.audit || 0); },
                            get warning() {
                                if (this.toComplete > this.offeredComputed) return 'Cannot exceed courses offered';
                                return '';
                            }
                        }"
                        x-init="
                            if (isAudit) {
                                comp = 0; elec = 0;
                                // keep audit as the controlling number for audit levels
                                toComplete = audit;
                            }
                            registerRow($data)
                        ">

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
                            <span class="inline-block w-16 text-center font-semibold text-indigo-700" x-text="offeredComputed"></span>
                            <input type="hidden" name="rows[{{ $level->id }}][total_courses_offered]" :value="offeredComputed">
                        </td>

                        {{-- Courses to Complete --}}
                        <td class="px-3 py-2 border border-gray-200">
                            <div class="relative">
                                <input type="number" name="rows[{{ $level->id }}][courses_to_complete]"
                                       x-model.number="toComplete" min="0"
                                       :readonly="isAudit"
                                       :class="warning ? 'border-red-400' : 'border-gray-200'"
                                       class="w-16 text-center rounded border text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                       @input="if (isAudit) toComplete = audit">
                                <p x-show="warning" x-text="warning" class="absolute -bottom-5 left-0 text-xs text-red-500 whitespace-nowrap z-10"></p>
                            </div>
                        </td>

                        {{-- Compulsory Courses --}}
                        <td class="px-3 py-2 border border-gray-200">
                            <input type="number" name="rows[{{ $level->id }}][compulsory_count]"
                                   x-model.number="comp" min="0"
                                   :readonly="isAudit"
                                   class="w-14 text-center rounded border border-gray-200 text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-gray-50"
                                   @input="if (isAudit) comp = 0">
                        </td>

                        {{-- Elective Courses --}}
                        <td class="px-3 py-2 border border-gray-200">
                            <input type="number" name="rows[{{ $level->id }}][elective_count]"
                                   x-model.number="elec" min="0"
                                   :readonly="isAudit"
                                   class="w-14 text-center rounded border border-gray-200 text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-gray-50"
                                   @input="if (isAudit) elec = 0">
                        </td>

                        {{-- Audit Courses --}}
                        <td class="px-3 py-2 border border-gray-200">
                            <input type="number" name="rows[{{ $level->id }}][audit_count]"
                                   x-model.number="audit" min="0"
                                   class="w-14 text-center rounded border border-gray-200 text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-gray-50"
                                   @input="if (isAudit) toComplete = audit">
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
                                       x-model.number="credits" min="0" step="0.5"
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
                                       x-model.number="marks" min="0"
                                       class="w-16 text-center rounded border border-gray-200 text-sm px-1 py-1 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            @endif
                        </td>

                        {{-- Notes --}}
                        <td class="px-3 py-2 border border-gray-200">
                            <input type="text" name="rows[{{ $level->id }}][notes]"
                                   value="{{ $s?->notes }}" placeholder="Optional notes…"
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
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.elec"></td>
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.audit"></td>
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.th"></td>
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.tu"></td>
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.pr"></td>
                        <td class="px-3 py-3 text-center border border-gray-200 text-indigo-700" x-text="totals.hours"></td>
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.credits.toFixed(2)"></td>
                        <td class="px-3 py-3 text-center border border-gray-200" x-text="totals.marks"></td>
                        <td class="px-3 py-3 text-center border border-gray-200"></td>
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
        <strong>Abbreviations:</strong> TH – Theory, TU – Tutorial, PR – Practical
    </p>
</div>

@push('scripts')
<script>
function schemeForm() {
    return {
        rows: [],
        calculating: false,
        warnings: [],

        registerRow(rowData) {
            this.rows.push(rowData);
        },

        get totals() {
            return this.rows.reduce((acc, r) => ({
                offered:    acc.offered    + (r.offered    || 0),
                toComplete: acc.toComplete + (r.toComplete || 0),
                comp:       acc.comp       + (r.comp       || 0),
                elec:       acc.elec       + (r.elec       || 0),
                audit:      acc.audit      + (r.audit      || 0),
                th:         acc.th         + (r.th         || 0),
                tu:         acc.tu         + (r.tu         || 0),
                pr:         acc.pr         + (r.pr         || 0),
                hours:      acc.hours      + (r.totalHrs   || 0),
                credits:    acc.credits    + (parseFloat(r.credits) || 0),
                marks:      acc.marks      + (r.marks      || 0),
            }), { offered: 0, toComplete: 0, comp: 0, elec: 0, audit: 0, th: 0, tu: 0, pr: 0, hours: 0, credits: 0, marks: 0 });
        },

        init() { /* rows register themselves via x-init */ },

        async calculateFromCourses() {
            this.calculating = true;
            this.warnings = [];
            try {
                const res = await fetch('{{ route('cdc.programmes.structure.calculate', $programme) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                // data = { levelId: { th_hours, tu_hours, pr_hours, total_credits, total_marks, total_courses_offered } }
                for (const [levelId, vals] of Object.entries(data)) {
                    const row = this.rows.find(r => r.levelId == levelId);
                    if (row) {
                        row.th        = vals.th_hours || 0;
                        row.tu        = vals.tu_hours || 0;
                        row.pr        = vals.pr_hours || 0;
                        row.offered   = vals.total_courses_offered || 0;
                        row.credits   = vals.total_credits || 0;
                        row.marks     = vals.total_marks || 0;
                    }
                }
                this.warnings = ['Values recalculated from course definitions. Click "Save Scheme" to persist.'];
            } catch (e) {
                this.warnings = ['Failed to fetch calculations. Please try again.'];
            } finally {
                this.calculating = false;
            }
        },

        submitForm(e) {
            this.warnings = [];
            const invalid = this.rows.filter(r => (r.toComplete > ((r.comp||0) + (r.elec||0) + (r.audit||0))));
            if (invalid.length) {
                invalid.forEach(r => {
                    const offeredComputed = (r.comp||0) + (r.elec||0) + (r.audit||0);
                    if (r.toComplete > offeredComputed) {
                        this.warnings.push(`Level ID ${r.levelId}: "Courses to Complete" cannot exceed "Total Courses Offered".`);
                    }
                });
                return;
            }
            e.target.submit();
        },
    };
}
</script>
@endpush
@endsection
