@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6">

    <div class="mb-6">
        <a href="{{ route('cdc.programmes.index') }}"
           class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">
            <- Back to Programmes
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">
            {{ $programme ? 'Edit Programme' : 'New Programme' }}
        </h1>
    </div>

    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
        <form method="POST"
              action="{{ $programme
                  ? route('cdc.programmes.update', $programme)
                  : route('cdc.programmes.store') }}">
            @csrf
            @if($programme) @method('PUT') @endif

            @if($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Name --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="name">
                    Programme Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name"
                       value="{{ old('name', $programme?->name) }}"
                       placeholder="e.g. Diploma in Information Technology"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Scheme Selection --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="scheme_id">
                    Scheme <span class="text-red-500">*</span>
                </label>
                <select id="scheme_id" name="scheme_id" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('scheme_id') border-red-400 @enderror"
                        {{ $programme ? 'disabled' : '' }}>
                    <option value="">-- Select Curriculum Scheme --</option>
                    @foreach($schemes as $schemeOption)
                        <option value="{{ $schemeOption->id }}" {{ old('scheme_id', $programme?->scheme_id) == $schemeOption->id ? 'selected' : '' }}>
                            {{ $schemeOption->name }} (Implemented: {{ $schemeOption->implemented_year }})
                        </option>
                    @endforeach
                </select>
                @if($programme)
                    <input type="hidden" name="scheme_id" value="{{ $programme->scheme_id }}">
                    <p class="mt-1 text-[10px] text-gray-500 italic">The scheme cannot be changed after a programme is created.</p>
                @endif
                @error('scheme_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Code + Academic Year --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="code">
                        Programme Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="code" name="code"
                           value="{{ old('code', $programme?->code) }}"
                           placeholder="e.g. IF, CM, CE"
                           maxlength="10"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('code') border-red-400 @enderror">
                    @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="academic_year">
                        Academic Year <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="academic_year" name="academic_year"
                           value="{{ old('academic_year', $programme?->academic_year ?? '2021-22') }}"
                           placeholder="e.g. 2021-22"
                           maxlength="10"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('academic_year') border-red-400 @enderror">
                    @error('academic_year')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Department mapping (CDC definies which dept this belongs to) --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="department_id">
                    Department Owner <span class="text-red-500">*</span>
                </label>
                <select id="department_id" name="department_id"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('department_id') border-red-400 @enderror">
                    <option value="">-- Assign to Department --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id', $programme?->department_id) == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <p class="mt-1 text-[10px] text-gray-500 italic">This connects the scheme to the HOD of the selected department.</p>
            </div>

            {{-- Status --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="status">
                    Status <span class="text-red-500">*</span>
                </label>
                <select id="status" name="status"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($statuses as $val => $label)
                        <option value="{{ $val }}" {{ old('status', $programme?->status ?? 'draft') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Levels Info --}}
            <div class="mb-8 border-t border-gray-100 pt-6">
                <div class="flex items-start gap-4 p-4 bg-indigo-50/50 border border-indigo-100 rounded-xl text-indigo-700">
                    <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <h4 class="text-sm font-bold">Curriculum Levels Automatically Applied</h4>
                        <p class="text-xs text-indigo-600/80 mt-1 leading-relaxed">
                            Based on the <strong>scheme</strong> you select above, the system will automatically inject that scheme's mandated course levels (e.g. Basic Sciences, Program Core, Audit, etc.) into this programme's structure. You do not need to build the hierarchy manually.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-6 border-t border-gray-100 mt-6">
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                    {{ $programme ? 'Update Programme' : 'Create Programme' }}
                </button>
                <a href="{{ route('cdc.programmes.index') }}"
                   class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
