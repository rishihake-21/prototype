@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Programme Structure</h1>
            <p class="mt-1 text-sm text-gray-500">Manage diploma programme curricula and course definitions</p>
        </div>
        <a href="{{ route('cdc.programmes.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Programme
        </a>
    </div>

    {{-- Programmes table --}}
    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
        @if($programmes->isEmpty())
            <div class="py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="mt-3 text-sm text-gray-500">No programmes yet. Create your first one above.</p>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Programme</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Code</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Academic Year</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Created By</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($programmes as $prog)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $prog->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $prog->code }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $prog->academic_year }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badge = match($prog->status) {
                                    'active'   => 'bg-green-100 text-green-800',
                                    'archived' => 'bg-gray-100 text-gray-600',
                                    default    => 'bg-amber-100 text-amber-800',
                                };
                            @endphp
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                {{ ucfirst($prog->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $prog->creator?->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('cdc.programmes.show', $prog) }}"
                                   class="rounded border border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
                                    Overview
                                </a>
                                <a href="{{ route('cdc.programmes.structure', $prog) }}"
                                   class="rounded border border-indigo-300 px-2.5 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-50 transition">
                                    Structure
                                </a>
                                <a href="{{ route('cdc.courses.index', $prog) }}"
                                   class="rounded border border-blue-300 px-2.5 py-1 text-xs font-medium text-blue-700 hover:bg-blue-50 transition">
                                    Courses
                                </a>
                                <a href="{{ route('cdc.programmes.sample-path', $prog) }}"
                                   class="rounded border border-teal-300 px-2.5 py-1 text-xs font-medium text-teal-700 hover:bg-teal-50 transition">
                                    Sample Path
                                </a>
                                <a href="{{ route('cdc.programmes.edit', $prog) }}"
                                   class="rounded border border-yellow-300 px-2.5 py-1 text-xs font-medium text-yellow-700 hover:bg-yellow-50 transition">
                                    Edit
                                </a>
                                @if($prog->isDraft())
                                <form method="POST" action="{{ route('cdc.programmes.destroy', $prog) }}"
                                      onsubmit="return confirm('Delete this programme and all its data?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="rounded border border-red-300 px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition">
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $programmes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
