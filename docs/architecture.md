# Architecture

## Overview

Smart Timetable is split into a Next.js frontend and a Laravel API. The frontend handles timetable configuration and presentation; the backend owns academic resources, persistence, validation, and scheduling rules.

```text
┌────────────────────────────────┐
│ Next.js 16 / React 19           │
│ Dashboard · Timetable grid      │
│ Generation panel · API helpers  │
└───────────────┬────────────────┘
                │ HTTP / JSON
                ▼
┌────────────────────────────────┐
│ Laravel 13 API                  │
│ Routes · Form Requests          │
│ Controllers · Sanctum           │
└───────────────┬────────────────┘
                │
                ▼
┌────────────────────────────────┐
│ Scheduling Domain               │
│ Generator · Conflict Detector   │
│ Availability · Schedule Scorer  │
└───────────────┬────────────────┘
                │ Eloquent ORM
                ▼
┌────────────────────────────────┐
│ Configured relational database  │
│ Academic data · Timetable data  │
└────────────────────────────────┘
```

The database engine is selected through Laravel's database configuration and environment variables; the migrations use Laravel's schema builder.

## Frontend

The frontend lives in `frontend/` and uses the Next.js App Router, React, and TypeScript.

- Dashboard routes are under `frontend/app/dashboard`.
- The timetable builder and generation panel are under `frontend/app/dashboard/timetables`.
- API clients and request helpers are under `frontend/lib`.
- The API base URL is configured with `NEXT_PUBLIC_API_URL`; the default is `http://localhost:8000/api/v1`.
- The API client reads the Sanctum bearer token from browser local storage using the `auth_token` key.

The current timetable page loads timetable entries for timetable ID `1`, supports drag-and-drop moves, checks conflicts, and exposes a publish action. It is an early dashboard workflow rather than a configurable multi-timetable administration experience.

## Backend boundaries

The backend lives in `backend/` and follows Laravel conventions.

```text
backend/app/
├── Domain/
│   └── Scheduling/
├── Http/
│   ├── Controllers/Api/V1/
│   ├── Middleware/
│   └── Requests/
├── Jobs/
└── Models/
```

- **Routes** in `backend/routes/api.php` expose versioned endpoints under `/api/v1`.
- **Form Requests** validate incoming resource, generation, and move payloads.
- **Controllers** coordinate HTTP requests and delegate scheduling decisions.
- **Domain/Scheduling** contains generation, availability, conflict detection, and scoring logic.
- **Jobs** contains a queued generation job; the current generation controller does not dispatch it.
- **Models** represent academic resources, availability windows, timetables, and entries.

All API routes except `GET /api/v1/health` are protected by `auth:sanctum`.

## Scheduling flow

The current generation endpoint:

1. Validates the submitted assignments and time-slot IDs.
2. Loads the selected time slots in day/time order.
3. Runs the deterministic first-fit generator inside a database transaction.
4. Checks each candidate against teacher, section, and room overlaps and room capacity.
5. Persists conflict-free entries and returns details for assignments that could not be placed.

The optimized generator is implemented separately. It additionally checks teacher and room availability and computes a schedule score, but it is not yet selected by the API controller.

Manual moves use `PATCH /api/v1/timetable-entries/{entry}/move`; conflict checks are exposed through `GET /api/v1/timetable-entries/{entry}/conflicts`.

## Persistence and background work

Migrations define institutions, departments, courses, teachers, students, sections, rooms, academic years, semesters, time slots, timetables, timetable entries, and teacher/room availability windows.

A `GenerateTimetableJob` implements Laravel's queue contract and stores generation status in the configured cache. Queue infrastructure and dispatch are not currently part of the generation endpoint's request flow.

## Deployment direction

The applications can be deployed independently: a Next.js-compatible host for the frontend and a PHP/Laravel host plus a configured relational database for the API. Queue workers and a shared cache should be provisioned when asynchronous generation is connected. No specific cloud provider is required by the application architecture.
