<?php

namespace App\Jobs;

use App\Models\Syllabus;
use App\Services\DocxService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateDocxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Syllabus $syllabus)
    {
    }

    public function handle(DocxService $docxService): void
    {
        $docxService->generate($this->syllabus);
    }
}
