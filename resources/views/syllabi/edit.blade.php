<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Syllabus
        </h2>
    </x-slot>

    <div class="py-6" x-data='syllabusForm(@json($syllabusData), null, @json($programmesMetadata))' x-init="init()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            
            @if($syllabus->status === \App\Models\Syllabus::STATUS_CHANGES_REQUESTED || $syllabus->status === \App\Models\Syllabus::STATUS_REJECTED)
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 shadow-sm rounded-r-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                Feedback from Reviewer
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p class="italic">"{{ $syllabus->rejection_reason ?? 'No comments provided.' }}"</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Progress Steps -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <template x-for="(step, index) in steps" :key="index">
                        <div class="flex items-center">
                            <div class="flex flex-col items-center">
                                <div :class="{
                                    'bg-indigo-600 text-white': currentStep >= index,
                                    'bg-gray-300 text-gray-600': currentStep < index,
                                    'ring-4 ring-indigo-200': currentStep === index
                                }" class="w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all duration-300">
                                    <span x-text="index + 1"></span>
                                </div>
                                <span class="text-xs mt-2 text-gray-600" x-text="step"></span>
                            </div>
                            <div x-show="index < steps.length - 1" class="w-16 h-1 mx-2"
                                :class="currentStep > index ? 'bg-indigo-600' : 'bg-gray-300'">
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex gap-6">
                <!-- Left: Form -->
                <div class="flex-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <form @submit.prevent="saveStep()" @keydown.enter.prevent id="syllabusForm" action="{{ route('syllabi.update', $syllabus) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <!-- Step 1: Basic Information -->
                                <div x-show="currentStep === 0" x-transition>
                                    @include('syllabi.steps.step1-basic')
                                </div>

                                <!-- Step 2: Teaching & Examination Scheme -->
                                <div x-show="currentStep === 1" x-transition style="display: none;">
                                    @include('syllabi.steps.step2-scheme')
                                </div>

                                <!-- Step 3: Course Content -->
                                <div x-show="currentStep === 2" x-transition style="display: none;">
                                    @include('syllabi.steps.step3-content')
                                </div>

                                <!-- Step 4: Practicals/Training -->
                                <div x-show="currentStep === 3" x-transition style="display: none;">
                                    @include('syllabi.steps.step4-practicals')
                                </div>

                                <!-- Step 5: Learning Resources -->
                                <div x-show="currentStep === 4" x-transition style="display: none;">
                                    @include('syllabi.steps.step5-resources')
                                </div>

                                <!-- Step 6: CO-PO Mapping -->
                                <div x-show="currentStep === 5" x-transition style="display: none;">
                                    @include('syllabi.steps.step6-mapping')
                                </div>

                                <!-- Step 7: Question Paper Profile -->
                                <div x-show="currentStep === 6" x-transition style="display: none;">
                                    @include('syllabi.steps.step7-paper-profile')
                                </div>

                                <!-- Step 8: Certification -->
                                <div x-show="currentStep === 7" x-transition style="display: none;">
                                    @include('syllabi.steps.step8-certification')
                                </div>

                                <!-- Navigation Buttons -->
                                <div class="mt-8 flex justify-between">
                                    <button type="button" @click="prevStep()" 
                                        x-show="currentStep > 0"
                                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                        Previous
                                    </button>
                                    
                                    <div class="flex gap-3">
                                        <button type="button" @click="saveAsDraft()" 
                                            class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                            Save as Draft
                                        </button>
                                        
                                        <button type="button" @click="updatePreview()"
                                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                            Update Preview
                                        </button>
                                        
                                        <button type="submit" 
                                            x-text="currentStep === steps.length - 1 ? 'Save Changes' : 'Next'"
                                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right: Live Preview -->
                <div class="w-2/5">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        <div class="p-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="font-semibold text-gray-700">Live Preview</h3>
                            <p class="text-xs text-gray-500 mt-1">Updates automatically as you type</p>
                        </div>
                        <div class="p-0">
                            @include('syllabi.partials.live-preview')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Syllabus form functions are included in the main app bundle
        // No additional script needed
    </script>
    @endpush
</x-app-layout>



