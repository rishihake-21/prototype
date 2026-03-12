@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6">

    <div class="mb-6">
        <h1 class="mt-2 text-2xl font-bold text-gray-900">
            Global Curriculum Levels
        </h1>
        <p class="text-sm text-gray-500 mt-1">Manage the hierarchy of course levels that all departments will follow for their respective programmes.</p>
    </div>

    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
        <form method="POST" action="{{ route('cdc.global-levels.update') }}">
            @csrf
            @method('PUT')

            @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-700">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="mb-8" x-data="levelsManager({{ $levels }})">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">College-Level Curriculum Definition</h3>
                        <p class="text-xs text-gray-500">Add or edit levels here. Note that renaming these levels will affect all newly created programmes.</p>
                    </div>
                    <button type="button" @click="addLevel()"
                            class="inline-flex items-center gap-1 px-3 py-1 rounded-md bg-indigo-50 text-indigo-700 text-xs font-medium hover:bg-indigo-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Level
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(level, index) in levels" :key="index">
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 bg-gray-50/50 group">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-xs font-bold text-gray-400 group-hover:text-indigo-600 transition" x-text="index + 1"></div>
                            
                            <div class="flex-1 grid grid-cols-5 gap-3">
                                <div class="col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-tight mb-0.5">Code</label>
                                    <input type="text" :name="'levels['+index+'][level_code]'" x-model="level.level_code" placeholder="L-1" required
                                           class="w-full rounded-md border-gray-200 bg-white px-3 py-1.5 text-sm focus:ring-1 focus:ring-indigo-500">
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-tight mb-0.5">Name</label>
                                    <input type="text" :name="'levels['+index+'][level_name]'" x-model="level.level_name" placeholder="Foundation Courses" required
                                           class="w-full rounded-md border-gray-200 bg-white px-3 py-1.5 text-sm focus:ring-1 focus:ring-indigo-500">
                                </div>
                                <input type="hidden" :name="'levels['+index+'][id]'" x-model="level.id">
                                <input type="hidden" :name="'levels['+index+'][sort_order]'" :value="index">
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
                    <p class="text-sm text-gray-400">No levels defined yet. Click "Add Level" to start.</p>
                </div>
            </div>

            <div class="flex gap-3 pt-6 border-t border-gray-100">
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                    Save Global Levels
                </button>
                <a href="{{ route('dashboard.cdc') }}"
                   class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Back to Dashboard
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function levelsManager(initialLevels) {
    return {
        levels: initialLevels.length > 0 ? initialLevels : [],
        addLevel() {
            this.levels.push({ level_code: 'Level-'+this.levels.length, level_name: '' });
        },
        removeLevel(index) {
            if (confirm('Are you sure? Removing a global level may be consequential.')) {
                this.levels.splice(index, 1);
            }
        }
    }
}
</script>
@endpush
@endsection
