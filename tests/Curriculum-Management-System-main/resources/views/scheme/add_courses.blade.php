@extends('layouts.app')

@section('content')

<h2 class="mb-4">Add Courses - {{ $schemeLevel->level_name }}</h2>


<div class="mb-4">

    <div class="d-flex justify-content-between mb-1">
        <span><strong>Level {{ $currentStep }} of {{ $totalSteps }}</strong></span>
        <span>{{ round((($currentStep - 1) / $totalSteps) * 100) }}% Complete</span>
    </div>

    <div class="progress" style="height: 10px;">
        <div class="progress-bar bg-success"
             role="progressbar"
             style="width: {{ (($currentStep - 1) / $totalSteps) * 100 }}%;">
        </div>
    </div>

</div>

@if($schemeLevel->is_audit)
    <div class="alert alert-info">
        This is an <strong>Audit Level</strong>. Credits and Marks are not applicable.
    </div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3"><strong>Scheme:</strong> {{ $scheme->programme_name }}</div>
            <div class="col-md-3"><strong>Courses:</strong> {{ $schemeLevel->courses_offered }}</div>
            <div class="col-md-2"><strong>TH:</strong> {{ $schemeLevel->th }}</div>
            <div class="col-md-2"><strong>TU:</strong> {{ $schemeLevel->tu }}</div>
            <div class="col-md-2"><strong>PR:</strong> {{ $schemeLevel->pr }}</div>
        </div>

        @if(!$schemeLevel->is_audit)
        <div class="row text-center mt-2">
            <div class="col-md-6"><strong>Credits:</strong> {{ $schemeLevel->total_credits }}</div>
            <div class="col-md-6"><strong>Marks:</strong> {{ $schemeLevel->marks }}</div>
        </div>
        @endif
    </div>
</div>

<form method="POST" action="{{ route('scheme.storeCourses', ['scheme' => $scheme->id, 'level' => $schemeLevel->id]) }}">
@csrf

<div class="course-container">

@for($i = 0; $i < $schemeLevel->courses_offered; $i++)

<div class="card mb-3 shadow-sm course-card">

    <div class="card-header bg-dark text-white">
        Course {{ $i + 1 }}
    </div>

    <div class="card-body">

        <!-- BASIC INFO -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Course Code</label>
                <input type="text" name="courses[{{$i}}][course_code]" class="form-control">
            </div>

            <div class="col-md-5">
                <label>Course Title</label>
                <input type="text" name="courses[{{$i}}][course_title]" class="form-control">
            </div>

            <div class="col-md-2">
                <label>Abbreviation</label>
                <input type="text" name="courses[{{$i}}][abbr]" class="form-control">
            </div>

            <div class="col-md-2">
                <label>Type</label>
                <select name="courses[{{$i}}][type]" class="form-control">
                    <option value="compulsory">Compulsory</option>
                    <option value="elective">Elective</option>
                </select>
            </div>

            <div class="form-check mt-2">
                <input type="checkbox"
                    name="courses[{{$i}}][is_award]"
                    value="1"
                    class="form-check-input">

                <label class="form-check-label">
                    Include in Award Class Calculation
                </label>
            </div>

            <div class="col-md-2">
            <label>Year</label>

            <select name="courses[{{$i}}][year]" class="form-control">

            <option value="1">First Year</option>
            <option value="2">Second Year</option>
            <option value="3">Third Year</option>

            </select>
            </div>
            <div class="col-md-2">

            <label>Term</label>

            <select name="courses[{{$i}}][term]" class="form-control">

            <option value="odd">Odd</option>
            <option value="even">Even</option>

            </select>

            </div>
        </div>

        <!-- TEACHING SCHEME -->
        <div class="border rounded p-3 mb-3 bg-light">
            <h6 class="mb-3">Teaching Scheme</h6>
            <div class="row">

                <div class="col-md-2">
                    <label>TH</label>
                    <input type="number" name="courses[{{$i}}][th]" class="form-control th-input">
                </div>

                <div class="col-md-2">
                    <label>TU</label>
                    <input type="number" name="courses[{{$i}}][tu]" class="form-control tu-input">
                </div>

                <div class="col-md-2">
                    <label>PR</label>
                    <input type="number" name="courses[{{$i}}][pr]" class="form-control pr-input">
                </div>

                <div class="col-md-3">
                    <label>Total Hours</label>
                    <input type="number" name="courses[{{$i}}][total_hours]" class="form-control total-hours-input" readonly>
                </div>

            @if(!$schemeLevel->is_audit)
                <div class="col-md-3">
                    <label>Credits</label>
                    <input type="number" name="courses[{{$i}}][credits]" class="form-control">
                </div>
            @endif

            </div>
        </div>

        <!-- EXAMINATION SCHEME -->
        @if(!$schemeLevel->is_audit)
        <div class="border rounded p-3 bg-white">
            <h6 class="mb-3">Examination Scheme</h6>
            <div class="row">

                <div class="col-md-2">
                    <label>Theory Hours</label>
                    <input type="number" name="courses[{{$i}}][theory_hours]" class="form-control">
                </div>

                <div class="col-md-2">
                    <label>Theory Marks</label>
                    <input type="number" name="courses[{{$i}}][theory_marks]" class="form-control theory-marks">
                </div>

                <div class="col-md-2">
                    <label>Test Marks</label>
                    <input type="number" name="courses[{{$i}}][test_marks]" class="form-control test-marks">
                </div>

                <div class="col-md-2">
                    <label>PR Marks</label>
                    <input type="number" name="courses[{{$i}}][pr_marks]" class="form-control pr-marks">
                </div>

                <div class="col-md-2">
                    <label>OR Marks</label>
                    <input type="number" name="courses[{{$i}}][or_marks]" class="form-control or-marks">
                </div>

                <div class="col-md-2">
                    <label>TW Marks</label>
                    <input type="number" name="courses[{{$i}}][tw_marks]" class="form-control tw-marks">
                </div>

                <div class="col-md-3 mt-3">
                    <label>Exam Total</label>
                    <input type="number" name="courses[{{$i}}][exam_total]" class="form-control exam-total" readonly>
                </div>

            </div>
        </div>
        @endif
    </div>
