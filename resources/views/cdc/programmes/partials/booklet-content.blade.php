<div class="booklet-content">
    @php
        $renderHeader = function (string $contentTitle, ?string $line2 = null) use ($programme) {
            $html = '<div class="section-header">';
            $html .= '<div class="programme-line">PROGRAMME - ' . e(strtoupper($programme->name)) . '</div>';
            $html .= '<div class="content-line">' . e(strtoupper($contentTitle)) . '</div>';
            if ($line2) {
                $html .= '<div class="content-line">' . e(strtoupper($line2)) . '</div>';
            }
            $html .= '</div>';

            return $html;
        };

        $showCellValue = function ($value, bool $zeroAsDash = true) {
            if ($value === null || $value === '') {
                return '--';
            }

            if ($zeroAsDash && is_numeric($value) && (float) $value === 0.0) {
                return '--';
            }

            return $value;
        };

        $showCredits = function ($value, bool $allowZero = false) use ($showCellValue) {
            if ($value === null || $value === '') {
                return '--';
            }

            if (! $allowZero && (float) $value === 0.0) {
                return '--';
            }

            return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
        };

        $toWordsMix = function ($section) {
            $level = $section['level'];
            $structure = $section['structure'];

            if ($level->isAudit()) {
                return '';
            }

            $compulsory = (int) ($structure?->compulsory_count ?? 0);
            $elective = (int) ($structure?->elective_count ?? 0);

            if ($compulsory > 0 && $elective > 0) {
                return sprintf('(%02d Compulsory<br>+%02d Electives)', $compulsory, $elective);
            }

            if ($compulsory > 0) {
                return 'Compulsory';
            }

            if ($elective > 0) {
                return sprintf('(%02d Electives)', $elective);
            }

            return '--';
        };

        $auditSections = $levelSections->filter(fn ($section) => $section['level']->isAudit())->values();
        $nonAuditSections = $levelSections->reject(fn ($section) => $section['level']->isAudit())->values();
        $auditStructureRows = $auditSections->pluck('structure')->filter();

        $auditTotals = [
            'offered' => $auditStructureRows->sum('total_courses_offered'),
            'to_complete' => $auditStructureRows->sum('courses_to_complete'),
            'th' => $auditStructureRows->sum('th_hours'),
            'tu' => $auditStructureRows->sum('tu_hours'),
            'pr' => $auditStructureRows->sum('pr_hours'),
            'hours' => $auditStructureRows->sum('total_hours'),
        ];

        $termLookup = collect($samplePathSections)->keyBy('term_number');
        $termNumbers = [1, 2, 3, 4, 5, 6];
        $yearTerms = [
            'First Year' => [1, 2],
            'Second Year' => [3, 4],
            'Third Year' => [5, 6],
        ];

        $courseCountForTerms = function (array $keys) use ($termLookup, $termNumbers) {
            return collect($termNumbers)->sum(function ($termNumber) use ($termLookup, $keys) {
                return collect($keys)->sum(function ($key) use ($termLookup, $termNumber) {
                    return collect($termLookup->get($termNumber)[$key] ?? [])->count();
                });
            });
        };

        $creditsForTermKeys = function (int $termNumber, array $keys) use ($termLookup) {
            return collect($keys)->sum(function ($key) use ($termLookup, $termNumber) {
                return collect($termLookup->get($termNumber)[$key] ?? [])
                    ->sum(fn ($path) => (float) ($path->course?->credits ?? 0));
            });
        };

        $assessmentColspan = max($assessmentLeafColumns->count(), 1);
    @endphp

    <section class="booklet-section">
        {!! $renderHeader('Programme Structure', 'Scheme at a Glance') !!}

        <table class="booklet-table glance-table">
            <thead>
                <tr>
                    <th>Level</th>
                    <th>Name of Level</th>
                    <th>Total Number of Courses offered</th>
                    <th>Number of Courses to be completed</th>
                    <th>TH</th>
                    <th>TU</th>
                    <th>PR</th>
                    <th>Total Hours</th>
                    <th>Total Credits</th>
                    <th>Marks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($nonAuditSections as $section)
                    @php
                        $level = $section['level'];
                        $structure = $section['structure'];
                    @endphp
                    <tr>
                        <td>{{ 'Level-' . $level->level_code }}</td>
                        <td>{{ $level->level_name }}</td>
                        <td class="text-center">{{ $showCellValue($structure?->total_courses_offered, false) }}</td>
                        <td class="text-center">
                            <div>{{ $showCellValue($structure?->courses_to_complete, false) }}</div>
                            <div class="muted-line">{!! $toWordsMix($section) !!}</div>
                        </td>
                        <td class="text-center">{{ $showCellValue($structure?->th_hours) }}</td>
                        <td class="text-center">{{ $showCellValue($structure?->tu_hours) }}</td>
                        <td class="text-center">{{ $showCellValue($structure?->pr_hours) }}</td>
                        <td class="text-center">{{ $showCellValue($structure?->total_hours) }}</td>
                        <td class="text-center">{{ $showCredits($structure?->total_credits) }}</td>
                        <td class="text-center">{{ $showCellValue($structure?->total_marks) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td></td>
                    <td>TOTAL</td>
                    <td class="text-center">{{ $showCellValue($structureTotals['offered'], false) }}</td>
                    <td class="text-center">
                        <div>{{ $structureTotals['compulsory'] }} Compulsory</div>
                        <div>+{{ $structureTotals['elective_to_complete'] }} Electives</div>
                    </td>
                    <td class="text-center">{{ $showCellValue($structureTotals['th']) }}</td>
                    <td class="text-center">{{ $showCellValue($structureTotals['tu']) }}</td>
                    <td class="text-center">{{ $showCellValue($structureTotals['pr']) }}</td>
                    <td class="text-center">{{ $showCellValue($structureTotals['hours']) }}</td>
                    <td class="text-center">{{ $showCredits($structureTotals['credits']) }}</td>
                    <td class="text-center">{{ $showCellValue($structureTotals['marks']) }}</td>
                </tr>
                @if ($auditSections->isNotEmpty())
                    <tr>
                        <td></td>
                        <td>Audit Courses</td>
                        <td class="text-center">{{ $showCellValue($auditTotals['offered'], false) }}</td>
                        <td class="text-center">{{ $showCellValue($auditTotals['to_complete'], false) }}</td>
                        <td class="text-center">{{ $showCellValue($auditTotals['th']) }}</td>
                        <td class="text-center">{{ $showCellValue($auditTotals['tu']) }}</td>
                        <td class="text-center">{{ $showCellValue($auditTotals['pr']) }}</td>
                        <td class="text-center">{{ $showCellValue($auditTotals['hours']) }}</td>
                        <td class="text-center">--</td>
                        <td class="text-center">--</td>
                    </tr>
                    <tr class="total-row">
                        <td></td>
                        <td>Grand Total</td>
                        <td class="text-center">{{ $showCellValue($structureTotals['offered'] + $auditTotals['offered'], false) }}</td>
                        <td class="text-center">{{ $showCellValue($structureTotals['to_complete'] + $auditTotals['to_complete'], false) }}</td>
                        <td class="text-center">{{ $showCellValue($structureTotals['th'] + $auditTotals['th']) }}</td>
                        <td class="text-center">{{ $showCellValue($structureTotals['tu'] + $auditTotals['tu']) }}</td>
                        <td class="text-center">{{ $showCellValue($structureTotals['pr'] + $auditTotals['pr']) }}</td>
                        <td class="text-center">{{ $showCellValue($structureTotals['hours'] + $auditTotals['hours']) }}</td>
                        <td class="text-center">{{ $showCredits($structureTotals['credits']) }}</td>
                        <td class="text-center">{{ $showCellValue($structureTotals['marks']) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="booklet-note">
            <strong>Abbreviations :</strong> TH : Theory, TU : Tutorial, PR : Practical.
        </div>
    </section>

    @foreach ($levelSections as $section)
        @php
            $level = $section['level'];
            $structure = $section['structure'];
            $isAudit = $level->isAudit();
            $serial = 1;

            $levelRows = $section['courses']->values();
            $assessmentTotals = $assessmentLeafColumns->mapWithKeys(function ($leaf) use ($levelRows) {
                return [$leaf['id'] => $levelRows->sum(fn ($row) => (int) ($row['assessment_marks']->get($leaf['id']) ?? 0))];
            });
        @endphp

        <section class="booklet-section page-break">
            {!! $renderHeader('Programme Structure', $isAudit ? 'Audit Courses' : 'Level - ' . $level->level_code . ' ' . $level->level_name) !!}

            <table class="booklet-table level-table">
                <thead>
                    <tr>
                        <th rowspan="3">Sr. No.</th>
                        <th rowspan="3">Course Code</th>
                        <th rowspan="3">Course Title</th>
                        <th rowspan="3">Course Abbr.</th>
                        <th colspan="{{ $isAudit ? 4 : 5 }}">TEACHING SCHEME</th>
                        @if (! $isAudit)
                            <th colspan="{{ $assessmentColspan + 2 }}">EXAMINATION SCHEME</th>
                        @endif
                    </tr>
                    <tr>
                        <th colspan="4">Hours per Week</th>
                        @if (! $isAudit)
                            <th rowspan="2">Total Credits</th>
                            <th rowspan="2">Paper Duration</th>
                            <th colspan="{{ $assessmentColspan }}">Assessment Scheme</th>
                            <th rowspan="2">Total</th>
                        @endif
                    </tr>
                    <tr>
                        <th>TH</th>
                        <th>TU</th>
                        <th>PR</th>
                        <th>Total Hours</th>
                        @if (! $isAudit)
                            @forelse ($assessmentLeafColumns as $leaf)
                                <th>{{ $leaf['name'] }}</th>
                            @empty
                                <th>Scheme Assessment</th>
                            @endforelse
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php $previousElectiveGroup = null; @endphp

                    @forelse ($levelRows as $row)
                        @php
                            $course = $row['course'];
                            $isElective = $course->course_type === \App\Models\Course::TYPE_ELECTIVE;
                            $electiveGroup = $isElective ? ($course->elective_group ?: 'Elective') : null;
                        @endphp

                        @if (! $isAudit && $isElective && $electiveGroup !== $previousElectiveGroup)
                            <tr class="group-row">
                                <td colspan="{{ 10 + $assessmentColspan + 1 }}">{{ $electiveGroup }} : Offered elective subjects</td>
                            </tr>
                            @php $previousElectiveGroup = $electiveGroup; @endphp
                        @elseif (! $isElective)
                            @php $previousElectiveGroup = null; @endphp
                        @endif

                        <tr>
                            <td class="text-center">{{ $serial++ }}</td>
                            <td class="text-center">{{ $showCellValue($course->course_code, false) }}</td>
                            <td class="text-left">{{ $showCellValue($course->course_title, false) }}</td>
                            <td class="text-center">{{ $showCellValue($course->course_abbr, false) }}</td>
                            <td class="text-center">{{ $showCellValue($course->th_hours) }}</td>
                            <td class="text-center">{{ $showCellValue($course->tu_hours) }}</td>
                            <td class="text-center">{{ $showCellValue($course->pr_hours) }}</td>
                            <td class="text-center">{{ $showCellValue($course->total_hours) }}</td>

                            @if (! $isAudit)
                                <td class="text-center">{{ $showCredits($course->credits) }}</td>
                                <td class="text-center">{{ $showCellValue($course->theory_paper_hrs) }}</td>
                                @forelse ($assessmentLeafColumns as $leaf)
                                    <td class="text-center">{{ $showCellValue($row['assessment_marks']->get($leaf['id'])) }}</td>
                                @empty
                                    <td class="text-center">--</td>
                                @endforelse
                                <td class="text-center">{{ $showCellValue($course->total_marks) }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAudit ? 8 : 11 + $assessmentColspan }}" class="booklet-empty">No courses defined for this level.</td>
                        </tr>
                    @endforelse

                    <tr class="total-row">
                        <td colspan="4">TOTAL</td>
                        <td class="text-center">{{ $showCellValue($levelRows->sum(fn ($row) => (int) $row['course']->th_hours)) }}</td>
                        <td class="text-center">{{ $showCellValue($levelRows->sum(fn ($row) => (int) $row['course']->tu_hours)) }}</td>
                        <td class="text-center">{{ $showCellValue($levelRows->sum(fn ($row) => (int) $row['course']->pr_hours)) }}</td>
                        <td class="text-center">{{ $showCellValue($levelRows->sum(fn ($row) => (int) $row['course']->total_hours)) }}</td>

                        @if (! $isAudit)
                            <td class="text-center">{{ $showCredits($levelRows->sum(fn ($row) => (float) $row['course']->credits)) }}</td>
                            <td class="text-center">--</td>
                            @forelse ($assessmentLeafColumns as $leaf)
                                <td class="text-center">{{ $showCellValue($assessmentTotals->get($leaf['id'])) }}</td>
                            @empty
                                <td class="text-center">--</td>
                            @endforelse
                            <td class="text-center">{{ $showCellValue($levelRows->sum(fn ($row) => (int) $row['course']->total_marks)) }}</td>
                        @endif
                    </tr>
                </tbody>
            </table>

            <div class="booklet-meta-lines">
                @if ($isAudit)
                    <div>Audit Courses : Total Courses : {{ $showCellValue($section['defined_courses'], false) }}</div>
                    <div>Total Credits : Nil</div>
                    <div>Total Marks : --</div>
                @else
                    <div>Level : {{ $level->level_code }}</div>
                    <div>Total Courses : {{ $showCellValue($section['defined_courses'], false) }}</div>
                    <div>Total Credits : {{ $showCredits($structure?->total_credits) }}</div>
                    <div>Total Marks : {{ $showCellValue($structure?->total_marks) }}</div>
                @endif
            </div>

            <div class="booklet-note">
                <div><strong>Abbreviations:</strong> TH : Theory, TU : Tutorial, PR : Practical.</div>
                @if (! $isAudit)
                    <div><strong>Assessment of PR / OR / TW :</strong> Assessment columns are shown as defined in the active scheme.</div>
                @endif
            </div>
        </section>
    @endforeach

    <section class="booklet-section page-break">
        {!! $renderHeader('Courses for Award of Class') !!}

        @if ($awardClassCourses->isEmpty())
            <div class="booklet-empty">No award class courses have been selected yet.</div>
        @else
            @php
                $awardAssessmentTotals = $assessmentLeafColumns->mapWithKeys(function ($leaf) use ($awardClassCourses) {
                    return [
                        $leaf['id'] => $awardClassCourses->sum(function ($item) use ($leaf) {
                            return (int) (($item->course?->assessments?->pluck('max_marks', 'component_id')->get($leaf['id'])) ?? 0);
                        }),
                    ];
                });
                $awardSerial = 1;
                $previousElectiveGroup = null;
            @endphp

            <table class="booklet-table level-table">
                <thead>
                    <tr>
                        <th rowspan="3">Sr. No.</th>
                        <th rowspan="3">Course Code</th>
                        <th rowspan="3">Course Title</th>
                        <th rowspan="3">Course Abbr.</th>
                        <th colspan="5">TEACHING SCHEME</th>
                        <th colspan="{{ $assessmentColspan + 2 }}">EXAMINATION SCHEME</th>
                    </tr>
                    <tr>
                        <th colspan="4">Hours per Week</th>
                        <th rowspan="2">Total Credits</th>
                        <th rowspan="2">Paper Duration</th>
                        <th colspan="{{ $assessmentColspan }}">Assessment Scheme</th>
                        <th rowspan="2">Total</th>
                    </tr>
                    <tr>
                        <th>TH</th>
                        <th>TU</th>
                        <th>PR</th>
                        <th>Total Hours</th>
                        @forelse ($assessmentLeafColumns as $leaf)
                            <th>{{ $leaf['name'] }}</th>
                        @empty
                            <th>Scheme Assessment</th>
                        @endforelse
                    </tr>
                </thead>
                <tbody>
                    @foreach ($awardClassCourses as $item)
                        @php
                            $course = $item->course;
                            $isElective = $course?->course_type === \App\Models\Course::TYPE_ELECTIVE;
                            $electiveGroup = $isElective ? ($course?->elective_group ?: 'Elective') : null;
                            $awardMarks = $course?->assessments?->pluck('max_marks', 'component_id') ?? collect();
                        @endphp

                        @if ($isElective && $electiveGroup !== $previousElectiveGroup)
                            <tr class="group-row">
                                <td colspan="{{ 11 + $assessmentColspan }}">{{ $electiveGroup }} : Selected elective subjects for Award of Class</td>
                            </tr>
                            @php $previousElectiveGroup = $electiveGroup; @endphp
                        @elseif (! $isElective)
                            @php $previousElectiveGroup = null; @endphp
                        @endif

                        <tr>
                            <td class="text-center">{{ $awardSerial++ }}</td>
                            <td class="text-center">{{ $showCellValue($course?->course_code, false) }}</td>
                            <td class="text-left">{{ $showCellValue($course?->course_title, false) }}</td>
                            <td class="text-center">{{ $showCellValue($course?->course_abbr, false) }}</td>
                            <td class="text-center">{{ $showCellValue($course?->th_hours) }}</td>
                            <td class="text-center">{{ $showCellValue($course?->tu_hours) }}</td>
                            <td class="text-center">{{ $showCellValue($course?->pr_hours) }}</td>
                            <td class="text-center">{{ $showCellValue($course?->total_hours) }}</td>
                            <td class="text-center">{{ $showCredits($course?->credits) }}</td>
                            <td class="text-center">{{ $showCellValue($course?->theory_paper_hrs) }}</td>
                            @forelse ($assessmentLeafColumns as $leaf)
                                <td class="text-center">{{ $showCellValue($awardMarks->get($leaf['id'])) }}</td>
                            @empty
                                <td class="text-center">--</td>
                            @endforelse
                            <td class="text-center">{{ $showCellValue($course?->total_marks) }}</td>
                        </tr>
                    @endforeach

                    <tr class="total-row">
                        <td colspan="4">TOTAL</td>
                        <td class="text-center">{{ $showCellValue($awardClassCourses->sum(fn ($item) => (int) ($item->course?->th_hours ?? 0))) }}</td>
                        <td class="text-center">{{ $showCellValue($awardClassCourses->sum(fn ($item) => (int) ($item->course?->tu_hours ?? 0))) }}</td>
                        <td class="text-center">{{ $showCellValue($awardClassCourses->sum(fn ($item) => (int) ($item->course?->pr_hours ?? 0))) }}</td>
                        <td class="text-center">{{ $showCellValue($awardClassCourses->sum(fn ($item) => (int) ($item->course?->total_hours ?? 0))) }}</td>
                        <td class="text-center">{{ $showCredits($awardClassCourses->sum(fn ($item) => (float) ($item->course?->credits ?? 0))) }}</td>
                        <td class="text-center">--</td>
                        @forelse ($assessmentLeafColumns as $leaf)
                            <td class="text-center">{{ $showCellValue($awardAssessmentTotals->get($leaf['id'])) }}</td>
                        @empty
                            <td class="text-center">--</td>
                        @endforelse
                        <td class="text-center">{{ $showCellValue($awardClassCourses->sum(fn ($item) => (int) ($item->course?->total_marks ?? 0))) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="booklet-meta-lines inline">
                <span>Total Courses : {{ $showCellValue($awardClassCourses->count(), false) }}</span>
                <span>Total Credits : {{ $showCredits($awardClassCourses->sum(fn ($item) => (float) ($item->course?->credits ?? 0))) }}</span>
                <span>Total Marks : {{ $showCellValue($awardClassCourses->sum(fn ($item) => (int) ($item->course?->total_marks ?? 0))) }}</span>
            </div>

            <div class="booklet-note">
                <div><strong>Assessment of PR / OR / TW :</strong> Assessment columns are shown as defined in the active scheme.</div>
            </div>
        @endif
    </section>

    @if ($samplePathSections->isNotEmpty())
        <section class="booklet-section page-break">
            {!! $renderHeader('Sample Path') !!}

            <table class="booklet-table sample-path-table">
                <thead>
                    <tr>
                        <th rowspan="2">Nature of Course</th>
                        @foreach ($yearTerms as $yearLabel => $terms)
                            <th colspan="2">{{ $yearLabel }}</th>
                        @endforeach
                        <th rowspan="2">Total</th>
                    </tr>
                    <tr>
                        @foreach ($termNumbers as $termNumber)
                            <th>{{ \App\Models\SamplePath::termLabel($termNumber) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Compulsory</strong></td>
                        @foreach ($termNumbers as $termNumber)
                            @php $term = $termLookup->get($termNumber); @endphp
                            <td>
                                @forelse (($term['compulsory'] ?? collect()) as $path)
                                    <div>{{ $path->course?->course_code }} ({{ $showCredits($path->course?->credits, true) }}) {{ $path->course?->course_abbr }}</div>
                                @empty
                                    --
                                @endforelse
                            </td>
                        @endforeach
                        <td class="text-center">{{ $showCellValue($courseCountForTerms(['compulsory']), false) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Elective</strong></td>
                        @foreach ($termNumbers as $termNumber)
                            @php
                                $term = $termLookup->get($termNumber);
                                $previousGroup = null;
                            @endphp
                            <td>
                                @forelse (($term['elective'] ?? collect()) as $path)
                                    @php $group = $path->course?->elective_group ?: 'Elective'; @endphp
                                    @if ($group !== $previousGroup)
                                        <div><strong>{{ $group }}</strong></div>
                                        @php $previousGroup = $group; @endphp
                                    @endif
                                    <div>{{ $path->course?->course_code }} ({{ $showCredits($path->course?->credits, true) }}) {{ $path->course?->course_abbr }}</div>
                                @empty
                                    --
                                @endforelse
                            </td>
                        @endforeach
                        <td class="text-center">{{ $showCellValue($courseCountForTerms(['elective']), false) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Audit Courses</strong></td>
                        @foreach ($termNumbers as $termNumber)
                            @php $term = $termLookup->get($termNumber); @endphp
                            <td>
                                @forelse (($term['audit'] ?? collect()) as $path)
                                    <div>{{ $path->course?->course_code }} ({{ $showCredits($path->course?->credits, true) }}) {{ $path->course?->course_abbr }}</div>
                                @empty
                                    --
                                @endforelse
                            </td>
                        @endforeach
                        <td class="text-center">{{ $showCellValue($courseCountForTerms(['audit']), false) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total Courses</td>
                        @foreach ($termNumbers as $termNumber)
                            @php
                                $count = collect($termLookup->get($termNumber)['compulsory'] ?? [])->count()
                                    + collect($termLookup->get($termNumber)['elective'] ?? [])->count()
                                    + collect($termLookup->get($termNumber)['audit'] ?? [])->count();
                            @endphp
                            <td class="text-center">{{ $showCellValue($count, false) }}</td>
                        @endforeach
                        <td class="text-center">{{ $showCellValue($courseCountForTerms(['compulsory', 'elective', 'audit']), false) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total Credits (Compulsory + Elective)</td>
                        @foreach ($termNumbers as $termNumber)
                            @php $creditTotal = $creditsForTermKeys($termNumber, ['compulsory', 'elective']); @endphp
                            <td class="text-center">{{ $showCredits($creditTotal) }}</td>
                        @endforeach
                        <td class="text-center">{{ $showCredits(collect($termNumbers)->sum(fn ($termNumber) => $creditsForTermKeys($termNumber, ['compulsory', 'elective']))) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="booklet-note">
                Note: Figures in brackets indicate course credits.
            </div>
        </section>
    @endif
</div>
