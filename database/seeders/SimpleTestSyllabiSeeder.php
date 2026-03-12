<?php

namespace Database\Seeders;

use App\Models\Syllabus;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;

class SimpleTestSyllabiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users and departments
        $users = User::all();
        $departments = Department::all();
        
        if ($users->isEmpty()) {
            $this->command->info('No users found. Creating a test user...');
            $user = User::create([
                'name' => 'Test Creator',
                'email' => 'test.creator@example.com',
                'password' => bcrypt('password'),
                'role' => 'creator'
            ]);
            $users = collect([$user]);
        }
        
        if ($departments->isEmpty()) {
            $this->command->info('No departments found. Creating a test department...');
            $dept = Department::create([
                'name' => 'Computer Engineering',
                'code' => 'CE',
                'description' => 'Computer Engineering Department'
            ]);
            $departments = collect([$dept]);
        }

        $creator = $users->first();
        $department = $departments->first();

        // Simple test data for all 5 levels
        $testData = [
            // Level 1: Foundation
            [
                'title' => 'Mathematics I',
                'course_code' => '241001',
                'level_digit' => 1
            ],
            // Level 2: Basic Technology
            [
                'title' => 'Digital Electronics',
                'course_code' => '242002',
                'level_digit' => 2
            ],
            // Level 3: Allied Courses
            [
                'title' => 'Machine Learning',
                'course_code' => '243003',
                'level_digit' => 3
            ],
            // Level 4: Applied Technology
            [
                'title' => 'Industrial Training',
                'course_code' => '244004',
                'level_digit' => 4
            ],
            // Level 5: Diversified Technology
            [
                'title' => 'Advanced Software Engineering',
                'course_code' => '245005',
                'level_digit' => 5
            ]
        ];

        foreach ($testData as $data) {
            $syllabus = new Syllabus();
            $syllabus->title = $data['title'];
            $syllabus->course_code = $data['course_code'];
            $syllabus->course_description = 'Test course for Level ' . $data['level_digit'];
            $syllabus->learning_outcomes = json_encode(['Learning outcome for ' . $data['title']]);
            $syllabus->prerequisites = 'None';
            $syllabus->credits = 4;
            $syllabus->duration_weeks = 16;
            $syllabus->instructor_name = 'Test Instructor';
            $syllabus->instructor_email = 'instructor@test.com';
            $syllabus->level = 'undergraduate';
            $syllabus->semester = 'Fall';
            $syllabus->year = 2025;
            $syllabus->objectives = 'Test objectives for ' . $data['title'];
            $syllabus->topics = json_encode(['Topic 1', 'Topic 2']);
            $syllabus->assessments = json_encode(['Test 1', 'Test 2']);
            $syllabus->grading_policy = 'Standard grading policy';
            $syllabus->policies = 'Standard policies';
            $syllabus->status = 'draft';
            $syllabus->department_id = $department->id;
            $syllabus->submitted_by = $creator->id;
            $syllabus->program_name = 'IF';
            $syllabus->academic_year = '2025-26';
            $syllabus->iks_hours = 0;
            $syllabus->is_online_exam = false;
            $syllabus->elective_group = '';
            $syllabus->is_part_of_group = false;
            $syllabus->training_location = '';
            $syllabus->teaching_scheme = json_encode([
                'th_hours' => 3,
                'tu_hours' => 1,
                'pr_hours' => 2,
                'credits' => 4,
                'slh_hours' => 1,
                'nlh_hours' => 1,
                'total_hours' => 7
            ]);
            $syllabus->examination_scheme = json_encode([
                'fa_th_max' => 30,
                'fa_th_min' => 12,
                'sa_th_max' => 70,
                'sa_th_min' => 28,
                'sa_pr_max' => 50,
                'sa_pr_min' => 20,
                'paper_duration' => 3.0,
                'tw_marks' => 25,
                'is_internal_practical' => false
            ]);
            $syllabus->rationale = 'Test rationale for ' . $data['title'];
            $syllabus->course_objectives = json_encode([
                'Objective 1 for ' . $data['title'],
                'Objective 2 for ' . $data['title']
            ]);
            $syllabus->course_outcomes = json_encode([
                ['code' => 'CO1', 'description' => 'Test outcome 1'],
                ['code' => 'CO2', 'description' => 'Test outcome 2'],
                ['code' => 'CO3', 'description' => 'Test outcome 3']
            ]);
            
            // Add level-specific content
            if ($data['level_digit'] == 1) {
                // Level 1: Foundation
                $syllabus->is_online_exam = true;
                $syllabus->units = json_encode([]);
                $syllabus->specification_table = json_encode([]);
                $syllabus->training_schedule = json_encode([]);
                $syllabus->practical_tasks = json_encode([]);
                $syllabus->question_paper_profile = json_encode([]);
                $syllabus->report_format = json_encode([]);
            } elseif ($data['level_digit'] == 2 || $data['level_digit'] == 5) {
                // Level 2 & 5: Technology with units
                $syllabus->units = json_encode([
                    ['unit_no' => 1, 'title' => 'Unit 1', 'cognitive_outcomes' => 'Remember', 'topics' => 'Topic 1', 'hours' => 8],
                    ['unit_no' => 2, 'title' => 'Unit 2', 'cognitive_outcomes' => 'Understand', 'topics' => 'Topic 2', 'hours' => 8],
                    ['unit_no' => 3, 'title' => 'Unit 3', 'cognitive_outcomes' => 'Apply', 'topics' => 'Topic 3', 'hours' => 8],
                    ['unit_no' => 4, 'title' => 'Unit 4', 'cognitive_outcomes' => 'Analyze', 'topics' => 'Topic 4', 'hours' => 8]
                ]);
                $syllabus->specification_table = json_encode([
                    ['unit_no' => 1, 'r' => 2, 'u' => 2, 'a' => 1],
                    ['unit_no' => 2, 'r' => 1, 'u' => 3, 'a' => 1],
                    ['unit_no' => 3, 'r' => 2, 'u' => 1, 'a' => 2],
                    ['unit_no' => 4, 'r' => 1, 'u' => 2, 'a' => 2]
                ]);
                $syllabus->training_schedule = json_encode([]);
                $syllabus->practical_tasks = json_encode([
                    ['s_no' => 1, 'title' => 'Practical 1', 'hours' => 2, 'is_mandatory' => true, 'co_code' => 'CO1', 'unit_no' => 1]
                ]);
                $syllabus->question_paper_profile = json_encode([
                    ['unit_no' => 1, 'two_mark_count' => 5, 'four_mark_count' => 3],
                    ['unit_no' => 2, 'two_mark_count' => 4, 'four_mark_count' => 4],
                    ['unit_no' => 3, 'two_mark_count' => 3, 'four_mark_count' => 5],
                    ['unit_no' => 4, 'two_mark_count' => 4, 'four_mark_count' => 3]
                ]);
                $syllabus->report_format = json_encode([]);
            } elseif ($data['level_digit'] == 3) {
                // Level 3: Allied (Elective)
                $syllabus->elective_group = 'Elective II';
                $syllabus->is_part_of_group = true;
                $syllabus->units = json_encode([
                    ['unit_no' => 1, 'title' => 'Elective Unit 1', 'cognitive_outcomes' => 'Remember', 'topics' => 'Elective Topic 1', 'hours' => 6],
                    ['unit_no' => 2, 'title' => 'Elective Unit 2', 'cognitive_outcomes' => 'Understand', 'topics' => 'Elective Topic 2', 'hours' => 8],
                    ['unit_no' => 3, 'title' => 'Elective Unit 3', 'cognitive_outcomes' => 'Apply', 'topics' => 'Elective Topic 3', 'hours' => 8],
                    ['unit_no' => 4, 'title' => 'Elective Unit 4', 'cognitive_outcomes' => 'Analyze', 'topics' => 'Elective Topic 4', 'hours' => 6]
                ]);
                $syllabus->specification_table = json_encode([
                    ['unit_no' => 1, 'r' => 2, 'u' => 2, 'a' => 1],
                    ['unit_no' => 2, 'r' => 1, 'u' => 3, 'a' => 1],
                    ['unit_no' => 3, 'r' => 2, 'u' => 1, 'a' => 2],
                    ['unit_no' => 4, 'r' => 1, 'u' => 2, 'a' => 2]
                ]);
                $syllabus->training_schedule = json_encode([]);
                $syllabus->practical_tasks = json_encode([
                    ['s_no' => 1, 'title' => 'Elective Practical', 'hours' => 2, 'is_mandatory' => true, 'co_code' => 'CO1', 'unit_no' => 1]
                ]);
                $syllabus->question_paper_profile = json_encode([
                    ['unit_no' => 1, 'two_mark_count' => 4, 'four_mark_count' => 2],
                    ['unit_no' => 2, 'two_mark_count' => 4, 'four_mark_count' => 3],
                    ['unit_no' => 3, 'two_mark_count' => 3, 'four_mark_count' => 4],
                    ['unit_no' => 4, 'two_mark_count' => 3, 'four_mark_count' => 2]
                ]);
                $syllabus->report_format = json_encode([]);
            } elseif ($data['level_digit'] == 4) {
                // Level 4: Applied (Training)
                $syllabus->training_location = 'Tech Solutions Inc.';
                $syllabus->units = json_encode([]);
                $syllabus->specification_table = json_encode([]);
                $syllabus->project_phase = 'execution';
                $syllabus->group_size_min = 1;
                $syllabus->group_size_max = 1;
                $syllabus->logbook_required = true;
                $syllabus->industry_supervisor = true;
                
                // 16-week training schedule
                $trainingSchedule = [];
                for ($i = 1; $i <= 16; $i++) {
                    $trainingSchedule[] = [
                        'week_no' => $i,
                        'activity' => 'Training Week ' . $i,
                        'marks_industry' => 5,
                        'marks_mentor' => 5
                    ];
                }
                $syllabus->training_schedule = json_encode($trainingSchedule);
                $syllabus->practical_tasks = json_encode([]);
                $syllabus->question_paper_profile = json_encode([]);
                $syllabus->report_format = json_encode([
                    ['chapter' => 1, 'title' => 'Introduction of Industry'],
                    ['chapter' => 2, 'title' => 'Organizational Structure'],
                    ['chapter' => 3, 'title' => 'Equipment/Software Specifications'],
                    ['chapter' => 4, 'title' => 'Safety Procedures']
                ]);
            }
            
            $syllabus->books = json_encode([
                ['title' => 'Test Book', 'author' => 'Test Author', 'edition' => '1st', 'publication' => 'Test Publisher', 'isbn' => '1234567890']
            ]);
            $syllabus->software_websites = json_encode([
                ['name' => 'Test Software', 'url' => 'https://test.com', 'description' => 'Test software description']
            ]);
            $syllabus->equipment_list = json_encode([
                ['s_no' => 1, 'name' => 'Test Equipment', 'specifications' => 'Test specifications']
            ]);
            $syllabus->mapping_matrix = json_encode([
                ['co_code' => 'CO1', 'po1' => 'H', 'po2' => 'M', 'po3' => 'L', 'po4' => 'H', 'po5' => 'M', 'po6' => '-', 'po7' => 'L', 'pso1' => 'M', 'pso2' => 'H', 'pso3' => 'L', 'pso4' => 'M'],
                ['co_code' => 'CO2', 'po1' => 'M', 'po2' => 'H', 'po3' => 'M', 'po4' => 'L', 'po5' => 'H', 'po6' => 'L', 'po7' => 'M', 'pso1' => 'L', 'pso2' => 'M', 'pso3' => 'H', 'pso4' => 'L'],
                ['co_code' => 'CO3', 'po1' => 'L', 'po2' => 'M', 'po3' => 'H', 'po4' => 'M', 'po5' => 'L', 'po6' => 'H', 'po7' => 'M', 'pso1' => 'H', 'pso2' => 'L', 'pso3' => 'M', 'pso4' => 'H']
            ]);
            $syllabus->certification_signatures = json_encode([
                'hod' => 'Dr. Test HOD',
                'principal' => 'Dr. Test Principal',
                'cdc_incharge' => 'Mr. Test CDC'
            ]);
            
            $syllabus->save();
            
            $this->command->info('Created: ' . $data['title'] . ' (Level ' . $data['level_digit'] . ')');
        }

        $this->command->info('Test syllabi created successfully for all 5 levels!');
    }
}