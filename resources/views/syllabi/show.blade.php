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

                @if(auth()->user()->isApprover() && $syllabus->isSubmitted())
                    <form action="{{ route('syllabi.start-review', $syllabus) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Start Review
                        </button>
                    </form>
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
<div id="reviewModal" class="fixed inset-0 z-50 hidden bg-gray-900/60 px-4 py-6 sm:px-6">
    <div class="mx-auto flex h-full max-w-7xl items-start justify-center">
        <div class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl" x-data='syllabusForm(@json($syllabus->toArray()))' x-init="init()">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Review Syllabus</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $syllabus->course_code }} - {{ $syllabus->title }}</p>
                </div>
                <button type="button" onclick="hideReviewModal()" class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                    Close
                </button>
            </div>

            <div class="grid max-h-[calc(100vh-6rem)] grid-cols-1 lg:grid-cols-[380px,minmax(0,1fr)]">
                <div class="overflow-y-auto border-b border-gray-200 p-6 lg:border-b-0 lg:border-r">
                    <div class="mb-5 rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Reviewing</div>
                        <div class="mt-2 text-sm font-medium text-gray-900">{{ $syllabus->course_code }}</div>
                        <div class="text-sm text-gray-700">{{ $syllabus->title }}</div>
                        <div class="mt-3 text-xs text-gray-600">
                            Created by {{ $syllabus->creator->name }} for {{ $syllabus->academic_year }}
                        </div>
                    </div>

                    <form action="{{ route('syllabi.approve', $syllabus) }}" method="POST" id="approveForm">
                        @csrf
                        @method('PATCH')
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Action</label>
                            <select name="action" id="reviewAction" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="toggleComments()">
                                <option value="approve">Approve</option>
                                <option value="request_changes">Request Changes</option>
                                <option value="reject">Reject</option>
                            </select>
                        </div>
                        <div class="mb-4" id="commentsSection" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700" id="commentsLabel">Comments</label>
                            <textarea name="comments" rows="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Please provide feedback for the creator..."></textarea>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button type="button" onclick="hideReviewModal()" class="rounded-md bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                                Cancel
                            </button>
                            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                                Submit Review
                            </button>
                        </div>
                    </form>
                </div>

                <div class="overflow-y-auto bg-gray-100 p-4 sm:p-6">
                    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-600">Syllabus Preview While Reviewing</h4>
                    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
                        @include('syllabi.partials.live-preview')
                    </div>
                </div>
            </div>
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
    const commentsLabel = document.getElementById('commentsLabel');
    if (action === 'request_changes' || action === 'reject') {
        commentsSection.style.display = 'block';
        commentsLabel.textContent = action === 'reject' ? 'Rejection Reason' : 'Comments';
    } else {
        commentsSection.style.display = 'none';
    }
}
</script>
@endsection
