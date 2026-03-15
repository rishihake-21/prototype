@extends('layouts.app')

@section('content')
@php
    $currentYear = now()->year;
    $levelsJson = htmlspecialchars(json_encode($scheme?->levels ?: []), ENT_QUOTES, 'UTF-8');

    $toStructure = function ($nodes) {
        $out = [];
        if (!$nodes) return $out;

        $l1 = $nodes->where('parent_id', null);
        foreach ($l1 as $n1) {
            $l1Data = ['name' => $n1->component_name, 'children' => []];
            $l2 = $nodes->where('parent_id', $n1->id);
            foreach ($l2 as $n2) {
                $l2Data = ['name' => $n2->component_name, 'columns' => []];
                $l3 = $nodes->where('parent_id', $n2->id);
                foreach ($l3 as $n3) {
                    $l2Data['columns'][] = $n3->component_name;
                }
                $l1Data['children'][] = $l2Data;
            }
            $out[] = $l1Data;
        }
        return $out;
    };

    $initialLearning = [];
    $initialAssessment = [];

    if ($scheme) {
        $learningNodes = $scheme->learningComponents()->orderBy('display_order')->get();
        $assessmentNodes = $scheme->assessmentComponents()->orderBy('display_order')->get();

        $initialLearning = $toStructure($learningNodes);
        $initialAssessment = $toStructure($assessmentNodes);
    }

    $learningJson = htmlspecialchars(json_encode($initialLearning), ENT_QUOTES, 'UTF-8');
    $assessmentJson = htmlspecialchars(json_encode($initialAssessment), ENT_QUOTES, 'UTF-8');
