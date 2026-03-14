@php
use App\Models\Course;

$courses = Course::where('scheme_id', $scheme->id)->get();
@endphp

@php

/* ---------------------------
COURSE GROUPING
----------------------------*/

$compulsory = $courses->where('type','compulsory')->where('is_audit',0);
$elective   = $courses->where('type','elective')->where('is_audit',0);
$audit      = $courses->where('is_audit',1);


/* ---------------------------
HELPER FUNCTIONS
----------------------------*/

function termCourses($collection,$year,$term){
    return $collection->where('year',$year)->where('term',$term);
}

function creditSum($collection,$year,$term){
    return $collection->where('year',$year)->where('term',$term)->sum('credits');
}

function courseCount($collection,$year,$term){
    return $collection->where('year',$year)->where('term',$term)->count();
}

@endphp



<div class="booklet-container">

<h5 class="text-center fw-bold">
PROGRAMME - {{ strtoupper($scheme->programme_name) }}
</h5>

<h6 class="text-center mb-3">
PROGRAMME STRUCTURE
</h6>


<table class="booklet-table">

<thead>

<tr>
<th rowspan="2">Nature of Course</th>

<th colspan="2">First Year</th>
<th colspan="2">Second Year</th>
<th colspan="2">Third Year</th>

<th rowspan="2">Total</th>
</tr>

<tr>

<th>Odd Term</th>
<th>Even Term</th>

<th>Odd Term</th>
<th>Even Term</th>

<th>Odd Term</th>
<th>Even Term</th>

</tr>

</thead>


<tbody>


{{-- ======================
COMPULSORY COURSES
====================== --}}

<tr>

<td class="section">Compulsory</td>

@foreach([1,2,3] as $year)
@foreach(['odd','even'] as $term)

<td>

@foreach(termCourses($compulsory,$year,$term) as $c)

<div class="course-block">

<div class="code">
{{ $c->course_code }} ({{ str_pad($c->credits,2,'0',STR_PAD_LEFT) }})
</div>

<div class="abbr">
{{ $c->Abbr }}
</div>

</div>

@endforeach

</td>

@endforeach
@endforeach

<td class="total">

{{ $compulsory->sum('credits') }}

</td>

</tr>



{{-- ======================
TOTAL CREDITS (COMPULSORY)
====================== --}}

<tr class="total-row">

<td>Total Credits (Compulsory)</td>

<td>{{ creditSum($compulsory,1,'odd') }}</td>
<td>{{ creditSum($compulsory,1,'even') }}</td>

<td>{{ creditSum($compulsory,2,'odd') }}</td>
<td>{{ creditSum($compulsory,2,'even') }}</td>

<td>{{ creditSum($compulsory,3,'odd') }}</td>
<td>{{ creditSum($compulsory,3,'even') }}</td>

<td>{{ $compulsory->sum('credits') }}</td>

</tr>



{{-- ======================
ELECTIVE COURSES
====================== --}}

<tr>

<td class="section">Elective</td>

@foreach([1,2,3] as $year)
@foreach(['odd','even'] as $term)

<td>

@foreach(termCourses($elective,$year,$term) as $c)

<div class="course-block">

<div class="code">
{{ $c->course_code }} ({{ str_pad($c->credits,2,'0',STR_PAD_LEFT) }})
</div>

<div class="abbr">
{{ $c->Abbr }}
</div>

</div>

@endforeach

</td>

@endforeach
@endforeach

<td>

{{ $elective->sum('credits') }}

</td>

</tr>



{{-- ======================
TOTAL COURSES (ELECTIVE)
====================== --}}

<tr class="total-row">

<td>Total Courses (Elective)</td>

<td>{{ courseCount($elective,1,'odd') }}</td>
<td>{{ courseCount($elective,1,'even') }}</td>

<td>{{ courseCount($elective,2,'odd') }}</td>
<td>{{ courseCount($elective,2,'even') }}</td>

<td>{{ courseCount($elective,3,'odd') }}</td>
<td>{{ courseCount($elective,3,'even') }}</td>

<td>{{ $elective->count() }}</td>

</tr>



