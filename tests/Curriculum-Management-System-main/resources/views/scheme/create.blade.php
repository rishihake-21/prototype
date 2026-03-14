@extends('layouts.app')

@section('content')

<h2 class="mb-4">Create Scheme</h2>

<form action="{{ route('scheme.store') }}" method="POST">
@csrf

<div class="row mb-4">

    <div class="col-md-4">
        <label>Programme Name</label>
        <input type="text" name="programme_name" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label>Programme Code</label>
        <input type="text" name="programme_code" class="form-control">
    </div>

    <div class="col-md-4">
        <label>Year</label>
        <input type="number" name="year" class="form-control" required>
    </div>

</div>

<button type="button" id="addLevelBtn" class="btn btn-primary mb-3">
    Add Level
</button>

<div class="form-check mb-3">
<input type="checkbox" 
       id="auditToggle" 
       name="audit_enabled" 
       value="1"
       class="form-check-input">
<label class="form-check-label">Include Audit Courses</label>
</div>

<div class="table-responsive">
<table class="table table-bordered text-center">

<thead class="table-dark">
<tr>
    <th>Level Name</th>
    <th>Courses</th>
    <th>TH</th>
    <th>TU</th>
    <th>PR</th>
    <th>Hours</th>
    <th>Credits</th>
    <th>Marks</th>
    <th></th>
</tr>
</thead>

<tbody id="levelTableBody">

<tr class="level-row">

<td>
<input type="text" name="levels[0][level_name]" class="form-control">
</td>

<td>
<input type="number" name="levels[0][courses_offered]" class="form-control courses">
</td>

<td>
<input type="number" name="levels[0][th]" class="form-control th">
</td>

<td>
<input type="number" name="levels[0][tu]" class="form-control tu">
</td>

<td>
<input type="number" name="levels[0][pr]" class="form-control pr">
</td>

<td>
<input type="number" name="levels[0][total_hours]" class="form-control hours" readonly>
</td>

<td>
<input type="number" name="levels[0][total_credits]" class="form-control credits">
</td>

<td>
<input type="number" name="levels[0][marks]" class="form-control marks">
</td>

<td>
<button type="button" class="btn btn-danger removeRow">X</button>
</td>

</tr>
</tbody>

<!-- TOTAL -->
<tr class="table-warning">
<td><strong>TOTAL</strong></td>
<td id="totalCourses">0</td>
<td id="totalTH">0</td>
<td id="totalTU">0</td>
<td id="totalPR">0</td>
<td id="totalHours">0</td>
<td id="totalCredits">0</td>
<td id="totalMarks">0</td>
<td></td>
</tr>

<!-- AUDIT -->
<tr id="auditRow" style="display:none;" class="audit-row">

<td>Audit Courses</td>

<td>
<input type="number" name="audit_courses" class="form-control audit-courses">
</td>

<td>
<input type="number" name="audit_th" class="form-control audit-th">
</td>

<td>
<input type="number" name="audit_tu" class="form-control audit-tu">
</td>

<td>
<input type="number" name="audit_pr" class="form-control audit-pr">
</td>

<td>
<input type="number" class="form-control audit-hours" readonly>
</td>

<td></td>
<td></td>
<td></td>

</tr>

<!-- GRAND TOTAL -->
<tr class="table-success">

<td><strong>GRAND TOTAL</strong></td>

<td id="grandCourses">0</td>
<td id="grandTH">0</td>
<td id="grandTU">0</td>
<td id="grandPR">0</td>
<td id="grandHours">0</td>
<td id="grandCredits">0</td>
<td id="grandMarks">0</td>
<td></td>

</tr>

</table>
</div>

<button type="submit" class="btn btn-success mt-3">
Save Scheme
</button>

</form>

<script>

document.addEventListener('DOMContentLoaded', function () {

function calculateTotals() {

let totals = {
courses: 0,
th: 0,
tu: 0,
pr: 0,
hours: 0,
credits: 0,
marks: 0
};

document.querySelectorAll('.level-row').forEach(row => {

let th = Number(row.querySelector('.th')?.value) || 0;
let tu = Number(row.querySelector('.tu')?.value) || 0;
let pr = Number(row.querySelector('.pr')?.value) || 0;

let calculatedHours = th + tu + pr;

row.querySelector('.hours').value = calculatedHours;

totals.courses += Number(row.querySelector('.courses')?.value) || 0;
totals.th += th;
totals.tu += tu;
totals.pr += pr;
totals.hours += calculatedHours;
totals.credits += Number(row.querySelector('.credits')?.value) || 0;
totals.marks += Number(row.querySelector('.marks')?.value) || 0;

});

totalCourses.innerText = totals.courses;
totalTH.innerText = totals.th;
totalTU.innerText = totals.tu;
totalPR.innerText = totals.pr;
totalHours.innerText = totals.hours;
totalCredits.innerText = totals.credits;
totalMarks.innerText = totals.marks;

let auditCourses = 0;
let auditTH = 0;
let auditTU = 0;
let auditPR = 0;
let auditHours = 0;

if (auditToggle.checked) {

auditCourses = Number(document.querySelector('.audit-courses')?.value) || 0;
auditTH = Number(document.querySelector('.audit-th')?.value) || 0;
auditTU = Number(document.querySelector('.audit-tu')?.value) || 0;
auditPR = Number(document.querySelector('.audit-pr')?.value) || 0;

auditHours = auditTH + auditTU + auditPR;

document.querySelector('.audit-hours').value = auditHours;

}

grandCourses.innerText = totals.courses + auditCourses;
grandTH.innerText = totals.th + auditTH;
grandTU.innerText = totals.tu + auditTU;
grandPR.innerText = totals.pr + auditPR;
grandHours.innerText = totals.hours + auditHours;
grandCredits.innerText = totals.credits;
grandMarks.innerText = totals.marks;

}

document.addEventListener('input', calculateTotals);

auditToggle.addEventListener('change', function () {

auditRow.style.display = this.checked ? '' : 'none';

calculateTotals();

});

let levelIndex = 1;

addLevelBtn.addEventListener('click', function () {

let newRow = document.querySelector('.level-row').cloneNode(true);

newRow.querySelectorAll('input').forEach(input => {

input.value = '';

if (input.name) {
input.name = input.name.replace(/\d+/, levelIndex);
}

});

levelTableBody.appendChild(newRow);

levelIndex++;

});

document.addEventListener('click', function (e) {

if (e.target.classList.contains('removeRow')) {

e.target.closest('tr').remove();

calculateTotals();

}

});

});

</script>

@endsection