</div>

@endfor
<div class="card mt-4 border-primary">
    <div class="card-header bg-primary text-white">
        Level Summary
    </div>

    <div class="card-body">

        <h6 class="mb-3">Teaching Totals</h6>
        <div class="row mb-4">
            <div class="col-md-2">
                <strong>TH:</strong> <span id="totalTH">0</span>
            </div>
            <div class="col-md-2">
                <strong>TU:</strong> <span id="totalTU">0</span>
            </div>
            <div class="col-md-2">
                <strong>PR:</strong> <span id="totalPR">0</span>
            </div>
            <div class="col-md-3">
                <strong>Total Hours:</strong> <span id="totalHours">0</span>
            </div>
            <div class="col-md-3">
                <strong>Credits:</strong> <span id="totalCredits">0</span>
            </div>
        </div>

        <h6 class="mb-3">Examination Totals</h6>
        <div class="row">
            <div class="col-md-2">
                <strong>Theory:</strong> <span id="sumTheory">0</span>
            </div>
            <div class="col-md-2">
                <strong>Test:</strong> <span id="sumTest">0</span>
            </div>
            <div class="col-md-2">
                <strong>PR:</strong> <span id="sumPRMarks">0</span>
            </div>
            <div class="col-md-2">
                <strong>OR:</strong> <span id="sumOR">0</span>
            </div>
            <div class="col-md-2">
                <strong>TW:</strong> <span id="sumTW">0</span>
            </div>
            <div class="col-md-2">
                <strong>Grand Total:</strong> <span id="sumExamTotal">0</span>
            </div>
        </div>

    </div>
</div>
</div>

<div id="validationAlert" class="alert alert-danger mt-3 d-none">
    <ul id="validationList" class="mb-0"></ul>
</div>
<button type="submit" id="saveBtn" class="btn btn-success mt-3 px-5">
    Save Courses
</button>

</form>

<style>
.scheme-table th {
    font-size: 13px;
    vertical-align: middle;
    padding: 6px !important;
}

.scheme-table td {
    padding: 4px !important;
}

.input-box {
    height: 32px !important;
    font-size: 14px !important;
    padding: 4px 6px !important;
    text-align: center;
}

.input-box.text-start {
    text-align: left !important;
}

.scheme-table input {
    min-width: 70px;
}

.scheme-table select {
    height: 32px !important;
    font-size: 14px !important;
}

.table thead th {
    white-space: nowrap;
}
</style>

