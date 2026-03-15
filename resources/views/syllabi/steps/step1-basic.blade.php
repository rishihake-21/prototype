<div class="space-y-6">
    <h3 class="text-lg font-medium text-gray-900">Step 1: Basic Information</h3>

    <div x-show="definitionLocked" class="rounded-md border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
        Course identity and scheme-linked fields are auto-fetched from CDC Course Definition. If these need changes, update the course in CDC first.
    </div>
    
    <!-- Scheme Type Hidden (Inherited from Programme) -->
    <input type="hidden" name="scheme_type" :value="form.scheme_type">

    <!-- Programme Name -->
    <div>
        <label class="block text-sm font-medium text-gray-700">Programme Name</label>
        <select x-model="form.program_name" @change="onProgrammeChange()" :disabled="definitionLocked" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-600">
            <option value="">Select Programme</option>
            @foreach(App\Models\Syllabus::getProgrammes() as $code => $name)
                <option value="{{ $code }}">{{ $code }} - {{ $name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-indigo-600 font-medium" x-show="form.scheme_type">
                <p class="text-xs font-semibold text-indigo-600 mt-1" 
                   x-text="'Scheme: ' + (form.academic_year ? form.academic_year.split('-')[0] : 'Standard')"></p>
        </p>
    </div>

    <!-- Course Title -->
    <div>
        <label class="block text-sm font-medium text-gray-700">Course Title</label>
        <input type="text" x-model="form.title" @input="updatePreview()" @keydown.enter.prevent :readonly="definitionLocked"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600"
            placeholder="e.g., Advanced Java Programming">
    </div>

    <!-- Course Code -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">
                Course Code
                <span class="text-xs text-gray-500">- e.g., 242001 or dynamic identifier</span>
            </label>
            <input type="text" x-model="form.course_code" @input="onCourseCodeChange()" @keydown.enter.prevent maxlength="20" :readonly="definitionLocked"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600"
                placeholder="e.g., 242001">
            <p class="mt-1 text-xs" :class="levelInfo.class" x-text="levelInfo.text"></p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Level / Classification</label>
            <select x-model="detectedLevel" @change="onManualLevelChange()" :disabled="definitionLocked"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-600">
                <option value="">Select Level</option>
                <option value="1">Level 1: Foundation (Science & Humanities)</option>
                <option value="2">Level 2: Basic Technology</option>
                <option value="3">Level 3: Allied Courses (Electives)</option>
                <option value="4">Level 4: Applied Technology (Training/Project)</option>
                <option value="5">Level 5: Diversified Technology</option>
                <option value="0">Level 0: Audit / Co-curricular</option>
            </select>
            <p class="mt-1 text-xs text-gray-500">Syllabus structure (units, marks) adapts to this level.</p>
        </div>
    </div>

    <!-- Academic Year -->
    <div>
        <label class="block text-sm font-medium text-gray-700">Academic Year</label>
        <input type="text" x-model="form.academic_year" @input="updatePreview()" @keydown.enter.prevent :readonly="definitionLocked"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 read-only:text-gray-600"
            placeholder="e.g., 2024-25">
    </div>

    <!-- IKS Hours -->
    <div x-show="form.scheme_type === 'standard'">
        <label class="block text-sm font-medium text-gray-700">IKS Content Hours</label>
        <input type="number" x-model="form.iks_hours" @input="updatePreview()" @keydown.enter.prevent min="0"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <p class="mt-1 text-xs text-gray-500">Indian Knowledge System hours (required for standard curriculum where applicable)</p>
    </div>

    <!-- Departments (Multi-select) -->
    <div>
        <label class="block text-sm font-medium text-gray-700">Departments</label>
        <select multiple x-model="form.department_ids" :disabled="definitionLocked" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-600" size="4">
            @foreach(App\Models\Department::all() as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">Hold Ctrl (Cmd on Mac) to select multiple departments</p>
    </div>

    <!-- Level-specific flags -->
    <div x-show="detectedLevel === 1" class="bg-blue-50 p-4 rounded-md">
        <label class="flex items-center">
            <input type="checkbox" x-model="form.is_online_exam" @change="updatePreview()" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <span class="ml-2 text-sm text-gray-700">Indicates Online Examination (#)</span>
        </label>
    </div>

    <div x-show="detectedLevel === 3" class="bg-green-50 p-4 rounded-md space-y-3">
        <div>
            <label class="block text-sm font-medium text-gray-700">Elective Group</label>
            <select x-model="form.elective_group" @change="updatePreview()" :disabled="definitionLocked" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-600">
                <option value="">Select Elective Group</option>
                @foreach(App\Models\Syllabus::getElectiveGroups() as $group)
                    <option value="{{ $group }}">{{ $group }}</option>
                @endforeach
            </select>
        </div>
        <label class="flex items-center">
            <input type="checkbox" x-model="form.is_part_of_group" @change="updatePreview()" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <span class="ml-2 text-sm text-gray-700">Part of a Course Group (adds "Any ONE of the following")</span>
        </label>
    </div>
</div>
