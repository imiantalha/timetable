# Scheduling Domain

The scheduling domain contains timetable-generation and validation rules. It is kept separate from HTTP controllers so scheduling behavior can be reused by API workflows and tested independently.

## Current components

| Component | Responsibility |
|---|---|
| `TimetableGenerator` | Deterministic first-fit placement. Tries supplied time slots in order and persists an entry only when no hard conflict is detected. Returns created entries and unplaced assignments. |
| `OptimizedTimetableGenerator` | Orders assignments by sessions per week, checks teacher/room availability and conflicts, then returns entries, unplaced assignments, and a score. |
| `ConflictDetector` | Detects teacher, section, and room overlaps in the same timetable/time slot, plus room-capacity violations. |
| `AvailabilityConstraint` | Checks whether a teacher or room has an availability window covering a proposed time slot. |
| `ScheduleScorer` | Calculates a penalty score for unplaced assignments and gaps within a section's daily schedule. A lower score is better. |

## Current request flow

The `POST /api/v1/timetables/{timetable}/generate` route is handled by `TimetableGenerationController`. It validates the request, loads the requested time slots, and calls `TimetableGenerator` inside a database transaction. The response reports the timetable ID, number of created entries, number of unplaced assignments, and unplaced assignment details.

The controller currently uses the deterministic generator. `OptimizedTimetableGenerator` and `GenerateTimetableJob` exist in the codebase but are not wired into this route yet.

## Conflict rules

The current conflict detector checks:

- A teacher already assigned to another entry in the same time slot.
- A section already assigned to another entry in the same time slot.
- A room already occupied in the same time slot.
- A room whose capacity is smaller than the section capacity.

Availability checks are implemented separately and are used by the optimized generator.

## Design boundaries

- Controllers and form requests handle HTTP input, validation, and response formatting.
- Domain classes own scheduling decisions and conflict rules.
- Eloquent models represent persisted academic and timetable records.
- Jobs provide a place for background processing; queue dispatch should be wired explicitly when asynchronous generation is enabled.

## Extension points

When extending this domain, keep hard constraints separate from scoring preferences, avoid duplicating validation in controllers, and add tests for both valid placements and rejected conflicts.