@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {

    const saveBtn = document.getElementById('saveBtn');
    const alertBox = document.getElementById('validationAlert');
    const validationList = document.getElementById('validationList');

    const maxTH = {{ $schemeLevel->th }};
    const maxTU = {{ $schemeLevel->tu }};
    const maxPR = {{ $schemeLevel->pr }};
    const maxCredits = {{ $schemeLevel->total_credits ?? 0 }};
    const maxMarks = {{ $schemeLevel->marks ?? 0 }};
    const isAudit = {{ $schemeLevel->is_audit ? 'true' : 'false' }};

    saveBtn.disabled = true;

    // ============================
    // 1️⃣ ROW CALCULATION
    // ============================
    function calculateRow(card) {

        let th = Number(card.querySelector('.th-input')?.value) || 0;
        let tu = Number(card.querySelector('.tu-input')?.value) || 0;
        let pr = Number(card.querySelector('.pr-input')?.value) || 0;

        let totalHours = th + tu + pr;

        let hoursInput = card.querySelector('.total-hours-input');
        if (hoursInput) hoursInput.value = totalHours;

        let theory = Number(card.querySelector('.theory-marks')?.value) || 0;
        let test   = Number(card.querySelector('.test-marks')?.value) || 0;
        let prMarks= Number(card.querySelector('.pr-marks')?.value) || 0;
        let or     = Number(card.querySelector('.or-marks')?.value) || 0;
        let tw     = Number(card.querySelector('.tw-marks')?.value) || 0;

        let examTotal = theory + test + prMarks + or + tw;

        let examInput = card.querySelector('.exam-total');
        if (examInput) examInput.value = examTotal;
    }

    // ============================
    // 2️⃣ LEVEL TOTALS
    // ============================
    function calculateLevelTotals() {

        let totalTH = 0, totalTU = 0, totalPR = 0;
        let totalHours = 0, totalCredits = 0;
        let totalMarks = 0;

        document.querySelectorAll('.course-card').forEach(card => {

            totalTH += Number(card.querySelector('.th-input')?.value) || 0;
            totalTU += Number(card.querySelector('.tu-input')?.value) || 0;
            totalPR += Number(card.querySelector('.pr-input')?.value) || 0;

            totalHours += Number(card.querySelector('.total-hours-input')?.value) || 0;
            totalCredits += Number(card.querySelector('input[name*="[credits]"]')?.value) || 0;
            totalMarks += Number(card.querySelector('.exam-total')?.value) || 0;
        });

        document.getElementById('totalTH').innerText = totalTH;
        document.getElementById('totalTU').innerText = totalTU;
        document.getElementById('totalPR').innerText = totalPR;
        document.getElementById('totalHours').innerText = totalHours;
        document.getElementById('totalCredits').innerText = totalCredits;
        document.getElementById('sumExamTotal').innerText = totalMarks;

        validateTotals(totalTH, totalTU, totalPR, totalCredits, totalMarks);
    }

    // ============================
    // 3️⃣ VALIDATION
    // ============================
    function validateTotals(totalTH, totalTU, totalPR, totalCredits, totalMarks) {

        let errors = [];

        // STRICT MODE — ALL COURSES MUST BE FILLED

        let totalCourses = document.querySelectorAll('.course-card').length;
        let filledCourses = 0;

        document.querySelectorAll('.course-card').forEach(card => {

            let code  = card.querySelector('input[name*="[course_code]"]')?.value.trim();
            let title = card.querySelector('input[name*="[course_title]"]')?.value.trim();

            if (code && title) {
                filledCourses++;
            }
        });

        if (filledCourses !== totalCourses) {
            errors.push(`All ${totalCourses} courses must be completed for this level.`);
        }

        if (totalTH !== maxTH)
            errors.push(`TH must be exactly ${maxTH}`);

        if (totalTU !== maxTU)
            errors.push(`TU must be exactly ${maxTU}`);

        if (totalPR !== maxPR)
            errors.push(`PR must be exactly ${maxPR}`);

        if (!isAudit) {
            if (totalCredits !== maxCredits)
                errors.push(`Credits must be exactly ${maxCredits}`);

            if (totalMarks !== maxMarks)
                errors.push(`Marks must be exactly ${maxMarks}`);
        }

        highlight('totalTH', totalTH !== maxTH);
        highlight('totalTU', totalTU !== maxTU);
        highlight('totalPR', totalPR !== maxPR);
        highlight('totalCredits', totalCredits !== maxCredits);
        highlight('sumExamTotal', totalMarks !== maxMarks);

        if (errors.length > 0) {

            alertBox.classList.remove('d-none');
            saveBtn.disabled = true;

            validationList.innerHTML = '';
            errors.forEach(error => {
                validationList.innerHTML += `<li>${error}</li>`;
            });

        } else {

            alertBox.classList.add('d-none');
            validationList.innerHTML = '';
            saveBtn.disabled = false;
        }
    }

    // ============================
    // 4️⃣ HIGHLIGHT FUNCTION
    // ============================
    function highlight(id, condition) {
        let el = document.getElementById(id);
        if (!el) return;

        if (condition) {
            el.classList.add('text-danger');
            el.classList.remove('text-success');
        } else {
            el.classList.remove('text-danger');
            el.classList.add('text-success');
        }
    }

    // ============================
    // 5️⃣ INPUT LISTENERS
    // ============================
    document.querySelectorAll('.course-container input').forEach(input => {

        input.addEventListener('input', function () {

            let card = input.closest('.card');
            calculateRow(card);
            calculateLevelTotals();
        });
    });

});
</script>