{{-- ======================
TOTAL CREDITS (ELECTIVE)
====================== --}}

<tr class="total-row">

<td>Total Credits (Elective)</td>

<td>{{ creditSum($elective,1,'odd') }}</td>
<td>{{ creditSum($elective,1,'even') }}</td>

<td>{{ creditSum($elective,2,'odd') }}</td>
<td>{{ creditSum($elective,2,'even') }}</td>

<td>{{ creditSum($elective,3,'odd') }}</td>
<td>{{ creditSum($elective,3,'even') }}</td>

<td>{{ $elective->sum('credits') }}</td>

</tr>



{{-- ======================
TOTAL COURSES (COMP + ELECTIVE)
====================== --}}

<tr class="total-row">

<td>Total Courses</td>

<td>{{ courseCount($courses->where('is_audit',0),1,'odd') }}</td>
<td>{{ courseCount($courses->where('is_audit',0),1,'even') }}</td>

<td>{{ courseCount($courses->where('is_audit',0),2,'odd') }}</td>
<td>{{ courseCount($courses->where('is_audit',0),2,'even') }}</td>

<td>{{ courseCount($courses->where('is_audit',0),3,'odd') }}</td>
<td>{{ courseCount($courses->where('is_audit',0),3,'even') }}</td>

<td>{{ $courses->where('is_audit',0)->count() }}</td>

</tr>



{{-- ======================
AUDIT COURSES
====================== --}}

<tr>

<td class="section">Audit Courses</td>

@foreach([1,2,3] as $year)
@foreach(['odd','even'] as $term)

<td>

@foreach(termCourses($audit,$year,$term) as $c)

<div class="course-block">

<div class="code">
{{ $c->course_code }} (00)
</div>

<div class="abbr">
{{ $c->Abbr }}
</div>

</div>

@endforeach

</td>

@endforeach
@endforeach

<td>{{ $audit->count() }}</td>

</tr>



{{-- ======================
TOTAL CREDITS (COMP + ELECTIVE)
====================== --}}

<tr class="total-row">

<td>Total Credits (Compulsory + Elective)</td>

<td>{{ creditSum($courses->where('is_audit',0),1,'odd') }}</td>
<td>{{ creditSum($courses->where('is_audit',0),1,'even') }}</td>

<td>{{ creditSum($courses->where('is_audit',0),2,'odd') }}</td>
<td>{{ creditSum($courses->where('is_audit',0),2,'even') }}</td>

<td>{{ creditSum($courses->where('is_audit',0),3,'odd') }}</td>
<td>{{ creditSum($courses->where('is_audit',0),3,'even') }}</td>

<td>{{ $courses->where('is_audit',0)->sum('credits') }}</td>

</tr>



{{-- ======================
GRAND TOTAL
====================== --}}

<tr class="grand-row">

<td>Grand Total of Credits</td>

<td>{{ creditSum($courses->where('is_audit',0),1,'odd') }}</td>
<td>{{ creditSum($courses->where('is_audit',0),1,'even') }}</td>

<td>{{ creditSum($courses->where('is_audit',0),2,'odd') }}</td>
<td>{{ creditSum($courses->where('is_audit',0),2,'even') }}</td>

<td>{{ creditSum($courses->where('is_audit',0),3,'odd') }}</td>
<td>{{ creditSum($courses->where('is_audit',0),3,'even') }}</td>

<td>{{ $courses->where('is_audit',0)->sum('credits') }}</td>

</tr>


</tbody>
</table>

</div>



<style>

.booklet-container{
background:#fff;
padding:40px;
}

.booklet-table{
width:100%;
border-collapse:collapse;
font-size:14px;
}

.booklet-table th,
.booklet-table td{
border:1px solid #000;
padding:6px;
vertical-align:top;
}

.section{
font-weight:bold;
text-align:center;
}

.total-row{
background:#f5f5f5;
font-weight:bold;
}

.grand-row{
background:#e0e0e0;
font-weight:bold;
}

.total{
font-weight:bold;
}

.course-block{
margin-bottom:4px;
text-align:center;
}

.code{
font-weight:600;
}

.abbr{
font-size:12px;
}

</style>
