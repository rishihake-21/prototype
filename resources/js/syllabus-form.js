const syllabusForm = function (initial = null, prefill = null, programmesMetadata = {}) {
    return {
        initial,
        prefill,
        programmesMetadata,
        isInitializing: false,
        currentStep: 0,
        steps: [
            'Basic Info',
            'Teaching / Learning Scheme',
            'Course Content',
            'Practicals',
            'Resources',
            'CO-PO Mapping',
            'Paper Profile',
            'Certification'
        ],
        detectedLevel: null,
        levelInfo: {
            text: '',
            class: 'text-gray-500'
        },
        form: {
            // Step 1: Basic Info
            scheme_type: 'standard', // Ruleset type
            program_name: '',
            title: '',
            course_code: '',
            academic_year: '',
            iks_hours: 0,
            department_ids: [],
            is_online_exam: false,
            elective_group: '',
            is_part_of_group: false,
            assignment_id: null,
            level: null,

            // Step 2: Teaching Scheme
            teaching_scheme: {
                th_hours: 0, // Legacy: TH, New: CL
                tu_hours: 0, // Legacy only
                pr_hours: 0, // Legacy: PR, New: LL
                credits: 0,
                slh_hours: 0,   // New only
                nlh_hours: 0,   // New only
                total_hours: 0  // Legacy: TH+TU+PR, New: TL (CL+LL)
            },
            examination_scheme: {
                fa_th_max: 30,
                fa_th_min: 12,
                sa_th_max: 70,
                sa_th_min: 28,
                sa_pr_max: null,
                sa_pr_min: null,
                paper_duration: 3.0,
                tw_marks: 0,
                is_internal_practical: false
            },

            // Step 3: Course Content
            rationale: '',
            course_objectives: [],
            course_outcomes: [],
            units: [],
            specification_table: [],
            training_location: '',
            training_schedule: [],
            project_phase: '',
            group_size_min: null,
            group_size_max: null,
            logbook_required: false,
            industry_supervisor: false,

            // Step 4: Practicals
            practical_tasks: [],

            // Step 5: Resources
            books: [],
            software_websites: [],
            equipment_list: [],

            // Step 6: Mapping Matrix
            mapping_matrix: [],

            // Step 7: Question Paper Profile
            question_paper_profile: [],

            // Step 8: Certification
            certification_signatures: {
                hod: '',
                principal: '',
                cdc_incharge: ''
            },

            // Report Format (Level 4)
            report_format: []
        },

        init() {
            if (this.initial) {
                // Populate form from existing syllabus data for edit mode
                const s = this.initial;

                this.form.program_name = s.program_name || '';
                this.form.title = s.title || '';
                this.form.course_code = s.course_code || '';
                this.form.academic_year = s.academic_year || this.getAcademicYear();
                this.form.iks_hours = s.iks_hours || 0;
                this.form.department_ids = (s.departments || []).map(d => typeof d === 'object' ? d.id : d);
                this.form.is_online_exam = !!s.is_online_exam;
                this.form.elective_group = s.elective_group || '';
                this.form.is_part_of_group = !!s.is_part_of_group;
                this.form.assignment_id = s.assignment_id || null;

                this.form.scheme_type = s.scheme_type || 'standard';

                // Complex JSON fields (already cast to arrays/objects by Eloquent)
                if (s.teaching_scheme) {
                    this.form.teaching_scheme = Object.assign({}, this.form.teaching_scheme, s.teaching_scheme);

                    // Sync top-level columns if they exist and aren't in the blob
                    if (s.slh_hours !== undefined && s.slh_hours !== null && !s.teaching_scheme.slh_hours) {
                        this.form.teaching_scheme.slh_hours = s.slh_hours;
                    }
                    if (s.nlh_hours !== undefined && s.nlh_hours !== null && !s.teaching_scheme.nlh_hours) {
                        this.form.teaching_scheme.nlh_hours = s.nlh_hours;
                    }
                }
                if (s.examination_scheme) {
                    this.form.examination_scheme = Object.assign({}, this.form.examination_scheme, s.examination_scheme);
                }

                this.form.rationale = s.rationale || '';
                this.form.course_objectives = s.course_objectives || [];
                this.form.course_outcomes = s.course_outcomes || [];
                this.form.units = s.units || [];
                this.form.specification_table = s.specification_table || [];
                this.form.training_location = s.training_location || '';
                this.form.training_schedule = s.training_schedule || [];
                this.form.project_phase = s.project_phase || '';
                this.form.group_size_min = s.group_size_min;
                this.form.group_size_max = s.group_size_max;
                this.form.logbook_required = !!s.logbook_required;
                this.form.industry_supervisor = !!s.industry_supervisor;

                this.form.practical_tasks = s.practical_tasks || [];
                this.form.books = s.books || [];
                this.form.software_websites = s.software_websites || [];
                this.form.equipment_list = s.equipment_list || [];
                this.form.mapping_matrix = s.mapping_matrix || [];
                this.form.question_paper_profile = s.question_paper_profile || [];

                // Sync Level
                if (s.level !== undefined && s.level !== null) {
                    this.detectedLevel = parseInt(s.level);
                    this.form.level = this.detectedLevel;
                    // Force levelInfo text to match the selected level during init
                    this.onManualLevelChange();
                } else if (this.form.course_code) {
                    this.onCourseCodeChange();
                }

                this.form.certification_signatures = Object.assign({}, this.form.certification_signatures, s.certification_signatures || {});
                this.form.report_format = s.report_format || [];

                // Set level & scheme info from course code
                this.onCourseCodeChange();

                // Ensure mapping matrix and paper profile align with COs/units
                this.updateMappingMatrix();
                this.updateQuestionPaperProfile();

                // Recalculate totals to catch any legacy data inconsistencies
                this.calculateCredits();
            } else if (this.prefill) {
                this.isInitializing = true;
                console.log('Prefilling from assignment:', this.prefill);
                // Populate from HOD assignment for create mode
                const p = this.prefill;

                if (p.course) {
                    this.fillFromCourse(p.course);
                    // Pre-fill department link
                    this.form.department_ids = [p.department_id];
                }

                this.form.academic_year = p.academic_year || this.getAcademicYear();
                this.form.assignment_id = p.id;

                // Add default placeholders for units/COs
                this.addObjective();
                this.addOutcome();
                this.addOutcome();
                this.addOutcome();

                // Trigger level info updates
                this.onCourseCodeChange();
                this.calculateCredits();

                this.isInitializing = false;
            } else {
                // Initialize with default values for create mode
                this.addObjective();
                this.addOutcome();
                this.addOutcome();
                this.addOutcome();

                // Pre-fill academic year
                this.form.academic_year = this.getAcademicYear();
            }

            // Prevent Enter key from submitting form in input fields
            this.$el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && e.target.tagName === 'INPUT' && e.target.type !== 'submit') {
                    e.preventDefault();
                }
            });
        },

        // Fill only the syllabus content (COs, Units, Resources) while keeping Basic Info/Schemes
        fillContentOnly() {
            // Step 3: Narrative
            this.form.rationale = 'This course focuses on digital logic design, combining theoretical principles with practical laboratory skills to build robust Electronic systems.';
            this.form.course_objectives = [
                'Analyze the operation of fundamental electronic logic gates.',
                'Design combinational and sequential circuits for real-world applications.',
                'Standardize syllabus documentation for modern electronics curriculum.'
            ];

            this.form.course_outcomes = [
                { code: 'CO1', description: 'Describe the operation of semiconductor devices and logic gates.' },
                { code: 'CO2', description: 'Formulate boolean expressions for digital circuit designs.' },
                { code: 'CO3', description: 'Analyze sequential circuits and state-machine transitions.' },
                { code: 'CO4', description: 'Evaluate circuit performance using simulation tools.' },
            ];

            // Units 
            this.form.units = [
                {
                    unit_no: 1,
                    title: 'Number Systems & Boolean Algebra',
                    cognitive_outcomes: 'Translate numbers between bases; simplify boolean functions.',
                    topics: 'Binary/Hex/Decimal; K-Maps; SOP/POS forms.',
                    hours: 10,
                },
                {
                    unit_no: 2,
                    title: 'Combinational Logic',
                    cognitive_outcomes: 'Design adders, muxes, and decoders.',
                    topics: 'Full Adder; 4:1 Mux; BCD to 7-segment.',
                    hours: 12,
                },
                {
                    unit_no: 3,
                    title: 'Sequential Logic',
                    cognitive_outcomes: 'Differentiate between latches and flip-flops.',
                    topics: 'JK/D Flip-flops; Counters; Shift Registers.',
                    hours: 12,
                },
                {
                    unit_no: 4,
                    title: 'Logic Families & Converters',
                    cognitive_outcomes: 'Compare TTL/CMOS traits; explain ADC/DAC flow.',
                    topics: 'TTL/CMOS; R-2R Ladder; Successive Approximation ADC.',
                    hours: 11,
                },
            ];

            // Step 4: practical tasks
            this.form.practical_tasks = [
                { s_no: 1, title: 'Verify Truth Tables of Logic Gates', hours: 2, is_mandatory: true, co_code: 'CO1', unit_no: 1 },
                { s_no: 2, title: 'Build a Full Adder using NAND gates', hours: 2, is_mandatory: true, co_code: 'CO2', unit_no: 2 },
                { s_no: 3, title: 'Design a 4-bit synchronous UP counter', hours: 4, is_mandatory: false, co_code: 'CO3', unit_no: 3 },
            ];

            // Step 5: resources
            this.form.books = [
                { title: 'Digital Principles and Applications', author: 'Leach & Malvino', edition: '8th', publication: 'McGraw Hill', isbn: '978-1259029646' },
                { title: 'Digital Design', author: 'Morris Mano', edition: '5th', publication: 'Pearson', isbn: '978-0132774208' },
            ];

            this.form.software_websites = [
                { name: 'Logisim-it', url: 'http://www.cburch.com/logisim/', description: 'Open-source logic simulator.' },
                { name: 'TutorialsPoint - Digital Electronics', url: 'https://tutorialspoint.com', description: 'Web resources for logic design.' },
            ];

            this.form.equipment_list = [
                { s_no: 1, name: 'Digital IC Trainer Kit', specifications: 'DC Power 5V/12V, logic switches, LEDs.' },
                { s_no: 2, name: 'Oscilloscope', specifications: 'Dual channel, 20MHz bandwidth.' },
            ];

            // Step 6: CO-PO mapping
            this.updateMappingMatrix();
            this.form.mapping_matrix = this.form.mapping_matrix.map((row, index) => {
                if (index === 0) return { ...row, po1: 'H', po2: 'M', pso1: 'H' };
                if (index === 1) return { ...row, po2: 'H', po3: 'H', pso1: 'M' };
                return { ...row, po3: 'H', po4: 'M', pso1: 'H' };
            });

            // Step 7: Question paper profile
            this.updateQuestionPaperProfile();
            const saThMax = parseInt(this.form.examination_scheme.sa_th_max) || 70;
            console.log('Quick Fill: SA-TH Max is', saThMax);

            this.form.question_paper_profile = this.form.question_paper_profile.map((row) => {
                if (saThMax > 75) {
                    // Aim for exactly 108 marks (1.35x 80)
                    console.log('Quick Fill: Targeting 108 marks (Unit', row.unit_no, ')');
                    if (row.unit_no <= 2) return { ...row, two_mark_count: 6, four_mark_count: 4 }; // 28 marks
                    return { ...row, two_mark_count: 5, four_mark_count: 4 }; // 26 marks
                } else {
                    // Aim for ~94 marks (1.35x 70)
                    console.log('Quick Fill: Targeting 94 marks (Unit', row.unit_no, ')');
                    if (row.unit_no === 1) return { ...row, two_mark_count: 5, four_mark_count: 3 }; // 22 marks
                    return { ...row, two_mark_count: 4, four_mark_count: 4 }; // 24 marks
                }
            });

            // Step 8: certification signatures
            this.form.certification_signatures = {
                hod: 'Prof. J. Doe',
                principal: 'Dr. A. Smith',
                cdc_incharge: 'Mr. B. Brown',
            };

            this.currentStep = 2; // Jump to "Course Content" step to show results
            this.updatePreview();
            console.log('Quick fill completed for:', this.form.course_code);
            alert('Syllabus content populated successfully! You are now on Step 3.');
        },

        // Quickly populate form with valid sample data for local testing
        loadSampleData() {
            // Reset to first step
            this.currentStep = 0;

            // Step 1: Basic info
            this.form.scheme_type = 'standard';
            this.form.program_name = 'IF';
            this.form.title = 'Digital Electronics';
            this.form.course_code = '242001';
            this.form.academic_year = this.getAcademicYear();
            this.form.department_ids = [1];
            this.form.iks_hours = 2; // Default for new standard

            // Step 2: Schemes
            this.form.teaching_scheme = Object.assign({}, this.form.teaching_scheme, {
                th_hours: 3, // CL
                tu_hours: 0, // TU
                pr_hours: 2, // LL
                slh_hours: 2, // SLH (Mandatory for Level 2)
                credits: 4,
                total_hours: 5, // TL = 3 + 2
                nlh_hours: 7 // NLH = 5 + 2
            });
            this.form.examination_scheme = Object.assign({}, this.form.examination_scheme, {
                fa_th_max: 30, sa_th_max: 70, sa_pr_max: 50, tw_marks: 25, paper_duration: 3.0
            });

            this.onCourseCodeChange();
            this.calculateCredits();

            // Fill the rest
            this.fillContentOnly();
        },

        // Course Code Change Handler
        onCourseCodeChange() {
            const code = this.form.course_code;
            if (code.length >= 3 && /^\d+$/.test(code)) {
                const levelDigit = parseInt(code.charAt(2));
                this.detectedLevel = levelDigit;
                this.form.level = levelDigit;

                switch (levelDigit) {
                    case 1:
                        this.levelInfo = { text: 'Level 1: Foundation (Science & Humanities) detected', class: 'text-blue-600' };
                        break;
                    case 2:
                        this.levelInfo = { text: 'Level 2: Basic Technology detected', class: 'text-green-600' };
                        break;
                    case 3:
                        this.levelInfo = { text: 'Level 3: Allied Courses (Electives) detected', class: 'text-purple-600' };
                        break;
                    case 4:
                        this.levelInfo = { text: 'Level 4: Applied Technology (Training/Project) detected', class: 'text-orange-600' };
                        break;
                    case 5:
                        this.levelInfo = { text: 'Level 5: Diversified Technology detected', class: 'text-indigo-600' };
                        break;
                    case 0:
                        this.levelInfo = { text: 'Level 0: Audit / Co-curricular Course detected (no credits, no marks).', class: 'text-teal-600' };
                        break;
                    default:
                        this.levelInfo = { text: 'Invalid level digit. Must be 0–5.', class: 'text-red-600' };
                        this.detectedLevel = null;
                }

                // Initialize level-specific data
                this.initializeLevelData();

                // Enforce audit rules for Level 0
                this.enforceAuditLevelConstraints();

                // If code is exactly 6 digits and not currently in prefill/init mode, 
                // try to fetch CDC data automatically
                if (code.length === 6 && !this.initial && !this.isInitializing) {
                    this.fetchCourseData(code);
                }
            } else {
                this.levelInfo = { text: 'Enter 6-digit course code (Format: YYLXXX)', class: 'text-gray-500' };
                this.detectedLevel = null;
            }

            this.updatePreview();
        },

        // Fetch Course Data from API
        fetchCourseData(code) {
            console.log('Fetching CDC data for:', code);
            const programme = this.form.program_name;
            const year = this.form.academic_year;

            let url = `/api/courses/${code}?`;
            if (programme) url += `programme=${encodeURIComponent(programme)}&`;
            if (year) url += `year=${encodeURIComponent(year)}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data && !data.error) {
                        this.fillFromCourse(data);
                        this.calculateCredits();
                    }
                })
                .catch(err => console.error('Auto-fetch failed:', err));
        },

        // Manual level change handler for dynamic schemes
        onManualLevelChange() {
            if (this.detectedLevel !== null && this.detectedLevel !== "") {
                this.detectedLevel = parseInt(this.detectedLevel);
                this.form.level = this.detectedLevel;

                // Set level info text for manual selection
                const levelNames = {
                    1: 'Level 1: Foundation (Science & Humanities)',
                    2: 'Level 2: Basic Technology',
                    3: 'Level 3: Allied Courses (Electives)',
                    4: 'Level 4: Applied Technology (Training/Project)',
                    5: 'Level 5: Diversified Technology',
                    0: 'Level 0: Audit / Co-curricular'
                };

                if (levelNames[this.detectedLevel]) {
                    this.levelInfo = {
                        text: levelNames[this.detectedLevel] + ' selected.',
                        class: 'text-indigo-600 font-semibold'
                    };
                }

                this.initializeLevelData();
                this.enforceAuditLevelConstraints();
            } else {
                this.levelInfo = { text: 'Select a level for dynamic course codes.', class: 'text-gray-500' };
            }
            this.updatePreview();
        },

        // Helper to map CDC Course model to Syllabus Form
        fillFromCourse(c) {
            if (!c) return;

            this.form.course_code = c.course_code || '';
            this.form.title = c.course_title || '';

            if (c.programme) {
                this.form.program_name = c.programme.code || '';
                this.form.academic_year = c.programme.academic_year || this.getAcademicYear();
                if (c.programme.scheme_type) {
                    this.form.scheme_type = c.programme.scheme_type;
                }
            }

            if (c.departments && c.departments.length > 0) {
                this.form.department_ids = c.departments.map(d => d.id);
            }

            if (c.level) {
                this.detectedLevel = c.level.sort_order;
            }

            // Pre-fill teaching scheme using Object.assign for reactivity
            this.form.teaching_scheme = Object.assign({}, this.form.teaching_scheme, {
                th_hours: c.th_hours || 0, // CL
                tu_hours: c.tu_hours || 0, // TU
                pr_hours: c.pr_hours || 0, // LL
                slh_hours: c.slh_hours || 0,
                credits: c.credits || 0,
                total_hours: (c.th_hours || 0) + (c.pr_hours || 0), // TL = CL + LL
                nlh_hours: (c.th_hours || 0) + (c.pr_hours || 0) + (c.slh_hours || 0) // NLH = TL + SLH
            });

            this.form.iks_hours = c.iks_hours || 0;

            // Pre-fill examination scheme marks
            this.form.examination_scheme = Object.assign({}, this.form.examination_scheme, {
                fa_th_max: c.test_max_marks || 30,
                sa_th_max: c.theory_max_marks || 70,
                sa_pr_max: c.pr_max_marks || (c.or_max_marks || 0),
                tw_marks: c.tw_max_marks || 0, // Maps to SLA/TW
                paper_duration: c.theory_paper_hrs || 3.0
            });

            if (c.course_type === 'elective') {
                this.form.elective_group = c.elective_group || '';
            }

            console.log('Autofilled constants from CDC:', c.course_code);
        },

        // Scheme type change handler - Now simplified
        onSchemeTypeChange() {
            this.enforceAuditLevelConstraints();
            this.updatePreview();
        },

        // Initialize data based on level
        initializeLevelData() {
            // Initialize units for levels 1, 2, 3, 5
            if (this.detectedLevel !== 4 && this.form.units.length === 0) {
                for (let i = 1; i <= 4; i++) {
                    this.addUnit();
                }
            }

            // Initialize specification table for levels 2 & 5
            if ((this.detectedLevel === 2 || this.detectedLevel === 5) && this.form.specification_table.length === 0) {
                for (let i = 1; i <= 4; i++) {
                    this.form.specification_table.push({
                        unit_no: i,
                        r: 0,
                        u: 0,
                        a: 0
                    });
                }
            }

            // Initialize training schedule for level 4
            if (this.detectedLevel === 4 && this.form.training_schedule.length === 0) {
                for (let i = 1; i <= 16; i++) {
                    this.form.training_schedule.push({
                        week_no: i,
                        activity: '',
                        marks_industry: 0,
                        marks_mentor: 0
                    });
                }

                // Initialize report format for Level 4
                if (this.form.report_format.length === 0) {
                    this.form.report_format = [
                        { chapter: 1, title: 'Introduction of Industry' },
                        { chapter: 2, title: 'Organizational Structure' },
                        { chapter: 3, title: 'Equipment/Software Specifications' },
                        { chapter: 4, title: 'Safety Procedures' }
                    ];
                }
            }
        },

        // Credit Calculation - Global Standard
        calculateCredits() {
            const th = parseInt(this.form.teaching_scheme.th_hours) || 0;
            const pr = parseInt(this.form.teaching_scheme.pr_hours) || 0;
            const slh = parseInt(this.form.teaching_scheme.slh_hours) || 0;

            const tl = th + pr; // TL = CL + LL
            const nlh = tl + slh; // NLH = TL + SLH

            this.form.teaching_scheme.total_hours = tl;
            this.form.teaching_scheme.nlh_hours = nlh;

            console.log(`Recalculated Credits: CL=${th}, LL=${pr}, SLH=${slh} => TL=${tl}, NLH=${nlh}`);
            this.updatePreview();
        },

        // Calculate Total Marks - Global Standard
        calculateTotalMarks() {
            const exam = this.form.examination_scheme;
            let total = 0;
            total += parseInt(exam.fa_th_max) || 0;
            total += parseInt(exam.sa_th_max) || 0;
            total += parseInt(exam.sa_pr_max) || 0;
            total += parseInt(exam.tw_marks) || 0;
            return total;
        },

        // Specification Total Update
        updateSpecTotal(index) {
            // Handled in template
        },

        // Dynamic Field Adders
        addObjective() {
            this.form.course_objectives.push('');
        },

        removeObjective(index) {
            this.form.course_objectives.splice(index, 1);
            this.updatePreview();
        },

        addOutcome() {
            const code = 'CO' + (this.form.course_outcomes.length + 1);
            this.form.course_outcomes.push({
                code: code,
                description: ''
            });
            this.updateMappingMatrix();
            this.updatePreview();
        },

        removeOutcome(index) {
            this.form.course_outcomes.splice(index, 1);
            // Renumber remaining outcomes
            this.form.course_outcomes.forEach((outcome, i) => {
                outcome.code = 'CO' + (i + 1);
            });
            this.updateMappingMatrix();
            this.updatePreview();
        },

        addUnit() {
            this.form.units.push({
                unit_no: this.form.units.length + 1,
                title: '',
                cognitive_outcomes: '',
                topics: '',
                hours: 0
            });
        },

        removeUnit(index) {
            this.form.units.splice(index, 1);
            // Renumber remaining units
            this.form.units.forEach((unit, i) => {
                unit.unit_no = i + 1;
            });
            this.updatePreview();
        },

        addTrainingWeek() {
            const weekNo = this.form.training_schedule.length + 1;
            if (weekNo <= 16) {
                this.form.training_schedule.push({
                    week_no: weekNo,
                    activity: '',
                    marks_industry: 0,
                    marks_mentor: 0
                });
            }
        },

        removeTrainingWeek(index) {
            this.form.training_schedule.splice(index, 1);
            // Renumber remaining weeks
            this.form.training_schedule.forEach((week, i) => {
                week.week_no = i + 1;
            });
            this.updatePreview();
        },

        addPracticalTask() {
            this.form.practical_tasks.push({
                s_no: this.form.practical_tasks.length + 1,
                title: '',
                hours: 0,
                is_mandatory: false,
                co_code: '',
                unit_no: null
            });
        },

        removePracticalTask(index) {
            this.form.practical_tasks.splice(index, 1);
            // Renumber remaining tasks
            this.form.practical_tasks.forEach((task, i) => {
                task.s_no = i + 1;
            });
            this.updatePreview();
        },

        addBook() {
            this.form.books.push({
                title: '',
                author: '',
                edition: '',
                publication: '',
                isbn: ''
            });
        },

        removeBook(index) {
            this.form.books.splice(index, 1);
            this.updatePreview();
        },

        addSoftware() {
            this.form.software_websites.push({
                name: '',
                url: '',
                description: ''
            });
        },

        removeSoftware(index) {
            this.form.software_websites.splice(index, 1);
            this.updatePreview();
        },

        addEquipment() {
            this.form.equipment_list.push({
                s_no: this.form.equipment_list.length + 1,
                name: '',
                specifications: ''
            });
        },

        removeEquipment(index) {
            this.form.equipment_list.splice(index, 1);
            this.form.equipment_list.forEach((eq, i) => {
                eq.s_no = i + 1;
            });
            this.updatePreview();
        },

        // Update Mapping Matrix based on COs
        updateMappingMatrix() {
            const newMatrix = [];
            this.form.course_outcomes.forEach((outcome) => {
                // Check if mapping already exists for this CO
                const existing = this.form.mapping_matrix.find(m => m.co_code === outcome.code);
                if (existing) {
                    newMatrix.push(existing);
                } else {
                    newMatrix.push({
                        co_code: outcome.code,
                        po1: '-', po2: '-', po3: '-', po4: '-', po5: '-', po6: '-', po7: '-',
                        pso1: '-', pso2: '-', pso3: '-', pso4: '-'
                    });
                }
            });
            this.form.mapping_matrix = newMatrix;

            // Also update question paper profile based on units
            this.updateQuestionPaperProfile();
        },

        // Update Question Paper Profile based on Units
        updateQuestionPaperProfile() {
            if (this.detectedLevel === 4) return; // Level 4 doesn't need paper profile

            const newProfile = [];
            this.form.units.forEach((unit) => {
                const existing = this.form.question_paper_profile.find(p => p.unit_no === unit.unit_no);
                if (existing) {
                    newProfile.push(existing);
                } else {
                    newProfile.push({
                        unit_no: unit.unit_no,
                        two_mark_count: 0,
                        four_mark_count: 0
                    });
                }
            });
            this.form.question_paper_profile = newProfile;
        },

        // Calculate 1.35x Weightage for question paper
        calculateWeightage(profile) {
            const marks = (parseInt(profile.two_mark_count) || 0) * 2 +
                (parseInt(profile.four_mark_count) || 0) * 4;
            return Math.round(marks * 1.35 * 100) / 100;
        },

        // Convert H/M/L/- to numeric value for calculations
        getMappingValue(val) {
            switch (val) {
                case 'H': return 3;
                case 'M': return 2;
                case 'L': return 1;
                default: return 0;
            }
        },

        // Calculate average for a specific PO/PSO column
        getColumnAverage(column) {
            if (this.form.mapping_matrix.length === 0) return 0;

            let sum = 0;
            let count = 0;

            this.form.mapping_matrix.forEach(mapping => {
                const value = this.getMappingValue(mapping[column]);
                if (value > 0) {
                    sum += value;
                    count++;
                }
            });

            return count > 0 ? sum / count : 0;
        },

        // Get mapping completion status for validation
        getMappingCompletionStatus() {
            if (this.form.mapping_matrix.length === 0) {
                return {
                    complete: false,
                    message: 'No Course Outcomes defined yet.',
                    mappedCOs: 0,
                    activePOs: 0,
                    activePSOs: 0
                };
            }

            // Count COs that have at least one mapping
            let mappedCOs = 0;
            this.form.mapping_matrix.forEach(mapping => {
                const hasMappings = ['po1', 'po2', 'po3', 'po4', 'po5', 'po6', 'po7', 'pso1', 'pso2', 'pso3', 'pso4']
                    .some(col => this.getMappingValue(mapping[col]) > 0);
                if (hasMappings) mappedCOs++;
            });

            // Count POs that have at least one mapping
            const activePOs = ['po1', 'po2', 'po3', 'po4', 'po5', 'po6', 'po7']
                .filter(po => this.getColumnAverage(po) > 0).length;

            // Count PSOs that have at least one mapping
            const activePSOs = ['pso1', 'pso2', 'pso3', 'pso4']
                .filter(pso => this.getColumnAverage(pso) > 0).length;

            const totalCOs = this.form.mapping_matrix.length;
            const complete = mappedCOs === totalCOs && activePOs >= 3 && activePSOs >= 1;

            let message = '';
            if (complete) {
                message = 'CO-PO Mapping is complete. All COs are mapped with adequate PO/PSO coverage.';
            } else if (mappedCOs < totalCOs) {
                message = `${totalCOs - mappedCOs} CO(s) have no mappings. Each CO should map to at least one PO.`;
            } else if (activePOs < 3) {
                message = 'At least 3 POs should have mappings for proper coverage.';
            } else if (activePSOs < 1) {
                message = 'At least 1 PSO should have mappings.';
            }

            return {
                complete,
                message,
                mappedCOs,
                activePOs,
                activePSOs
            };
        },

        // Calculate total paper marks
        getTotalPaperMarks() {
            let total = 0;
            this.form.question_paper_profile.forEach(p => {
                total += (parseInt(p.two_mark_count) || 0) * 2 +
                    (parseInt(p.four_mark_count) || 0) * 4;
            });
            return total;
        },

        // Enforce special rules for Level 0 (audit courses)
        enforceAuditLevelConstraints() {
            if (this.detectedLevel === 0) {
                // No credits, no marks
                this.form.teaching_scheme.credits = 0;
                this.form.examination_scheme.fa_th_max = 0;
                this.form.examination_scheme.fa_th_min = 0;
                this.form.examination_scheme.sa_th_max = 0;
                this.form.examination_scheme.sa_th_min = 0;
                this.form.examination_scheme.sa_pr_max = 0;
                this.form.examination_scheme.sa_pr_min = 0;
                this.form.examination_scheme.tw_marks = 0;

                // No specification table or question paper profile
                this.form.specification_table = [];
                this.form.question_paper_profile = [];
            }
        },

        // Get academic year
        getAcademicYear() {
            const now = new Date();
            const month = now.getMonth() + 1; // 0-indexed
            let startYear = now.getFullYear();

            // Academic year starts in July
            if (month < 7) {
                startYear = startYear - 1;
            }

            const endYear = (startYear + 1) % 100;
            return startYear + '-' + endYear.toString().padStart(2, '0');
        },

        // Navigation
        nextStep() {
            if (this.currentStep < this.steps.length - 1) {
                this.currentStep++;
            }
        },

        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
            }
        },

        // Form Actions
        saveStep() {
            if (this.currentStep < this.steps.length - 1) {
                this.nextStep();
            } else {
                this.submitForm();
            }
        },

        buildPayload(extra = {}) {
            const formEl = document.getElementById('syllabusForm');
            const methodOverride = formEl?.querySelector('input[name="_method"]')?.value;

            const payload = {
                ...this.form,
                ...extra,
            };

            if (methodOverride) {
                payload._method = methodOverride;
            }

            return payload;
        },

        saveAsDraft() {
            const form = document.getElementById('syllabusForm');
            const actionUrl = form?.getAttribute('action') || window.location.href;
            const payload = this.buildPayload({ status: 'draft' });

            fetch(actionUrl, {
                method: 'POST',
                body: JSON.stringify(payload),
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                redirect: 'follow'
            })
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                    } else if (response.ok) {
                        alert('Saved as draft!');
                        // Reload to show the saved draft in the list
                        window.location.href = '/syllabi';
                    } else {
                        if (response.status === 422) {
                            return response.json().then(data => {
                                const first = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
                                throw new Error(first || 'Validation failed');
                            });
                        }
                        throw new Error('Save failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error saving draft: ' + error.message);
                });
        },

        submitForm() {
            const form = document.getElementById('syllabusForm');
            const actionUrl = form?.getAttribute('action') || window.location.href;
            const payload = this.buildPayload({ status: 'submitted' });

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.textContent : 'Submit';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
            }

            fetch(actionUrl, {
                method: 'POST',
                body: JSON.stringify(payload),
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                redirect: 'follow'
            })
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                    } else if (response.ok) {
                        // Success - navigate to syllabi list
                        window.location.href = '/syllabi';
                    } else {
                        if (response.status === 422) {
                            return response.json().then(data => {
                                console.error('Validation errors:', data);
                                const first = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
                                throw new Error(first || 'Validation failed');
                            });
                        }

                        return response.text().then(text => {
                            console.error('Server error:', text);
                            throw new Error('Form submission failed: ' + response.status);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error submitting form: ' + error.message);
                    // Re-enable submit button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                });
        },

        // Sync Scheme Type when Programme selection changes (for manual entry)
        onProgrammeChange() {
            if (this.programmesMetadata && this.programmesMetadata[this.form.program_name]) {
                this.form.scheme_type = this.programmesMetadata[this.form.program_name];
                console.log('Inherited scheme type:', this.form.scheme_type);
                this.onSchemeTypeChange();
            }
            this.updatePreview();
        },

        updatePreview() {
            // Preview updates automatically via Alpine.js reactivity
            // This method can be used for additional preview logic if needed
        }
    };
}

// Provide safe global fallbacks so Alpine expressions referencing
// form/detectedLevel/helpers never throw ReferenceError, even if the
// component is not initialized for some reason on a given page.
if (typeof window !== 'undefined') {
    if (typeof window.form === 'undefined') {
        window.form = {};
    }
    if (typeof window.detectedLevel === 'undefined') {
        window.detectedLevel = null;
    }
    if (typeof window.getTotalPaperMarks === 'undefined') {
        window.getTotalPaperMarks = function () {
            return 0;
        };
    }
    if (typeof window.calculateWeightage === 'undefined') {
        window.calculateWeightage = function () {
            return 0;
        };
    }

    // Export for use in Blade templates
    window.syllabusForm = syllabusForm;
}
