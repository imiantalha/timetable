# Database Design

The database is PostgreSQL and is designed around academic resources and schedule assignments.

## Core entities

```text
Institution
 ├── Department ── Course
 ├── Teacher
 ├── Student ── Section
 └── Room

Academic Year ── Semester ── Timetable ── Timetable Entry
                                      │
                 ┌────────────────────┼────────────────────┐
                 ▼                    ▼                    ▼
               Course              Teacher              Section
                                      │
                                    Room + Time Slot
```

## Planned tables

- users
- roles / permissions
- institutions
- departments
- teachers
- students
- sections
- courses
- rooms
- academic_years
- semesters
- time_slots
- timetables
- timetable_entries
- teacher_availabilities
- room_availabilities

## Timetable entry

A timetable entry associates a course, teacher, section, room and time slot within a timetable/semester.

The schema will enforce relational integrity and add database-level indexes/unique constraints where they are safe and useful. Scheduling conflicts that depend on business rules will be validated in the application/domain layer.

## Future scheduling data

Course session requirements and generator configuration will be added as the scheduling engine is implemented rather than prematurely coupling the initial schema to one algorithm.
