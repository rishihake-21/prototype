<div class="h-[calc(100vh-200px)] overflow-y-auto bg-gray-100 p-4" id="livePreviewContainer">
    <!-- Preview Content -->
    <div class="bg-white shadow-lg mx-auto" style="width: 210mm; min-height: 297mm; padding: 15mm; font-family: 'Times New Roman', serif; font-size: 12pt;">
        
        <!-- Header Section -->
        <div class="text-center border-b-2 border-black pb-4 mb-4">
            <h1 class="text-xl font-bold uppercase"
                x-text="typeof form !== 'undefined' ? (form.program_name + ' - Syllabus') : ''"></h1>
            <p class="text-sm mt-1">
                Academic Year:
                <span
                    x-text="typeof form !== 'undefined'
                        ? (form.academic_year || '{{ \App\Models\Syllabus::getAcademicYear() }}')
                        : '{{ \App\Models\Syllabus::getAcademicYear() }}'">
                </span>
            </p>
            <div x-show="typeof form !== 'undefined' && form.is_part_of_group && detectedLevel === 3" class="mt-2 text-lg font-semibold italic">
                Any ONE of the following
            </div>
        </div>

        <!-- Course Identity -->
        <div class="mb-4">
            <table class="w-full border-collapse border border-black">
                <tr>
                    <td class="border border-black px-2 py-1 font-semibold w-1/4">Course Title</td>
                    <td class="border border-black px-2 py-1" x-text="form.title" colspan="3"></td>
                </tr>
                <tr>
                    <td class="border border-black px-2 py-1 font-semibold">Course Code</td>
                    <td class="border border-black px-2 py-1" x-text="form.course_code"></td>
                    <td class="border border-black px-2 py-1 font-semibold">Credits</td>
                    <td class="border border-black px-2 py-1" x-text="form.teaching_scheme.credits"></td>
                </tr>
                <tr>
                    <td class="border border-black px-2 py-1 font-semibold">IKS Hours</td>
                    <td class="border border-black px-2 py-1" x-text="form.iks_hours || 0"></td>
                    <td class="border border-black px-2 py-1 font-semibold">Level</td>
                    <td class="border border-black px-2 py-1" x-text="levelInfo.text.replace('Level ', '').replace(' detected', '')"></td>
                </tr>
            </table>
        </div>

        <!-- Teaching & Examination Scheme -->
        <div class="mb-4">
            <h3 class="font-bold text-sm mb-2">1. TEACHING & EXAMINATION SCHEME</h3>
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-1" colspan="6">Teaching Scheme</th>
                        <th class="border border-black px-2 py-1" colspan="5">Examination Scheme</th>
                    </tr>
                    <tr class="bg-gray-100">
                         <th class="border border-black px-1 py-1">CL</th>
                        <th class="border border-black px-1 py-1">TU</th>
                        <th class="border border-black px-1 py-1">LL</th>
                        <th class="border border-black px-1 py-1">TL</th>
                        <th class="border border-black px-1 py-1">Credits</th>
                        <th class="border border-black px-1 py-1">SLH</th>
                        <th class="border border-black px-1 py-1">FA-TH</th>
                        <th class="border border-black px-1 py-1">SA-TH</th>
                        <th class="border border-black px-1 py-1">SA-PR</th>
                        <th class="border border-black px-1 py-1">TW</th>
                        <th class="border border-black px-1 py-1">Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-black px-1 py-1 text-center"
                            x-text="(form.teaching_scheme && form.teaching_scheme.th_hours) || 0"></td>
                        <td class="border border-black px-1 py-1 text-center"
                            x-text="(form.teaching_scheme && form.teaching_scheme.tu_hours) || 0"></td>
                        <td class="border border-black px-1 py-1 text-center"
                            x-text="(form.teaching_scheme && form.teaching_scheme.pr_hours) || 0"></td>
                        <td class="border border-black px-1 py-1 text-center"
                            x-text="(form.teaching_scheme && form.teaching_scheme.total_hours) || 0"></td>
                        <td class="border border-black px-1 py-1 text-center"
                            x-text="(form.teaching_scheme && form.teaching_scheme.credits) || 0"></td>
                        <td class="border border-black px-1 py-1 text-center"
                            x-text="(form.teaching_scheme && form.teaching_scheme.slh_hours) || 0"></td>
                        <td class="border border-black px-1 py-1 text-center">
                            <span
                                x-text="((form.examination_scheme && form.examination_scheme.fa_th_max) || 0) + '#'"
                                x-show="form.is_online_exam"></span>
                            <span
                                x-text="(form.examination_scheme && form.examination_scheme.fa_th_max) || 0"
                                x-show="!form.is_online_exam"></span>
                        </td>
                        <td class="border border-black px-1 py-1 text-center"
                            x-text="((form.examination_scheme && form.examination_scheme.sa_th_max) || 0)
                                    + '/' +
                                    ((form.examination_scheme && form.examination_scheme.sa_th_min) || 0)"></td>
                        <td class="border border-black px-1 py-1 text-center">
                            <span x-show="form.examination_scheme && form.examination_scheme.sa_pr_max">
                                <span
                                    x-text="form.examination_scheme.sa_pr_max + '/' + form.examination_scheme.sa_pr_min"></span>
                                <span
                                    x-show="form.examination_scheme && form.examination_scheme.is_internal_practical">@</span>
                            </span>
                            <span x-show="!(form.examination_scheme && form.examination_scheme.sa_pr_max)">-</span>
                        </td>
                        <td class="border border-black px-1 py-1 text-center"
                            x-text="(form.examination_scheme && form.examination_scheme.tw_marks)
                                    ? form.examination_scheme.tw_marks
                                    : '-'"></td>
                        <td class="border border-black px-1 py-1 text-center"
                            x-text="((form.examination_scheme && form.examination_scheme.paper_duration) || 0) + ' Hrs'"></td>
                    </tr>
                </tbody>
            </table>
            <p class="text-xs mt-1"
               x-show="form.is_online_exam
                        || (form.examination_scheme && form.examination_scheme.is_internal_practical)">
                <span x-show="form.is_online_exam"># Online Examination</span>
                <span
                    x-show="form.is_online_exam
                            && form.examination_scheme
                            && form.examination_scheme.is_internal_practical">
                    |
                </span>
                <span
                    x-show="form.examination_scheme
                            && form.examination_scheme.is_internal_practical">
                    @ Internal Assessment
                </span>
            </p>
        </div>

        <!-- Rationale -->
        <div class="mb-4" x-show="form.rationale">
            <h3 class="font-bold text-sm mb-2">2. RATIONALE</h3>
            <p class="text-xs text-justify" x-text="form.rationale"></p>
        </div>

        <!-- Course Objectives -->
        <div class="mb-4"
             x-show="typeof form !== 'undefined'
                    && Array.isArray(form.course_objectives)
                    && form.course_objectives.length > 0">
            <h3 class="font-bold text-sm mb-2">3. COURSE OBJECTIVES</h3>
            <p class="text-xs mb-1">After studying this course, the student will be able to:</p>
            <ol class="text-xs list-decimal list-inside">
                <template
                    x-for="(obj, index) in (Array.isArray(form.course_objectives) ? form.course_objectives : [])"
                    :key="index">
                    <li x-text="obj"></li>
                </template>
            </ol>
        </div>

        <!-- Course Outcomes -->
        <div class="mb-4"
             x-show="typeof form !== 'undefined'
                    && Array.isArray(form.course_outcomes)
                    && form.course_outcomes.length > 0">
            <h3 class="font-bold text-sm mb-2">4. COURSE OUTCOMES (COs)</h3>
            <table class="w-full border-collapse border border-black text-xs">
                <tbody>
                    <template
                        x-for="(outcome, index) in (Array.isArray(form.course_outcomes) ? form.course_outcomes : [])"
                        :key="index">
                        <tr>
                            <td class="border border-black px-2 py-1 font-semibold w-16" x-text="outcome.code || ('CO' + (index + 1))"></td>
                            <td class="border border-black px-2 py-1" x-text="outcome.description"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Units / Training Schedule (Level-aware) -->
        <div class="mb-4"
             x-show="typeof detectedLevel !== 'undefined'
                    && typeof form !== 'undefined'
                    && Array.isArray(form.training_schedule)
                    && form.training_schedule.length > 0">
            <h3 class="font-bold text-sm mb-2">5. TRAINING SCHEDULE</h3>
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-1">Week</th>
                        <th class="border border-black px-2 py-1">Activity to be Performed</th>
                        <th class="border border-black px-2 py-1">Industry</th>
                        <th class="border border-black px-2 py-1">Mentor</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(week, index) in form.training_schedule" :key="index">
                        <tr>
                            <td class="border border-black px-2 py-1 text-center w-16" x-text="week.week_no"></td>
                            <td class="border border-black px-2 py-1" x-text="week.activity"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="week.marks_industry || '-'"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="week.marks_mentor || '-'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Report Format (Level 4) -->
        <div class="mb-4"
             x-show="typeof detectedLevel !== 'undefined'
                    && typeof form !== 'undefined'
                    && Array.isArray(form.report_format)
                    && form.report_format.length > 0">
            <h3 class="font-bold text-sm mb-2">6. REPORT FORMAT</h3>
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-1 w-24">Chapter</th>
                        <th class="border border-black px-2 py-1">Title</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(chapter, index) in form.report_format" :key="index">
                        <tr>
                            <td class="border border-black px-2 py-1 text-center" x-text="'Chapter ' + chapter.chapter"></td>
                            <td class="border border-black px-2 py-1" x-text="chapter.title"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="mb-4"
             x-show="typeof detectedLevel !== 'undefined'
                    && detectedLevel !== 4
                    && typeof form !== 'undefined'
                    && Array.isArray(form.units)
                    && form.units.length > 0">
            <h3 class="font-bold text-sm mb-2">5. COURSE CONTENT</h3>
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-1">Unit</th>
                        <th class="border border-black px-2 py-1">Title</th>
                        <th class="border border-black px-2 py-1">Learning Outcomes</th>
                        <th class="border border-black px-2 py-1">Hrs</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(unit, index) in form.units" :key="index">
                        <tr>
                            <td class="border border-black px-2 py-1 text-center" x-text="unit.unit_no"></td>
                            <td class="border border-black px-2 py-1" x-text="unit.title"></td>
                            <td class="border border-black px-2 py-1" x-text="unit.cognitive_outcomes"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="unit.hours"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Specification Table (Levels 2 & 5) -->
        <div class="mb-4"
             x-show="typeof detectedLevel !== 'undefined'
                    && (detectedLevel === 2 || detectedLevel === 5)
                    && typeof form !== 'undefined'
                    && Array.isArray(form.specification_table)
                    && form.specification_table.length > 0">
            <h3 class="font-bold text-sm mb-2">6. THEORY SPECIFICATION</h3>
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-1">Unit</th>
                        <th class="border border-black px-2 py-1">R</th>
                        <th class="border border-black px-2 py-1">U</th>
                        <th class="border border-black px-2 py-1">A</th>
                        <th class="border border-black px-2 py-1">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(spec, index) in form.specification_table" :key="index">
                        <tr>
                            <td class="border border-black px-2 py-1 text-center" x-text="spec.unit_no"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="spec.r || 0"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="spec.u || 0"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="spec.a || 0"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="(parseInt(spec.r || 0) + parseInt(spec.u || 0) + parseInt(spec.a || 0))"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Practical Tasks -->
        <div class="mb-4"
             x-show="typeof form !== 'undefined'
                    && Array.isArray(form.practical_tasks)
                    && form.practical_tasks.length > 0">
            <h3 class="font-bold text-sm mb-2"
                x-text="typeof detectedLevel !== 'undefined' && detectedLevel === 4
                        ? '7. PRACTICAL EXERCISES'
                        : '7. PRACTICAL TASKS'"></h3>
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-1">S.No</th>
                        <th class="border border-black px-2 py-1">Unit</th>
                        <th class="border border-black px-2 py-1">Title</th>
                        <th class="border border-black px-2 py-1">Hrs</th>
                        <th class="border border-black px-2 py-1">CO</th>
                    </tr>
                </thead>
                <tbody>
                    <template
                        x-for="(task, index) in (typeof form !== 'undefined'
                            && Array.isArray(form.practical_tasks)
                            ? form.practical_tasks
                            : [])" :key="index">
                        <tr>
                            <td class="border border-black px-2 py-1 text-center" x-text="task.s_no + (task.is_mandatory ? '*' : '')"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="task.unit_no || '-'"></td>
                            <td class="border border-black px-2 py-1" x-text="task.title"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="task.hours"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="task.co_code || '-'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p class="text-xs mt-1"
               x-show="Array.isArray(form.practical_tasks)
                        && form.practical_tasks.some(t => t.is_mandatory)">
                * Mandatory task
            </p>
        </div>

        <!-- Learning Resources -->
        <div class="mb-4"
             x-show="typeof form !== 'undefined'
                    && ((Array.isArray(form.books) && form.books.length > 0)
                        || (Array.isArray(form.software_websites) && form.software_websites.length > 0))">
            <h3 class="font-bold text-sm mb-2">8. LEARNING RESOURCES</h3>
            
            <div x-show="Array.isArray(form.books) && form.books.length > 0" class="mb-2">
                <p class="text-xs font-semibold">Books:</p>
                <ol class="text-xs list-decimal list-inside">
                    <template
                        x-for="(book, index) in (Array.isArray(form.books) ? form.books : [])"
                        :key="index">
                        <li x-text="book.author + ', ' + book.title + (book.edition ? ', ' + book.edition : '') + (book.publication ? ', ' + book.publication : '') + (book.isbn ? ', ISBN: ' + book.isbn : '')"></li>
                    </template>
                </ol>
            </div>
            
            <div x-show="Array.isArray(form.software_websites) && form.software_websites.length > 0">
                <p class="text-xs font-semibold">Software/Websites:</p>
                <ul class="text-xs list-disc list-inside">
                    <template
                        x-for="(sw, index) in (Array.isArray(form.software_websites) ? form.software_websites : [])"
                        :key="index">
                        <li x-text="sw.name + (sw.url ? ' (' + sw.url + ')' : '')"></li>
                    </template>
                </ul>
            </div>
        </div>

        <!-- CO-PO Mapping Matrix -->
        <div class="mb-4"
             x-show="typeof form !== 'undefined'
                    && Array.isArray(form.mapping_matrix)
                    && form.mapping_matrix.length > 0">
            <h3 class="font-bold text-sm mb-2">9. CO-PO MAPPING MATRIX</h3>
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-1 py-1">CO</th>
                        <template x-for="i in 7" :key="i">
                            <th class="border border-black px-1 py-1" x-text="'PO' + i"></th>
                        </template>
                        <template x-for="i in 4" :key="i">
                            <th class="border border-black px-1 py-1 bg-gray-100" x-text="'PSO' + i"></th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <template
                        x-for="(mapping, index) in (typeof form !== 'undefined'
                            && Array.isArray(form.mapping_matrix)
                            ? form.mapping_matrix
                            : [])"
                        :key="index">
                        <tr>
                            <td class="border border-black px-1 py-1 text-center font-semibold" x-text="mapping.co_code"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.po1 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.po2 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.po3 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.po4 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.po5 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.po6 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.po7 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center bg-gray-50" x-text="mapping.pso1 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center bg-gray-50" x-text="mapping.pso2 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center bg-gray-50" x-text="mapping.pso3 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center bg-gray-50" x-text="mapping.pso4 || '-'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Question Paper Profile (Levels 1, 2, 3, 5) -->
        <div class="mb-4"
             x-show="typeof detectedLevel !== 'undefined'
                    && detectedLevel !== 4
                    && typeof form !== 'undefined'
                    && Array.isArray(form.question_paper_profile)
                    && form.question_paper_profile.length > 0">
            <h3 class="font-bold text-sm mb-2">10. QUESTION PAPER PROFILE</h3>
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-1">Unit</th>
                        <th class="border border-black px-2 py-1">2-Mark Q</th>
                        <th class="border border-black px-2 py-1">4-Mark Q</th>
                        <th class="border border-black px-2 py-1">Marks</th>
                        <th class="border border-black px-2 py-1">1.35x</th>
                    </tr>
                </thead>
                <tbody>
                    <template
                        x-for="(profile, index) in (typeof form !== 'undefined'
                            && Array.isArray(form.question_paper_profile)
                            ? form.question_paper_profile
                            : [])"
                        :key="index">
                        <tr>
                            <td class="border border-black px-2 py-1 text-center" x-text="profile.unit_no"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="profile.two_mark_count || 0"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="profile.four_mark_count || 0"></td>
                            <td class="border border-black px-2 py-1 text-center"
                                x-text="(parseInt(profile.two_mark_count || 0) * 2
                                        + parseInt(profile.four_mark_count || 0) * 4)"></td>
                            <td class="border border-black px-2 py-1 text-center"
                                x-text="typeof calculateWeightage === 'function'
                                        ? calculateWeightage(profile)
                                        : 0"></td>
                        </tr>
                    </template>
                </tbody>
                <tfoot class="bg-gray-100">
                    <tr>
                        <td colspan="3" class="border border-black px-2 py-1 text-right font-semibold">Total:</td>
                        <td class="border border-black px-2 py-1 text-center font-semibold"
                            x-text="typeof getTotalPaperMarks === 'function'
                                    ? getTotalPaperMarks()
                                    : 0"></td>
                        <td class="border border-black px-2 py-1 text-center font-semibold"
                            x-text="typeof getTotalPaperMarks === 'function'
                                    ? Math.round(getTotalPaperMarks() * 1.35 * 100) / 100
                                    : 0"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Certification Page -->
        <div class="mt-8 pt-8 border-t-2 border-black"
             x-show="typeof currentStep !== 'undefined' && currentStep === 7">
            <div class="text-center mb-6">
                <h2 class="text-lg font-bold uppercase">Certificate</h2>
                <p class="text-xs text-gray-600">Syllabus Approval Document</p>
            </div>
            
            <div class="text-xs mb-4">
                <p class="mb-2">
                    <strong>Course Title:</strong>
                    <span
                        x-text="typeof form !== 'undefined'
                            ? (form.title || '[Course Title]')
                            : '[Course Title]'">
                    </span>
                </p>
                <p class="mb-2">
                    <strong>Course Code:</strong>
                    <span
                        x-text="typeof form !== 'undefined'
                            ? (form.course_code || '[Course Code]')
                            : '[Course Code]'">
                    </span>
                </p>
                <p class="mb-2">
                    <strong>Programme:</strong>
                    <span
                        x-text="typeof form !== 'undefined'
                            ? (form.program_name || '[Programme]')
                            : '[Programme]'">
                    </span>
                </p>
                <p class="mb-2">
                    <strong>Academic Year:</strong>
                    <span
                        x-text="typeof form !== 'undefined'
                            ? (form.academic_year || '{{ \App\Models\Syllabus::getAcademicYear() }}')
                            : '{{ \App\Models\Syllabus::getAcademicYear() }}'">
                    </span>
                </p>
            </div>
            
                <p class="text-xs text-justify mb-6">
                This is to certify that the syllabus for the above-mentioned course has been reviewed and approved 
                by the Curriculum Development Committee (CDC) for implementation in the academic year 
                <span class="font-semibold"
                      x-text="typeof form !== 'undefined'
                            ? (form.academic_year || '{{ \App\Models\Syllabus::getAcademicYear() }}')
                            : '{{ \App\Models\Syllabus::getAcademicYear() }}'"></span>.
            </p>
            
            <div class="grid grid-cols-3 gap-4 mt-12 text-center text-xs">
                <div>
                    <div class="border-t border-black pt-1"></div>
                    <p class="font-semibold"
                       x-text="typeof form !== 'undefined' && form.certification_signatures
                            ? (form.certification_signatures.hod || '________________')
                            : '________________'"></p>
                    <p>Head of Department</p>
                </div>
                <div>
                    <div class="border-t border-black pt-1"></div>
                    <p class="font-semibold"
                       x-text="typeof form !== 'undefined' && form.certification_signatures
                            ? (form.certification_signatures.principal || '________________')
                            : '________________'"></p>
                    <p>Principal</p>
                </div>
                <div>
                    <div class="border-t border-black pt-1"></div>
                    <p class="font-semibold"
                       x-text="typeof form !== 'undefined' && form.certification_signatures
                            ? (form.certification_signatures.cdc_incharge || '________________')
                            : '________________'"></p>
                    <p>CDC Incharge</p>
                </div>
            </div>
        </div>

    </div>
</div>
