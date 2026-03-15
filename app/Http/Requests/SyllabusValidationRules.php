<?php

namespace App\Http\Requests;

use App\Models\Syllabus;

trait SyllabusValidationRules
{
    public function syllabusRules(): array
    {
        // Check if this is a draft save
        $isDraft = $this->input('status') === 'draft';
        
        // Use 'nullable' for drafts, 'required' for final submission
        $required = $isDraft ? 'nullable' : 'required';
        
        return [
            'assignment_id' => 'nullable|exists:course_assignments,id',
            'course_id' => 'nullable|exists:courses,id',
            'scheme_type' => 'nullable|string',
            'title' => $required . '|string|max:255',
            'status' => 'nullable|in:draft,submitted',
            'course_code' => $required . '|string|max:20',
            'level' => 'nullable|integer',
            'program_name' => $required . '|string|max:255',
            'academic_year' => $required . '|string|max:10',
            'iks_hours' => $required . '|integer|min:0',
            'department_ids' => $required . '|array|min:1',
            'department_ids.*' => 'exists:departments,id',
            
            'teaching_scheme' => $required . '|array',
            'teaching_scheme.th_hours' => $required . '|integer|min:0',
            'teaching_scheme.tu_hours' => $required . '|integer|min:0',
            'teaching_scheme.pr_hours' => $required . '|integer|min:0',
            'teaching_scheme.credits' => $required . '|integer|min:0',
            'teaching_scheme.slh_hours' => 'nullable|integer|min:0',
            'teaching_scheme.nlh_hours' => 'nullable|integer|min:0',
            
            'examination_scheme' => $required . '|array',
            'examination_scheme.fa_th_max' => $required . '|integer|min:0',
            'examination_scheme.fa_th_min' => $required . '|integer|min:0',
            'examination_scheme.sa_th_max' => $required . '|integer|min:0',
            'examination_scheme.sa_th_min' => $required . '|integer|min:0',
            'examination_scheme.sa_pr_max' => 'nullable|integer|min:0',
            'examination_scheme.sa_pr_min' => 'nullable|integer|min:0',
            'examination_scheme.paper_duration' => 'nullable|numeric|min:0',
            'examination_scheme.tw_marks' => 'nullable|integer|min:0',
            'examination_scheme.is_internal_practical' => 'boolean',
            
            'is_online_exam' => 'boolean',
            'elective_group' => 'nullable|string|max:50',
            'is_part_of_group' => 'boolean',
            
            'rationale' => $required . '|string|max:2000',
            'industry_employer_outcome' => $required . '|string|max:2000',
            'course_objectives' => 'nullable|array',
            'course_objectives.*' => 'required|string',
            'course_outcomes' => $required . '|array|min:3',
            'course_outcomes.*.code' => 'required|string',
            'course_outcomes.*.description' => 'required|string',
            
            'units' => 'nullable|array',
            'units.*.unit_no' => 'required|integer',
            'units.*.title' => 'required|string',
            'units.*.cognitive_outcomes' => 'required|string',
            'units.*.topics' => 'required|string',
            'units.*.hours' => 'required|integer|min:0',
            
            'specification_table' => 'nullable|array',
            'specification_table.*.unit_no' => 'required|integer',
            'specification_table.*.r' => 'required|integer|min:0',
            'specification_table.*.u' => 'required|integer|min:0',
            'specification_table.*.a' => 'required|integer|min:0',
            
            'training_location' => 'nullable|string|max:100',
            'training_schedule' => 'nullable|array',
            'training_schedule.*.week_no' => 'required|integer|min:1|max:20',
            'training_schedule.*.activity' => 'required|string',
            'training_schedule.*.marks_industry' => 'nullable|integer|min:0',
            'training_schedule.*.marks_mentor' => 'nullable|integer|min:0',
            
            'books' => 'nullable|array',
            'books.*.title' => 'required|string',
            'books.*.author' => 'required|string',
            'books.*.publication' => 'required|string',
            
            'software_websites' => 'nullable|array',
            'software_websites.*.name' => 'required|string',
            'software_websites.*.url' => 'required|string',
            
            'equipment_list' => 'nullable|array',
            'equipment_list.*.name' => 'required|string',
            'equipment_list.*.specifications' => 'required|string',

            'self_learning' => 'nullable|string|max:4000',
            'special_instructional_strategies' => 'nullable|array',
            'special_instructional_strategies.*' => 'required|string',
            
            'mapping_matrix' => 'nullable|array',
            'question_paper_profile' => 'nullable|array',
            'certification_signatures' => 'nullable|array',
            'report_format' => 'nullable|array',
        ];
    }

    public function syllabusMessages(): array
    {
        return [
            'course_outcomes.min' => 'At least 3 Course Outcomes are required for final submission.',
            'department_ids.min' => 'Please select at least one department.',
        ];
    }
}
