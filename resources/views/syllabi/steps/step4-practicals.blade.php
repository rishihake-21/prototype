<div class="space-y-6">
    <h3 class="text-lg font-medium text-gray-900">Step 4: Practical Tasks</h3>
    <p class="text-sm text-gray-500">List all practical exercises and tasks for this course.</p>
    
    <div class="flex justify-between items-center">
        <label class="block text-sm font-medium text-gray-700">Task List</label>
        <button type="button" @click="addPracticalTask()" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add Task</button>
    </div>

    <template x-for="(task, index) in form.practical_tasks" :key="index">
        <div class="bg-gray-50 p-4 rounded-md">
            <div class="flex justify-between items-center mb-3">
                <h5 class="font-medium text-gray-700" x-text="'Task ' + task.s_no"></h5>
                <button type="button" @click="removePracticalTask(index)" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
            </div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-4">
                    <label class="block text-sm text-gray-600">Laboratory Learning Outcome</label>
                    <input type="text" x-model="task.llo" @input="updatePreview()" @keydown.enter.prevent
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Example: Implement basic constructs of PHP">
                </div>
                <div class="col-span-4">
                    <label class="block text-sm text-gray-600">Practical Exercise</label>
                    <input type="text" x-model="task.title" @input="updatePreview()" @keydown.enter.prevent
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm text-gray-600">Unit No.</label>
                    <select x-model="task.unit_no" @change="updatePreview()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Unit</option>
                        <template x-for="(unit, idx) in form.units" :key="idx">
                            <option :value="unit.unit_no" x-text="'Unit ' + unit.unit_no"></option>
                        </template>
                    </select>
                </div>
                <div class="col-span-1">
                    <label class="block text-sm text-gray-600">Hours</label>
                    <input type="number" x-model="task.hours" @input="updatePreview()" @keydown.enter.prevent min="0"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="col-span-1">
                    <label class="block text-sm text-gray-600">Mapped CO</label>
                    <select x-model="task.co_code" @change="updatePreview()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select CO</option>
                        <template x-for="(outcome, idx) in form.course_outcomes" :key="idx">
                            <option :value="'CO' + (idx + 1)" x-text="'CO' + (idx + 1)"></option>
                        </template>
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <label class="flex items-center">
                    <input type="checkbox" x-model="task.is_mandatory" @change="updatePreview()" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Mandatory Task (marked with * in preview)</span>
                </label>
            </div>
        </div>
    </template>

    <div x-show="form.practical_tasks.length === 0" class="text-center py-8 text-gray-500 bg-gray-50 rounded-md">
        No practical tasks added yet. Click "+ Add Task" to add one.
    </div>
</div>
