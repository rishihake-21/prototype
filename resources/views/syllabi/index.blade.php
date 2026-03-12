<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                My Syllabi
            </h2>
            <a href="{{ route('syllabi.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                + Create New
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" class="flex space-x-4">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="rounded-md border-gray-300">
                    <select name="status" class="rounded-md border-gray-300">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md">Filter</button>
                </form>
            </div>

            <!-- Syllabi List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($syllabi->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($syllabi as $syllabus)
                                    <tr>
                                        <td class="px-6 py-4">{{ $syllabus->title }}</td>
                                        <td class="px-6 py-4">{{ $syllabus->course_code }}</td>
                                        <td class="px-6 py-4">
                                            {{ $syllabus->departments->pluck('name')->join(', ') ?: 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs rounded-full
                                                @if($syllabus->isDraft()) bg-gray-100 text-gray-800
                                                @elseif($syllabus->isSubmitted()) bg-blue-100 text-blue-800
                                                @elseif($syllabus->isUnderReview()) bg-yellow-100 text-yellow-800
                                                @elseif($syllabus->isApproved()) bg-green-100 text-green-800
                                                @elseif($syllabus->isRejected()) bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $syllabus->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('syllabi.show', $syllabus) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            @if($syllabus->canBeEdited())
                                                <a href="{{ route('syllabi.edit', $syllabus) }}" class="ml-2 text-indigo-600 hover:text-indigo-900">Edit</a>
                                            @endif
                                            @if($syllabus->isDraft())
                                                <form action="{{ route('syllabi.submit', $syllabus) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="ml-2 text-blue-600 hover:text-blue-900" onclick="return confirm('Submit this syllabus for review?')">Submit</button>
                                                </form>
                                            @endif
                                            @if($syllabus->isApproved())
                                                <a href="{{ route('syllabi.download-pdf', $syllabus) }}" class="ml-2 text-green-600 hover:text-green-900">PDF</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $syllabi->links() }}
                        </div>
                    @else
                        <p class="text-gray-500">No syllabi found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
