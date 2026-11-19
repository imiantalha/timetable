# Scheduling Domain

This directory owns timetable generation and scheduling constraints. HTTP controllers delegate placement and conflict decisions to these classes.

## Components

- **GenerationPlan** — expands each selected course into the number of weekly sessions configured on the course and carries its required duration into each assignment
- **TimetableGenerator** — synchronous first-fit generator used by the current API endpoint
- **OptimizedTimetableGenerator** — optimized generator used by the queued job implementation
- **AvailabilityConstraint** — checks teacher and room availability windows; resources without configured availability are unrestricted
- **ConflictDetector** — checks teacher, section and room collisions across overlapping time ranges and enforces room capacity
- **ScheduleScorer** — scores completed schedules and penalizes unplaced sessions and gaps

## Hard constraints

A placement must not:

- Overlap another class for the same teacher, section or room
- Exceed room capacity
- Use a break slot or a slot shorter than the course duration
- Fall outside configured teacher or room availability
- Duplicate an existing course session in the same timetable

Generation is transactional. A timetable can only be generated while it is a draft and while it has no existing entries. Published schedules cannot be edited.