@endphp

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6">
    <div class="mb-6">
        <a href="{{ route('cdc.schemes.index') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">
            Back to Schemes
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">
            {{ $scheme ? 'Edit Configuration: ' . $scheme->name : 'New Central Scheme' }}
        </h1>
    </div>

    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
        <form method="POST"
              action="{{ $scheme ? route('cdc.schemes.update', $scheme) : route('cdc.schemes.store') }}"
              x-data="schemeEditor({!! $levelsJson !!}, {!! $learningJson !!}, {!! $assessmentJson !!})">
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Scheme Name (e.g. K-Scheme) <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $scheme?->name) }}" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Implemented Year</label>
                    <input type="number" name="implemented_year" value="{{ old('implemented_year', $scheme?->implemented_year ?? $currentYear) }}" placeholder="{{ $currentYear }}" min="{{ $currentYear }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Past-year schemes are not created in this system. Use {{ $currentYear }} or later.</p>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">{{ old('description', $scheme?->description) }}</textarea>
            </div>

            <div class="mb-8">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $scheme?->is_active ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-medium text-gray-700">Scheme is currently Active</span>
                </label>
                <p class="text-[10px] text-gray-500 italic mt-0.5 ml-6">Uncheck if this is a legacy scheme.</p>
            </div>

            <div class="mb-8 pt-6 border-t border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Define Level Categories</h3>
                        <p class="text-xs text-gray-500">These level codes are used when creating Programmes.</p>
                    </div>
                    <button type="button" @click="addLevel()"
                            class="inline-flex items-center gap-1 px-3 py-1 rounded-md bg-indigo-50 text-indigo-700 text-xs font-medium hover:bg-indigo-100 transition">
                        Add Level
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(lv, idx) in levels" :key="idx">
                        <div class="grid grid-cols-12 gap-2 items-end">
                            <div class="col-span-3">
                                <label class="block text-xs text-gray-600 mb-1">Level Code</label>
                                <input type="text" :name="`levels[${idx}][level_code]`" x-model="lv.level_code" required
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            </div>
                            <div class="col-span-6">
                                <label class="block text-xs text-gray-600 mb-1">Level Name</label>
                                <input type="text" :name="`levels[${idx}][level_name]`" x-model="lv.level_name" required
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs text-gray-600 mb-1">Sort Order</label>
                                <input type="number" :name="`levels[${idx}][sort_order]`" x-model.number="lv.sort_order" required
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-center">
                            </div>
                            <div class="col-span-1 text-right">
                                <button type="button" class="text-xs text-red-600 hover:underline" @click="removeLevel(idx)">Del</button>
                            </div>

                            <template x-if="lv.id">
                                <input type="hidden" :name="`levels[${idx}][id]`" :value="lv.id">
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Define Learning Scheme Structure</h3>
                        <p class="text-xs text-gray-500">Hours/week, credits, learning scheme columns.</p>
                    </div>
                    <button type="button" @click="addL1('learning')"
                            class="inline-flex items-center gap-1 px-3 py-1 rounded-md bg-indigo-50 text-indigo-700 text-xs font-medium hover:bg-indigo-100 transition">
                        Add Group
                    </button>
                </div>
                <p class="text-[11px] text-gray-500 mb-4">This is not used to calculate course marks.</p>

                <template x-if="learningStructure.length === 0">
                    <p class="text-sm text-gray-400">No learning structure defined.</p>
                </template>

                <div class="space-y-4">
                    <template x-for="(l1, l1Index) in learningStructure" :key="'l_' + l1Index">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="flex items-center gap-2">
                                <input type="text" :name="`learning_structure[${l1Index}][name]`" x-model="l1.name" placeholder="e.g. Learning Scheme" required
                                       class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <button type="button" class="text-xs text-red-600 hover:underline" @click="removeL1('learning', l1Index)">Delete</button>
                            </div>

                            <div class="mt-3 space-y-3">
                                <template x-for="(l2, l2Index) in l1.children" :key="'l2_' + l1Index + '_' + l2Index">
                                    <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                                        <div class="flex items-center gap-2">
                                            <input type="text" :name="`learning_structure[${l1Index}][children][${l2Index}][name]`" x-model="l2.name" placeholder="e.g. Actual Contact Hours / Week" required
                                                   class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <button type="button" class="text-xs text-red-600 hover:underline" @click="removeL2('learning', l1Index, l2Index)">Delete</button>
                                        </div>

                                        <div class="mt-2 space-y-2">
                                            <template x-for="(col, colIndex) in l2.columns" :key="'l3_' + l1Index + '_' + l2Index + '_' + colIndex">
                                                <div class="flex items-center gap-2">
                                                    <input type="text" :name="`learning_structure[${l1Index}][children][${l2Index}][columns][${colIndex}]`" x-model="l2.columns[colIndex]" placeholder="e.g. CL" required
                                                           class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                                                    <button type="button" class="text-xs text-gray-500 hover:underline" @click="removeL3('learning', l1Index, l2Index, colIndex)">Remove</button>
                                                </div>
                                            </template>
                                            <button type="button" class="text-xs text-indigo-700 hover:underline" @click="addL3('learning', l1Index, l2Index)">Add Column</button>
                                        </div>
                                    </div>
                                </template>
                                <button type="button" class="text-xs text-indigo-700 hover:underline" @click="addL2('learning', l1Index)">Add Sub-Group</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="pt-6 mt-6 border-t border-gray-100">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Define Assessment Scheme Structure</h3>
                        <p class="text-xs text-gray-500">Only include mark components here (FA/SA Max etc).</p>
                    </div>
                    <button type="button" @click="addL1('assessment')"
                            class="inline-flex items-center gap-1 px-3 py-1 rounded-md bg-indigo-50 text-indigo-700 text-xs font-medium hover:bg-indigo-100 transition">
                        Add Group
                    </button>
                </div>

                <template x-if="assessmentStructure.length === 0">
                    <p class="text-sm text-gray-400">No assessment structure defined.</p>
                </template>

                <div class="space-y-4">
                    <template x-for="(l1, l1Index) in assessmentStructure" :key="'a_' + l1Index">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="flex items-center gap-2">
                                <input type="text" :name="`assessment_structure[${l1Index}][name]`" x-model="l1.name" placeholder="e.g. Assessment Scheme" required
                                       class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <button type="button" class="text-xs text-red-600 hover:underline" @click="removeL1('assessment', l1Index)">Delete</button>
                            </div>

                            <div class="mt-3 space-y-3">
                                <template x-for="(l2, l2Index) in l1.children" :key="'a2_' + l1Index + '_' + l2Index">
                                    <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                                        <div class="flex items-center gap-2">
                                            <input type="text" :name="`assessment_structure[${l1Index}][children][${l2Index}][name]`" x-model="l2.name" placeholder="e.g. Theory" required
                                                   class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                            <button type="button" class="text-xs text-red-600 hover:underline" @click="removeL2('assessment', l1Index, l2Index)">Delete</button>
                                        </div>

                                        <div class="mt-2 space-y-2">
                                            <template x-for="(col, colIndex) in l2.columns" :key="'a3_' + l1Index + '_' + l2Index + '_' + colIndex">
                                                <div class="flex items-center gap-2">
                                                    <input type="text" :name="`assessment_structure[${l1Index}][children][${l2Index}][columns][${colIndex}]`" x-model="l2.columns[colIndex]" placeholder="e.g. FA-TH (Max)" required
                                                           class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                                                    <button type="button" class="text-xs text-gray-500 hover:underline" @click="removeL3('assessment', l1Index, l2Index, colIndex)">Remove</button>
                                                </div>
                                            </template>
                                            <button type="button" class="text-xs text-indigo-700 hover:underline" @click="addL3('assessment', l1Index, l2Index)">Add Column</button>
                                        </div>
                                    </div>
                                </template>
                                <button type="button" class="text-xs text-indigo-700 hover:underline" @click="addL2('assessment', l1Index)">Add Sub-Group</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex gap-3 mt-8">
                <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                    {{ $scheme ? 'Update Scheme' : 'Create Scheme' }}
                </button>
                <a href="{{ route('cdc.schemes.index') }}"
                   class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function schemeEditor(initialLevels, initialLearning, initialAssessment) {
    const defaultLearning = [
        {
            name: 'Learning Scheme',
            children: [
                { name: 'Actual Contact Hours / Week', columns: ['CL', 'TL', 'LL', 'Practical'] },
                { name: 'Self Learning (Activity / Assignment / Micro Project)', columns: [] },
                { name: 'Notional Learning Hours / Week', columns: [] },
            ]
        },
        { name: 'Credits', children: [] },
    ];

    const defaultAssessment = [
        {
            name: 'Assessment Scheme',
            children: [
                { name: 'Theory', columns: ['FA-TH (Max)', 'SA-TH (Max)'] },
                { name: 'Practical', columns: ['FA-PR (Max)', 'SA-PR (Max)'] },
                { name: 'SLA', columns: ['Max (SLA)', 'Min (SLA)'] },
            ]
        }
    ];

    const normalizeLevels = (arr) => (arr || []).map(lv => ({
        id: lv.id || null,
        level_code: lv.level_code || '',
        level_name: lv.level_name || '',
        sort_order: lv.sort_order || 0,
    }));

    return {
        levels: normalizeLevels(initialLevels),
        learningStructure: (initialLearning && initialLearning.length) ? initialLearning : defaultLearning,
        assessmentStructure: (initialAssessment && initialAssessment.length) ? initialAssessment : defaultAssessment,

        addLevel() {
            this.levels.push({ id: null, level_code: '', level_name: '', sort_order: 0 });
        },
        removeLevel(index) {
            if (confirm('Remove this level? This may affect linked programmes.')) {
                this.levels.splice(index, 1);
            }
        },

        addL1(kind) {
            const target = (kind === 'learning') ? this.learningStructure : this.assessmentStructure;
            target.push({ name: '', children: [] });
        },
        removeL1(kind, index) {
            const target = (kind === 'learning') ? this.learningStructure : this.assessmentStructure;
            target.splice(index, 1);
        },
        addL2(kind, l1Index) {
            const target = (kind === 'learning') ? this.learningStructure : this.assessmentStructure;
            target[l1Index].children.push({ name: '', columns: [] });
        },
        removeL2(kind, l1Index, l2Index) {
            const target = (kind === 'learning') ? this.learningStructure : this.assessmentStructure;
            target[l1Index].children.splice(l2Index, 1);
        },
        addL3(kind, l1Index, l2Index) {
            const target = (kind === 'learning') ? this.learningStructure : this.assessmentStructure;
            target[l1Index].children[l2Index].columns.push('');
        },
        removeL3(kind, l1Index, l2Index, l3Index) {
            const target = (kind === 'learning') ? this.learningStructure : this.assessmentStructure;
            target[l1Index].children[l2Index].columns.splice(l3Index, 1);
        },
    };
}
</script>
@endpush
@endsection
