@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    {{-- Breadcrumb --}}
    <div class="mb-5 text-sm text-gray-500 flex items-center gap-1">
        <a href="{{ route('cdc.programmes.index') }}" class="hover:text-indigo-600">Programmes</a>
        <span>/</span>
        <a href="{{ route('cdc.programmes.show', $programme) }}" class="hover:text-indigo-600">{{ $programme->name }}</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">Sample Path</span>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Sample Path (Term-wise Distribution)</h1>
            <p class="text-sm text-gray-500 mt-1">Recommended course sequence per term - {{ $programme->name }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Entry Level switcher --}}
    <div class="flex items-center gap-4 mb-6">
        <span class="text-sm font-medium text-gray-700">Entry Level:</span>
        @foreach($entryLevels as $el)
            <a href="{{ route('cdc.programmes.sample-path', ['programme' => $programme, 'entry_level' => $el]) }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium transition
                {{ $entryLevel === $el
                    ? 'bg-teal-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                {{ $el }}
            </a>
        @endforeach
    </div>

    <form method="POST" action="{{ route('cdc.programmes.sample-path.update', $programme) }}">
        @csrf @method('PUT')
        <input type="hidden" name="entry_level" value="{{ $entryLevel }}">

        <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-x-auto">
            <table class="min-w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-teal-700 text-white text-xs uppercase tracking-wide">
                        <th class="px-3 py-3 text-left border border-teal-600 min-w-[180px]">Course</th>
                        <th class="px-3 py-3 text-center border border-teal-600">Level</th>
                        @foreach($terms as $term)
                        <th class="px-3 py-3 text-center border border-teal-600 min-w-[110px]">
                            {{ \App\Models\SamplePath::termLabel($term) }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($courses as $i => $course)
                    <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-teal-50 transition">
                        <td class="px-3 py-2 border border-gray-200">
                            <div class="font-mono text-gray-600 text-[10px]">{{ $course->course_code }}</div>
                            <div class="font-medium text-gray-800 text-xs leading-snug">{{ $course->course_title }}</div>
                            <div class="flex items-center gap-1 mt-0.5">
                                <span class="text-[9px] px-1 py-0.5 rounded leading-none {{ $course->course_type === 'compulsory' ? 'bg-blue-50 text-blue-600' : ($course->course_type === 'audit' ? 'bg-gray-100 text-gray-600' : 'bg-purple-50 text-purple-600') }}">
                                    {{ ucfirst($course->course_type) }}
                                </span>
                                @if($course->elective_group)
                                    <span class="text-[9px] px-1 py-0.5 rounded leading-none bg-purple-50 text-purple-600">
                                        {{ $course->elective_group }}
                                    </span>
                                @endif
                                <span class="text-[10px] text-gray-400 ml-auto">{{ $course->course_abbr }} - {{ (float)$course->credits }}cr</span>
                            </div>
                        </td>
                        <td class="px-3 py-2 border border-gray-200 text-center text-gray-500">
                            {{ $course->level?->level_code }}
                        </td>
                        @foreach($terms as $term)
                        @php
                            $isChecked = isset($assigned[$term]) && isset($assigned[$term][$course->id]);
                        @endphp
                        <td class="px-3 py-2 border border-gray-200 text-center">
                            <label class="flex items-center justify-center cursor-pointer group">
                                <input type="checkbox"
                                       name="terms[{{ $term }}][]"
                                       value="{{ $course->id }}"
                                       {{ $isChecked ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-teal-600 border-gray-300 focus:ring-teal-500
                                              group-hover:border-teal-400 transition">
                            </label>
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 2 + count($terms) }}" class="px-4 py-10 text-center text-sm text-gray-500">
                            No courses found for this programme. 
                            <a href="{{ route('cdc.courses.create', $programme) }}" class="text-teal-600 hover:underline">Add courses first</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($courses->isNotEmpty())
        <div class="flex gap-3 mt-5">
            <button type="submit"
                    class="rounded-lg bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-teal-700 transition">
                Save Sample Path
            </button>
            <a href="{{ route('cdc.programmes.show', $programme) }}"
               class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
        </div>
        <p class="mt-3 text-xs text-gray-400">
            Check the box to assign a course to a particular term. A course can appear in only one term per entry level.
            Terms: Odd = 1st semester of academic year, Even = 2nd semester.
        </p>
        @endif
    </form>
</div>
@endsection
