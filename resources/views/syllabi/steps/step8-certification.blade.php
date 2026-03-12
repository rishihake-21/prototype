<div class="space-y-6">
    <h3 class="text-lg font-medium text-gray-900">Step 8: Certification</h3>
    
    <div class="bg-green-50 p-4 rounded-md">
        <p class="text-sm text-green-800">
            Review the certification details below. Upon submission, this certification page will be appended to the final syllabus document.
        </p>
    </div>

    <!-- Certificate Preview -->
    <div class="bg-white border-2 border-gray-300 rounded-lg p-8">
        <div class="text-center mb-8">
            <h2 class="text-xl font-bold text-gray-900 uppercase">Certificate</h2>
            <p class="text-sm text-gray-600 mt-2">Syllabus Approval Document</p>
        </div>

        <!-- Course Details -->
        <div class="mb-8 p-4 bg-gray-50 rounded-md">
            <table class="w-full text-sm">
                <tr>
                    <td class="py-2 font-semibold text-gray-700 w-1/3">Course Title:</td>
                    <td class="py-2 text-gray-900" x-text="form.title || '[Course Title]'"></td>
                </tr>
                <tr>
                    <td class="py-2 font-semibold text-gray-700">Course Code:</td>
                    <td class="py-2 text-gray-900" x-text="form.course_code || '[Course Code]'"></td>
                </tr>
                <tr>
                    <td class="py-2 font-semibold text-gray-700">Programme:</td>
                    <td class="py-2 text-gray-900" x-text="form.program_name || '[Programme Name]'"></td>
                </tr>
                <tr>
                    <td class="py-2 font-semibold text-gray-700">Academic Year:</td>
                    <td class="py-2 text-gray-900" x-text="form.academic_year || '{{ \App\Models\Syllabus::getAcademicYear() }}'"></td>
                </tr>
                <tr>
                    <td class="py-2 font-semibold text-gray-700">Credits:</td>
                    <td class="py-2 text-gray-900" x-text="form.teaching_scheme.credits || 0"></td>
                </tr>
            </table>
        </div>

        <!-- Certification Statement -->
        <div class="mb-8 text-sm text-gray-700 text-justify">
            <p>
                This is to certify that the syllabus for the above-mentioned course has been reviewed and approved 
                by the Curriculum Development Committee (CDC) for implementation in the academic year 
                <span class="font-semibold" x-text="form.academic_year || '{{ \App\Models\Syllabus::getAcademicYear() }}'"></span>.
            </p>
            <p class="mt-3">
                The syllabus complies with the guidelines set forth by the institution and aligns with the 
                Programme Outcomes (POs) and Programme Specific Outcomes (PSOs) of the respective programme.
            </p>
        </div>

        <!-- Signature Section -->
        <div class="mt-12">
            <h4 class="text-sm font-semibold text-gray-700 mb-6">Approved By:</h4>
            
            <div class="grid grid-cols-3 gap-8">
                <!-- HOD Signature -->
                <div class="text-center">
                    <div class="border-b-2 border-gray-400 mb-2 h-16"></div>
                    <input type="text" x-model="form.certification_signatures.hod" @input="updatePreview()" @keydown.enter.prevent
                        class="w-full text-center text-sm border-0 border-b border-gray-300 focus:ring-0 focus:border-indigo-500 bg-transparent"
                        placeholder="Enter Name">
                    <p class="text-xs text-gray-600 font-semibold mt-1">Head of Department</p>
                    <p class="text-xs text-gray-500">Date: ____________</p>
                </div>

                <!-- Principal Signature -->
                <div class="text-center">
                    <div class="border-b-2 border-gray-400 mb-2 h-16"></div>
                    <input type="text" x-model="form.certification_signatures.principal" @input="updatePreview()" @keydown.enter.prevent
                        class="w-full text-center text-sm border-0 border-b border-gray-300 focus:ring-0 focus:border-indigo-500 bg-transparent"
                        placeholder="Enter Name">
                    <p class="text-xs text-gray-600 font-semibold mt-1">Principal</p>
                    <p class="text-xs text-gray-500">Date: ____________</p>
                </div>

                <!-- CDC Incharge Signature -->
                <div class="text-center">
                    <div class="border-b-2 border-gray-400 mb-2 h-16"></div>
                    <input type="text" x-model="form.certification_signatures.cdc_incharge" @input="updatePreview()" @keydown.enter.prevent
                        class="w-full text-center text-sm border-0 border-b border-gray-300 focus:ring-0 focus:border-indigo-500 bg-transparent"
                        placeholder="Enter Name">
                    <p class="text-xs text-gray-600 font-semibold mt-1">CDC Incharge</p>
                    <p class="text-xs text-gray-500">Date: ____________</p>
                </div>
            </div>
        </div>

        <!-- Official Seal Placeholder -->
        <div class="mt-8 text-center">
            <div class="inline-block border-2 border-dashed border-gray-300 rounded-full w-24 h-24 flex items-center justify-center">
                <span class="text-xs text-gray-400">Official Seal</span>
            </div>
        </div>
    </div>

    <!-- Submission Info -->
    <div class="bg-yellow-50 p-4 rounded-md">
        <h4 class="font-medium text-yellow-800 mb-2">Before Submission:</h4>
        <ul class="text-sm text-yellow-700 list-disc list-inside space-y-1">
            <li>Ensure all course content is complete and accurate</li>
            <li>Verify CO-PO mapping matrix is filled correctly</li>
            <li>Check that total marks align with examination scheme</li>
            <li>Review the live preview for formatting issues</li>
        </ul>
    </div>

    <!-- Final Summary -->
    <div class="bg-gray-50 p-4 rounded-md">
        <h4 class="font-medium text-gray-700 mb-3">Syllabus Summary:</h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-600">Course Outcomes:</span>
                <span class="font-medium ml-2" x-text="form.course_outcomes.length"></span>
            </div>
            <div>
                <span class="text-gray-600">Units:</span>
                <span class="font-medium ml-2" x-text="form.units.length"></span>
            </div>
            <div>
                <span class="text-gray-600">Practical Tasks:</span>
                <span class="font-medium ml-2" x-text="form.practical_tasks.length"></span>
            </div>
            <div>
                <span class="text-gray-600">Books:</span>
                <span class="font-medium ml-2" x-text="form.books.length"></span>
            </div>
            <div>
                <span class="text-gray-600">Total Credits:</span>
                <span class="font-medium ml-2" x-text="form.teaching_scheme.credits"></span>
            </div>
            <div>
                <span class="text-gray-600">Total Marks:</span>
                <span class="font-medium ml-2" x-text="calculateTotalMarks()"></span>
            </div>
        </div>
    </div>
</div>
