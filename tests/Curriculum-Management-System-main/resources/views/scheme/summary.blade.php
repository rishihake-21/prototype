@extends('layouts.app')

@section('content')

@php
function showValue($value) {
    return ($value === null || $value === '' || $value === 0) ? '--' : $value;
}
@endphp

<div class="booklet-container">

<div class="text-center header-section">
<h4 class="fw-bold">PROGRAMME - {{ strtoupper($scheme->programme_name) }}</h4>
<h5>PROGRAMME STRUCTURE</h5>
</div>


@foreach($levels as $level)

<div class="level-section">

<h5 class="level-heading text-center">
{{ strtoupper($level->level_name) }}
</h5>

<table class="booklet-table">

<thead>
<tr>
<th rowspan="3">Sr.<br>No.</th>
<th rowspan="3">Course<br>Code</th>
<th rowspan="3">Course Title</th>
<th rowspan="3">Course<br>Abbr.</th>

@if($level->is_audit)
<th colspan="4">TEACHING SCHEME</th>
@else
<th colspan="5">TEACHING SCHEME</th>
<th colspan="7">EXAMINATION SCHEME</th>
@endif
</tr>

<tr>
<th colspan="4">Hours per Week</th>

@if(!$level->is_audit)
<th rowspan="2">Total<br>Credits</th>
<th colspan="2">Theory<br>Paper</th>
<th rowspan="2">Test</th>
<th rowspan="2">PR</th>
<th rowspan="2">OR</th>
<th rowspan="2">TW</th>
<th rowspan="2">Total</th>
@endif
</tr>

<tr>
<th>TH</th>
<th>TU</th>
<th>PR</th>
<th>Total<br>Hours</th>

@if(!$level->is_audit)
<th>Hrs</th>
<th>Marks</th>
@endif
</tr>

</thead>

<tbody>

@php
$sr = 1;
$totalTH = 0;
$totalTU = 0;
$totalPR = 0;
$totalHours = 0;
$totalCredits = 0;
$totalTheoryHrs = 0;
$totalTheoryMarks = 0;
$totalTest = 0;
$totalPRMarks = 0;
$totalOR = 0;
$totalTW = 0;
$totalExam = 0;
@endphp

@foreach($courses[$level->id] ?? [] as $course)

@php
$totalTH += $course->th ?? 0;
$totalTU += $course->tu ?? 0;
$totalPR += $course->pr ?? 0;
$totalHours += $course->total_hours ?? 0;

if(!$level->is_audit){
$totalCredits += $course->credits ?? 0;
$totalTheoryHrs += $course->theory_hours ?? 0;
$totalTheoryMarks += $course->theory_marks ?? 0;
$totalTest += $course->test_marks ?? 0;
$totalPRMarks += $course->pr_marks ?? 0;
$totalOR += $course->or_marks ?? 0;
$totalTW += $course->tw_marks ?? 0;
$totalExam += $course->marks ?? 0;
}
@endphp

<tr>

<td>{{ $sr++ }}</td>
<td>{{ showValue($course->course_code) }}</td>
<td class="text-left">{{ showValue($course->course_title) }}</td>
<td>{{ showValue($course->Abbr) }}</td>

<td>{{ showValue($course->th) }}</td>
<td>{{ showValue($course->tu) }}</td>
<td>{{ showValue($course->pr) }}</td>
<td>{{ showValue($course->total_hours) }}</td>

@if(!$level->is_audit)

<td>{{ showValue($course->credits) }}</td>
<td>{{ showValue($course->theory_hours) }}</td>
<td>{{ showValue($course->theory_marks) }}</td>
<td>{{ showValue($course->test_marks) }}</td>
<td>{{ showValue($course->pr_marks) }}</td>
<td>{{ showValue($course->or_marks) }}</td>
<td>{{ showValue($course->tw_marks) }}</td>
<td>{{ showValue($course->marks) }}</td>

@endif

</tr>

@endforeach


<tr class="total-row">

<td colspan="4"><strong>TOTAL</strong></td>

<td>{{ showValue($totalTH) }}</td>
<td>{{ showValue($totalTU) }}</td>
<td>{{ showValue($totalPR) }}</td>
<td>{{ showValue($totalHours) }}</td>

@if(!$level->is_audit)

<td>{{ showValue($totalCredits) }}</td>
<td>{{ showValue($totalTheoryHrs) }}</td>
<td>{{ showValue($totalTheoryMarks) }}</td>
<td>{{ showValue($totalTest) }}</td>
<td>{{ showValue($totalPRMarks) }}</td>
<td>{{ showValue($totalOR) }}</td>
<td>{{ showValue($totalTW) }}</td>
<td>{{ showValue($totalExam) }}</td>

@endif

</tr>

</tbody>

</table>

</div>

@endforeach


@php
$awardCourses = \App\Models\Course::where('scheme_id', $scheme->id)
->where('is_award', 1)
->get();

