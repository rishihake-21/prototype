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
            if (collect($out)->contains(fn ($row) => ($row['name'] ?? null) === $n1->component_name)) {
                continue;
            }

            $l1Data = ['name' => $n1->component_name, 'children' => []];
            $l2 = $nodes->where('parent_id', $n1->id);
            foreach ($l2 as $n2) {
                if (collect($l1Data['children'])->contains(fn ($row) => ($row['name'] ?? null) === $n2->component_name)) {
                    continue;
                }

                $l2Data = ['name' => $n2->component_name, 'columns' => []];
                $l3 = $nodes->where('parent_id', $n2->id);
                foreach ($l3 as $n3) {
                    if (collect($l2Data['columns'])->contains(function ($col) use ($n3) {
                        return is_array($col)
                            ? (($col['name'] ?? null) === $n3->component_name)
                            : ($col === $n3->component_name);
                    })) {
                        continue;
                    }

                    $l2Data['columns'][] = [
                        'name' => $n3->component_name,
                        'semantic_key' => $n3->semantic_key,
                        'usage_scope' => $n3->usage_scope,
                        'entry_mode' => $n3->entry_mode,
                        'total_role' => $n3->total_role,
                    ];
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
                                                <div class="rounded-lg border border-gray-200 bg-white p-3">
                                                    <div class="grid gap-2 sm:grid-cols-3">
                                                        <div>
                                                            <label class="block text-[11px] text-gray-600 mb-1">Column Name</label>
                                                            <input type="text" :name="`learning_structure[${l1Index}][children][${l2Index}][columns][${colIndex}][name]`" x-model="col.name" placeholder="e.g. CL" required
                                                                   class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[11px] text-gray-600 mb-1">Meaning</label>
                                                            <select :name="`learning_structure[${l1Index}][children][${l2Index}][columns][${colIndex}][semantic_key]`"
                                                                    x-model="col.semantic_key"
                                                                    class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                                                                <template x-for="option in learningSemanticOptions" :key="option.value">
                                                                    <option :value="option.value" x-text="option.label"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-[11px] text-gray-600 mb-1">Used In</label>
                                                            <select :name="`learning_structure[${l1Index}][children][${l2Index}][columns][${colIndex}][usage_scope]`"
                                                                    x-model="col.usage_scope"
                                                                    class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                                                                <template x-for="option in learningUsageScopeOptions" :key="option.value">
                                                                    <option :value="option.value" x-text="option.label"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 flex justify-end">
                                                        <button type="button" class="text-xs text-gray-500 hover:underline" @click="removeL3('learning', l1Index, l2Index, colIndex)">Remove</button>
                                                    </div>
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
                        <p class="text-xs text-gray-500">Define the assessment columns and also what each one means in the workflow.</p>
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
                                                <div class="rounded-lg border border-gray-200 bg-white p-3">
                                                    <div class="grid gap-2 sm:grid-cols-3">
                                                        <div class="sm:col-span-1">
                                                            <label class="block text-[11px] text-gray-600 mb-1">Column Name</label>
                                                            <input type="text" :name="`assessment_structure[${l1Index}][children][${l2Index}][columns][${colIndex}][name]`" x-model="col.name" placeholder="e.g. FA-TH (Max)" required
                                                                   class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[11px] text-gray-600 mb-1">Meaning</label>
                                                            <select :name="`assessment_structure[${l1Index}][children][${l2Index}][columns][${colIndex}][semantic_key]`"
                                                                    x-model="col.semantic_key"
                                                                    class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                                                                <template x-for="option in semanticOptions" :key="option.value">
                                                                    <option :value="option.value" x-text="option.label"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-[11px] text-gray-600 mb-1">Used In</label>
                                                            <select :name="`assessment_structure[${l1Index}][children][${l2Index}][columns][${colIndex}][usage_scope]`"
                                                                    x-model="col.usage_scope"
                                                                    class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                                                                <template x-for="option in usageScopeOptions" :key="option.value">
                                                                    <option :value="option.value" x-text="option.label"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 flex justify-end">
                                                        <button type="button" class="text-xs text-gray-500 hover:underline" @click="removeL3('assessment', l1Index, l2Index, colIndex)">Remove</button>
                                                    </div>
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
                {
                    name: 'Actual Contact Hours / Week',
                    columns: [
                        { name: 'CL', semantic_key: 'th_hours', usage_scope: 'syllabus' },
                        { name: 'TU', semantic_key: 'tu_hours', usage_scope: 'syllabus' },
                        { name: 'LL', semantic_key: 'pr_hours', usage_scope: 'syllabus' },
                    ]
                },
                {
                    name: 'Self Learning (Activity / Assignment / Micro Project)',
                    columns: [
                        { name: 'SLH', semantic_key: 'slh_hours', usage_scope: 'syllabus' },
                    ]
                },
                {
                    name: 'Notional Learning Hours / Week',
                    columns: [
                        { name: 'TL', semantic_key: 'total_hours', usage_scope: 'syllabus' },
                        { name: 'NLH', semantic_key: 'nlh_hours', usage_scope: 'syllabus' },
                    ]
                },
            ]
        },
        {
            name: 'Credits',
            children: [
                {
                    name: 'Summary',
                    columns: [
                        { name: 'Credits', semantic_key: 'credits', usage_scope: 'syllabus' },
                    ]
                }
            ],
        },
    ];

    const defaultAssessment = [
        {
            name: 'Assessment Scheme',
            children: [
                {
                    name: 'Theory',
                    columns: [
                        { name: 'FA-TH (Max)', semantic_key: 'fa_th_max', usage_scope: 'course_definition' },
                        { name: 'FA-TH (Min)', semantic_key: 'fa_th_min', usage_scope: 'course_definition' },
                        { name: 'SA-TH (Max)', semantic_key: 'sa_th_max', usage_scope: 'course_definition' },
                        { name: 'SA-TH (Min)', semantic_key: 'sa_th_min', usage_scope: 'course_definition' },
                    ]
                },
                {
                    name: 'Practical',
                    columns: [
                        { name: 'FA-PR (Max)', semantic_key: 'fa_pr_max', usage_scope: 'course_definition' },
                        { name: 'FA-PR (Min)', semantic_key: 'fa_pr_min', usage_scope: 'course_definition' },
                        { name: 'SA-PR (Max)', semantic_key: 'sa_pr_max', usage_scope: 'course_definition' },
                        { name: 'SA-PR (Min)', semantic_key: 'sa_pr_min', usage_scope: 'course_definition' },
                    ]
                },
                {
                    name: 'SLA',
                    columns: [
                        { name: 'Max (SLA)', semantic_key: 'sla_max', usage_scope: 'course_definition' },
                        { name: 'Min (SLA)', semantic_key: 'sla_min', usage_scope: 'course_definition' },
                    ]
                },
            ]
        }
    ];

    const normalizeLevels = (arr) => (arr || []).map(lv => ({
        id: lv.id || null,
        level_code: lv.level_code || '',
        level_name: lv.level_name || '',
        sort_order: lv.sort_order || 0,
    }));

    const inferSemanticKey = (name) => {
        const n = (name || '').trim().toLowerCase();

        if (!n) return '';
        if (n === 'paper duration' || n.includes('duration') || n.includes('hrs')) return 'paper_duration';
        if (n.includes('fa-th') && n.includes('min')) return 'fa_th_min';
        if (n.includes('fa-th')) return 'fa_th_max';
        if (n.includes('sa-th') && n.includes('min')) return 'sa_th_min';
        if (n.includes('sa-th')) return 'sa_th_max';
        if (n.includes('fa-pr') && n.includes('min')) return 'fa_pr_min';
        if (n.includes('fa-pr')) return 'fa_pr_max';
        if (n.includes('sa-pr') && n.includes('min')) return 'sa_pr_min';
        if (n.includes('sa-pr')) return 'sa_pr_max';
        if (n.includes('sla') && n.includes('min')) return 'sla_min';
        if (n.includes('sla')) return 'sla_max';
        if (n.includes('total')) return 'total_marks';
        if (n.includes('min')) return 'min_marks';

        return '';
    };

    const inferUsageScope = (semanticKey, name) => {
        if (semanticKey === 'paper_duration') return 'course_definition';
        if (semanticKey === 'total_marks') return 'display_only';
        if (semanticKey.endsWith('_min') || semanticKey === 'min_marks') return 'course_definition';

        const n = (name || '').trim().toLowerCase();
        if (n.includes('total')) return 'display_only';
        if (n.includes('min')) return 'course_definition';

        return 'course_definition';
    };

    const dedupeByName = (arr) => {
        const seen = new Set();
        return (arr || []).filter(item => {
            const key = (item?.name || '').trim().toLowerCase();
            if (!key || seen.has(key)) return false;
            seen.add(key);
            return true;
        });
    };

    const normalizeAssessmentStructure = (arr) => dedupeByName((arr || []).map(group => ({
        name: group.name || '',
        children: dedupeByName((group.children || []).map(child => ({
            name: child.name || '',
            columns: dedupeByName((child.columns || []).map(col => {
                if (typeof col === 'string') {
                    const semanticKey = inferSemanticKey(col);
                    return { name: col, semantic_key: semanticKey, usage_scope: inferUsageScope(semanticKey, col) };
                }

                const semanticKey = col.semantic_key || inferSemanticKey(col.name || '');
                return {
                    name: col.name || '',
                    semantic_key: semanticKey,
                    usage_scope: col.usage_scope || inferUsageScope(semanticKey, col.name || ''),
                };
            })),
        }))),
    })));

    const normalizeLearningSemanticKey = (name) => {
        const n = (name || '').trim().toLowerCase();
        if (!n) return '';
        if (n === 'credits') return 'credits';
        if (n === 'cl' || n.includes('classroom')) return 'th_hours';
        if (n === 'tu' || n.includes('tutorial')) return 'tu_hours';
        if (n === 'll' || n === 'practical' || n.includes('laboratory')) return 'pr_hours';
        if (n.includes('self learning') || n.includes('slh')) return 'slh_hours';
        if (n.includes('notional') || n.includes('nlh')) return 'nlh_hours';
        if (n.includes('total learning') || n === 'tl' || n.includes('total hrs')) return 'total_hours';
        return '';
    };

    const normalizeLearningStructure = (arr) => dedupeByName((arr || []).map(group => ({
        name: group.name || '',
        children: dedupeByName((group.children || []).map(child => ({
            name: child.name || '',
            columns: dedupeByName((child.columns || []).map(col => {
                if (typeof col === 'string') {
                    const semanticKey = normalizeLearningSemanticKey(col);
                    return { name: col, semantic_key: semanticKey, usage_scope: 'syllabus' };
                }

                const semanticKey = col.semantic_key || normalizeLearningSemanticKey(col.name || '');
                return {
                    name: col.name || '',
                    semantic_key: semanticKey,
                    usage_scope: col.usage_scope || 'syllabus',
                };
            })),
        }))),
    })));

    return {
        levels: normalizeLevels(initialLevels),
        learningStructure: (initialLearning && initialLearning.length) ? normalizeLearningStructure(initialLearning) : defaultLearning,
        assessmentStructure: (initialAssessment && initialAssessment.length) ? normalizeAssessmentStructure(initialAssessment) : defaultAssessment,
        learningSemanticOptions: [
            { value: '', label: 'Custom / Not mapped' },
            { value: 'th_hours', label: 'Theory / CL Hours' },
            { value: 'tu_hours', label: 'Tutorial Hours' },
            { value: 'pr_hours', label: 'Practical / LL Hours' },
            { value: 'total_hours', label: 'Total Learning Hours' },
            { value: 'slh_hours', label: 'Self Learning Hours' },
            { value: 'nlh_hours', label: 'Notional Learning Hours' },
            { value: 'credits', label: 'Credits' },
        ],
        learningUsageScopeOptions: [
            { value: 'syllabus', label: 'Syllabus' },
            { value: 'course_definition', label: 'Course Definition' },
            { value: 'display_only', label: 'Display Only' },
        ],
        semanticOptions: [
            { value: '', label: 'Custom / Not mapped' },
            { value: 'fa_th_max', label: 'FA Theory Max' },
            { value: 'fa_th_min', label: 'FA Theory Min' },
            { value: 'sa_th_max', label: 'SA Theory Max' },
            { value: 'sa_th_min', label: 'SA Theory Min' },
            { value: 'fa_pr_max', label: 'FA Practical Max' },
            { value: 'fa_pr_min', label: 'FA Practical Min' },
            { value: 'sa_pr_max', label: 'SA Practical Max' },
            { value: 'sa_pr_min', label: 'SA Practical Min' },
            { value: 'sla_max', label: 'SLA Max' },
            { value: 'sla_min', label: 'SLA Min' },
            { value: 'oral_max', label: 'Oral Max' },
            { value: 'tw_max', label: 'TW Max' },
            { value: 'paper_duration', label: 'Paper Duration' },
            { value: 'total_marks', label: 'Total Marks' },
            { value: 'min_marks', label: 'Generic Min Marks' },
        ],
        usageScopeOptions: [
            { value: 'course_definition', label: 'Course Definition' },
            { value: 'syllabus', label: 'Syllabus' },
            { value: 'result', label: 'Result' },
            { value: 'transcript', label: 'Transcript' },
            { value: 'display_only', label: 'Display Only' },
        ],

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
            if (kind === 'learning') {
                target[l1Index].children[l2Index].columns.push({
                    name: '',
                    semantic_key: '',
                    usage_scope: 'syllabus',
                });
                return;
            }

            target[l1Index].children[l2Index].columns.push({
                name: '',
                semantic_key: '',
                usage_scope: 'course_definition',
            });
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
