@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6">

    <div class="mb-6">
        <a href="{{ route('cdc.programmes.show', $programme) }}"
           class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">
            ← Back to Programme
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Courses for Award of Class</h1>
        <p class="text-sm text-gray-500">Select the courses that will be used to compute the final class for <strong>{{ $programme->name }}</strong>.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 rounded-md bg-green-50 text-green-700 text-sm border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('cdc.programmes.award-class.update', $programme) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <span class="sr-only">Select</span>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Code</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Course Title</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Level</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Type</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @php $currentLevel = null; @endphp
                        @foreach($courses as $course)
                            @if($currentLevel !== $course->level_id)
                                @php $currentLevel = $course->level_id; @endphp
                                <tr class="bg-gray-50/50">
                                    <td colspan="5" class="px-4 py-2 font-semibold text-indigo-700 bg-indigo-50/30">
                                        {{ $course->level->level_name }} ({{ $course->level->level_code }})
                                    </td>
                                </tr>
                            @endif
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" 
                                           name="course_ids[]" 
                                           value="{{ $course->id }}"
                                           {{ in_array($course->id, $assigned) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $course->course_code }}</td>
                                <td class="px-4 py-3 text-gray-900 font-medium">{{ $course->course_title }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $course->level->level_code }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $course->course_type === 'compulsory' ? 'bg-blue-100 text-blue-800' : ($course->course_type === 'audit' ? 'bg-gray-100 text-gray-800' : 'bg-purple-100 text-purple-800') }}">
                                        {{ ucfirst($course->course_type) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach

                        @if($courses->isEmpty())
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500 italic">
                                    No courses found for this programme yet. Please add courses first.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-500">Only selected courses will appear in the Award of Class section of the curriculum.</p>
                <div class="flex gap-3">
                    <a href="{{ route('cdc.programmes.show', $programme) }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition">
                        Save Selection
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
