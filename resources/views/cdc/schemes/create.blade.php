@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6">
    <div class="mb-6">
        <a href="{{ route('cdc.schemes.index') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">
            ← Back to Schemes
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">
            {{ $scheme ? 'Edit Configuration: ' . $scheme->name : 'New Central Scheme' }}
        </h1>
    </div>

    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
        @php
            $initialStructure = [];
            if ($scheme) {
                $allNodes = $scheme->assessmentComponents()->orderBy('display_order')->get();
                $l1Nodes = $allNodes->where('parent_id', null);
                
                foreach($l1Nodes as $l1) {
                    $l1Data = [ 'name' => $l1->component_name, 'children' => [] ];
                    $l2Nodes = $allNodes->where('parent_id', $l1->id);
                    
                    foreach ($l2Nodes as $l2) {
                        $l2Data = [ 'name' => $l2->component_name, 'columns' => [] ];
                        $l3Nodes = $allNodes->where('parent_id', $l2->id);
                        
                        foreach ($l3Nodes as $l3) {
                            $l2Data['columns'][] = $l3->component_name;
                        }
                        $l1Data['children'][] = $l2Data;
                    }
                    $initialStructure[] = $l1Data;
                }
            }
        @endphp
        <form method="POST" action="{{ $scheme ? route('cdc.schemes.update', $scheme) : route('cdc.schemes.store') }}"
              x-data="schemeLevels({!! htmlspecialchars(json_encode($scheme?->levels ?: []), ENT_QUOTES, 'UTF-8') !!}, {!! htmlspecialchars(json_encode($initialStructure ?? []), ENT_QUOTES, 'UTF-8') !!})">
            @csrf
            @if($scheme) @method('PUT') @endif

            @if($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Scheme Name (e.g. K-Scheme) <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $scheme?->name) }}" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Implemented Year</label>
                    <input type="number" name="implemented_year" value="{{ old('implemented_year', $scheme?->implemented_year) }}" placeholder="2023"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">{{ old('description', $scheme?->description) }}</textarea>
            </div>

            <div class="mb-8">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $scheme?->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-medium text-gray-700">Scheme is currently Active</span>
                </label>
                <p class="text-[10px] text-gray-500 italic mt-0.5 ml-6">Mark false if this is a legacy scheme no longer admitting new students.</p>
            </div>

            <div class="mb-8 pt-6 border-t border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Define Level Categories</h3>
                        <p class="text-xs text-gray-500">Add the MSBTE mandated course groupings for this scheme.</p>
                    </div>
                    <button type="button" @click="addLevel()"
                            class="inline-flex items-center gap-1 px-3 py-1 rounded-md bg-indigo-50 text-indigo-700 text-xs font-medium hover:bg-indigo-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Category
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(level, index) in levels" :key="index">
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 bg-gray-50/50 group">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-xs font-bold text-gray-400 group-hover:text-indigo-600 transition" x-text="index + 1"></div>
                            
                            <div class="flex-1 space-y-3">
                                <div class="grid grid-cols-5 gap-3">
                                    <div class="col-span-2">
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-tight mb-0.5">Code</label>
                                        <input type="text" :name="'levels['+index+'][level_code]'" x-model="level.level_code" placeholder="Level-1" required
                                               class="w-full rounded-md border-gray-200 bg-white px-3 py-1.5 text-sm focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                    <div class="col-span-3">
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-tight mb-0.5">Name</label>
                                        <input type="text" :name="'levels['+index+'][level_name]'" x-model="level.level_name" placeholder="Basic Science & Mathematics" required
                                               class="w-full rounded-md border-gray-200 bg-white px-3 py-1.5 text-sm focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                    <input type="hidden" :name="'levels['+index+'][id]'" x-model="level.id">
                                    <input type="hidden" :name="'levels['+index+'][sort_order]'" :value="index">
                                </div>
                            </div>

                            <button type="button" @click="removeLevel(index)"
                                    class="p-1.5 text-gray-300 hover:text-red-500 transition"
                                    title="Remove Level">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                
                <div x-show="levels.length === 0" class="text-center py-8 border-2 border-dashed border-gray-100 rounded-xl">
                    <p class="text-sm text-gray-400">No categories defined yet. Start by adding MSBTE level categories.</p>
                </div>
            </div>

            {{-- Scheme Structure Configuration (3-Level Tree) --}}
            <div class="mb-8 pt-6 border-t border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Define Scheme Structure</h3>
                        <p class="text-xs text-gray-500">Define Learning and Assessment groupings (e.g. Assessment Scheme -> Theory -> FA-TH).</p>
                    </div>
                    <button type="button" @click="addL1()"
                            class="inline-flex items-center gap-1 px-3 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-medium hover:bg-blue-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Main Group (Level 1)
                    </button>
                </div>

                <div class="space-y-6">
                    <template x-for="(l1, l1Index) in schemeStructure" :key="l1Index">
                        <div class="p-4 rounded-xl border border-gray-300 bg-gray-50 shadow-sm relative">
                            {{-- L1 Header --}}
                            <div class="flex items-center gap-3 mb-4 border-b border-gray-200 pb-3">
                                <span class="text-xs font-bold text-gray-500 uppercase flex-shrink-0">Level 1</span>
                                <input type="text" :name="`scheme_structure[${l1Index}][name]`" x-model="l1.name" placeholder="e.g. Assessment Scheme" required
                                       class="flex-1 rounded-md border-gray-300 bg-white px-3 py-1.5 text-sm font-bold focus:ring-1 focus:ring-indigo-500">
                                
                                <button type="button" @click="removeL1(l1Index)" class="p-1 text-gray-400 hover:text-red-500 transition" title="Remove Main Group">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>

                            {{-- L2 Container --}}
                            <div class="pl-6 space-y-3">
                                <div class="flex justify-end mb-2">
                                    <button type="button" @click="addL2(l1Index)"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded bg-green-50 text-green-700 text-[10px] font-bold uppercase transition hover:bg-green-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Add Sub-Group (Level 2)
                                    </button>
                                </div>

                                <template x-for="(l2, l2Index) in l1.children" :key="l2Index">
                                    <div class="p-3 rounded-lg border border-gray-200 bg-white shadow-sm relative group/l2">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Level 2</span>
                                            <input type="text" :name="`scheme_structure[${l1Index}][children][${l2Index}][name]`" x-model="l2.name" placeholder="e.g. Theory" required
                                                   class="flex-1 rounded border-gray-300 bg-gray-50 px-2 py-1 text-sm font-semibold focus:ring-1 focus:ring-indigo-500">
                                            <button type="button" @click="removeL2(l1Index, l2Index)" class="p-1 text-gray-400 hover:text-red-500 transition" title="Remove Sub-Group">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>

                                        {{-- L3 Container (Leaves) --}}
                                        <div class="pl-4 border-l-2 border-indigo-100 mt-2">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-[9px] font-bold text-indigo-400 uppercase tracking-tight">Level 3 (Leaf Columns)</span>
                                                <button type="button" @click="addL3(l1Index, l2Index)"
                                                        class="text-[9px] font-bold text-indigo-600 hover:text-indigo-800 transition">
                                                    + Add Leaf Column
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                                <template x-for="(col, l3Index) in l2.columns" :key="l3Index">
                                                    <div class="flex items-center gap-1 group/l3 relative">
                                                        <input type="text" :name="`scheme_structure[${l1Index}][children][${l2Index}][columns][${l3Index}]`" x-model="l2.columns[l3Index]" placeholder="e.g. FA-TH" required
                                                               class="w-full rounded border-gray-200 bg-indigo-50/30 px-2 py-1 text-xs focus:ring-1 focus:ring-indigo-500 transition">
                                                        <button type="button" @click="removeL3(l1Index, l2Index, l3Index)" class="absolute right-1 text-gray-400 hover:text-red-500 opacity-0 group-hover/l3:opacity-100 transition">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                            <div x-show="l2.columns.length === 0" class="text-[10px] text-gray-400 italic">No leaves. This Level 2 node is a leaf column itself.</div>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="l1.children.length === 0" class="text-xs text-gray-400 italic text-center py-2">No sub-groups. This Level 1 node is a leaf column itself.</div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="schemeStructure.length === 0" class="text-center py-4 border-2 border-dashed border-gray-100 rounded-xl mt-3">
                    <p class="text-sm text-gray-400">No structure defined. Add groupings like 'Learning Scheme' or 'Assessment Scheme'.</p>
                </div>
            </div>

            {{-- Live Preview Table --}}
            <div class="mb-8 pt-6 border-t border-gray-100" x-show="schemeStructure.length > 0">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-gray-900">Live Form Preview</h3>
                    <p class="text-xs text-gray-500">This is how the columns will appear when adding/editing courses under this scheme.</p>
                </div>
                
                <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 text-gray-700">
                            <template x-for="(row, rIndex) in headerRows" :key="'r'+rIndex">
                                <tr>
                                    <template x-if="rIndex === 0">
                                        <th class="border-b border-r border-gray-200 px-4 py-3 font-semibold bg-gray-100 text-center align-middle" :rowspan="headerRows.length">
                                            Subject Code & Name
                                        </th>
                                    </template>
                                    <template x-for="(col, cIndex) in row" :key="'c'+rIndex+'-'+cIndex">
                                        <th class="border-b border-r border-gray-200 px-3 py-2 font-medium text-center"
                                            :class="col.is_leaf ? 'bg-indigo-50/50 text-xs text-gray-600' : 'bg-white font-bold text-sm'"
                                            :colspan="col.colspan"
                                            :rowspan="col.rowspan"
                                            x-text="col.name">
                                        </th>
                                    </template>
                                </tr>
                            </template>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-2 border-r border-gray-200 font-mono text-xs text-indigo-600 bg-gray-50/50">CS101 (Programming)</td>
                                <template x-for="(col, idx) in leafColumns" :key="'val'+idx">
                                    <td class="px-3 py-2 border-r border-gray-200 text-center text-gray-400 italic text-xs hover:bg-indigo-50/30 transition">
                                        Val
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex gap-3 pt-6 border-t border-gray-100">
                <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                    {{ $scheme ? 'Save Scheme Configuration' : 'Create Scheme' }}
                </button>
                <a href="{{ route('cdc.schemes.index') }}" class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function schemeLevels(initialLevels, initialStructure) {
    return {
        levels: initialLevels.length > 0 ? initialLevels : [
            { level_code: 'Level-1', level_name: 'Basic Courses' },
            { level_code: 'Level-2', level_name: 'Foundation Courses ' },
            { level_code: 'Level-3', level_name: 'Allied Courses' },
            { level_code: 'Level-4', level_name: 'Applied Courses' },
            { level_code: 'Level-5', level_name: 'Diversified Courses' },
            { level_code: '-', level_name: 'Audit Courses' },
        ],
        schemeStructure: initialStructure && initialStructure.length > 0 ? initialStructure : [
            {
                name: 'Learning Scheme',
                children: [
                    { name: 'Actual Contact Hours / Week', columns: ['CL', 'TL', 'LL', 'Practical'] },
                    { name: 'Notional Learning Hours / Week', columns: [] },
                    { name: 'Credits', columns: [] }
                ]
            },
            {
                name: 'Assessment Scheme',
                children: [
                    { name: 'Theory', columns: ['FA-TH (Max)', 'SA-TH (Max)'] },
                    { name: 'Practical', columns: ['FA-PR (Max)', 'SA-PR (Max)'] },
                    { name: 'SLA', columns: ['Max', 'Min'] }
                ]
            }
        ],
        addLevel() {
            this.levels.push({ 
                level_code: '', 
                level_name: ''
            });
        },
        removeLevel(index) {
            if (confirm('Are you sure? Removing a level type affects programmes.')) {
                this.levels.splice(index, 1);
            }
        },
        addL1() {
            this.schemeStructure.push({ name: '', children: [] });
        },
        removeL1(index) {
            this.schemeStructure.splice(index, 1);
        },
        addL2(l1Index) {
            this.schemeStructure[l1Index].children.push({ name: '', columns: [] });
        },
        removeL2(l1Index, l2Index) {
            this.schemeStructure[l1Index].children.splice(l2Index, 1);
        },
        addL3(l1Index, l2Index) {
            this.schemeStructure[l1Index].children[l2Index].columns.push('');
        },
        removeL3(l1Index, l2Index, l3Index) {
            this.schemeStructure[l1Index].children[l2Index].columns.splice(l3Index, 1);
        },
        get headerRows() {
            const rows = [[], [], []];

            this.schemeStructure.forEach(l1 => {
                let l1Span = 0;

                if (!l1.children || l1.children.length === 0) {
                    rows[0].push({
                        name: l1.name,
                        colspan: 1,
                        rowspan: 3,
                        is_leaf: true
                    });
                    return;
                }

                l1.children.forEach(l2 => {
                    if (!l2.columns || l2.columns.length === 0) {
                        l1Span += 1;
                    } else {
                        l1Span += l2.columns.length;
                    }
                });

                rows[0].push({
                    name: l1.name,
                    colspan: l1Span,
                    rowspan: 1,
                    is_leaf: false
                });

                l1.children.forEach(l2 => {
                    if (!l2.columns || l2.columns.length === 0) {
                        rows[1].push({
                            name: l2.name,
                            colspan: 1,
                            rowspan: 2,
                            is_leaf: true
                        });
                    } else {
                        rows[1].push({
                            name: l2.name,
                            colspan: l2.columns.length,
                            rowspan: 1,
                            is_leaf: false
                        });

                        l2.columns.forEach(col => {
                            rows[2].push({
                                name: col,
                                colspan: 1,
                                rowspan: 1,
                                is_leaf: true
                            });
                        });
                    }
                });
            });

            // Filter out empty rows if any level was completely empty
            return rows.filter(row => row.length > 0);
        },
        
        get leafColumns() {
            let leaves = [];
            for (let l1 of this.schemeStructure) {
                if (!l1.children || l1.children.length === 0) {
                    leaves.push(l1.name || 'Group');
                } else {
                    for (let l2 of l1.children) {
                        if (!l2.columns || l2.columns.length === 0) {
                            leaves.push(l2.name || 'Sub-Group');
                        } else {
                            for (let l3 of l2.columns) {
                                leaves.push(l3 || 'Col');
                            }
                        }
                    }
                }
            }
            return leaves;
        }
    }
}
</script>
@endpush
@endsection
