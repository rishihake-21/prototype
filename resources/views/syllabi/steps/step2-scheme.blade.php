<div class="space-y-6">
    <h3 class="text-lg font-medium text-gray-900">Step 2: Teaching & Examination Scheme</h3>

    <div x-show="definitionLocked" class="rounded-md border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
        This entire learning and assessment scheme is inherited from the linked CDC Course Definition and scheme structure.
    </div>
    
    <!-- Teaching / Learning Scheme -->
    <div class="bg-gray-50 p-4 rounded-md">
        <h4 class="font-medium text-gray-700 mb-4" x-text="'Scheme: ' + (form.academic_year ? form.academic_year.split('-')[0] : 'Standard')"></h4>

        <div x-show="Array.isArray(form.learning_scheme_rows) && form.learning_scheme_rows.length > 0" class="mb-6 overflow-x-auto rounded-md border border-gray-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead class="bg-gray-100 text-gray-700">
                    <template x-for="(row, rowIndex) in form.learning_scheme_rows" :key="'learning-row-' + rowIndex">
                        <tr>
                            <template x-for="(cell, cellIndex) in row" :key="'learning-cell-' + rowIndex + '-' + cellIndex">
                                <th
                                    class="border border-gray-200 px-3 py-2 text-center font-medium"
                                    :colspan="cell.colspan || 1"
                                    :rowspan="cell.rowspan || 1"
                                    x-text="cell.name">
                                </th>
                            </template>
                        </tr>
                    </template>
                </thead>
                <tbody>
                    <tr>
                        <template x-for="leaf in form.learning_scheme_leaf_columns" :key="'learning-value-' + leaf.id">
                            <td class="border border-gray-200 px-3 py-2 text-center text-gray-800">
                                <div class="font-medium" x-text="getLearningCellValue(leaf.id)"></div>
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
            <p class="px-3 py-2 text-xs text-gray-500">
                Teaching / Learning scheme columns are fetched from the linked scheme metadata snapshot.
            </p>
        </div>

        <div class="grid grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Classroom Learning (CL)</label>
                <input type="number" x-model="form.teaching_scheme.th_hours" @input="calculateCredits()" @keydown.enter.prevent min="0" :readonly="definitionLocked"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tutorial (TU)</label>
                <input type="number" x-model="form.teaching_scheme.tu_hours" @input="calculateCredits()" @keydown.enter.prevent min="0" :readonly="definitionLocked"
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600">
                <p class="text-xs text-gray-500 mt-1">Independent/Guided problem solving.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Laboratory Learning (LL)</label>
                <input type="number" x-model="form.teaching_scheme.pr_hours" @input="calculateCredits()" @keydown.enter.prevent min="0" :readonly="definitionLocked"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Total Learning (TL)</label>
                <input type="number" x-model="form.teaching_scheme.total_hours" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                <p class="text-xs text-gray-500 mt-1">Auto: TL = CL + LL</p>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Credits</label>
                <input type="number" x-model="form.teaching_scheme.credits" @input="updatePreview()" @keydown.enter.prevent min="0" :readonly="definitionLocked"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600">
                <p class="text-xs text-gray-500 mt-1">Enter as per curriculum rules.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Self-Learning (SLH)</label>
                <input type="number" x-model="form.teaching_scheme.slh_hours" @input="calculateCredits()" @keydown.enter.prevent min="0" :readonly="definitionLocked"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600">
                <p class="text-xs text-gray-500 mt-1">Term-based independent study</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Non-Lecture (NLH)</label>
                <input type="number" x-model="form.teaching_scheme.nlh_hours" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                <p class="text-xs text-gray-500 mt-1">Auto: NLH = TL + SLH</p>
            </div>
        </div>
    </div>

    <!-- Assessment Scheme -->
    <div class="bg-gray-50 p-4 rounded-md">
        <h4 class="font-medium text-gray-700 mb-4">Assessment Scheme</h4>

        <div x-show="Array.isArray(form.assessment_scheme_rows) && form.assessment_scheme_rows.length > 0" class="mb-6 overflow-x-auto rounded-md border border-gray-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead class="bg-gray-100 text-gray-700">
                    <template x-for="(row, rowIndex) in form.assessment_scheme_rows" :key="'scheme-row-' + rowIndex">
                        <tr>
                            <template x-for="(cell, cellIndex) in row" :key="'scheme-cell-' + rowIndex + '-' + cellIndex">
                                <th
                                    class="border border-gray-200 px-3 py-2 text-center font-medium"
                                    :colspan="cell.colspan || 1"
                                    :rowspan="cell.rowspan || 1"
                                    x-text="cell.name">
                                </th>
                            </template>
                        </tr>
                    </template>
                </thead>
                <tbody>
                    <tr>
                        <template x-for="leaf in form.assessment_scheme_leaf_columns" :key="'scheme-value-' + leaf.id">
                            <td class="border border-gray-200 px-3 py-2 text-center text-gray-800">
                                <div class="font-medium" x-text="getAssessmentCellValue(leaf.id, 'max_marks')"></div>
                                <div class="text-xs text-gray-500" x-show="getAssessmentCellValue(leaf.id, 'min_marks') !== '--'">
                                    Min: <span x-text="getAssessmentCellValue(leaf.id, 'min_marks')"></span>
                                </div>
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
            <p class="px-3 py-2 text-xs text-gray-500">
                Assessment columns are fetched from the linked scheme metadata snapshot.
            </p>
        </div>
        
        <!-- Formative Assessment (FA-TH) -->
        <div class="mb-4">
            <h5 class="text-sm font-medium text-gray-600 mb-2">Formative Assessment - Theory/Tests (FA-TH)</h5>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-600">Max Marks</label>
                    <input type="number" x-model="form.examination_scheme.fa_th_max" @input="updatePreview()" @keydown.enter.prevent min="0" :readonly="definitionLocked"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600">
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Min Marks</label>
                    <input type="number" x-model="form.examination_scheme.fa_th_min" @input="updatePreview()" @keydown.enter.prevent min="0" :readonly="definitionLocked"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600">
                </div>
            </div>
        </div>

        <!-- Summative Assessment (SA-TH) -->
        <div class="mb-4">
            <h5 class="text-sm font-medium text-gray-600 mb-2">Summative Assessment - Theory/End Exam (SA-TH)</h5>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-600">Max Marks</label>
                    <input type="number" x-model="form.examination_scheme.sa_th_max" @input="updatePreview()" @keydown.enter.prevent min="0" :readonly="definitionLocked"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600">
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Min Marks</label>
                    <input type="number" x-model="form.examination_scheme.sa_th_min" @input="updatePreview()" @keydown.enter.prevent min="0" :readonly="definitionLocked"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600">
                </div>
            </div>
        </div>

        <!-- Summative Assessment (SA-PR) -->
        <div class="mb-4" x-show="form.teaching_scheme.pr_hours > 0">
            <h5 class="text-sm font-medium text-gray-600 mb-2">Summative Assessment - Practical (SA-PR)</h5>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-600">Max Marks</label>
                    <input type="number" x-model="form.examination_scheme.sa_pr_max" @input="updatePreview()" @keydown.enter.prevent min="0" :readonly="definitionLocked"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600">
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Min Marks</label>
                    <input type="number" x-model="form.examination_scheme.sa_pr_min" @input="updatePreview()" @keydown.enter.prevent min="0" :readonly="definitionLocked"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600">
                </div>
            </div>
        </div>

        <!-- Paper Duration -->
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Paper Duration (Hours)</label>
                <input type="number" step="0.5" x-model="form.examination_scheme.paper_duration" @input="updatePreview()" @keydown.enter.prevent min="0" :readonly="definitionLocked"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600"
                    placeholder="e.g., 3.0">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Term Work (TW)</label>
                <input type="number" x-model="form.examination_scheme.tw_marks" @input="updatePreview()" @keydown.enter.prevent min="0" :readonly="definitionLocked"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600"
                    placeholder="Marks for continuous assessment">
            </div>
            <div class="flex items-end">
                <label class="flex items-center mb-2">
                    <input type="checkbox" x-model="form.examination_scheme.is_internal_practical" @change="updatePreview()" 
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Internal Practical (@)</span>
                </label>
            </div>
        </div>
        
        <!-- Total Marks Display -->
        <div class="mt-4 p-3 bg-indigo-50 rounded-md">
            <div class="flex justify-between items-center">
                <span class="font-medium text-indigo-800">Total Marks:</span>
                <span class="text-lg font-bold text-indigo-900" x-text="calculateTotalMarks()"></span>
            </div>
        </div>
    </div>
</div>
