<?php

namespace App\Services;

use App\Models\Syllabus;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class DocxService
{
    public function generate(Syllabus $syllabus): string
    {
        $phpWord = new PhpWord();
        
        $section = $phpWord->addSection();

        $section->addText(
            $this->clean(config('sms.institution_name', 'Institution Name')),
            ['bold' => true, 'size' => 16],
            ['alignment' => Jc::CENTER]
        );
        
        $section->addTextBreak();
        
        $section->addText(
            'Course Syllabus',
            ['bold' => true, 'size' => 14],
            ['alignment' => Jc::CENTER]
        );
        
        $section->addTextBreak(2);
        
        $this->addField($section, 'Course Title', $syllabus->title);
        $this->addField($section, 'Course Code', $syllabus->course_code);
        $this->addField($section, 'Programme', $syllabus->program_name ?? '');
        $this->addField($section, 'Academic Year', $syllabus->academic_year ?? '');
        $this->addField($section, 'Credits', (string) ($syllabus->teaching_scheme['credits'] ?? ''));
        
        $section->addTextBreak();
        
        if (!empty($syllabus->rationale)) {
            $section->addText('Rationale', ['bold' => true, 'size' => 12]);
            $section->addText($this->clean($syllabus->rationale));
            $section->addTextBreak();
        }
        
        // Course Objectives
        $objectives = $syllabus->course_objectives ?? [];
        if (!empty($objectives)) {
            $section->addText('Course Objectives', ['bold' => true, 'size' => 12]);
            foreach ($objectives as $objective) {
                $section->addListItem($this->clean($objective), 0);
            }
            $section->addTextBreak();
        }
 
        // Course Outcomes
        $outcomes = $syllabus->course_outcomes ?? [];
        if (!empty($outcomes)) {
            $section->addText('Course Outcomes (COs)', ['bold' => true, 'size' => 12]);
            foreach ($outcomes as $outcome) {
                $code = $outcome['code'] ?? '';
                $desc = $outcome['description'] ?? '';
                $section->addListItem($this->clean(trim($code . ' ' . $desc)), 0);
            }
            $section->addTextBreak();
        }

        // Units table with Topics & Sub-topics
        $level = $syllabus->getCourseLevel();
        if ($level === 4) {
             // Training Schedule for Level 4
            $schedule = $syllabus->training_schedule ?? [];
            if (!empty($schedule)) {
                $section->addText('Training Schedule', ['bold' => true, 'size' => 12]);
                $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
                $table->addRow();
                $table->addCell(1000)->addText('Week', ['bold' => true]);
                $table->addCell(5000)->addText('Activity', ['bold' => true]);
                $table->addCell(1500)->addText('Industry', ['bold' => true]);
                $table->addCell(1500)->addText('Mentor', ['bold' => true]);
                
                foreach ($schedule as $week) {
                    $table->addRow();
                    $table->addCell(1000)->addText($this->clean((string)$week['week_no']));
                    $table->addCell(5000)->addText($this->clean($week['activity']));
                    $table->addCell(1500)->addText($this->clean($week['marks_industry'] ?? '-'));
                    $table->addCell(1500)->addText($this->clean($week['marks_mentor'] ?? '-'));
                }
                $section->addTextBreak();
            }
        } else {
            $units = $syllabus->units ?? [];
            if (!empty($units)) {
                $section->addText('Units, Topics & Sub-topics', ['bold' => true, 'size' => 12]);
                $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
                $table->addRow();
                $table->addCell(800)->addText('Unit', ['bold' => true]);
                $table->addCell(2600)->addText('Title', ['bold' => true]);
                $table->addCell(1200)->addText('Hours', ['bold' => true]);
                $table->addCell(5000)->addText('Learning Outcomes', ['bold' => true]);

                foreach ($units as $unit) {
                    $table->addRow();
                    $table->addCell(800)->addText($this->clean((string)($unit['unit_no'] ?? '')));
                    $table->addCell(2600)->addText($this->clean($unit['title'] ?? ''));
                    $table->addCell(1200)->addText($this->clean((string)($unit['hours'] ?? '')));
                    $table->addCell(5000)->addText($this->clean($unit['cognitive_outcomes'] ?? ''));
                }
                $section->addTextBreak();
            }

            // Specification Table (Levels 2 & 5)
            if (($level === 2 || $level === 5) && !empty($syllabus->specification_table)) {
                $section->addText('Theory Specification', ['bold' => true, 'size' => 12]);
                $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
                $table->addRow();
                $table->addCell(2000)->addText('Unit', ['bold' => true]);
                $table->addCell(2000)->addText('R', ['bold' => true]);
                $table->addCell(2000)->addText('U', ['bold' => true]);
                $table->addCell(2000)->addText('A', ['bold' => true]);
                $table->addCell(2000)->addText('Total', ['bold' => true]);

                foreach ($syllabus->specification_table as $spec) {
                    $table->addRow();
                    $table->addCell(2000)->addText((string)$spec['unit_no']);
                    $table->addCell(2000)->addText((string)($spec['r'] ?? 0));
                    $table->addCell(2000)->addText((string)($spec['u'] ?? 0));
                    $table->addCell(2000)->addText((string)($spec['a'] ?? 0));
                    $total = (int)($spec['r'] ?? 0) + (int)($spec['u'] ?? 0) + (int)($spec['a'] ?? 0);
                    $table->addCell(2000)->addText((string)$total);
                }
                $section->addTextBreak();
            }
        }

        // Practical Tasks
        if (!empty($syllabus->practical_tasks)) {
            $section->addText($level === 4 ? 'Practical Exercises' : 'Practical Tasks', ['bold' => true, 'size' => 12]);
            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
            $table->addRow();
            $table->addCell(1000)->addText('S.No', ['bold' => true]);
            $table->addCell(1000)->addText('Unit', ['bold' => true]);
            $table->addCell(5000)->addText('Title', ['bold' => true]);
            $table->addCell(1000)->addText('Hrs', ['bold' => true]);
            $table->addCell(1000)->addText('CO', ['bold' => true]);

            foreach ($syllabus->practical_tasks as $task) {
                $table->addRow();
                $m = ($task['is_mandatory'] ?? false) ? '*' : '';
                $table->addCell(1000)->addText($this->clean($task['s_no'] . $m));
                $table->addCell(1000)->addText($this->clean((string)($task['unit_no'] ?? '-')));
                $table->addCell(5000)->addText($this->clean($task['title']));
                $table->addCell(1000)->addText($this->clean((string)$task['hours']));
                $table->addCell(1000)->addText($this->clean($task['co_code'] ?? '-'));
            }
            $section->addTextBreak();
        }
        
        // Resources (books, software, equipment)
        if (!empty($syllabus->books)) {
            $section->addText('Course Resources: Books', ['bold' => true, 'size' => 12]);
            foreach ($syllabus->books as $book) {
                $parts = array_filter([$this->clean($book['author'] ?? ''), $this->clean($book['title'] ?? ''), $this->clean($book['edition'] ?? ''), $this->clean($book['publication'] ?? ''), $this->clean($book['isbn'] ?? '')]);
                $section->addListItem($this->clean(implode(', ', $parts)), 0);
            }
            $section->addTextBreak();
        }

        if (!empty($syllabus->software_websites)) {
            $section->addText('Software/Websites', ['bold' => true, 'size' => 12]);
            foreach ($syllabus->software_websites as $sw) {
                $text = ($sw['name'] ?? '') . ($sw['url'] ? ' (' . $sw['url'] . ')' : '');
                $section->addListItem($this->clean($text), 0);
            }
            $section->addTextBreak();
        }

        // CO-PO Mapping
        if (!empty($syllabus->mapping_matrix)) {
            $section->addText('CO-PO Mapping Matrix', ['bold' => true, 'size' => 12]);
            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
            $table->addRow();
            $table->addCell(800)->addText('CO', ['bold' => true]);
            for($i=1; $i<=7; $i++) $table->addCell(600)->addText("PO$i", ['bold' => true]);
            for($i=1; $i<=4; $i++) $table->addCell(800)->addText("PSO$i", ['bold' => true]);

            foreach ($syllabus->mapping_matrix as $mapping) {
                $table->addRow();
                $table->addCell(800)->addText($this->clean($mapping['co_code']));
                for($i=1; $i<=7; $i++) $table->addCell(600)->addText($this->clean($mapping["po$i"] ?? '-'));
                for($i=1; $i<=4; $i++) $table->addCell(800)->addText($this->clean($mapping["pso$i"] ?? '-'));
            }
            $section->addTextBreak();
        }

        // Question Paper Profile
        if ($level !== 4 && !empty($syllabus->question_paper_profile)) {
            $section->addText('Question Paper Profile', ['bold' => true, 'size' => 12]);
            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
            $table->addRow();
            $table->addCell(2000)->addText('Unit', ['bold' => true]);
            $table->addCell(2000)->addText('2-Mark Q', ['bold' => true]);
            $table->addCell(2000)->addText('4-Mark Q', ['bold' => true]);
            $table->addCell(2000)->addText('Marks', ['bold' => true]);
            $table->addCell(2000)->addText('1.35x', ['bold' => true]);

            $totalMarks = 0;
            $saThMax = (int)($syllabus->examination_scheme['sa_th_max'] ?? 0);
            foreach ($syllabus->question_paper_profile as $profile) {
                $marks = (int)($profile['two_mark_count'] ?? 0) * 2 + (int)($profile['four_mark_count'] ?? 0) * 4;
                $totalMarks += $marks;
                $weightage = $saThMax > 0 ? round(($marks / ($saThMax * 1.35)) * 100, 1) . '%' : '0%';
                
                $table->addRow();
                $table->addCell(2000)->addText($this->clean((string)$profile['unit_no']));
                $table->addCell(2000)->addText($this->clean((string)($profile['two_mark_count'] ?? 0)));
                $table->addCell(2000)->addText($this->clean((string)($profile['four_mark_count'] ?? 0)));
                $table->addCell(2000)->addText($this->clean((string)$marks));
                $table->addCell(2000)->addText($this->clean($weightage));
            }
            $table->addRow();
            $table->addCell(6000, ['gridSpan' => 3])->addText('Total:', ['bold' => true], ['alignment' => Jc::RIGHT]);
            $table->addCell(2000)->addText((string)$totalMarks, ['bold' => true]);
            $table->addCell(2000)->addText('100%', ['bold' => true]);
            $section->addTextBreak();
        }

        // Certification
        $section->addPageBreak();
        $section->addText('Certificate', ['bold' => true, 'size' => 14], ['alignment' => Jc::CENTER]);
        $section->addText('Syllabus Approval Document', ['size' => 10], ['alignment' => Jc::CENTER]);
        $section->addTextBreak();
        
        $this->addField($section, 'Course Title', $syllabus->title);
        $this->addField($section, 'Course Code', $syllabus->course_code);
        $this->addField($section, 'Programme', $syllabus->program_name);
        $this->addField($section, 'Academic Year', $syllabus->academic_year ?? \App\Models\Syllabus::getAcademicYear());
        $section->addTextBreak();

        $section->addText(
            $this->clean("This is to certify that the syllabus for the above-mentioned course has been reviewed and approved by the Curriculum Development Committee (CDC) for implementation in the academic year " . ($syllabus->academic_year ?? \App\Models\Syllabus::getAcademicYear()) . "."),
            [],
            ['alignment' => Jc::BOTH]
        );
        $section->addTextBreak(2);

        $sigTable = $section->addTable();
        $sigTable->addRow();
        $sigTable->addCell(3000)->addText($this->clean($syllabus->certification_signatures['hod'] ?? '________________'), ['bold' => true], ['alignment' => Jc::CENTER]);
        $sigTable->addCell(3000)->addText($this->clean($syllabus->certification_signatures['principal'] ?? '________________'), ['bold' => true], ['alignment' => Jc::CENTER]);
        $sigTable->addCell(3000)->addText($this->clean($syllabus->certification_signatures['cdc_incharge'] ?? '________________'), ['bold' => true], ['alignment' => Jc::CENTER]);
        
        $sigTable->addRow();
        $sigTable->addCell(3000)->addText('Head of Department', [], ['alignment' => Jc::CENTER]);
        $sigTable->addCell(3000)->addText('Principal', [], ['alignment' => Jc::CENTER]);
        $sigTable->addCell(3000)->addText('CDC Incharge', [], ['alignment' => Jc::CENTER]);

        $filename = 'syllabus_' . $syllabus->course_code . '_v' . $syllabus->version_number . '_' . time() . '.docx';
        $path = 'syllabi/docx/' . $filename;
        $fullPath = Storage::path($path);
        
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($fullPath);
        
        return $path;
    }

    private function clean(mixed $text): string
    {
        if ($text === null) return '';
        $text = (string)$text;
        
        // Remove illegal XML control characters that break Word documents
        // Disallowed in XML 1.0: #x0-#x8, #xB-#xC, #xE-#x1F
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);
        
        return $text;
    }

    /**
     * Parse the free-text "topics & sub-topics" field into a structured
     * array of topics with nested subtopics, based on simple line
     * conventions:
     *  - New topic: any non-empty line not starting with -, * or •
     *  - Subtopic: lines starting with -, * or • attach to the last topic
     */
    private function parseTopics(?string $raw): array
    {
        if (!$raw) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $topics = [];
        $current = null;

        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '') {
                continue;
            }

            $firstChar = $trim[0];
            $isSubtopic = in_array($firstChar, ['-', '*', '•'], true);

            if ($isSubtopic) {
                $text = ltrim(substr($trim, 1));
                if ($current === null) {
                    $current = ['text' => $text, 'subtopics' => []];
                } else {
                    $current['subtopics'][] = $text;
                }
            } else {
                if ($current !== null) {
                    $topics[] = $current;
                }
                $current = ['text' => $trim, 'subtopics' => []];
            }
        }

        if ($current !== null) {
            $topics[] = $current;
        }

        return $topics;
    }

    private function addField($section, string $label, string $value): void
    {
        $table = $section->addTable();
        $table->addRow();
        $table->addCell(2000)->addText($this->clean($label) . ':', ['bold' => true]);
        $table->addCell(6000)->addText($this->clean($value));
    }

    public function download(Syllabus $syllabus)
    {
        $path = $this->generate($syllabus);
        $fullPath = Storage::path($path);
        
        return response()->download($fullPath, 'syllabus_' . $syllabus->course_code . '_v' . $syllabus->version_number . '.docx')
            ->deleteFileAfterSend();
    }
}