$sr = 1;

$totalTH = 0;
$totalTU = 0;
$totalPR = 0;
$totalHours = 0;
$totalCredits = 0;
$totalTheoryHrs = 0;
$totalTheoryMarks = 0;
$totalTest = 0;
$totalPRMarks = 0;
$totalOR = 0;
$totalTW = 0;
$totalExam = 0;
@endphp


@if($awardCourses->count() > 0)

<hr class="mt-5 mb-4">

<h4 class="text-center mb-4 fw-bold">
COURSES FOR AWARD OF DIPLOMA
</h4>

<table class="booklet-table">

<thead>
<tr>
<th rowspan="3">Sr.<br>No.</th>
<th rowspan="3">Course<br>Code</th>
<th rowspan="3">Course Title</th>
<th rowspan="3">Course<br>Abbr.</th>
<th colspan="5">TEACHING SCHEME</th>
<th colspan="7">EXAMINATION SCHEME</th>
</tr>

<tr>
<th colspan="4">Hours per Week</th>
<th rowspan="2">Total<br>Credits</th>
<th colspan="2">Theory<br>Paper</th>
<th rowspan="2">Test</th>
<th rowspan="2">PR</th>
<th rowspan="2">OR</th>
<th rowspan="2">TW</th>
<th rowspan="2">Total</th>
</tr>

<tr>
<th>TH</th>
<th>TU</th>
<th>PR</th>
<th>Total<br>Hours</th>
<th>Hrs</th>
<th>Marks</th>
</tr>
</thead>


<tbody>

@foreach($awardCourses as $course)

@php
$totalTH += $course->th;
$totalTU += $course->tu;
$totalPR += $course->pr;
$totalHours += $course->total_hours;
$totalCredits += $course->credits;
$totalTheoryHrs += $course->theory_hours;
$totalTheoryMarks += $course->theory_marks;
$totalTest += $course->test_marks;
$totalPRMarks += $course->pr_marks;
$totalOR += $course->or_marks;
$totalTW += $course->tw_marks;
$totalExam += $course->marks;
@endphp

<tr>

<td>{{ $sr++ }}</td>
<td>{{ showValue($course->course_code) }}</td>
<td class="text-left">{{ showValue($course->course_title) }}</td>
<td>{{ showValue($course->Abbr) }}</td>

<td>{{ showValue($course->th) }}</td>
<td>{{ showValue($course->tu) }}</td>
<td>{{ showValue($course->pr) }}</td>
<td>{{ showValue($course->total_hours) }}</td>
<td>{{ showValue($course->credits) }}</td>

<td>{{ showValue($course->theory_hours) }}</td>
<td>{{ showValue($course->theory_marks) }}</td>
<td>{{ showValue($course->test_marks) }}</td>
<td>{{ showValue($course->pr_marks) }}</td>
<td>{{ showValue($course->or_marks) }}</td>
<td>{{ showValue($course->tw_marks) }}</td>
<td>{{ showValue($course->marks) }}</td>

</tr>

@endforeach


<tr class="total-row">

<td colspan="4"><strong>TOTAL</strong></td>

<td>{{ showValue($totalTH) }}</td>
<td>{{ showValue($totalTU) }}</td>
<td>{{ showValue($totalPR) }}</td>
<td>{{ showValue($totalHours) }}</td>
<td>{{ showValue($totalCredits) }}</td>

<td>{{ showValue($totalTheoryHrs) }}</td>
<td>{{ showValue($totalTheoryMarks) }}</td>
<td>{{ showValue($totalTest) }}</td>
<td>{{ showValue($totalPRMarks) }}</td>
<td>{{ showValue($totalOR) }}</td>
<td>{{ showValue($totalTW) }}</td>
<td>{{ showValue($totalExam) }}</td>

</tr>

</tbody>

</table>

@endif

{{-- PAGE 18 STRUCTURE --}}
<div class="page-break"></div>

@php
use App\Models\Course;

$page18Courses = Course::where('scheme_id',$scheme->id)->get();
@endphp

@include('scheme.page18',[
    'scheme'=>$scheme,
    'courses'=>$page18Courses
])

</div>


<style>

.booklet-container{
background:#fff;
padding:40px;
}

.booklet-table{
width:100%;
border-collapse:collapse;
margin-top:20px;
margin-bottom:40px;
font-size:14px;
}

.booklet-table th,
.booklet-table td{
border:1px solid #000;
padding:8px;
vertical-align:middle;
}

.booklet-table th{
background:#f2f2f2;
}

.text-left{
text-align:left;
}

.level-heading{
margin-top:40px;
margin-bottom:20px;
font-weight:600;
}

.total-row{
font-weight:bold;
background-color:#f9f9f9;
}

.page-break{
page-break-before:always;
}

@media print{

.navbar,
.btn{
display:none !important;
}

.booklet-container{
padding:0;
}

body{
background:#fff;
}

}

</style>

@endsection