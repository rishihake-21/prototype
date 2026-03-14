@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6">

    <div class="mb-6">
        <a href="{{ route('cdc.programmes.index') }}"
           class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">
            <- Back to Programmes
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $programme->name }}</h1>
        <p class="text-sm text-gray-500">{{ $programme->code }} - {{ $programme->academic_year }}</p>
    </div>

    {{-- Quick navigation cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('cdc.programmes.structure', $programme) }}"
           class="group flex items-start gap-3 rounded-xl bg-indigo-50 border border-indigo-200 p-4 hover:bg-indigo-100 transition">
            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-indigo-900 text-sm">Scheme at a Glance</p>
                <p class="text-xs text-indigo-600 mt-0.5">Level-wise credit & hour summary</p>
            </div>
        </a>

        <a href="{{ route('cdc.courses.index', $programme) }}"
           class="group flex items-start gap-3 rounded-xl bg-blue-50 border border-blue-200 p-4 hover:bg-blue-100 transition">
            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-blue-900 text-sm">Course Definitions</p>
                <p class="text-xs text-blue-600 mt-0.5">Level-wise course entry & editing</p>
            </div>
        </a>

        <a href="{{ route('cdc.programmes.sample-path', $programme) }}"
           class="group flex items-start gap-3 rounded-xl bg-teal-50 border border-teal-200 p-4 hover:bg-teal-100 transition">
            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-teal-600 flex items-center justify-center text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-teal-900 text-sm">Sample Path</p>
                <p class="text-xs text-teal-600 mt-0.5">Term-wise course distribution</p>
            </div>
        </a>

        <a href="{{ route('cdc.programmes.award-class', $programme) }}"
           class="group flex items-start gap-3 rounded-xl bg-amber-50 border border-amber-200 p-4 hover:bg-amber-100 transition">
            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-amber-600 flex items-center justify-center text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-amber-900 text-sm">Award of Class</p>
                <p class="text-xs text-amber-600 mt-0.5">Courses for classification</p>
            </div>
        </a>
    </div>

    {{-- Level summary --}}
    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900">Programme Levels</h2>
            <a href="{{ route('cdc.programmes.edit', $programme) }}"
               class="text-xs text-indigo-600 hover:underline">Edit Programme Details</a>
        </div>
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Level</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Level Name</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide">Courses Offered</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide">To Complete</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide">Total Credits</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide">Total Marks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($programme->levels as $level)
                @php $s = $level->structure; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $level->level_code }}</td>
                    <td class="px-4 py-3 text-gray-900">{{ $level->level_name }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ $s?->total_courses_offered ?? '-' }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ $s?->courses_to_complete ?? '-' }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ $s?->total_credits ?? '-' }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ $s?->total_marks ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
