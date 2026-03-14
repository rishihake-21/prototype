<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The application uses the singular table name via ProgrammeStructure::$table.
        if (! Schema::hasTable('programme_structure')) {
            return;
        }

        Schema::table('programme_structure', function (Blueprint $table) {
            if (! Schema::hasColumn('programme_structure', 'audit_count')) {
                $table->unsignedInteger('audit_count')->default(0)->after('elective_count');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('programme_structure')) {
            return;
        }

        Schema::table('programme_structure', function (Blueprint $table) {
            if (Schema::hasColumn('programme_structure', 'audit_count')) {
                $table->dropColumn('audit_count');
            }
        });
    }
};

