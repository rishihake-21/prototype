<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This table is unused; course marks are stored in course_assessments linked to scheme_assessment_components.
        Schema::dropIfExists('assessment_components');
    }

    public function down(): void
    {
        // Intentionally no-op.
    }
};

