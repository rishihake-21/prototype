<div class="booklet-content">
    <div class="booklet-cover">
        <div class="booklet-kicker">{{ $institutionName }}</div>
        <h1 class="booklet-title">Programme - {{ strtoupper($programme->name) }}</h1>
        <p class="booklet-subtitle">Curriculum Structure and Course Definition Summary</p>

        <table class="booklet-meta-table">
            <tbody>
                <tr>
                    <th>Programme Code</th>
                    <td>{{ $programme->code }}</td>
                    <th>Academic Year</th>
                    <td>{{ $programme->academic_year }}</td>
                </tr>
                <tr>
                    <th>Department</th>
                    <td>{{ $programme->department?->name ?? '-' }}</td>
                    <th>Scheme</th>
                    <td>{{ $programme->scheme?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ ucfirst($programme->status) }}</td>
                    <th>Prepared By</th>
                    <td>{{ $programme->creator?->name ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="booklet-stats">
            <div class="booklet-stat">
                <span class="booklet-stat-label">Levels</span>
                <strong>{{ $levelSections->count() }}</strong>
            </div>
            <div class="booklet-stat">
                <span class="booklet-stat-label">Defined Courses</span>
                <strong>{{ $courseTotals['defined'] }}</strong>
            </div>
            <div class="booklet-stat">
                <span class="booklet-stat-label">Common Courses</span>
                <strong>{{ $courseTotals['common'] }}</strong>
            </div>
            <div class="booklet-stat">
                <span class="booklet-stat-label">Elective Pool</span>
                <strong>{{ $courseTotals['electives'] }}</strong>
            </div>
        </div>
    </div>

    <section class="booklet-section">
        <div class="booklet-section-head">
            <div>
                <h2>Scheme at a Glance</h2>
                <p>CDC planning summary by level.</p>
            </div>
        </div>

        <table class="booklet-table compact">
            <thead>
                <tr>
                    <th>Level</th>
                    <th>Name of Level</th>
                    <th>Offered</th>
                    <th>To Complete</th>
                    <th>Compulsory</th>
                    <th>Elective Offered</th>
                    <th>Elective To Complete</th>
                    <th>TH</th>
                    <th>TU</th>
                    <th>PR</th>
                    <th>Total Hours</th>
                    <th>Total Credits</th>
                    <th>Marks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($levelSections as $section)
                    @php
                        $level = $section['level'];
                        $structure = $section['structure'];
                    @endphp
                    <tr>
                        <td>{{ $level->level_code }}</td>
                        <td>{{ $level->level_name }}</td>
                        <td class="text-right">{{ $structure?->total_courses_offered ?? '-' }}</td>
                        <td class="text-right">{{ $structure?->courses_to_complete ?? '-' }}</td>
                        <td class="text-right">{{ $structure?->compulsory_count ?? '-' }}</td>
                        <td class="text-right">{{ $structure?->elective_offered_count ?? '-' }}</td>
                        <td class="text-right">{{ $structure?->elective_count ?? '-' }}</td>
                        <td class="text-right">{{ $structure?->th_hours ?? '-' }}</td>
                        <td class="text-right">{{ $structure?->tu_hours ?? '-' }}</td>
                        <td class="text-right">{{ $structure?->pr_hours ?? '-' }}</td>
                        <td class="text-right">{{ $structure?->total_hours ?? '-' }}</td>
                        <td class="text-right">{{ $structure ? number_format((float) $structure->total_credits, 2) : '-' }}</td>
                        <td class="text-right">{{ $structure?->total_marks ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-left">Total</th>
                    <th class="text-right">{{ $structureTotals['offered'] }}</th>
                    <th class="text-right">{{ $structureTotals['to_complete'] }}</th>
                    <th class="text-right">{{ $structureTotals['compulsory'] }}</th>
                    <th class="text-right">{{ $structureTotals['elective_offered'] }}</th>
                    <th class="text-right">{{ $structureTotals['elective_to_complete'] }}</th>
                    <th class="text-right">{{ $structureTotals['th'] }}</th>
                    <th class="text-right">{{ $structureTotals['tu'] }}</th>
                    <th class="text-right">{{ $structureTotals['pr'] }}</th>
                    <th class="text-right">{{ $structureTotals['hours'] }}</th>
                    <th class="text-right">{{ number_format((float) $structureTotals['credits'], 2) }}</th>
                    <th class="text-right">{{ $structureTotals['marks'] }}</th>
                </tr>
            </tfoot>
        </table>
    </section>

    <section class="booklet-section">
        <div class="booklet-section-head">
            <div>
                <h2>Elective Pool and HOD Selection</h2>
                <p>Only HOD-selected electives move into the next syllabus cycle.</p>
            </div>
        </div>

        <table class="booklet-table compact">
            <thead>
                <tr>
                    <th>Level</th>
                    <th>Pool Limit</th>
                    <th>Pool Defined</th>
                    <th>HOD To Pick</th>
                    <th>Selected</th>
                    <th>Remaining</th>
                    <th>Selected Electives</th>
                </tr>
            </thead>
            <tbody>
                @foreach($levelSections as $section)
                    @php
                        $structure = $section['structure'];
                        $selectedCodes = $section['selected_elective_codes']->isNotEmpty()
                            ? $section['selected_elective_codes']->join(', ')
                            : 'Pending HOD selection';
                    @endphp
                    <tr>
                        <td>{{ $section['level']->level_code }}</td>
                        <td class="text-right">{{ $structure?->elective_offered_count ?? 0 }}</td>
                        <td class="text-right">{{ $section['elective_pool_defined'] }}</td>
                        <td class="text-right">{{ $structure?->elective_count ?? 0 }}</td>
                        <td class="text-right">{{ $section['elective_selected_count'] }}</td>
                        <td class="text-right">{{ $section['elective_remaining'] }}</td>
                        <td>{{ $selectedCodes }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    @foreach($levelSections as $section)
        <section class="booklet-section keep-together">
            <div class="booklet-section-head">
                <div>
                    <h2>{{ $section['level']->level_code }} - {{ $section['level']->level_name }}</h2>
                    <p>
                        Defined {{ $section['defined_courses'] }} of {{ $section['offered_courses'] ?: 0 }} planned courses.
                        Credits planned: {{ number_format((float) ($section['structure']?->total_credits ?? 0), 2) }}.
                    </p>
                </div>
            </div>

            @if($section['courses']->isEmpty())
                <div class="booklet-empty">No course definitions have been added for this level yet.</div>
            @else
                <table class="booklet-table course-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>Type</th>
                            <th>Elective Group</th>
                            <th>TH</th>
                            <th>TU</th>
                            <th>PR</th>
                            <th>Hours</th>
                            <th>Credits</th>
                            <th>Marks</th>
                            <th>Applicability</th>
                            <th>HOD Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section['courses'] as $row)
                            @php
                                $course = $row['course'];
                                $availability = $row['availability']->isNotEmpty() ? $row['availability']->join(', ') : '-';
                                $selectionText = $course->course_type === \App\Models\Course::TYPE_ELECTIVE
                                    ? ($row['selected_by']->isNotEmpty() ? 'Selected by ' . $row['selected_by']->join(', ') : 'Pending selection')
                                    : 'Not required';
                            @endphp
                            <tr>
                                <td class="text-right">{{ $row['index'] }}</td>
                                <td>{{ $course->course_code }}</td>
                                <td>{{ $course->course_title }}</td>
                                <td>{{ ucfirst($course->course_type) }}</td>
                                <td>{{ $course->elective_group ?: '-' }}</td>
                                <td class="text-right">{{ $course->th_hours }}</td>
                                <td class="text-right">{{ $course->tu_hours }}</td>
                                <td class="text-right">{{ $course->pr_hours }}</td>
                                <td class="text-right">{{ $course->total_hours }}</td>
                                <td class="text-right">{{ number_format((float) $course->credits, 2) }}</td>
                                <td class="text-right">{{ $course->total_marks }}</td>
                                <td>{{ $availability }}</td>
                                <td>{{ $selectionText }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    @endforeach
</div>
