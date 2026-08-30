# Scheduling Domain

This boundary owns timetable business rules and generation logic.

## Planned structure

- `Rules/` — hard and soft scheduling constraints
- `Services/` — conflict detection and orchestration
- `Generator/` — candidate generation and schedule optimization
- `DTOs/` — typed application inputs/outputs

Controllers should remain thin and delegate scheduling decisions to this domain.
