# Database Design

PostgreSQL is the primary database. The migrations create the following tables:

- **Identity and tenancy:** institutions, users, personal_access_tokens
- **Academic resources:** departments, courses, teachers, students, sections, rooms
- **Academic periods:** academic_years, semesters
- **Scheduling:** time_slots, timetables, timetable_entries
- **Availability:** teacher_availabilities, room_availabilities

## Relationships

~~~text
Institution
 ├── Department ── Course
 │       └── Section
 ├── Teacher
 ├── Student ── Section
 ├── Room
 ├── TimeSlot
 └── AcademicYear ── Semester ── Timetable ── TimetableEntry
~~~

Users belong optionally to an institution. Institution-scoped API access is enforced for authenticated users; an administrator without an institution assignment is treated as a platform administrator.

## Timetable entries

Each entry associates one timetable, course, teacher, section, room, time slot and session number. A unique database constraint prevents the same course session from being inserted twice into one timetable.

Indexes support lookups by timetable/time slot and by teacher, section or room/time slot. The scheduling domain additionally detects overlaps between time ranges, not only identical time-slot IDs.

## Course and time-slot rules

Courses store credits, sessions_per_week and duration_minutes. Generation expands a course into its required weekly sessions and only uses slots long enough for the course.

Time slots store a day_of_week (0–6), starts_at, ends_at and is_break flag. Overlapping time-slot definitions for the same institution/day are rejected.

## Availability

Teacher and room availability rows define a day and an available time interval. If a resource has no availability rows, it is treated as unrestricted. Once availability rows exist, a selected slot must fit entirely inside an available interval.
