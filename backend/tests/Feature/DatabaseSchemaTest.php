<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_core_scheduling_tables_are_created(): void
    {
        foreach ([
            'institutions',
            'users',
            'departments',
            'courses',
            'teachers',
            'sections',
            'rooms',
            'time_slots',
            'timetables',
            'timetable_entries',
            'teacher_availabilities',
            'room_availabilities',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected the {$table} table to exist.");
        }
    }
}
