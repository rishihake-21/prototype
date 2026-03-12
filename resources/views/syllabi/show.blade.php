@extends('layouts.app')

@section('title', 'Syllabus Details')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $syllabus->title }}</h1>
                <p class="text-lg text-gray-600">{{ $syllabus->course_code }}</p>
            </div>
            <div class="flex space-x-3">
                @if($canEdit)
                    <a href="{{ route('syllabi.edit', $syllabus) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        Edit Syllabus
                    </a>
                @endif

                @if(auth()->user()->isApprover() && $syllabus->canBeApprovedOrRejected())
                    <button onclick="showReviewModal()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        Review Syllabus
                    </button>
                @endif

                <a href="{{ route('syllabi.download-pdf', $syllabus) }}" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    Download PDF
                </a>
                <a href="{{ route('syllabi.download-docx', $syllabus) }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Download DOCX
                </a>
            </div>
        </div>

        <!-- Status Badge -->
        @unless(auth()->user()->isObserver())
        <div class="mb-6">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                @if($syllabus->isDraft()) bg-gray-100 text-gray-800
                @elseif($syllabus->isSubmitted()) bg-yellow-100 text-yellow-800
                @elseif($syllabus->isUnderReview()) bg-blue-100 text-blue-800
                @elseif($syllabus->isApproved()) bg-green-100 text-green-800
                @elseif($syllabus->isRejected()) bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800 @endif">
                {{ ucfirst(str_replace('_', ' ', $syllabus->status)) }}
            </span>
        </div>
        @endunless

        <!-- Syllabus Details -->
        @unless(auth()->user()->isObserver())
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Course Code</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $syllabus->course_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Program</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $syllabus->program_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Academic Year</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $syllabus->academic_year }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Credits</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $syllabus->teaching_scheme['credits'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Departments</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @foreach($syllabus->departments as $department)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                    {{ $department->name }}
                                </span>
                            @endforeach
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Instructor</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $syllabus->instructor_name }}<br>
                            <a href="mailto:{{ $syllabus->instructor_email }}" class="text-indigo-600 hover:text-indigo-500">
                                {{ $syllabus->instructor_email }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created By</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $syllabus->creator->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $syllabus->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        @endunless

        <!-- Syllabus Preview -->
        <div class="mt-8" x-data='syllabusForm(@json($syllabus->toArray()))' x-init="init()">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Syllabus Content Preview</h3>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200">
                <div class="p-0">
                    @include('syllabi.partials.live-preview')
                </div>
            </div>
        </div>

        <!-- Review History -->
        @if($syllabus->reviews->count() > 0)
        @unless(auth()->user()->isObserver())
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Review History</h3>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @foreach($syllabus->reviews as $review)
                    <li class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ substr($review->reviewer->name, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $review->reviewer->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $review->review_date->format('M d, Y H:i') }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($review->status === 'approved') bg-green-100 text-green-800
                                    @elseif($review->status === 'rejected') bg-red-100 text-red-800
                                    @elseif($review->status === 'changes_requested') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $review->status)) }}
                                </span>
                            </div>
                        </div>
                        @if($review->comments)
                        <div class="mt-2 text-sm text-gray-600">
                            <p>{{ $review->comments }}</p>
                        </div>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endunless
        @endif
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" id="my-modal">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Review Syllabus</h3>
            <form action="{{ route('syllabi.approve', $syllabus) }}" method="POST" id="approveForm">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Action</label>
                    <select name="action" id="reviewAction" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="toggleComments()">
                        <option value="approve">Approve</option>
                        <option value="request_changes">Request Changes</option>
                    </select>
                </div>
                <div class="mb-4" id="commentsSection" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700">Comments</label>
                    <textarea name="comments" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Please provide feedback for the creator..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideReviewModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showReviewModal() {
    document.getElementById('reviewModal').classList.remove('hidden');
}

function hideReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
}

function toggleComments() {
    const action = document.getElementById('reviewAction').value;
    const commentsSection = document.getElementById('commentsSection');
    if (action === 'request_changes') {
        commentsSection.style.display = 'block';
    } else {
        commentsSection.style.display = 'none';
    }
}
</script>
@endsection