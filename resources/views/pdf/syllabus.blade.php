<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Syllabus - {{ $syllabus->course_code }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm;
            margin: 0 auto;
            background: white;
            box-sizing: border-box;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .border-b-2 { border-bottom: 2px solid black; }
        .pb-4 { padding-bottom: 1rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-8 { margin-top: 2rem; }
        .pt-8 { padding-top: 2rem; }
        .text-sm { font-size: 0.875rem; }
        .text-xs { font-size: 0.75rem; }
        .text-lg { font-size: 1.125rem; }
        .italic { font-style: italic; }
        .w-full { width: 100%; }
        .border-collapse { border-collapse: collapse; }
        .border { border: 1px solid black; }
        .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
        .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
        .px-1 { padding-left: 0.25rem; padding-right: 0.25rem; }
        .w-1\/4 { width: 25%; }
        .w-16 { width: 4rem; }
        .w-24 { width: 6rem; }
        .bg-gray-100 { background-color: #f3f4f6; }
        .text-justify { text-align: justify; }
        .list-decimal { list-style-type: decimal; }
        .list-disc { list-style-type: disc; }
        .list-inside { list-style-position: inside; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        th, td {
            border: 1px solid black;
            padding: 4px 8px;
            vertical-align: top;
        }
        .no-border {
            border: none;
        }
        .page-break {
            page-break-after: always;
        }
        .grid {
            display: table;
            width: 100%;
        }
        .grid-cols-3 > div {
            display: table-cell;
            width: 33.33%;
        }
        .border-t { border-top: 1px solid black; }
        
        /* Specific widths for scheme table to match preview */
        .scheme-table th, .scheme-table td {
            font-size: 9pt;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header Section -->
        <div class="text-center border-b-2 pb-4 mb-4">
            <h1 class="text-lg font-bold uppercase">{{ $syllabus->program_name }} - SYLLABUS</h1>
            <p class="text-sm mt-1">
                Academic Year: {{ $syllabus->academic_year ?? \App\Models\Syllabus::getAcademicYear() }}
            </p>
            @php $level = $syllabus->getCourseLevel(); @endphp
            @if($syllabus->is_part_of_group && $level === 3)
                <div class="mt-2 text-sm font-bold italic">
                    Any ONE of the following
                </div>
            @endif
        </div>

        <!-- Course Identity -->
        <div class="mb-4">
            <table>
                <tr>
                    <td class="font-bold w-1/4">Course Title</td>
                    <td colspan="3">{{ $syllabus->title }}</td>
                </tr>
                <tr>
                    <td class="font-bold">Course Code</td>
                    <td>{{ $syllabus->course_code }}</td>
                    <td class="font-bold">Credits</td>
                    <td>{{ $syllabus->teaching_scheme['credits'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="font-bold">IKS Hours</td>
                    <td>{{ $syllabus->iks_hours ?? 0 }}</td>
                    <td class="font-bold">Level</td>
                    <td>
                        @switch($level)
                            @case(1) 1: Foundation (Science & Humanities) @break
                            @case(2) 2: Basic Technology @break
                            @case(3) 3: Allied Courses (Electives) @break
                            @case(4) 4: Applied Technology (Training/Project) @break
                            @case(5) 5: Diversified Technology @break
                            @case(0) 0: Audit / Co-curricular Course @break
                            @default {{ $level }}
                        @endswitch
                    </td>
                </tr>
            </table>
        </div>

        <!-- Teaching & Examination Scheme -->
        <div class="mb-4">
            <h3 class="font-bold text-sm mb-2">1. TEACHING & EXAMINATION SCHEME</h3>
            <table class="scheme-table">
                <thead>
                    <tr class="bg-gray-100">
                        <th colspan="6">Teaching Scheme</th>
                        <th colspan="5">Examination Scheme</th>
                    </tr>
                    <tr class="bg-gray-100">
                        <th>CL</th>
                        <th>TU</th>
                        <th>LL</th>
                        <th>TL</th>
                        <th>Credits</th>
                        <th>SLH</th>
                        <th>FA-TH</th>
                        <th>SA-TH</th>
                        <th>SA-PR</th>
                        <th>TW</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $syllabus->teaching_scheme['th_hours'] ?? 0 }}</td>
                        <td>{{ $syllabus->teaching_scheme['tu_hours'] ?? 0 }}</td>
                        <td>{{ $syllabus->teaching_scheme['pr_hours'] ?? 0 }}</td>
                        <td>{{ $syllabus->teaching_scheme['total_hours'] ?? 0 }}</td>
                        <td>{{ $syllabus->teaching_scheme['credits'] ?? 0 }}</td>
                        <td>{{ $syllabus->teaching_scheme['slh_hours'] ?? 0 }}</td>
                        <td>
                            {{ $syllabus->examination_scheme['fa_th_max'] ?? 0 }}{{ $syllabus->is_online_exam ? '#' : '' }}
                        </td>
                        <td>
                            {{ $syllabus->examination_scheme['sa_th_max'] ?? 0 }}/{{ $syllabus->examination_scheme['sa_th_min'] ?? 0 }}
                        </td>
                        <td>
                            @if(!empty($syllabus->examination_scheme['sa_pr_max']))
                                {{ $syllabus->examination_scheme['sa_pr_max'] }}/{{ $syllabus->examination_scheme['sa_pr_min'] }}{{ ($syllabus->examination_scheme['is_internal_practical'] ?? false) ? '@' : '' }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $syllabus->examination_scheme['tw_marks'] ?? '-' }}</td>
                        <td>{{ $syllabus->examination_scheme['paper_duration'] ?? 0 }} Hrs</td>
                    </tr>
                </tbody>
            </table>
            @if($syllabus->is_online_exam || ($syllabus->examination_scheme['is_internal_practical'] ?? false))
                <p class="text-xs mt-1">
                    @if($syllabus->is_online_exam) # Online Examination @endif
                    @if($syllabus->is_online_exam && ($syllabus->examination_scheme['is_internal_practical'] ?? false)) | @endif
                    @if($syllabus->examination_scheme['is_internal_practical'] ?? false) @ Internal Assessment @endif
                </p>
            @endif
        </div>

        <!-- Rationale -->
        @if($syllabus->rationale)
        <div class="mb-4">
            <h3 class="font-bold text-sm mb-2">2. RATIONALE</h3>
            <p class="text-xs text-justify">{{ $syllabus->rationale }}</p>
        </div>
        @endif

        <!-- Course Objectives -->
        @if(!empty($syllabus->course_objectives))
        <div class="mb-4">
            <h3 class="font-bold text-sm mb-2">3. COURSE OBJECTIVES</h3>
            <p class="text-xs mb-1">After studying this course, the student will be able to:</p>
            <ol class="text-xs list-decimal list-inside">
                @foreach($syllabus->course_objectives as $obj)
                    <li>{{ $obj }}</li>
                @endforeach
            </ol>
        </div>
        @endif

        <!-- Course Outcomes -->
        @if(!empty($syllabus->course_outcomes))
        <div class="mb-4">
            <h3 class="font-bold text-sm mb-2">4. COURSE OUTCOMES (COs)</h3>
            <table>
                <tbody>
                    @foreach($syllabus->course_outcomes as $index => $outcome)
                        <tr>
                            <td class="font-bold w-16">{{ $outcome['code'] ?? ('CO' . ($index + 1)) }}</td>
                            <td class="text-xs">{{ $outcome['description'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($level === 4)
            <!-- Training Schedule (Level 4) -->
            @if(!empty($syllabus->training_schedule))
            <div class="mb-4">
                <h3 class="font-bold text-sm mb-2">5. TRAINING SCHEDULE</h3>
                <table class="text-xs">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="w-16">Week</th>
                            <th>Activity to be Performed</th>
                            <th>Industry</th>
                            <th>Mentor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($syllabus->training_schedule as $week)
                            <tr>
                                <td class="text-center">{{ $week['week_no'] }}</td>
                                <td>{{ $week['activity'] }}</td>
                                <td class="text-center">{{ $week['marks_industry'] ?? '-' }}</td>
                                <td class="text-center">{{ $week['marks_mentor'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Report Format (Level 4) -->
            @if(!empty($syllabus->report_format))
            <div class="mb-4">
                <h3 class="font-bold text-sm mb-2">6. REPORT FORMAT</h3>
                <table class="text-xs">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="w-24">Chapter</th>
                            <th>Title</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($syllabus->report_format as $chapter)
                            <tr>
                                <td class="text-center">Chapter {{ $chapter['chapter'] }}</td>
                                <td>{{ $chapter['title'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        @else
            <!-- Course Content (Other levels) -->
            @if(!empty($syllabus->units))
            <div class="mb-4">
                <h3 class="font-bold text-sm mb-2">5. COURSE CONTENT</h3>
                <table class="text-xs">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="w-16">Unit</th>
                            <th>Title</th>
                            <th>Learning Outcomes</th>
                            <th class="w-16">Hrs</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($syllabus->units as $unit)
                            <tr>
                                <td class="text-center">{{ $unit['unit_no'] }}</td>
                                <td>{{ $unit['title'] }}</td>
                                <td>{{ $unit['cognitive_outcomes'] }}</td>
                                <td class="text-center">{{ $unit['hours'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Specification Table (Levels 2 & 5) -->
            @if(($level === 2 || $level === 5) && !empty($syllabus->specification_table))
            <div class="mb-4">
                <h3 class="font-bold text-sm mb-2">6. THEORY SPECIFICATION</h3>
                <table class="text-xs">
                    <thead>
                        <tr class="bg-gray-100">
                            <th>Unit</th>
                            <th>R</th>
                            <th>U</th>
                            <th>A</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($syllabus->specification_table as $spec)
                            <tr>
                                <td class="text-center">{{ $spec['unit_no'] }}</td>
                                <td class="text-center">{{ $spec['r'] ?? 0 }}</td>
                                <td class="text-center">{{ $spec['u'] ?? 0 }}</td>
                                <td class="text-center">{{ $spec['a'] ?? 0 }}</td>
                                <td class="text-center">{{ (int)($spec['r'] ?? 0) + (int)($spec['u'] ?? 0) + (int)($spec['a'] ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        @endif

        <!-- Practical Tasks -->
        @if(!empty($syllabus->practical_tasks))
        <div class="mb-4">
            <h3 class="font-bold text-sm mb-2">{{ $level === 4 ? '7. PRACTICAL EXERCISES' : '7. PRACTICAL TASKS' }}</h3>
            <table class="text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="w-16">S.No</th>
                        <th class="w-16">Unit</th>
                        <th>Title</th>
                        <th class="w-16">Hrs</th>
                        <th class="w-16">CO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($syllabus->practical_tasks as $task)
                        <tr>
                            <td class="text-center">{{ $task['s_no'] }}{{ ($task['is_mandatory'] ?? false) ? '*' : '' }}</td>
                            <td class="text-center">{{ $task['unit_no'] ?? '-' }}</td>
                            <td>{{ $task['title'] }}</td>
                            <td class="text-center">{{ $task['hours'] }}</td>
                            <td class="text-center">{{ $task['co_code'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if(collect($syllabus->practical_tasks)->some(fn($t) => $t['is_mandatory'] ?? false))
                <p class="text-xs">* Mandatory task</p>
            @endif
        </div>
        @endif

        <!-- Learning Resources -->
        @if(!empty($syllabus->books) || !empty($syllabus->software_websites))
        <div class="mb-4">
            <h3 class="font-bold text-sm mb-2">8. LEARNING RESOURCES</h3>
            @if(!empty($syllabus->books))
                <div class="mb-2">
                    <p class="text-xs font-bold">Books:</p>
                    <ol class="text-xs list-decimal list-inside">
                        @foreach($syllabus->books as $book)
                            <li>{{ $book['author'] }}, {{ $book['title'] }}{{ $book['edition'] ? ', ' . $book['edition'] : '' }}{{ $book['publication'] ? ', ' . $book['publication'] : '' }}{{ $book['isbn'] ? ', ISBN: ' . $book['isbn'] : '' }}</li>
                        @endforeach
                    </ol>
                </div>
            @endif
            @if(!empty($syllabus->software_websites))
                <div>
                    <p class="text-xs font-bold">Software/Websites:</p>
                    <ul class="text-xs list-disc list-inside">
                        @foreach($syllabus->software_websites as $sw)
                            <li>{{ $sw['name'] }}{{ $sw['url'] ? ' (' . $sw['url'] . ')' : '' }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        @endif

        <!-- CO-PO Mapping Matrix -->
        @if(!empty($syllabus->mapping_matrix))
        <div class="mb-4">
            <h3 class="font-bold text-sm mb-2">9. CO-PO MAPPING MATRIX</h3>
            <table class="scheme-table" style="font-size: 8pt;">
                <thead>
                    <tr class="bg-gray-100">
                        <th>CO</th>
                        @for($i=1; $i<=7; $i++) <th>PO{{ $i }}</th> @endfor
                        @for($i=1; $i<=4; $i++) <th class="bg-gray-100">PSO{{ $i }}</th> @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($syllabus->mapping_matrix as $mapping)
                        <tr>
                            <td class="font-bold">{{ $mapping['co_code'] }}</td>
                            <td>{{ $mapping['po1'] ?? '-' }}</td>
                            <td>{{ $mapping['po2'] ?? '-' }}</td>
                            <td>{{ $mapping['po3'] ?? '-' }}</td>
                            <td>{{ $mapping['po4'] ?? '-' }}</td>
                            <td>{{ $mapping['po5'] ?? '-' }}</td>
                            <td>{{ $mapping['po6'] ?? '-' }}</td>
                            <td>{{ $mapping['po7'] ?? '-' }}</td>
                            <td class="bg-gray-100">{{ $mapping['pso1'] ?? '-' }}</td>
                            <td class="bg-gray-100">{{ $mapping['pso2'] ?? '-' }}</td>
                            <td class="bg-gray-100">{{ $mapping['pso3'] ?? '-' }}</td>
                            <td class="bg-gray-100">{{ $mapping['pso4'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Question Paper Profile -->
        @if($level !== 4 && !empty($syllabus->question_paper_profile))
        <div class="mb-4">
            <h3 class="font-bold text-sm mb-2">10. QUESTION PAPER PROFILE</h3>
            <table class="text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th>Unit</th>
                        <th>2-Mark Q</th>
                        <th>4-Mark Q</th>
                        <th>Marks</th>
                        <th>1.35x</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalMarks = 0;
                        $saThMax = (int)($syllabus->examination_scheme['sa_th_max'] ?? 0);
                    @endphp
                    @foreach($syllabus->question_paper_profile as $profile)
                        @php 
                            $marks = (int)($profile['two_mark_count'] ?? 0) * 2 + (int)($profile['four_mark_count'] ?? 0) * 4;
                            $totalMarks += $marks;
                            $weightage = $saThMax > 0 ? round(($marks / ($saThMax * 1.35)) * 100, 1) . '%' : '0%';
                        @endphp
                        <tr>
                            <td class="text-center">{{ $profile['unit_no'] }}</td>
                            <td class="text-center">{{ $profile['two_mark_count'] ?? 0 }}</td>
                            <td class="text-center">{{ $profile['four_mark_count'] ?? 0 }}</td>
                            <td class="text-center">{{ $marks }}</td>
                            <td class="text-center">{{ $weightage }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100">
                    <tr>
                        <td colspan="3" class="text-right font-bold">Total:</td>
                        <td class="text-center font-bold">{{ $totalMarks }}</td>
                        <td class="text-center font-bold">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        <div class="page-break"></div>

        <!-- Certification Page -->
        <div class="page pt-8">
            <div class="text-center mb-6">
                <h2 class="text-lg font-bold uppercase">Certificate</h2>
                <p class="text-xs text-gray-600">Syllabus Approval Document</p>
            </div>
            
            <div class="text-xs mb-4">
                <p class="mb-2"><strong>Course Title:</strong> {{ $syllabus->title }}</p>
                <p class="mb-2"><strong>Course Code:</strong> {{ $syllabus->course_code }}</p>
                <p class="mb-2"><strong>Programme:</strong> {{ $syllabus->program_name }}</p>
                <p class="mb-2"><strong>Academic Year:</strong> {{ $syllabus->academic_year ?? \App\Models\Syllabus::getAcademicYear() }}</p>
            </div>
            
            <p class="text-xs text-justify mb-6" style="line-height:1.6;">
                This is to certify that the syllabus for the above-mentioned course has been reviewed and approved 
                by the Curriculum Development Committee (CDC) for implementation in the academic year 
                <span class="font-bold">{{ $syllabus->academic_year ?? \App\Models\Syllabus::getAcademicYear() }}</span>.
            </p>
            
            <div class="grid grid-cols-3 mt-12 text-center text-xs">
                <div style="padding: 0 10px;">
                    <div style="height: 40px;"></div>
                    <div class="border-t pt-1"></div>
                    <p class="font-bold">{{ $syllabus->certification_signatures['hod'] ?? '________________' }}</p>
                    <p>Head of Department</p>
                </div>
                <div style="padding: 0 10px;">
                    <div style="height: 40px;"></div>
                    <div class="border-t pt-1"></div>
                    <p class="font-bold">{{ $syllabus->certification_signatures['principal'] ?? '________________' }}</p>
                    <p>Principal</p>
                </div>
                <div style="padding: 0 10px;">
                    <div style="height: 40px;"></div>
                    <div class="border-t pt-1"></div>
                    <p class="font-bold">{{ $syllabus->certification_signatures['cdc_incharge'] ?? '________________' }}</p>
                    <p>CDC Incharge</p>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
