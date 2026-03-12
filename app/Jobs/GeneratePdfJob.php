<?php

namespace App\Jobs;

use App\Models\Syllabus;
use App\Services\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Syllabus $syllabus)
    {
    }

    public function handle(PdfService $pdfService): void
    {
        $path = $pdfService->generate($this->syllabus);
        
        $this->syllabus->update(['file_path' => $path]);
    }
}
