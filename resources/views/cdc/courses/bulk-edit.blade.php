@extends('layouts.app')

@section('content')
<style>
    /* Remove number input spinners */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }

    .grid-input {
        @apply w-full border-none bg-transparent px-1 py-1.5 text-center focus:ring-2 focus:ring-indigo-500 rounded-md transition-all duration-200 outline-none;
    }
    .grid-input-left {
        @apply text-left px-2;
    }
    .active-row {
        @apply bg-indigo-50/50 shadow-inner;
    }
</style>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8"
     x-data="bulkCourseForm({{ json_encode($courses) }}, {{ json_encode($budget) }})" x-init="init()">

    {{-- Breadcrumb --}}
    <div class="mb-5 text-sm text-gray-500 flex items-center gap-1">
        <a href="{{ route('cdc.programmes.index') }}" class="hover:text-indigo-600">Programmes</a>
        <span>/</span>
        <a href="{{ route('cdc.courses.index', $programme) }}" class="hover:text-indigo-600">Courses</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">Bulk Edit: {{ $level->level_code }}</span>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Level-wise Course Entry</h1>
            <p class="text-sm text-gray-500 mt-1">
                Programme: <strong>{{ $programme->name }}</strong> | Level: <strong>{{ $level->level_code }} – {{ $level->level_name }}</strong>
            </p>
        </div>

        {{-- Budget Constraint Monitor --}}
        <div class="flex items-center gap-6 bg-indigo-50 border border-indigo-100 px-5 py-3 rounded-xl shadow-sm">
            <div class="text-xs font-semibold text-indigo-700 uppercase tracking-widest border-r border-indigo-200 pr-4">Level Budget Status</div>
            
            <div class="flex items-center gap-4">
                {{-- Course Count Monitor --}}
                <div class="flex flex-col">
                    <span class="text-[10px] text-indigo-400 font-bold uppercase">Courses</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg font-black" :class="rows.length != {{ $level->courses_limit }} ? 'text-amber-600' : 'text-indigo-800'" x-text="rows.length"></span>
                        <span class="text-[10px] text-indigo-400">/ <span>{{ $level->courses_limit }}</span></span>
                    </div>
                </div>

                {{-- TH Monitor --}}
                <div class="flex flex-col border-l border-indigo-200 pl-4">
                    <span class="text-[10px] text-indigo-400 font-bold uppercase">TH Hours</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg font-black" :class="sum('th_hours') != {{ $level->th_limit }} ? 'text-amber-600' : 'text-indigo-800'" x-text="sum('th_hours')"></span>
                        <span class="text-[10px] text-indigo-400">/ <span>{{ $level->th_limit }}</span></span>
                    </div>
                </div>

                {{-- Credits Monitor --}}
                <div class="flex flex-col border-l border-indigo-200 pl-4">
                    <span class="text-[10px] text-indigo-400 font-bold uppercase">Credits</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg font-black" :class="sum('credits') != {{ $level->credits_limit }} ? 'text-amber-600' : 'text-indigo-800'" x-text="sum('credits')"></span>
                        <span class="text-[10px] text-indigo-400">/ <span>{{ $level->credits_limit }}</span></span>
                    </div>
                </div>

                {{-- Marks Monitor --}}
                <div class="flex flex-col border-l border-indigo-200 pl-4">
                    <span class="text-[10px] text-indigo-400 font-bold uppercase">Total Marks</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg font-black" :class="totalMarksSum() != {{ $level->marks_limit }} ? 'text-amber-600' : 'text-indigo-800'" x-text="totalMarksSum()"></span>
                        <span class="text-[10px] text-indigo-400">/ <span>{{ $level->marks_limit }}</span></span>
                    </div>
                </div>
            </div>

            <button type="button" @click="addRow"
                    class="ml-auto inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-indigo-700 transition active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Course Row
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('cdc.courses.bulk-update', [$programme, $level]) }}">
        @csrf @method('PUT')

        <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-x-auto">
            <table class="min-w-full text-[11px] border-collapse">
                <thead>
                    <tr class="bg-indigo-700 text-white uppercase tracking-tighter">
                        <th rowspan="2" class="px-2 py-3 text-center border border-indigo-600 w-10">Sr.</th>
                        <th rowspan="2" class="px-2 py-3 text-center border border-indigo-600 w-12">Code</th>
                        <th rowspan="2" class="px-2 py-3 text-left border border-indigo-600 min-w-[140px]">Course Title</th>
                        <th rowspan="2" class="px-1 py-1 text-center border border-indigo-600 w-8">Abbr</th>
                        <th rowspan="2" class="px-1 py-1 text-center border border-indigo-600 w-8">Yr</th>
                        <th rowspan="2" class="px-1 py-1 text-center border border-indigo-600 w-10">Trm</th>
                        <th colspan="4" class="px-1 py-1 text-center border border-indigo-600">Teaching Scheme (Hrs)</th>
                        <th rowspan="2" class="px-1 py-3 text-center border border-indigo-600 bg-indigo-600 w-10">Cr</th>
                        <th colspan="7" class="px-1 py-1 text-center border border-indigo-600">Examination Scheme (Marks)</th>
                        <th rowspan="2" class="px-2 py-3 text-center border border-indigo-600">Type</th>
                        <th rowspan="2" class="px-2 py-3 text-center border border-indigo-600">Awd</th>
                        <th rowspan="2" class="px-2 py-3 text-center border border-indigo-600">Elective<br>Gp</th>
                        <th rowspan="2" class="px-1 py-3 text-center border border-indigo-600"></th>
                    </tr>
                    <tr class="bg-indigo-600 text-white uppercase tracking-tighter">
                        <th class="px-1 py-2 text-center border border-indigo-500 w-8">TH</th>
                        <th class="px-1 py-2 text-center border border-indigo-500 w-8">TU</th>
                        <th class="px-1 py-2 text-center border border-indigo-500 w-8">PR</th>
                        <th class="px-1 py-2 text-center border border-indigo-500 w-8 font-bold bg-indigo-700">Tot</th>
                        <th class="px-1 py-2 text-center border border-indigo-500 w-8">Ppr</th>
                        <th class="px-1 py-2 text-center border border-indigo-500 w-10">TH</th>
                        <th class="px-1 py-2 text-center border border-indigo-500 w-10">Tst</th>
                        <th class="px-1 py-2 text-center border border-indigo-500 w-10">PR</th>
                        <th class="px-1 py-2 text-center border border-indigo-500 w-10">OR</th>
                        <th class="px-1 py-2 text-center border border-indigo-500 w-10">TW</th>
                        <th class="px-1 py-2 text-center border border-indigo-500 font-bold bg-indigo-700">Tot</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(row, index) in rows" :key="row.id">
                        <tr class="hover:bg-gray-50 transition border-b border-gray-100" 
                            :class="focusedRow === row.id ? 'active-row' : ''">
                            {{-- Sr --}}
                            <td class="px-1 py-1 border-r border-gray-200 text-center text-gray-400 font-medium select-none" x-text="index + 1"></td>

                            {{-- Code --}}
                             <td class="px-0 py-0 border-r border-gray-200">
                                <input type="text" :name="'rows['+row.id+'][course_code]'" x-model="row.course_code"
                                       @focus="focusedRow = row.id" @blur="focusedRow = null"
                                       maxlength="6"
                                       class="grid-input font-mono font-bold" 
                                       :class="isValidCode(row.course_code) ? 'text-indigo-900' : 'text-red-500 bg-red-50'"
                                       placeholder="23{{ $level->sort_order }}001">
                            </td>

                            {{-- Title --}}
                            <td class="px-0 py-0 border-r border-gray-200">
                                <input type="text" :name="'rows['+row.id+'][course_title]'" x-model="row.course_title"
                                       @focus="focusedRow = row.id" @blur="focusedRow = null"
                                       class="grid-input grid-input-left font-medium" placeholder="Communication Skills">
                            </td>

                            {{-- Abbr --}}
                            <td class="px-0 py-0 border-r border-gray-200">
                                <input type="text" :name="'rows['+row.id+'][course_abbr]'" x-model="row.course_abbr"
                                       @focus="focusedRow = row.id" @blur="focusedRow = null"
                                       class="grid-input uppercase text-[10px]" placeholder="ENG">
                            </td>

                            {{-- Year --}}
                            <td class="px-0 py-0 border-r border-gray-200">
                                <input type="number" :name="'rows['+row.id+'][year]'" x-model.number="row.year"
                                       @focus="focusedRow = row.id" @blur="focusedRow = null"
                                       class="grid-input" placeholder="1">
                            </td>

                            {{-- Term --}}
                            <td class="px-0 py-0 border-r border-gray-200">
                                <select :name="'rows['+row.id+'][term]'" x-model="row.term"
                                        @focus="focusedRow = row.id" @blur="focusedRow = null"
                                        class="grid-input text-[10px] appearance-none cursor-pointer">
                                    <option value="">--</option>
                                    <option value="odd">Odd</option>
                                    <option value="even">Even</option>
                                </select>
                            </td>

                            {{-- TH, TU, PR --}}
                            <td class="px-0 py-0 border-r border-gray-100">
                                <input type="number" :name="'rows['+row.id+'][th_hours]'" x-model.number="row.th_hours"
                                       @focus="focusedRow = row.id" @blur="focusedRow = null" class="grid-input">
                            </td>
                            <td class="px-0 py-0 border-r border-gray-100">
                                <input type="number" :name="'rows['+row.id+'][tu_hours]'" x-model.number="row.tu_hours"
                                       @focus="focusedRow = row.id" @blur="focusedRow = null" class="grid-input">
                            </td>
                            <td class="px-0 py-0 border-r border-gray-100">
                                <input type="number" :name="'rows['+row.id+'][pr_hours]'" x-model.number="row.pr_hours"
                                       @focus="focusedRow = row.id" @blur="focusedRow = null" class="grid-input">
                            </td>
                            {{-- Total Hours --}}
                            <td class="px-1 py-1 border-r border-gray-200 bg-gray-50/50 text-center font-bold text-indigo-600 select-none">
                                <span x-text="(row.th_hours||0) + (row.tu_hours||0) + (row.pr_hours||0)"></span>
                            </td>

                            {{-- Credits --}}
                            <td class="px-0 py-0 border-r border-gray-200 bg-indigo-50/30">
                                <input type="number" :name="'rows['+row.id+'][credits]'" x-model.number="row.credits" step="0.5"
                                       @focus="focusedRow = row.id" @blur="focusedRow = null"
                                       class="grid-input font-black text-indigo-700">
                            </td>

                            {{-- Paper Hrs --}}
                            <td class="px-0 py-0 border-r border-gray-100">
                                <input type="number" :name="'rows['+row.id+'][theory_paper_hrs]'" x-model.number="row.theory_paper_hrs"
                                       @focus="focusedRow = row.id" @blur="focusedRow = null" class="grid-input">
                            </td>

                            {{-- Max Marks: TH, Tst, PR, OR, TW --}}
                            <td class="px-0 py-0 border-r border-gray-100">
                                <input type="number" :name="'rows['+row.id+'][theory_max_marks]'" x-model.number="row.theory_max_marks"
                                       @focus="focusedRow = row.id" @blur="focusedRow = null" class="grid-input">
                            </td>
                            <td class="px-0 py-0 border-r border-gray-100">
                                <input type="number" :name="'rows['+row.id+'][test_max_marks]'" x-model.number="row.test_max_marks"
                                       @focus="focusedRow = row.id" @blur="focusedRow = null" class="grid-input">
                            </td>
                            <td class="px-0 py-0 border-r border-gray-100">
                                <input type="number" :name="'rows['+row.id+'][pr_max_marks]'" x-model.number="row.pr_max_marks"
                                       @focus="focusedRow = row.id" @blur="focusedRow = null" class="grid-input">
                            </td>
                            <td class="px-0 py-0 border-r border-gray-100">
                                <input type="number" :name="'rows['+row.id+'][or_max_marks]'" x-model.number="row.or_max_marks"
                                       @focus="focusedRow = row.id" @blur="focusedRow = null" class="grid-input">
                            </td>
                            <td class="px-0 py-0 border-r border-gray-200">
                                <input type="number" :name="'rows['+row.id+'][tw_max_marks]'" x-model.number="row.tw_max_marks"
                                       @focus="focusedRow = row.id" @blur="focusedRow = null" class="grid-input">
                            </td>

                            {{-- Total Marks --}}
                            <td class="px-1 py-1 border-r border-gray-200 bg-gray-50/50 text-center font-bold text-gray-700 select-none">
                                <span x-text="(row.theory_max_marks||0) + (row.test_max_marks||0) + (row.pr_max_marks||0) + (row.or_max_marks||0) + (row.tw_max_marks||0)"></span>
                            </td>

                            {{-- Course Type --}}
                            <td class="px-0 py-0 border-r border-gray-200">
                                <select :name="'rows['+row.id+'][course_type]'" x-model="row.course_type"
                                        @focus="focusedRow = row.id" @blur="focusedRow = null"
                                        class="grid-input text-[10px] font-semibold appearance-none cursor-pointer">
                                    <option value="compulsory">Comp</option>
                                    <option value="elective">Elec</option>
                                    <option value="audit">Aud</option>
                                </select>
                            </td>

                            {{-- Is Award --}}
                            <td class="px-0 py-0 border-r border-gray-200 text-center">
                                <input type="checkbox" :name="'rows['+row.id+'][is_award]'" x-model="row.is_award" value="1"
                                       class="rounded text-indigo-600 focus:ring-indigo-500">
                            </td>

                            {{-- Elective Group --}}
                            <td class="px-0 py-0 border-r border-gray-200">
                                <select x-show="row.course_type === 'elective'" 
                                        :name="'rows['+row.id+'][elective_group]'" x-model="row.elective_group"
                                        @focus="focusedRow = row.id" @blur="focusedRow = null"
                                        class="grid-input text-[10px] font-medium italic appearance-none cursor-pointer">
                                    <option value="">--</option>
                                    @foreach($electiveGroups as $grp)
                                        <option value="{{ $grp }}">{{ $grp }}</option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Actions --}}
                            <td class="px-1 py-1 text-center">
                                <button type="button" @click="removeRow(index)" class="text-red-300 hover:text-red-600 transition p-1 rounded-full hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-bold text-[11px]">
                        <td colspan="4" class="px-2 py-2 text-right">LEVEL TOTAL:</td>
                        <td class="px-1 py-2 text-center" x-text="sum('th_hours')"></td>
                        <td class="px-1 py-2 text-center" x-text="sum('tu_hours')"></td>
                        <td class="px-1 py-2 text-center" x-text="sum('pr_hours')"></td>
                        <td class="px-1 py-2 text-center bg-indigo-50 text-indigo-700" x-text="sum('th_hours') + sum('tu_hours') + sum('pr_hours')"></td>
                        <td class="px-1 py-2 text-center" x-text="sum('credits')"></td>
                        <td colspan="6" class="px-1 py-2 text-right">MARKS TOTAL:</td>
                        <td class="px-1 py-2 text-center" x-text="totalMarksSum()"></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit"
                    class="rounded-lg bg-indigo-600 px-8 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 shadow-md transition">
                Save Level Definitions
            </button>
            <a href="{{ route('cdc.courses.index', $programme) }}"
               class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function bulkCourseForm(initialCourses, budget) {
    return {
        rows: initialCourses.length > 0 ? initialCourses : [],
        budget: budget || { th_hours: 0, tu_hours: 0, pr_hours: 0, total_credits: 0, total_marks: 0 },
        focusedRow: null,
        levelSortOrder: {{ (int) $level->sort_order }},
        
        init() {
            if (this.rows.length === 0) {
                this.addRow();
            }
        },

        addRow() {
            const newId = 'new_' + Date.now();
            this.rows.push({
                id: newId,
                course_code: '',
                course_title: '',
                course_abbr: '',
                th_hours: 0,
                tu_hours: 0,
                pr_hours: 0,
                credits: 0,
                theory_paper_hrs: 0,
                theory_max_marks: 0,
                test_max_marks: 0,
                pr_max_marks: 0,
                or_max_marks: 0,
                tw_max_marks: 0,
                course_type: 'compulsory',
                year: null,
                term: '',
                is_award: true,
                elective_group: ''
            });
        },

        removeRow(index) {
            if (confirm('Are you sure you want to remove this row? It will only be deleted from the database if you click "Save".')) {
                this.rows.splice(index, 1);
            }
        },

        sum(field) {
            return this.rows.reduce((s, r) => s + (parseFloat(r[field]) || 0), 0);
        },

        totalMarksSum() {
            return this.rows.reduce((s, r) => {
                const total = (parseInt(r.theory_max_marks)||0) + (parseInt(r.test_max_marks)||0) + 
                              (parseInt(r.pr_max_marks)||0) + (parseInt(r.or_max_marks)||0) + 
                              (parseInt(r.tw_max_marks)||0);
                return s + total;
            }, 0);
        },

        isValidCode(code) {
            if (!code) return true;
            if (code.length !== 6) return false;
            return parseInt(code.charAt(2)) === this.levelSortOrder;
        }
    }
}
</script>
@endpush
@endsection
