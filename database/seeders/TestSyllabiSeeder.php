<?php

namespace Database\Seeders;

use App\Models\Syllabus;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;

class TestSyllabiSeeder extends Seeder
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

        // Level 1: Foundation (Science & Humanities)
        Syllabus::create([
            'title' => 'Mathematics I',
            'course_code' => '241001',
            'course_description' => 'Fundamental mathematical concepts for engineering students',
            'learning_outcomes' => json_encode([
                'Understand calculus fundamentals',
                'Apply mathematical reasoning',
                'Solve engineering problems using mathematics'
            ]),
            'prerequisites' => 'None',
            'credits' => 4,
            'duration_weeks' => 16,
            'instructor_name' => 'Dr. John Smith',
            'instructor_email' => 'john.smith@college.edu',
            'level' => 'undergraduate',
            'semester' => 'Fall',
            'year' => 2025,
            'objectives' => 'Develop mathematical foundation for engineering studies',
            'topics' => json_encode([
                'Differential Calculus',
                'Integral Calculus',
                'Differential Equations'
            ]),
            'assessments' => json_encode([
                'Class Tests',
                'Assignments',
                'End Semester Examination'
            ]),
            'grading_policy' => 'Continuous assessment with end semester examination',
            'policies' => 'Regular attendance and participation required',
            'program_name' => 'IF',
            'academic_year' => Syllabus::getAcademicYear(),
            'department_id' => $department->id,
            'submitted_by' => $creator->id,
            'status' => 'draft',
            
            // Basic Info
            'iks_hours' => 0,
            'is_online_exam' => true,
            'elective_group' => '',
            'is_part_of_group' => false,
            'training_location' => '',
            
            // Teaching Scheme
            'teaching_scheme' => json_encode([
                'th_hours' => 4,
                'tu_hours' => 0,
                'pr_hours' => 0,
                'credits' => 4,
                'slh_hours' => 2,
                'nlh_hours' => 2,
                'total_hours' => 8
            ]),
            
            // Examination Scheme
            'examination_scheme' => json_encode([
                'fa_th_max' => 30,
                'fa_th_min' => 12,
                'sa_th_max' => 70,
                'sa_th_min' => 28,
                'sa_pr_max' => 0,
                'sa_pr_min' => 0,
                'paper_duration' => 3.0,
                'tw_marks' => 0,
                'is_internal_practical' => false
            ]),
            
            // Course Content
            'rationale' => 'This course introduces fundamental mathematical concepts essential for engineering studies.',
            'course_objectives' => json_encode([
                'Develop mathematical reasoning skills',
                'Introduce calculus fundamentals',
                'Build foundation for advanced mathematics'
            ]),
            'course_outcomes' => json_encode([
                ['code' => 'CO1', 'description' => 'Apply differentiation techniques to solve engineering problems'],
                ['code' => 'CO2', 'description' => 'Use integration methods for area and volume calculations'],
                ['code' => 'CO3', 'description' => 'Solve differential equations for physical systems']
            ]),
            'units' => json_encode([]),
            'specification_table' => json_encode([]),
            
            // Training Schedule (empty for Level 1)
            'training_schedule' => json_encode([]),
            
            // Project details (empty for Level 1)
            'project_phase' => '',
            'group_size_min' => null,
            'group_size_max' => null,
            'logbook_required' => false,
            'industry_supervisor' => false,
            
            // Practical Tasks (empty for Level 1)
            'practical_tasks' => json_encode([]),
            
            // Resources
            'books' => json_encode([
                [
                    'title' => 'Advanced Engineering Mathematics',
                    'author' => 'Erwin Kreyszig',
                    'edition' => '10th',
                    'publication' => 'Wiley',
                    'isbn' => '978-0470458365'
                ]
            ]),
            'software_websites' => json_encode([
                [
                    'name' => 'Wolfram Alpha',
                    'url' => 'https://www.wolframalpha.com',
                    'description' => 'Computational knowledge engine'
                ]
            ]),
            'equipment_list' => json_encode([
                ['s_no' => 1, 'name' => 'Scientific Calculator', 'specifications' => 'CASIO fx-991EX']
            ]),
            
            // Mapping Matrix
            'mapping_matrix' => json_encode([
                [
                    'co_code' => 'CO1',
                    'po1' => 'H', 'po2' => 'M', 'po3' => 'L', 'po4' => 'H', 'po5' => 'M', 'po6' => '-', 'po7' => 'L',
                    'pso1' => 'M', 'pso2' => 'H', 'pso3' => 'L', 'pso4' => 'M'
                ],
                [
                    'co_code' => 'CO2',
                    'po1' => 'M', 'po2' => 'H', 'po3' => 'M', 'po4' => 'L', 'po5' => 'H', 'po6' => 'L', 'po7' => 'M',
                    'pso1' => 'L', 'pso2' => 'M', 'pso3' => 'H', 'pso4' => 'L'
                ],
                [
                    'co_code' => 'CO3',
                    'po1' => 'L', 'po2' => 'M', 'po3' => 'H', 'po4' => 'M', 'po5' => 'L', 'po6' => 'H', 'po7' => 'M',
                    'pso1' => 'H', 'pso2' => 'L', 'pso3' => 'M', 'pso4' => 'H'
                ]
            ]),
            
            // Question Paper Profile (empty for Level 1 since no units)
            'question_paper_profile' => json_encode([]),
            
            // Certification Signatures
            'certification_signatures' => json_encode([
                'hod' => 'Dr. John Smith',
                'principal' => 'Dr. Jane Doe',
                'cdc_incharge' => 'Mr. Bob Johnson'
            ]),
            
            // Report Format (empty for Level 1)
            'report_format' => json_encode([])
        ]);

        // Level 2: Basic Technology
        Syllabus::create([
            'title' => 'Digital Electronics',
            'course_code' => '242002',
            'program_name' => 'IF',
            'academic_year' => Syllabus::getAcademicYear(),
            'department_id' => $department->id,
            'submitted_by' => $creator->id,
            'status' => 'draft',
            
            // Basic Info
            'iks_hours' => 0,
            'is_online_exam' => false,
            'elective_group' => '',
            'is_part_of_group' => false,
            'training_location' => '',
            
            // Teaching Scheme
            'teaching_scheme' => [
                'th_hours' => 3,
                'tu_hours' => 1,
                'pr_hours' => 2,
                'credits' => 4,
                'slh_hours' => 1,
                'nlh_hours' => 1,
                'total_hours' => 7
            ],
            
            // Examination Scheme
            'examination_scheme' => [
                'fa_th_max' => 30,
                'fa_th_min' => 12,
                'sa_th_max' => 70,
                'sa_th_min' => 28,
                'sa_pr_max' => 50,
                'sa_pr_min' => 20,
                'paper_duration' => 3.0,
                'tw_marks' => 25,
                'is_internal_practical' => false
            ],
            
            // Course Content
            'rationale' => 'This course introduces digital circuit design principles and practical implementation techniques.',
            'course_objectives' => [
                'Understand digital logic fundamentals',
                'Design combinational circuits',
                'Implement sequential logic circuits'
            ],
            'course_outcomes' => [
                ['code' => 'CO1', 'description' => 'Analyze digital logic gates and Boolean algebra'],
                ['code' => 'CO2', 'description' => 'Design combinational circuits using MSI components'],
                ['code' => 'CO3', 'description' => 'Implement sequential circuits with flip-flops']
            ],
            'units' => [
                [
                    'unit_no' => 1,
                    'title' => 'Number Systems and Logic Gates',
                    'cognitive_outcomes' => 'Remember, Understand',
                    'topics' => 'Binary, Octal, Hexadecimal systems; Logic gates; Boolean algebra',
                    'hours' => 8
                ],
                [
                    'unit_no' => 2,
                    'title' => 'Combinational Circuits',
                    'cognitive_outcomes' => 'Apply, Analyze',
                    'topics' => 'Adders, Subtractors, Multiplexers, Demultiplexers',
                    'hours' => 10
                ],
                [
                    'unit_no' => 3,
                    'title' => 'Sequential Circuits',
                    'cognitive_outcomes' => 'Apply, Evaluate',
                    'topics' => 'Flip-flops, Counters, Registers',
                    'hours' => 10
                ],
                [
                    'unit_no' => 4,
                    'title' => 'Memory Elements',
                    'cognitive_outcomes' => 'Understand, Evaluate',
                    'topics' => 'RAM, ROM, Programmable Logic Devices',
                    'hours' => 8
                ]
            ],
            'specification_table' => [
                ['unit_no' => 1, 'r' => 3, 'u' => 2, 'a' => 0],
                ['unit_no' => 2, 'r' => 2, 'u' => 3, 'a' => 2],
                ['unit_no' => 3, 'r' => 1, 'u' => 2, 'a' => 3],
                ['unit_no' => 4, 'r' => 2, 'u' => 2, 'a' => 1]
            ],
            
            // Training Schedule (empty for Level 2)
            'training_schedule' => [],
            
            // Project details (empty for Level 2)
            'project_phase' => '',
            'group_size_min' => null,
            'group_size_max' => null,
            'logbook_required' => false,
            'industry_supervisor' => false,
            
            // Practical Tasks
            'practical_tasks' => [
                [
                    's_no' => 1,
                    'title' => 'Logic Gates Implementation',
                    'hours' => 2,
                    'is_mandatory' => true,
                    'co_code' => 'CO1',
                    'unit_no' => 1
                ],
                [
                    's_no' => 2,
                    'title' => 'Adder Circuit Design',
                    'hours' => 2,
                    'is_mandatory' => true,
                    'co_code' => 'CO2',
                    'unit_no' => 2
                ]
            ],
            
            // Resources
            'books' => [
                [
                    'title' => 'Digital Design',
                    'author' => 'M. Morris Mano',
                    'edition' => '5th',
                    'publication' => 'Pearson',
                    'isbn' => '978-0132774208'
                ],
                [
                    'title' => 'Digital Electronics',
                    'author' => 'William Gothmann',
                    'edition' => '2nd',
                    'publication' => 'Prentice Hall',
                    'isbn' => '978-0132148492'
                ]
            ],
            'software_websites' => [
                [
                    'name' => 'Logisim',
                    'url' => 'https://sourceforge.net/projects/circuit/',
                    'description' => 'Digital logic design tool'
                ]
            ],
            'equipment_list' => [
                ['s_no' => 1, 'name' => 'Digital Trainer Kit', 'specifications' => 'With logic gates and switches'],
                ['s_no' => 2, 'name' => 'ICs', 'specifications' => '74LS series']
            ],
            
            // Mapping Matrix
            'mapping_matrix' => [
                [
                    'co_code' => 'CO1',
                    'po1' => 'H', 'po2' => 'M', 'po3' => 'L', 'po4' => 'H', 'po5' => 'M', 'po6' => '-', 'po7' => 'L',
                    'pso1' => 'M', 'pso2' => 'H', 'pso3' => 'L', 'pso4' => 'M'
                ],
                [
                    'co_code' => 'CO2',
                    'po1' => 'M', 'po2' => 'H', 'po3' => 'M', 'po4' => 'L', 'po5' => 'H', 'po6' => 'L', 'po7' => 'M',
                    'pso1' => 'L', 'pso2' => 'M', 'pso3' => 'H', 'pso4' => 'L'
                ],
                [
                    'co_code' => 'CO3',
                    'po1' => 'L', 'po2' => 'M', 'po3' => 'H', 'po4' => 'M', 'po5' => 'L', 'po6' => 'H', 'po7' => 'M',
                    'pso1' => 'H', 'pso2' => 'L', 'pso3' => 'M', 'pso4' => 'H'
                ]
            ],
            
            // Question Paper Profile
            'question_paper_profile' => [
                [
                    'unit_no' => 1,
                    'two_mark_count' => 6,
                    'four_mark_count' => 3
                ],
                [
                    'unit_no' => 2,
                    'two_mark_count' => 5,
                    'four_mark_count' => 4
                ],
                [
                    'unit_no' => 3,
                    'two_mark_count' => 4,
                    'four_mark_count' => 5
                ],
                [
                    'unit_no' => 4,
                    'two_mark_count' => 3,
                    'four_mark_count' => 3
                ]
            ],
            
            // Certification Signatures
            'certification_signatures' => [
                'hod' => 'Dr. Alice Brown',
                'principal' => 'Dr. Michael Wilson',
                'cdc_incharge' => 'Mrs. Sarah Davis'
            ],
            
            // Report Format (empty for Level 2)
            'report_format' => []
        ]);

        // Level 3: Allied Courses (Electives)
        Syllabus::create([
            'title' => 'Machine Learning',
            'course_code' => '243003',
            'program_name' => 'IF',
            'academic_year' => Syllabus::getAcademicYear(),
            'department_id' => $department->id,
            'submitted_by' => $creator->id,
            'status' => 'draft',
            
            // Basic Info
            'iks_hours' => 0,
            'is_online_exam' => false,
            'elective_group' => 'Elective II',
            'is_part_of_group' => true,
            'training_location' => '',
            
            // Teaching Scheme
            'teaching_scheme' => [
                'th_hours' => 3,
                'tu_hours' => 0,
                'pr_hours' => 2,
                'credits' => 4,
                'slh_hours' => 2,
                'nlh_hours' => 1,
                'total_hours' => 6
            ],
            
            // Examination Scheme
            'examination_scheme' => [
                'fa_th_max' => 30,
                'fa_th_min' => 12,
                'sa_th_max' => 70,
                'sa_th_min' => 28,
                'sa_pr_max' => 50,
                'sa_pr_min' => 20,
                'paper_duration' => 3.0,
                'tw_marks' => 25,
                'is_internal_practical' => true
            ],
            
            // Course Content
            'rationale' => 'This elective course introduces machine learning algorithms and their practical applications.',
            'course_objectives' => [
                'Understand ML algorithm fundamentals',
                'Implement supervised learning models',
                'Apply unsupervised learning techniques'
            ],
            'course_outcomes' => [
                ['code' => 'CO1', 'description' => 'Identify appropriate ML algorithms for given problems'],
                ['code' => 'CO2', 'description' => 'Implement regression and classification models'],
                ['code' => 'CO3', 'description' => 'Apply clustering and dimensionality reduction techniques']
            ],
            'units' => [
                [
                    'unit_no' => 1,
                    'title' => 'Introduction to ML',
                    'cognitive_outcomes' => 'Remember, Understand',
                    'topics' => 'ML concepts, types of learning, data preprocessing',
                    'hours' => 6
                ],
                [
                    'unit_no' => 2,
                    'title' => 'Supervised Learning',
                    'cognitive_outcomes' => 'Apply, Analyze',
                    'topics' => 'Regression, Classification, Decision Trees',
                    'hours' => 10
                ],
                [
                    'unit_no' => 3,
                    'title' => 'Unsupervised Learning',
                    'cognitive_outcomes' => 'Apply, Evaluate',
                    'topics' => 'Clustering, Association rules, Dimensionality reduction',
                    'hours' => 10
                ],
                [
                    'unit_no' => 4,
                    'title' => 'Deep Learning Basics',
                    'cognitive_outcomes' => 'Understand, Evaluate',
                    'topics' => 'Neural networks, CNN basics, RNN introduction',
                    'hours' => 6
                ]
            ],
            'specification_table' => [
                ['unit_no' => 1, 'r' => 2, 'u' => 3, 'a' => 1],
                ['unit_no' => 2, 'r' => 1, 'u' => 3, 'a' => 3],
                ['unit_no' => 3, 'r' => 2, 'u' => 2, 'a' => 2],
                ['unit_no' => 4, 'r' => 1, 'u' => 2, 'a' => 1]
            ],
            
            // Training Schedule (empty for Level 3)
            'training_schedule' => [],
            
            // Project details (empty for Level 3)
            'project_phase' => '',
            'group_size_min' => null,
            'group_size_max' => null,
            'logbook_required' => false,
            'industry_supervisor' => false,
            
            // Practical Tasks
            'practical_tasks' => [
                [
                    's_no' => 1,
                    'title' => 'Data Preprocessing Lab',
                    'hours' => 2,
                    'is_mandatory' => true,
                    'co_code' => 'CO1',
                    'unit_no' => 1
                ],
                [
                    's_no' => 2,
                    'title' => 'Regression Model Implementation',
                    'hours' => 2,
                    'is_mandatory' => true,
                    'co_code' => 'CO2',
                    'unit_no' => 2
                ]
            ],
            
            // Resources
            'books' => [
                [
                    'title' => 'Hands-On Machine Learning',
                    'author' => 'Aurélien Géron',
                    'edition' => '2nd',
                    'publication' => 'O\'Reilly Media',
                    'isbn' => '978-1492032649'
                ],
                [
                    'title' => 'Pattern Recognition and Machine Learning',
                    'author' => 'Christopher Bishop',
                    'edition' => '1st',
                    'publication' => 'Springer',
                    'isbn' => '978-0387310732'
                ]
            ],
            'software_websites' => [
                [
                    'name' => 'Python scikit-learn',
                    'url' => 'https://scikit-learn.org',
                    'description' => 'Machine learning library'
                ],
                [
                    'name' => 'TensorFlow',
                    'url' => 'https://tensorflow.org',
                    'description' => 'Deep learning framework'
                ]
            ],
            'equipment_list' => [
                ['s_no' => 1, 'name' => 'Development Computer', 'specifications' => '8GB RAM minimum']
            ],
            
            // Mapping Matrix
            'mapping_matrix' => [
                [
                    'co_code' => 'CO1',
                    'po1' => 'H', 'po2' => 'M', 'po3' => 'H', 'po4' => 'M', 'po5' => 'L', 'po6' => 'M', 'po7' => 'H',
                    'pso1' => 'H', 'pso2' => 'M', 'pso3' => 'L', 'pso4' => 'M'
                ],
                [
                    'co_code' => 'CO2',
                    'po1' => 'M', 'po2' => 'H', 'po3' => 'M', 'po4' => 'H', 'po5' => 'M', 'po6' => 'L', 'po7' => 'M',
                    'pso1' => 'M', 'pso2' => 'H', 'pso3' => 'M', 'pso4' => 'L'
                ],
                [
                    'co_code' => 'CO3',
                    'po1' => 'L', 'po2' => 'M', 'po3' => 'L', 'po4' => 'M', 'po5' => 'H', 'po6' => 'H', 'po7' => 'M',
                    'pso1' => 'L', 'pso2' => 'M', 'pso3' => 'H', 'pso4' => 'H'
                ]
            ],
            
            // Question Paper Profile
            'question_paper_profile' => [
                [
                    'unit_no' => 1,
                    'two_mark_count' => 5,
                    'four_mark_count' => 2
                ],
                [
                    'unit_no' => 2,
                    'two_mark_count' => 4,
                    'four_mark_count' => 4
                ],
                [
                    'unit_no' => 3,
                    'two_mark_count' => 4,
                    'four_mark_count' => 4
                ],
                [
                    'unit_no' => 4,
                    'two_mark_count' => 3,
                    'four_mark_count' => 2
                ]
            ],
            
            // Certification Signatures
            'certification_signatures' => [
                'hod' => 'Dr. Robert Taylor',
                'principal' => 'Dr. Jennifer Martinez',
                'cdc_incharge' => 'Mr. David Anderson'
            ],
            
            // Report Format (empty for Level 3)
            'report_format' => []
        ]);

        // Level 4: Applied Technology (Industrial Training/Project)
        Syllabus::create([
            'title' => 'Industrial Training',
            'course_code' => '244004',
            'program_name' => 'IF',
            'academic_year' => Syllabus::getAcademicYear(),
            'department_id' => $department->id,
            'submitted_by' => $creator->id,
            'status' => 'draft',
            
            // Basic Info
            'iks_hours' => 0,
            'is_online_exam' => false,
            'elective_group' => '',
            'is_part_of_group' => false,
            'training_location' => 'Tech Solutions Inc.',
            
            // Teaching Scheme
            'teaching_scheme' => [
                'th_hours' => 0,
                'tu_hours' => 0,
                'pr_hours' => 16,
                'credits' => 8,
                'slh_hours' => 0,
                'nlh_hours' => 0,
                'total_hours' => 16
            ],
            
            // Examination Scheme
            'examination_scheme' => [
                'fa_th_max' => 0,
                'fa_th_min' => 0,
                'sa_th_max' => 0,
                'sa_th_min' => 0,
                'sa_pr_max' => 100,
                'sa_pr_min' => 40,
                'paper_duration' => 0,
                'tw_marks' => 0,
                'is_internal_practical' => false
            ],
            
            // Course Content
            'rationale' => 'Students undergo industrial training to gain practical experience in real-world environments.',
            'course_objectives' => [
                'Gain hands-on industrial experience',
                'Apply theoretical knowledge in practice',
                'Develop professional skills'
            ],
            'course_outcomes' => [
                ['code' => 'CO1', 'description' => 'Demonstrate professional workplace behavior'],
                ['code' => 'CO2', 'description' => 'Apply technical skills in industrial setting'],
                ['code' => 'CO3', 'description' => 'Communicate effectively in professional environment']
            ],
            'units' => [], // No units for Level 4
            'specification_table' => [], // No specification table for Level 4
            
            // Training Schedule (16 weeks for Level 4)
            'training_schedule' => [
                [
                    'week_no' => 1,
                    'activity' => 'Orientation and company introduction',
                    'marks_industry' => 5,
                    'marks_mentor' => 5
                ],
                [
                    'week_no' => 2,
                    'activity' => 'Department allocation and initial tasks',
                    'marks_industry' => 5,
                    'marks_mentor' => 5
                ],
                [
                    'week_no' => 3,
                    'activity' => 'Technical training and skill development',
                    'marks_industry' => 5,
                    'marks_mentor' => 5
                ],
                [
                    'week_no' => 4,
                    'activity' => 'Project assignment and planning',
                    'marks_industry' => 5,
                    'marks_mentor' => 5
                ],
                [
                    'week_no' => 5,
                    'activity' => 'Project development phase 1',
                    'marks_industry' => 6,
                    'marks_mentor' => 4
                ],
                [
                    'week_no' => 6,
                    'activity' => 'Project development phase 2',
                    'marks_industry' => 6,
                    'marks_mentor' => 4
                ],
                [
                    'week_no' => 7,
                    'activity' => 'Project development phase 3',
                    'marks_industry' => 6,
                    'marks_mentor' => 4
                ],
                [
                    'week_no' => 8,
                    'activity' => 'Project development phase 4',
                    'marks_industry' => 6,
                    'marks_mentor' => 4
                ],
                [
                    'week_no' => 9,
                    'activity' => 'Mid-project review and feedback',
                    'marks_industry' => 5,
                    'marks_mentor' => 5
                ],
                [
                    'week_no' => 10,
                    'activity' => 'Project refinement and optimization',
                    'marks_indory' => 6,
                    'marks_mentor' => 4
                ],
                [
                    'week_no' => 11,
                    'activity' => 'Testing and quality assurance',
                    'marks_industry' => 6,
                    'marks_mentor' => 4
                ],
                [
                    'week_no' => 12,
                    'activity' => 'Documentation and reporting',
                    'marks_industry' => 5,
                    'marks_mentor' => 5
                ],
                [
                    'week_no' => 13,
                    'activity' => 'Presentation preparation',
                    'marks_industry' => 4,
                    'marks_mentor' => 6
                ],
                [
                    'week_no' => 14,
                    'activity' => 'Final presentation and evaluation',
                    'marks_industry' => 6,
                    'marks_mentor' => 4
                ],
                [
                    'week_no' => 15,
                    'activity' => 'Feedback and improvement suggestions',
                    'marks_industry' => 5,
                    'marks_mentor' => 5
                ],
                [
                    'week_no' => 16,
                    'activity' => 'Project closure and certificate preparation',
                    'marks_industry' => 5,
                    'marks_mentor' => 5
                ]
            ],
            
            // Project details for Level 4
            'project_phase' => 'execution',
            'group_size_min' => 1,
            'group_size_max' => 1,
            'logbook_required' => true,
            'industry_supervisor' => true,
            
            // Practical Tasks (empty for Level 4)
            'practical_tasks' => [],
            
            // Resources
            'books' => [
                [
                    'title' => 'Professional Industrial Training Guide',
                    'author' => 'Industry Experts',
                    'edition' => '1st',
                    'publication' => 'Company Publications',
                    'isbn' => '978-1234567890'
                ]
            ],
            'software_websites' => [
                [
                    'name' => 'Company Portal',
                    'url' => 'https://company.com/training',
                    'description' => 'Access to training materials and resources'
                ]
            ],
            'equipment_list' => [
                ['s_no' => 1, 'name' => 'Workstation Access', 'specifications' => 'Assigned to each trainee'],
                ['s_no' => 2, 'name' => 'Badge and Security Access', 'specifications' => 'Required for facility entry']
            ],
            
            // Mapping Matrix
            'mapping_matrix' => [
                [
                    'co_code' => 'CO1',
                    'po1' => 'H', 'po2' => 'H', 'po3' => 'M', 'po4' => 'H', 'po5' => 'M', 'po6' => 'L', 'po7' => 'M',
                    'pso1' => 'H', 'pso2' => 'M', 'pso3' => 'L', 'pso4' => 'H'
                ],
                [
                    'co_code' => 'CO2',
                    'po1' => 'M', 'po2' => 'H', 'po3' => 'H', 'po4' => 'M', 'po5' => 'H', 'po6' => 'M', 'po7' => 'L',
                    'pso1' => 'M', 'pso2' => 'H', 'pso3' => 'M', 'pso4' => 'L'
                ],
                [
                    'co_code' => 'CO3',
                    'po1' => 'H', 'po2' => 'M', 'po3' => 'L', 'po4' => 'H', 'po5' => 'H', 'po6' => 'H', 'po7' => 'M',
                    'pso1' => 'L', 'pso2' => 'M', 'pso3' => 'H', 'pso4' => 'H'
                ]
            ],
            
            // Question Paper Profile (empty for Level 4)
            'question_paper_profile' => [],
            
            // Certification Signatures
            'certification_signatures' => [
                'hod' => 'Dr. Susan Clark',
                'principal' => 'Dr. Mark Johnson',
                'cdc_incharge' => 'Mr. James Wilson'
            ],
            
            // Report Format for Level 4
            'report_format' => [
                ['chapter' => 1, 'title' => 'Introduction of Industry'],
                ['chapter' => 2, 'title' => 'Organizational Structure'],
                ['chapter' => 3, 'title' => 'Equipment/Software Specifications'],
                ['chapter' => 4, 'title' => 'Safety Procedures'],
                ['chapter' => 5, 'title' => 'Training Activities and Learning'],
                ['chapter' => 6, 'title' => 'Project Work and Deliverables'],
                ['chapter' => 7, 'title' => 'Challenges and Solutions'],
                ['chapter' => 8, 'title' => 'Conclusion and Recommendations']
            ]
        ]);

        // Level 5: Diversified Technology
        Syllabus::create([
            'title' => 'Advanced Software Engineering',
            'course_code' => '245005',
            'program_name' => 'IF',
            'academic_year' => Syllabus::getAcademicYear(),
            'department_id' => $department->id,
            'submitted_by' => $creator->id,
            'status' => 'draft',
            
            // Basic Info
            'iks_hours' => 0,
            'is_online_exam' => false,
            'elective_group' => '',
            'is_part_of_group' => false,
            'training_location' => '',
            
            // Teaching Scheme
            'teaching_scheme' => [
                'th_hours' => 2,
                'tu_hours' => 1,
                'pr_hours' => 3,
                'credits' => 4,
                'slh_hours' => 1,
                'nlh_hours' => 2,
                'total_hours' => 6
            ],
            
            // Examination Scheme
            'examination_scheme' => [
                'fa_th_max' => 30,
                'fa_th_min' => 12,
                'sa_th_max' => 70,
                'sa_th_min' => 28,
                'sa_pr_max' => 50,
                'sa_pr_min' => 20,
                'paper_duration' => 3.0,
                'tw_marks' => 25,
                'is_internal_practical' => false
            ],
            
            // Course Content
            'rationale' => 'This advanced course covers software engineering methodologies and modern development practices.',
            'course_objectives' => [
                'Master agile development methodologies',
                'Understand software architecture patterns',
                'Implement DevOps practices'
            ],
            'course_outcomes' => [
                ['code' => 'CO1', 'description' => 'Design scalable software architectures'],
                ['code' => 'CO2', 'description' => 'Implement continuous integration/deployment pipelines'],
                ['code' => 'CO3', 'description' => 'Apply software quality assurance techniques']
            ],
            'units' => [
                [
                    'unit_no' => 1,
                    'title' => 'Software Architecture',
                    'cognitive_outcomes' => 'Analyze, Evaluate',
                    'topics' => 'Architectural patterns, Design principles, Microservices',
                    'hours' => 8
                ],
                [
                    'unit_no' => 2,
                    'title' => 'Agile Development',
                    'cognitive_outcomes' => 'Apply, Analyze',
                    'topics' => 'Scrum, Kanban, XP, Team collaboration',
                    'hours' => 8
                ],
                [
                    'unit_no' => 3,
                    'title' => 'DevOps Practices',
                    'cognitive_outcomes' => 'Apply, Create',
                    'topics' => 'CI/CD, Containerization, Cloud deployment',
                    'hours' => 10
                ],
                [
                    'unit_no' => 4,
                    'title' => 'Quality Assurance',
                    'cognitive_outcomes' => 'Evaluate, Create',
                    'topics' => 'Testing strategies, Code quality, Performance',
                    'hours' => 8
                ]
            ],
            'specification_table' => [
                ['unit_no' => 1, 'r' => 1, 'u' => 2, 'a' => 3],
                ['unit_no' => 2, 'r' => 2, 'u' => 2, 'a' => 2],
                ['unit_no' => 3, 'r' => 1, 'u' => 1, 'a' => 4],
                ['unit_no' => 4, 'r' => 1, 'u' => 2, 'a' => 3]
            ],
            
            // Training Schedule (empty for Level 5)
            'training_schedule' => [],
            
            // Project details (empty for Level 5)
            'project_phase' => '',
            'group_size_min' => null,
            'group_size_max' => null,
            'logbook_required' => false,
            'industry_supervisor' => false,
            
            // Practical Tasks
            'practical_tasks' => [
                [
                    's_no' => 1,
                    'title' => 'Architecture Design Workshop',
                    'hours' => 3,
                    'is_mandatory' => true,
                    'co_code' => 'CO1',
                    'unit_no' => 1
                ],
                [
                    's_no' => 2,
                    'title' => 'Agile Sprint Simulation',
                    'hours' => 3,
                    'is_mandatory' => true,
                    'co_code' => 'CO2',
                    'unit_no' => 2
                ],
                [
                    's_no' => 3,
                    'title' => 'CI/CD Pipeline Implementation',
                    'hours' => 4,
                    'is_mandatory' => true,
                    'co_code' => 'CO2',
                    'unit_no' => 3
                ]
            ],
            
            // Resources
            'books' => [
                [
                    'title' => 'Clean Architecture',
                    'author' => 'Robert Martin',
                    'edition' => '1st',
                    'publication' => 'Prentice Hall',
                    'isbn' => '978-0134494166'
                ],
                [
                    'title' => 'DevOps Handbook',
                    'author' => 'Gene Kim',
                    'edition' => '2nd',
                    'publication' => 'IT Revolution Press',
                    'isbn' => '978-1942788003'
                ]
            ],
            'software_websites' => [
                [
                    'name' => 'Docker',
                    'url' => 'https://docker.com',
                    'description' => 'Container platform'
                ],
                [
                    'name' => 'GitHub',
                    'url' => 'https://github.com',
                    'description' => 'Version control and CI/CD'
                ]
            ],
            'equipment_list' => [
                ['s_no' => 1, 'name' => 'Development Laptop', 'specifications' => '16GB RAM recommended'],
                ['s_no' => 2, 'name' => 'Cloud Credits', 'specifications' => 'For cloud deployment practice']
            ],
            
            // Mapping Matrix
            'mapping_matrix' => [
                [
                    'co_code' => 'CO1',
                    'po1' => 'H', 'po2' => 'H', 'po3' => 'M', 'po4' => 'H', 'po5' => 'M', 'po6' => 'L', 'po7' => 'M',
                    'pso1' => 'H', 'pso2' => 'M', 'pso3' => 'L', 'pso4' => 'H'
                ],
                [
                    'co_code' => 'CO2',
                    'po1' => 'M', 'po2' => 'H', 'po3' => 'H', 'po4' => 'M', 'po5' => 'H', 'po6' => 'M', 'po7' => 'L',
                    'pso1' => 'M', 'pso2' => 'H', 'pso3' => 'M', 'pso4' => 'L'
                ],
                [
                    'co_code' => 'CO3',
                    'po1' => 'H', 'po2' => 'M', 'po3' => 'L', 'po4' => 'H', 'po5' => 'H', 'po6' => 'H', 'po7' => 'M',
                    'pso1' => 'L', 'pso2' => 'M', 'pso3' => 'H', 'pso4' => 'H'
                ]
            ],
            
            // Question Paper Profile
            'question_paper_profile' => [
                [
                    'unit_no' => 1,
                    'two_mark_count' => 4,
                    'four_mark_count' => 3
                ],
                [
                    'unit_no' => 2,
                    'two_mark_count' => 5,
                    'four_mark_count' => 3
                ],
                [
                    'unit_no' => 3,
                    'two_mark_count' => 4,
                    'four_mark_count' => 4
                ],
                [
                    'unit_no' => 4,
                    'two_mark_count' => 3,
                    'four_mark_count' => 3
                ]
            ],
            
            // Certification Signatures
            'certification_signatures' => [
                'hod' => 'Dr. Lisa Thompson',
                'principal' => 'Dr. Richard Garcia',
                'cdc_incharge' => 'Ms. Patricia Rodriguez'
            ],
            
            // Report Format (empty for Level 5)
            'report_format' => []
        ]);

        $this->command->info('Test syllabi created for all 5 levels:');
        $this->command->info('- Level 1: Foundation (Mathematics I)');
        $this->command->info('- Level 2: Basic Technology (Digital Electronics)');
        $this->command->info('- Level 3: Allied Courses (Machine Learning)');
        $this->command->info('- Level 4: Applied Technology (Industrial Training)');
        $this->command->info('- Level 5: Diversified Technology (Advanced Software Engineering)');
    }
}