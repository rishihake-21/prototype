












<div class="space-y-6">
    <h3 class="text-lg font-medium text-gray-900">Step 3: Course Content</h3>
    
    <!-- Rationale -->
    <div>
        <label class="block text-sm font-medium text-gray-700">Rationale</label>
        <p class="text-xs text-gray-500 mb-1">Why is this course needed? (Max 1000 characters)</p>
        <textarea x-model="form.rationale" @input="updatePreview()" rows="4" maxlength="1000"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Explain the importance and need for this course..."></textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Industry / Employer Expected Outcome</label>
        <p class="text-xs text-gray-500 mb-1">State the main outcome industry expects from a student completing this course.</p>
        <textarea x-model="form.industry_employer_outcome" @input="updatePreview()" rows="3" maxlength="2000"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Example: Develop web based applications using server side scripting with PHP."></textarea>
    </div>

    <!-- Course Objectives -->
    <div>
        <div class="flex justify-between items-center mb-2">
            <label class="block text-sm font-medium text-gray-700">Course Objectives (Optional)</label>
            <button type="button" @click="addObjective()" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add Objective</button>
        </div>
        <p class="text-xs text-gray-500 mb-2">Keep this only if your department wants internal planning notes beyond the sample syllabus format.</p>
        <template x-for="(objective, index) in form.course_objectives" :key="index">
            <div class="flex gap-2 mb-2">
                <input type="text" x-model="form.course_objectives[index]" @input="updatePreview()" @keydown.enter.prevent
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :placeholder="'Objective ' + (index + 1)">
                <button type="button" @click="removeObjective(index)" class="text-red-500 hover:text-red-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Course Outcomes (COs) -->
    <div>
        <div class="flex justify-between items-center mb-2">
            <label class="block text-sm font-medium text-gray-700">Course Outcomes (COs)</label>
            <button type="button" @click="addOutcome()" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add Outcome</button>
        </div>
        <p class="text-xs text-gray-500 mb-2">Minimum 3 outcomes required. These will be used for CO-PO mapping.</p>
        <template x-for="(outcome, index) in form.course_outcomes" :key="index">
            <div class="flex gap-2 mb-2">
                <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-700 text-sm rounded" x-text="'CO' + (index + 1)"></span>
                <input type="text" x-model="outcome.description" @input="updatePreview()" @keydown.enter.prevent
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Outcome description...">
                <button type="button" @click="removeOutcome(index)" class="text-red-500 hover:text-red-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Level 4: Training/Project Specifics -->
    <div x-show="detectedLevel === 4" class="bg-yellow-50 p-4 rounded-md space-y-4">
        <h4 class="font-medium text-gray-700">Training/Project Details</h4>
        
        <div>
            <label class="block text-sm font-medium text-gray-700">Training Location</label>
            <select x-model="form.training_location" @change="updatePreview()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select Location</option>
                <option value="Industry">Industry</option>
                <option value="Field">Field</option>
                <option value="Laboratory">Laboratory</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Project Phase</label>
            <select x-model="form.project_phase" @change="updatePreview()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select Phase</option>
                @foreach(App\Models\Syllabus::getProjectPhases() as $key => $phase)
                    <option value="{{ $key }}">{{ $phase }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Min Group Size</label>
                <input type="number" x-model="form.group_size_min" @input="updatePreview()" @keydown.enter.prevent min="1"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Max Group Size</label>
                <input type="number" x-model="form.group_size_max" @input="updatePreview()" @keydown.enter.prevent min="1"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex gap-4">
            <label class="flex items-center">
                <input type="checkbox" x-model="form.logbook_required" @change="updatePreview()" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Logbook Required</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" x-model="form.industry_supervisor" @change="updatePreview()" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Industry Supervisor</span>
            </label>
        </div>
    </div>

    <!-- Units Table (Levels 1, 2, 3, 5) -->
    <div x-show="detectedLevel !== 4">
        <div class="flex justify-between items-center mb-2">
            <label class="block text-sm font-medium text-gray-700">Units</label>
            <button type="button" @click="addUnit()" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add Unit</button>
        </div>
        
        <template x-for="(unit, index) in form.units" :key="index">
            <div class="bg-gray-50 p-4 rounded-md mb-4">
                <div class="flex justify-between items-center mb-3">
                    <h5 class="font-medium text-gray-700" x-text="'Unit ' + unit.unit_no"></h5>
                    <button type="button" @click="removeUnit(index)" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm text-gray-600">Unit Title</label>
                        <input type="text" x-model="unit.title" @input="updatePreview()" @keydown.enter.prevent
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Major Learning Outcomes (Cognitive Domain)</label>
                        <textarea x-model="unit.cognitive_outcomes" @input="updatePreview()" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Topics & Sub-topics</label>
                        <textarea x-model="unit.topics" @input="updatePreview()" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Enter topics as bullet points..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Hours</label>
                        <input type="number" x-model="unit.hours" @input="updatePreview()" @keydown.enter.prevent min="0"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Specification Table (Levels 2 & 5) -->
    <div x-show="detectedLevel === 2 || detectedLevel === 5">
        <h4 class="font-medium text-gray-700 mb-2">Theory Specification Table</h4>
        <p class="text-xs text-gray-500 mb-2">Distribution of marks across Remembrance (R), Understanding (U), and Application (A)</p>
        
        <table class="min-w-full divide-y divide-gray-200 border">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Unit</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">R</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">U</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">A</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <template x-for="(spec, index) in form.specification_table" :key="index">
                    <tr>
                        <td class="px-3 py-2 text-sm" x-text="'Unit ' + spec.unit_no"></td>
                        <td class="px-3 py-2">
                            <input type="number" x-model="spec.r" @input="updateSpecTotal(index); updatePreview()" @keydown.enter.prevent min="0"
                                class="w-16 text-center rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" x-model="spec.u" @input="updateSpecTotal(index); updatePreview()" @keydown.enter.prevent min="0"
                                class="w-16 text-center rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" x-model="spec.a" @input="updateSpecTotal(index); updatePreview()" @keydown.enter.prevent min="0"
                                class="w-16 text-center rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="px-3 py-2 text-center font-medium" x-text="(parseInt(spec.r || 0) + parseInt(spec.u || 0) + parseInt(spec.a || 0))"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Training Schedule (Level 4) -->
    <div x-show="detectedLevel === 4">
        <div class="flex justify-between items-center mb-2">
            <label class="block text-sm font-medium text-gray-700">Weekly Training Schedule</label>
            <button type="button" @click="addTrainingWeek()" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add Week</button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Week</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Activity to be Completed</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">Industry Marks</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">Mentor Marks</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="(week, index) in form.training_schedule" :key="index">
                        <tr>
                            <td class="px-3 py-2 text-sm font-medium text-gray-900" x-text="'Week ' + week.week_no"></td>
                            <td class="px-3 py-2">
                                <input type="text" x-model="week.activity" @input="updatePreview()" @keydown.enter.prevent
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    placeholder="Activity to be performed...">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" x-model="week.marks_industry" @input="updatePreview()" @keydown.enter.prevent min="0"
                                    class="w-20 text-center rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" x-model="week.marks_mentor" @input="updatePreview()" @keydown.enter.prevent min="0"
                                    class="w-20 text-center rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" @click="removeTrainingWeek(index)" class="text-red-500 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        
        <!-- Report Format Section -->
        <div class="mt-6 bg-orange-50 p-4 rounded-md">
            <h4 class="font-medium text-gray-700 mb-3">Report Format (Chapter Structure)</h4>
            <p class="text-xs text-gray-500 mb-3">Standard chapters for the training report:</p>
            <div class="space-y-3">
                <template x-for="(chapter, index) in form.report_format" :key="index">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-600 w-24" x-text="'Chapter ' + chapter.chapter + ':'"></span>
                        <input type="text" x-model="chapter.title" @input="updatePreview()" @keydown.enter.prevent
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
