<?php

declare(strict_types=1);

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('timetable_entries', function (Blueprint $table): void {
            $table->dropUnique('timetable_entries_timetable_id_course_id_session_number_unique');
            $table->unique(
                ['timetable_id', 'course_id', 'section_id', 'session_number'],
                'timetable_entries_course_section_session_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('timetable_entries', function (Blueprint $table): void {
            $table->dropUnique('timetable_entries_course_section_session_unique');
            $table->unique(
                ['timetable_id', 'course_id', 'session_number'],
                'timetable_entries_timetable_id_course_id_session_number_unique',
            );
        });
    }
};
