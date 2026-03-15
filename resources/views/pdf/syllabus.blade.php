@php
    $programmeMap = \App\Models\Syllabus::getProgrammes();
    $programmeLabel = $syllabus->program_name
        ? ($programmeMap[$syllabus->program_name] ?? $syllabus->program_name)
        : '';
    $level = $syllabus->getCourseLevel();
    $unitHoursTotal = collect($syllabus->units ?? [])->sum(fn ($unit) => (int) ($unit['hours'] ?? 0));
    $practicalHoursTotal = collect($syllabus->practical_tasks ?? [])->sum(fn ($task) => (int) ($task['hours'] ?? 0));
    $hasPso4 = collect($syllabus->mapping_matrix ?? [])->contains(fn ($row) => !empty($row['pso4']) && $row['pso4'] !== '-');
    $totalMarks = (int) ($syllabus->examination_scheme['fa_th_max'] ?? 0)
        + (int) ($syllabus->examination_scheme['sa_th_max'] ?? 0)
        + (int) ($syllabus->examination_scheme['sa_pr_max'] ?? 0)
        + (int) ($syllabus->examination_scheme['tw_marks'] ?? 0);
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Syllabus - {{ $syllabus->course_code }}</title>
    <style>
        @page { margin: 10mm; }
        body {
            font-family: "Times New Roman", serif;
            font-size: 11pt;
            line-height: 1.35;
            margin: 0;
            color: #000;
        }
        .page { width: 100%; }
        .page-break { page-break-after: always; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .mb-4 { margin-bottom: 16px; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }
        .pt-8 { padding-top: 32px; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-xs { font-size: 9pt; }
        .text-sm { font-size: 10pt; }
        .text-justify { text-align: justify; }
        .whitespace-pre-line { white-space: pre-line; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }
        thead th {
            background: #f1f1f1;
        }
        ol, ul {
            margin: 0;
            padding-left: 18px;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="mb-4 text-sm">
            <div class="font-bold uppercase mb-1">
                PROGRAMME : {{ $programmeLabel ? 'Diploma Programme in ' . $programmeLabel . ' (' . $syllabus->program_name . ')' : $syllabus->program_name }}
            </div>
            <div class="font-bold uppercase">
                COURSE : {{ $syllabus->title }}    COURSE CODE : {{ $syllabus->course_code }}
            </div>
        </div>

        <div class="mb-4">
            <h3 class="font-bold text-sm uppercase mb-2">Learning and Assessment Scheme:</h3>
            <table class="text-xs">
                <thead>
                    <tr>
                        <th colspan="5">Learning Scheme</th>
                        <th rowspan="2">SLH</th>
                        <th rowspan="2">NLH</th>
                        <th rowspan="2">Paper Duration</th>
                        <th colspan="5">Assessment Scheme</th>
                        <th rowspan="2">Total Marks</th>
                    </tr>
                    <tr>
                        <th>CL</th>
                        <th>TU</th>
                        <th>LL</th>
                        <th>TL</th>
                        <th>Credits</th>
                        <th>FA-TH</th>
                        <th>SA-TH</th>
                        <th>FA-PR / TW</th>
                        <th>SA-PR</th>
                        <th>SLA</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">{{ $syllabus->teaching_scheme['th_hours'] ?? 0 }}</td>
                        <td class="text-center">{{ $syllabus->teaching_scheme['tu_hours'] ?? 0 }}</td>
                        <td class="text-center">{{ $syllabus->teaching_scheme['pr_hours'] ?? 0 }}</td>
                        <td class="text-center">{{ $syllabus->teaching_scheme['total_hours'] ?? 0 }}</td>
                        <td class="text-center">{{ $syllabus->teaching_scheme['credits'] ?? 0 }}</td>
                        <td class="text-center">{{ $syllabus->teaching_scheme['slh_hours'] ?? 0 }}</td>
                        <td class="text-center">{{ $syllabus->teaching_scheme['nlh_hours'] ?? 0 }}</td>
                        <td class="text-center">{{ $syllabus->examination_scheme['paper_duration'] ?? 0 }} Hrs</td>
                        <td class="text-center">{{ $syllabus->examination_scheme['fa_th_max'] ?? 0 }}{{ $syllabus->is_online_exam ? '#' : '' }}</td>
                        <td class="text-center">{{ $syllabus->examination_scheme['sa_th_max'] ?? 0 }} / {{ $syllabus->examination_scheme['sa_th_min'] ?? 0 }}</td>
                        <td class="text-center">{{ $syllabus->examination_scheme['tw_marks'] ?? '--' }}</td>
                        <td class="text-center">
                            @if(!empty($syllabus->examination_scheme['sa_pr_max']))
                                {{ $syllabus->examination_scheme['sa_pr_max'] ?? 0 }} / {{ $syllabus->examination_scheme['sa_pr_min'] ?? 0 }}{{ ($syllabus->examination_scheme['is_internal_practical'] ?? false) ? '@' : '' }}
                            @else
                                --
                            @endif
                        </td>
                        <td class="text-center">--</td>
                        <td class="text-center font-bold">{{ $totalMarks }}</td>
                    </tr>
                </tbody>
            </table>
            <p class="text-xs mb-1">
                <span class="font-bold">IKS Content:</span> {{ $syllabus->iks_hours ?? 0 }} Hrs
                <span class="font-bold" style="margin-left: 16px;">Total Learning Hours for Term:</span> {{ $syllabus->teaching_scheme['nlh_hours'] ?? 0 }} Hrs
            </p>
            <p class="text-xs">@ Internal assessment, # External assessment</p>
        </div>

        @if($syllabus->rationale)
            <div class="mb-4">
                <h3 class="font-bold text-sm uppercase mb-2">1.0 Rationale:</h3>
                <p class="text-xs text-justify whitespace-pre-line">{{ $syllabus->rationale }}</p>
            </div>
        @endif

        @if($syllabus->industry_employer_outcome)
            <div class="mb-4">
                <h3 class="font-bold text-sm uppercase mb-2">2.0 Industry / Employer Expected Outcome:</h3>
                <p class="text-xs mb-1">The aim of this course is to help the student attain the following industry identified outcome:</p>
                <p class="text-xs">• {{ $syllabus->industry_employer_outcome }}</p>
            </div>
        @endif

        @if(!empty($syllabus->course_outcomes))
            <div class="mb-4">
                <h3 class="font-bold text-sm uppercase mb-2">3.0 Course Outcomes:</h3>
                <p class="text-xs mb-1">The course content should be taught in such a manner that students demonstrate the following course outcomes:</p>
                @foreach($syllabus->course_outcomes as $index => $outcome)
                    <p class="text-xs mb-1"><span class="font-bold">{{ $outcome['code'] ?? ('CO' . ($index + 1)) }} - </span>{{ $outcome['description'] ?? '' }}</p>
                @endforeach
            </div>
        @endif

        @if($level !== 4 && !empty($syllabus->units))
            <div class="mb-4">
                <h3 class="font-bold text-sm uppercase mb-2">4.0 Course Details:</h3>
                <table class="text-xs">
                    <thead>
                        <tr>
                            <th>Unit</th>
                            <th>Major Learning Outcomes (in cognitive domain)</th>
                            <th>Topics and Sub-topics</th>
                            <th>Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($syllabus->units as $unit)
                            @php
                                $roman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'][(int) ($unit['unit_no'] ?? 0)] ?? ($unit['unit_no'] ?? '');
                            @endphp
                            <tr>
                                <td><span class="font-bold">Unit-{{ $roman }}</span><br>{{ $unit['title'] ?? '' }}</td>
                                <td class="whitespace-pre-line">{{ $unit['cognitive_outcomes'] ?? '' }}</td>
                                <td class="whitespace-pre-line">{{ $unit['topics'] ?? '' }}</td>
                                <td class="text-center">{{ $unit['hours'] ?? 0 }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="3" class="text-right font-bold">TOTAL</td>
                            <td class="text-center font-bold">{{ $unitHoursTotal }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        @if(($level === 2 || $level === 5) && !empty($syllabus->specification_table))
            <div class="mb-4">
                <h3 class="font-bold text-sm uppercase mb-2">5.0 Suggested Specification Table with Marks (Theory):</h3>
                <table class="text-xs">
                    <thead>
                        <tr>
                            <th rowspan="2">Unit No.</th>
                            <th rowspan="2">Unit Title</th>
                            <th colspan="4">Distribution of Theory Marks</th>
                        </tr>
                        <tr>
                            <th>R Level</th>
                            <th>U Level</th>
                            <th>A and above Levels</th>
                            <th>Total Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($syllabus->specification_table as $spec)
                            @php
                                $unitForSpec = collect($syllabus->units ?? [])->firstWhere('unit_no', $spec['unit_no'] ?? null);
                                $unitTitle = $unitForSpec['title'] ?? '-';
                            @endphp
                            <tr>
                                <td class="text-center">{{ $spec['unit_no'] ?? '-' }}</td>
                                <td>{{ $unitTitle }}</td>
                                <td class="text-center">{{ $spec['r'] ?? 0 }}</td>
                                <td class="text-center">{{ $spec['u'] ?? 0 }}</td>
                                <td class="text-center">{{ $spec['a'] ?? 0 }}</td>
                                <td class="text-center">{{ (int) ($spec['r'] ?? 0) + (int) ($spec['u'] ?? 0) + (int) ($spec['a'] ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(!empty($syllabus->practical_tasks))
            <div class="mb-4">
                <h3 class="font-bold text-sm uppercase mb-2">6.0 Laboratory Learning Outcome and Allied Practical / Tutorial Experiences:</h3>
                <p class="text-xs mb-2">The tutorial / practical / assignment / task should be properly designed so that students are able to acquire the desired programme outcomes and course outcomes.</p>
                <table class="text-xs">
                    <thead>
                        <tr>
                            <th>Sr. No.</th>
                            <th>Unit No.</th>
                            <th>LLO</th>
                            <th>Practical Exercises</th>
                            <th>Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($syllabus->practical_tasks as $task)
                            <tr>
                                <td class="text-center">{{ $task['s_no'] ?? '-' }}</td>
                                <td class="text-center">{{ $task['unit_no'] ?? '-' }}</td>
                                <td>{{ $task['llo'] ?? ($task['co_code'] ?? '-') }}</td>
                                <td>{{ $task['title'] ?? '' }}</td>
                                <td class="text-center">{{ $task['hours'] ?? 0 }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="4" class="text-right font-bold">Total</td>
                            <td class="text-center font-bold">{{ $practicalHoursTotal }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mb-4">
            <h3 class="font-bold text-sm uppercase mb-2">7.0 Self Learning:</h3>
            <p class="text-xs whitespace-pre-line">{{ $syllabus->self_learning ?: 'Not Applicable' }}</p>
        </div>

        <div class="mb-4">
            <h3 class="font-bold text-sm uppercase mb-2">8.0 Special Instructional Strategies (If any):</h3>
            @if(!empty($syllabus->special_instructional_strategies))
                <ol class="text-xs">
                    @foreach($syllabus->special_instructional_strategies as $strategy)
                        <li>{{ $strategy }}</li>
                    @endforeach
                </ol>
            @else
                <p class="text-xs">Not Applicable</p>
            @endif
        </div>

        <div class="mb-4">
            <h3 class="font-bold text-sm uppercase mb-2">9.0 Assessment Methodology</h3>
            <p class="text-xs mb-1"><span class="font-bold">Formative assessment: TH</span> Periodic Test: {{ $syllabus->examination_scheme['fa_th_max'] ?? 0 }} marks</p>
            <p class="text-xs mb-1"><span class="font-bold">Summative assessment: TH</span> {{ $syllabus->examination_scheme['sa_th_max'] ?? 0 }} marks final theory paper.</p>
            @if(($syllabus->examination_scheme['tw_marks'] ?? 0) > 0)
                <p class="text-xs mb-1"><span class="font-bold">Formative assessment: PR</span> {{ $syllabus->examination_scheme['tw_marks'] ?? 0 }} marks for practical / term work / viva.</p>
            @endif
            @if(($syllabus->examination_scheme['sa_pr_max'] ?? 0) > 0)
                <p class="text-xs mb-1"><span class="font-bold">Summative assessment: PR</span> {{ $syllabus->examination_scheme['sa_pr_max'] ?? 0 }} marks for practical / viva.</p>
            @endif
        </div>

        @if(!empty($syllabus->books) || !empty($syllabus->software_websites) || !empty($syllabus->equipment_list))
            <div class="mb-4">
                <h3 class="font-bold text-sm uppercase mb-2">10.0 Learning Resources:</h3>

                @if(!empty($syllabus->books))
                    <p class="text-xs font-bold mb-1">A) Books:</p>
                    <table class="text-xs">
                        <thead>
                            <tr>
                                <th>Sr. No.</th>
                                <th>Author</th>
                                <th>Title of Book</th>
                                <th>Publication</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($syllabus->books as $index => $book)
                                <tr>
                                    <td class="text-center">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $book['author'] ?? '' }}</td>
                                    <td>{{ $book['title'] ?? '' }}{{ !empty($book['edition']) ? ' (' . $book['edition'] . ')' : '' }}</td>
                                    <td>{{ $book['publication'] ?? '' }}{{ !empty($book['isbn']) ? ', ISBN: ' . $book['isbn'] : '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @if(!empty($syllabus->software_websites))
                    <p class="text-xs font-bold mb-1">B) Software / Learning Websites:</p>
                    <table class="text-xs">
                        <thead>
                            <tr>
                                <th>Sr. No.</th>
                                <th>Link</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($syllabus->software_websites as $index => $resource)
                                <tr>
                                    <td class="text-center">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $resource['url'] ?? ($resource['name'] ?? '') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @if(!empty($syllabus->equipment_list))
                    <p class="text-xs font-bold mb-1">C) Major Equipment / Instrument with Broad Specifications:</p>
                    <table class="text-xs">
                        <thead>
                            <tr>
                                <th>Sr. No.</th>
                                <th>Equipment</th>
                                <th>Specification</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($syllabus->equipment_list as $index => $equipment)
                                <tr>
                                    <td class="text-center">{{ $equipment['s_no'] ?? ($index + 1) }}</td>
                                    <td>{{ $equipment['name'] ?? '' }}</td>
                                    <td>{{ $equipment['specifications'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        @if(!empty($syllabus->mapping_matrix))
            <div class="mb-4">
                <h3 class="font-bold text-sm uppercase mb-2">11.0 Mapping Matrix of PO's, CO's and PSO's:</h3>
                <table class="text-xs">
                    <thead>
                        <tr>
                            <th>Course Outcomes</th>
                            @for($i = 1; $i <= 7; $i++)
                                <th>{{ $i }}</th>
                            @endfor
                            @for($i = 1; $i <= ($hasPso4 ? 4 : 3); $i++)
                                <th>{{ $i }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($syllabus->mapping_matrix as $mapping)
                            <tr>
                                <td class="text-center font-bold">{{ $mapping['co_code'] ?? '-' }}</td>
                                <td class="text-center">{{ $mapping['po1'] ?? '-' }}</td>
                                <td class="text-center">{{ $mapping['po2'] ?? '-' }}</td>
                                <td class="text-center">{{ $mapping['po3'] ?? '-' }}</td>
                                <td class="text-center">{{ $mapping['po4'] ?? '-' }}</td>
                                <td class="text-center">{{ $mapping['po5'] ?? '-' }}</td>
                                <td class="text-center">{{ $mapping['po6'] ?? '-' }}</td>
                                <td class="text-center">{{ $mapping['po7'] ?? '-' }}</td>
                                <td class="text-center">{{ $mapping['pso1'] ?? '-' }}</td>
                                <td class="text-center">{{ $mapping['pso2'] ?? '-' }}</td>
                                <td class="text-center">{{ $mapping['pso3'] ?? '-' }}</td>
                                @if($hasPso4)
                                    <td class="text-center">{{ $mapping['pso4'] ?? '-' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="text-xs">H: High Relationship, M: Moderate Relationship, L: Low Relationship</p>
            </div>
        @endif

        <div class="page-break"></div>

        <div class="page pt-8">
            <div class="text-center mb-4">
                <h2 class="font-bold uppercase">Certificate</h2>
                <p class="text-xs">Syllabus Approval Document</p>
            </div>

            <p class="text-xs text-justify mb-4">
                This is to certify that the syllabus for the above-mentioned course has been reviewed and approved by the Curriculum Development Committee (CDC) for implementation in the academic year {{ $syllabus->academic_year ?? \App\Models\Syllabus::getAcademicYear() }}.
            </p>

            <table style="border: 0; margin-top: 40px;">
                <tr>
                    <td style="border: 0; text-align: center; width: 33%;">
                        <div style="border-top: 1px solid #000; padding-top: 4px;">{{ $syllabus->certification_signatures['hod'] ?? '________________' }}<br>Head of Department</div>
                    </td>
                    <td style="border: 0; text-align: center; width: 33%;">
                        <div style="border-top: 1px solid #000; padding-top: 4px;">{{ $syllabus->certification_signatures['principal'] ?? '________________' }}<br>Principal</div>
                    </td>
                    <td style="border: 0; text-align: center; width: 33%;">
                        <div style="border-top: 1px solid #000; padding-top: 4px;">{{ $syllabus->certification_signatures['cdc_incharge'] ?? '________________' }}<br>CDC Incharge</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
