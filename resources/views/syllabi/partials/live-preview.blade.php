@php($programmeMap = \App\Models\Syllabus::getProgrammes())

<div class="h-[calc(100vh-200px)] overflow-y-auto bg-gray-100 p-4" id="livePreviewContainer">
    <div class="bg-white shadow-lg mx-auto" style="width: 210mm; min-height: 297mm; padding: 15mm; font-family: 'Times New Roman', serif; font-size: 11pt;">
        <div class="mb-4 text-sm leading-6">
            <div class="font-bold uppercase"
                 x-text="(() => {
                    const map = @js($programmeMap);
                    const code = form.program_name || '';
                    if (!code) return 'PROGRAMME :';
                    return 'PROGRAMME : ' + (map[code] ? ('Diploma Programme in ' + map[code] + ' (' + code + ')') : code);
                 })()"></div>
            <div class="font-bold uppercase"
                 x-text="'COURSE : ' + (form.title || '') + '    COURSE CODE : ' + (form.course_code || '')"></div>
        </div>

        <div class="mb-4">
            <h3 class="font-bold text-sm mb-2 uppercase">Learning and Assessment Scheme:</h3>
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-1 py-1" colspan="5">Learning Scheme</th>
                        <th class="border border-black px-1 py-1" rowspan="2">SLH</th>
                        <th class="border border-black px-1 py-1" rowspan="2">NLH</th>
                        <th class="border border-black px-1 py-1" rowspan="2">Paper Duration</th>
                        <th class="border border-black px-1 py-1" colspan="5">Assessment Scheme</th>
                        <th class="border border-black px-1 py-1" rowspan="2">Total Marks</th>
                    </tr>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-1 py-1">CL</th>
                        <th class="border border-black px-1 py-1">TU</th>
                        <th class="border border-black px-1 py-1">LL</th>
                        <th class="border border-black px-1 py-1">TL</th>
                        <th class="border border-black px-1 py-1">Credits</th>
                        <th class="border border-black px-1 py-1">FA-TH</th>
                        <th class="border border-black px-1 py-1">SA-TH</th>
                        <th class="border border-black px-1 py-1">FA-PR / TW</th>
                        <th class="border border-black px-1 py-1">SA-PR</th>
                        <th class="border border-black px-1 py-1">SLA</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-black px-1 py-1 text-center" x-text="form.teaching_scheme?.th_hours || 0"></td>
                        <td class="border border-black px-1 py-1 text-center" x-text="form.teaching_scheme?.tu_hours || 0"></td>
                        <td class="border border-black px-1 py-1 text-center" x-text="form.teaching_scheme?.pr_hours || 0"></td>
                        <td class="border border-black px-1 py-1 text-center" x-text="form.teaching_scheme?.total_hours || 0"></td>
                        <td class="border border-black px-1 py-1 text-center" x-text="form.teaching_scheme?.credits || 0"></td>
                        <td class="border border-black px-1 py-1 text-center" x-text="form.teaching_scheme?.slh_hours || 0"></td>
                        <td class="border border-black px-1 py-1 text-center" x-text="form.teaching_scheme?.nlh_hours || 0"></td>
                        <td class="border border-black px-1 py-1 text-center" x-text="((form.examination_scheme?.paper_duration) || 0) + ' Hrs'"></td>
                        <td class="border border-black px-1 py-1 text-center">
                            <span x-text="(form.examination_scheme?.fa_th_max || 0) + (form.is_online_exam ? '#' : '')"></span>
                        </td>
                        <td class="border border-black px-1 py-1 text-center"
                            x-text="(form.examination_scheme?.sa_th_max || 0) + ' / ' + (form.examination_scheme?.sa_th_min || 0)"></td>
                        <td class="border border-black px-1 py-1 text-center"
                            x-text="form.examination_scheme?.tw_marks || '--'"></td>
                        <td class="border border-black px-1 py-1 text-center">
                            <span x-show="form.examination_scheme?.sa_pr_max"
                                  x-text="form.examination_scheme.sa_pr_max + ' / ' + (form.examination_scheme.sa_pr_min || 0) + (form.examination_scheme?.is_internal_practical ? '@' : '')"></span>
                            <span x-show="!form.examination_scheme?.sa_pr_max">--</span>
                        </td>
                        <td class="border border-black px-1 py-1 text-center">--</td>
                        <td class="border border-black px-1 py-1 text-center font-semibold" x-text="calculateTotalMarks()"></td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-2 text-xs">
                <span class="font-semibold">IKS Content:</span>
                <span x-text="(form.iks_hours || 0) + ' Hrs'"></span>
                <span class="ml-3 font-semibold">Total Learning Hours for Term:</span>
                <span x-text="(form.teaching_scheme?.nlh_hours || 0) + ' Hrs'"></span>
            </p>
            <p class="text-xs">@ Internal assessment, # External assessment</p>
        </div>

        <div class="mb-4" x-show="form.rationale">
            <h3 class="font-bold text-sm mb-2 uppercase">1.0 Rationale:</h3>
            <p class="text-xs text-justify whitespace-pre-line" x-text="form.rationale"></p>
        </div>

        <div class="mb-4" x-show="form.industry_employer_outcome">
            <h3 class="font-bold text-sm mb-2 uppercase">2.0 Industry / Employer Expected Outcome:</h3>
            <p class="text-xs mb-1">The aim of this course is to help the student attain the following industry identified outcome:</p>
            <p class="text-xs">• <span x-text="form.industry_employer_outcome"></span></p>
        </div>

        <div class="mb-4" x-show="Array.isArray(form.course_outcomes) && form.course_outcomes.length > 0">
            <h3 class="font-bold text-sm mb-2 uppercase">3.0 Course Outcomes:</h3>
            <p class="text-xs mb-1">The course content should be taught in such a manner that students demonstrate the following course outcomes:</p>
            <template x-for="(outcome, index) in form.course_outcomes" :key="index">
                <p class="text-xs mb-1">
                    <span class="font-semibold" x-text="(outcome.code || ('CO' + (index + 1))) + ' - '"></span>
                    <span x-text="outcome.description"></span>
                </p>
            </template>
        </div>

        <div class="mb-4" x-show="detectedLevel !== 4 && Array.isArray(form.units) && form.units.length > 0">
            <h3 class="font-bold text-sm mb-2 uppercase">4.0 Course Details:</h3>
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-1">Unit</th>
                        <th class="border border-black px-2 py-1">Major Learning Outcomes (in cognitive domain)</th>
                        <th class="border border-black px-2 py-1">Topics and Sub-topics</th>
                        <th class="border border-black px-2 py-1">Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(unit, index) in form.units" :key="index">
                        <tr>
                            <td class="border border-black px-2 py-1 align-top">
                                <div class="font-semibold"
                                     x-text="'Unit-' + ((['','I','II','III','IV','V','VI','VII','VIII','IX','X'][parseInt(unit.unit_no) || 0]) || unit.unit_no)"></div>
                                <div x-text="unit.title"></div>
                            </td>
                            <td class="border border-black px-2 py-1 whitespace-pre-line" x-text="unit.cognitive_outcomes"></td>
                            <td class="border border-black px-2 py-1 whitespace-pre-line" x-text="unit.topics"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="unit.hours"></td>
                        </tr>
                    </template>
                    <tr class="bg-gray-100 font-semibold">
                        <td class="border border-black px-2 py-1 text-right" colspan="3">TOTAL</td>
                        <td class="border border-black px-2 py-1 text-center"
                            x-text="Array.isArray(form.units) ? form.units.reduce((sum, unit) => sum + (parseInt(unit.hours || 0) || 0), 0) : 0"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mb-4" x-show="(detectedLevel === 2 || detectedLevel === 5) && Array.isArray(form.specification_table) && form.specification_table.length > 0">
            <h3 class="font-bold text-sm mb-2 uppercase">5.0 Suggested Specification Table with Marks (Theory):</h3>
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-1" rowspan="2">Unit No.</th>
                        <th class="border border-black px-2 py-1" rowspan="2">Unit Title</th>
                        <th class="border border-black px-2 py-1" colspan="4">Distribution of Theory Marks</th>
                    </tr>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-1">R Level</th>
                        <th class="border border-black px-2 py-1">U Level</th>
                        <th class="border border-black px-2 py-1">A and above Levels</th>
                        <th class="border border-black px-2 py-1">Total Marks</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(spec, index) in form.specification_table" :key="index">
                        <tr>
                            <td class="border border-black px-2 py-1 text-center" x-text="spec.unit_no"></td>
                            <td class="border border-black px-2 py-1"
                                x-text="(form.units.find(u => String(u.unit_no) === String(spec.unit_no)) || {}).title || '-'"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="spec.r || 0"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="spec.u || 0"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="spec.a || 0"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="(parseInt(spec.r || 0) + parseInt(spec.u || 0) + parseInt(spec.a || 0))"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="mb-4" x-show="Array.isArray(form.practical_tasks) && form.practical_tasks.length > 0">
            <h3 class="font-bold text-sm mb-2 uppercase">6.0 Laboratory Learning Outcome and Allied Practical / Tutorial Experiences:</h3>
            <p class="text-xs mb-2">The tutorial / practical / assignment / task should be properly designed so that students are able to acquire the desired programme outcomes and course outcomes.</p>
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-1">Sr. No.</th>
                        <th class="border border-black px-2 py-1">Unit No.</th>
                        <th class="border border-black px-2 py-1">LLO</th>
                        <th class="border border-black px-2 py-1">Practical Exercises</th>
                        <th class="border border-black px-2 py-1">Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(task, index) in form.practical_tasks" :key="index">
                        <tr>
                            <td class="border border-black px-2 py-1 text-center" x-text="task.s_no"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="task.unit_no || '-'"></td>
                            <td class="border border-black px-2 py-1" x-text="task.llo || task.co_code || '-'"></td>
                            <td class="border border-black px-2 py-1" x-text="task.title"></td>
                            <td class="border border-black px-2 py-1 text-center" x-text="task.hours"></td>
                        </tr>
                    </template>
                    <tr class="bg-gray-100 font-semibold">
                        <td class="border border-black px-2 py-1 text-right" colspan="4">Total</td>
                        <td class="border border-black px-2 py-1 text-center"
                            x-text="Array.isArray(form.practical_tasks) ? form.practical_tasks.reduce((sum, task) => sum + (parseInt(task.hours || 0) || 0), 0) : 0"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mb-4">
            <h3 class="font-bold text-sm mb-2 uppercase">7.0 Self Learning:</h3>
            <p class="text-xs whitespace-pre-line" x-text="form.self_learning || 'Not Applicable'"></p>
        </div>

        <div class="mb-4">
            <h3 class="font-bold text-sm mb-2 uppercase">8.0 Special Instructional Strategies (If any):</h3>
            <template x-if="Array.isArray(form.special_instructional_strategies) && form.special_instructional_strategies.length > 0">
                <ol class="text-xs list-decimal list-inside">
                    <template x-for="(strategy, index) in form.special_instructional_strategies" :key="index">
                        <li x-text="strategy"></li>
                    </template>
                </ol>
            </template>
            <p class="text-xs" x-show="!Array.isArray(form.special_instructional_strategies) || form.special_instructional_strategies.length === 0">Not Applicable</p>
        </div>

        <div class="mb-4">
            <h3 class="font-bold text-sm mb-2 uppercase">9.0 Assessment Methodology</h3>
            <div class="text-xs space-y-1">
                <p>
                    <span class="font-semibold">Formative assessment: TH</span>
                    <span x-text="'Periodic Test: ' + (form.examination_scheme?.fa_th_max || 0) + ' marks'"></span>
                </p>
                <p>
                    <span class="font-semibold">Summative assessment: TH</span>
                    <span x-text="(form.examination_scheme?.sa_th_max || 0) + ' marks final theory paper.'"></span>
                </p>
                <p x-show="(form.examination_scheme?.tw_marks || 0) > 0">
                    <span class="font-semibold">Formative assessment: PR</span>
                    <span x-text="(form.examination_scheme?.tw_marks || 0) + ' marks for practical / term work / viva.'"></span>
                </p>
                <p x-show="(form.examination_scheme?.sa_pr_max || 0) > 0">
                    <span class="font-semibold">Summative assessment: PR</span>
                    <span x-text="(form.examination_scheme?.sa_pr_max || 0) + ' marks for practical / viva.'"></span>
                </p>
            </div>
        </div>

        <div class="mb-4" x-show="(Array.isArray(form.books) && form.books.length > 0) || (Array.isArray(form.software_websites) && form.software_websites.length > 0) || (Array.isArray(form.equipment_list) && form.equipment_list.length > 0)">
            <h3 class="font-bold text-sm mb-2 uppercase">10.0 Learning Resources:</h3>

            <div class="mb-3" x-show="Array.isArray(form.books) && form.books.length > 0">
                <p class="text-xs font-semibold mb-1">A) Books:</p>
                <table class="w-full border-collapse border border-black text-xs">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-black px-2 py-1">Sr. No.</th>
                            <th class="border border-black px-2 py-1">Author</th>
                            <th class="border border-black px-2 py-1">Title of Book</th>
                            <th class="border border-black px-2 py-1">Publication</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(book, index) in form.books" :key="index">
                            <tr>
                                <td class="border border-black px-2 py-1 text-center" x-text="String(index + 1).padStart(2, '0')"></td>
                                <td class="border border-black px-2 py-1" x-text="book.author"></td>
                                <td class="border border-black px-2 py-1" x-text="book.title + (book.edition ? ' (' + book.edition + ')' : '')"></td>
                                <td class="border border-black px-2 py-1" x-text="book.publication + (book.isbn ? ', ISBN: ' + book.isbn : '')"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mb-3" x-show="Array.isArray(form.software_websites) && form.software_websites.length > 0">
                <p class="text-xs font-semibold mb-1">B) Software / Learning Websites:</p>
                <table class="w-full border-collapse border border-black text-xs">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-black px-2 py-1">Sr. No.</th>
                            <th class="border border-black px-2 py-1">Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(resource, index) in form.software_websites" :key="index">
                            <tr>
                                <td class="border border-black px-2 py-1 text-center" x-text="String(index + 1).padStart(2, '0')"></td>
                                <td class="border border-black px-2 py-1"
                                    x-text="resource.url || resource.name"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div x-show="Array.isArray(form.equipment_list) && form.equipment_list.length > 0">
                <p class="text-xs font-semibold mb-1">C) Major Equipment / Instrument with Broad Specifications:</p>
                <table class="w-full border-collapse border border-black text-xs">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-black px-2 py-1">Sr. No.</th>
                            <th class="border border-black px-2 py-1">Equipment</th>
                            <th class="border border-black px-2 py-1">Specification</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(equipment, index) in form.equipment_list" :key="index">
                            <tr>
                                <td class="border border-black px-2 py-1 text-center" x-text="equipment.s_no || (index + 1)"></td>
                                <td class="border border-black px-2 py-1" x-text="equipment.name"></td>
                                <td class="border border-black px-2 py-1" x-text="equipment.specifications"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mb-4" x-show="Array.isArray(form.mapping_matrix) && form.mapping_matrix.length > 0">
            <h3 class="font-bold text-sm mb-2 uppercase">11.0 Mapping Matrix of PO's, CO's and PSO's:</h3>
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-1 py-1">Course Outcomes</th>
                        <template x-for="i in 7" :key="'po' + i">
                            <th class="border border-black px-1 py-1" x-text="i"></th>
                        </template>
                        <template x-for="i in 4" :key="'pso' + i">
                            <th class="border border-black px-1 py-1" x-text="i"></th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(mapping, index) in form.mapping_matrix" :key="index">
                        <tr>
                            <td class="border border-black px-1 py-1 text-center font-semibold" x-text="mapping.co_code"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.po1 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.po2 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.po3 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.po4 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.po5 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.po6 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.po7 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.pso1 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.pso2 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.pso3 || '-'"></td>
                            <td class="border border-black px-1 py-1 text-center" x-text="mapping.pso4 || '-'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p class="text-xs mt-1">H: High Relationship, M: Moderate Relationship, L: Low Relationship</p>
        </div>

        <div class="mt-8 pt-8 border-t-2 border-black" x-show="typeof currentStep !== 'undefined' && currentStep === 7">
            <div class="text-center mb-6">
                <h2 class="text-lg font-bold uppercase">Certificate</h2>
                <p class="text-xs text-gray-600">Syllabus Approval Document</p>
            </div>

            <p class="text-xs text-justify mb-6">
                This is to certify that the syllabus for the above-mentioned course has been reviewed and approved by the Curriculum Development Committee (CDC) for implementation in the academic year
                <span class="font-semibold" x-text="form.academic_year || '{{ \App\Models\Syllabus::getAcademicYear() }}'"></span>.
            </p>

            <div class="grid grid-cols-3 gap-4 mt-12 text-center text-xs">
                <div>
                    <div class="border-t border-black pt-1"></div>
                    <p class="font-semibold" x-text="form.certification_signatures?.hod || '________________'"></p>
                    <p>Head of Department</p>
                </div>
                
                </div>
            </div>
        </div>
    </div>
</div>
