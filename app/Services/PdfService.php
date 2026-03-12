<?php

namespace App\Services;

use App\Models\Syllabus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    public function generate(Syllabus $syllabus): string
    {
        $pdf = Pdf::loadView('pdf.syllabus', [
            'syllabus' => $syllabus,
            'institutionName' => config('sms.institution_name', 'Institution Name'),
        ]);

        $pdf->setPaper(config('sms.pdf_paper_size', 'A4'));

        $filename = 'syllabus_' . $syllabus->course_code . '_v' . $syllabus->version_number . '_' . time() . '.pdf';
        $path = 'syllabi/pdfs/' . $filename;

        Storage::put($path, $pdf->output());

        return $path;
    }

    public function stream(Syllabus $syllabus)
    {
        $pdf = Pdf::loadView('pdf.syllabus', [
            'syllabus' => $syllabus,
            'institutionName' => config('sms.institution_name', 'Institution Name'),
        ]);

        $pdf->setPaper(config('sms.pdf_paper_size', 'A4'));

        return $pdf->stream('syllabus_' . $syllabus->course_code . '.pdf');
    }

    public function download(Syllabus $syllabus)
    {
        $pdf = Pdf::loadView('pdf.syllabus', [
            'syllabus' => $syllabus,
            'institutionName' => config('sms.institution_name', 'Institution Name'),
        ]);

        $pdf->setPaper(config('sms.pdf_paper_size', 'A4'));

        return $pdf->download('syllabus_' . $syllabus->course_code . '_v' . $syllabus->version_number . '.pdf');
    }
}
