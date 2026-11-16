# Database Design

The backend uses Laravel migrations and Eloquent models. The database driver is configured through Laravel's environment settings; the schema is expressed with Laravel's schema builder rather than being tied in this document to a specific database engine.

## Entity overview

```text
Institution
 ├── Departments ── Courses
 │       └───────── Sections
 ├── Teachers ── Teacher Availability
 ├── Students ── Section (optional)
 ├── Rooms ── Room Availability
 ├── Academic Years ── Semesters
 ├── Time Slots
 └── Timetables ── Timetable Entries
                         ├── Course
                         ├── Teacher
                         ├── Section
                         ├── Room
                         └── Time Slot

Semester ── Timetables
```

## Implemented tables

| Table | Purpose / key relationships |
|---|---|
| `institutions` | Institution name, unique code, timezone, and active state. |
| `departments` | Belongs to an institution; department code is unique within that institution. |
| `courses` | Belongs to a department; stores course code, credits, sessions per week, and duration. |
| `teachers` | Belongs to an institution; employee number is unique within the institution. |
| `students` | Belongs to an institution and optionally to a section. |
| `sections` | Belongs to a department and stores a capacity used during room-capacity validation. |
| `rooms` | Belongs to an institution and stores capacity and room type. |
| `academic_years` | Belongs to an institution and stores its date range and active state. |
| `semesters` | Belongs to an academic year and stores its date range and active state. |
| `time_slots` | Belongs to an institution; stores weekday, start/end times, and break flag. |
| `timetables` | Belongs to an institution and semester; stores name, draft/published status, and publication timestamp. |
| `timetable_entries` | Joins a timetable, course, teacher, section, room, and time slot; includes session number. |
| `teacher_availabilities` | Teacher availability windows by weekday and time range. |
| `room_availabilities` | Room availability windows by weekday and time range. |

Laravel's default user and Sanctum personal-access-token migrations also provide the authentication tables.

## Timetable entries and conflict checks

A timetable entry assigns one course, teacher, section, and room to one time slot within a timetable.

The migration adds lookup indexes for timetable/time slot, teacher/time slot, section/time slot, and room/time slot. These support the conflict detector's checks for simultaneous assignments. Foreign keys protect the relationships and define deletion behavior.

The application-level conflict detector currently checks:

- Teacher overlap in the same timetable and time slot.
- Section overlap in the same timetable and time slot.
- Room overlap in the same timetable and time slot.
- Room capacity against section capacity.

Teacher and room availability are stored separately and evaluated by the availability constraint service.

## Data integrity

Foreign keys and selected unique constraints are defined in migrations, including institution-scoped department, teacher, and room identifiers and department-scoped course and section codes.

Scheduling rules that involve multiple related records are validated in the application/domain layer. The schema should be extended with database constraints only where they can be expressed safely without preventing valid timetable use cases.

## Source of truth

The current schema is defined in `backend/database/migrations/`. Update this document alongside migrations whenever tables, relationships, indexes, or constraint behavior change.
