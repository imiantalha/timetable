# Smart Timetable

A university timetable planner. A **Laravel 13 REST API** models departments, courses, teachers, rooms, sections and time slots, generates timetables in a queued job using constraint checks and schedule scoring, and detects clashes. A **Next.js 16** admin dashboard lets you configure generation and adjust the result with drag and drop.

<!-- Add a screenshot or GIF of the timetable builder here: ![Timetable builder](docs/screenshot.png) -->

## Features

- **Timetable generation:** `TimetableGenerator` and `OptimizedTimetableGenerator` build schedules from a generation plan; `ScheduleScorer` ranks the candidates
- **Constraints and conflicts:** `AvailabilityConstraint` and `ConflictDetector` catch teacher, room and section clashes; any entry can be checked through the API
- **Background processing:** generation runs in a queued `GenerateTimetableJob`, keeping API requests fast
- **Drag-and-drop editing:** move entries in the builder grid; moves go through a dedicated `PATCH /move` endpoint
- **Publishing:** timetables are drafted, then published
- **Academic resources:** full CRUD for departments, courses, teachers, rooms, sections and time slots
- **Typed frontend:** the Next.js dashboard talks to the API through a typed TypeScript client

## Tech stack

| Area | Technology |
|---|---|
| Backend | PHP 8.3+, Laravel 13, Sanctum, queued jobs |
| Frontend | Next.js 16 (App Router), React 19, TypeScript 5 |
| API | Versioned REST under `/api/v1` |

## Architecture

```
┌────────────────────────────────────┐
│ Next.js admin dashboard            │
│ Generation config · Builder grid   │
│ Drag & drop · Typed API client     │
└─────────────────┬──────────────────┘
                  │ HTTP / JSON
                  ▼
┌────────────────────────────────────┐
│ Laravel 13 API (/api/v1, Sanctum)  │
│ Resource controllers · Validation  │
├────────────────────────────────────┤
│ Domain/Scheduling                  │
│ Generator · Scorer · Constraints   │
│ ConflictDetector                   │
├────────────────────────────────────┤
│ GenerateTimetableJob (queue)       │
└────────────────────────────────────┘
```

Scheduling logic lives in [`backend/app/Domain/Scheduling`](backend/app/Domain/Scheduling), separate from the HTTP layer. More detail in [docs/architecture.md](docs/architecture.md).

```
.
├── backend/    # Laravel API and scheduling domain
├── frontend/   # Next.js admin dashboard
└── docs/       # Architecture notes
```

## API

All routes are under `/api/v1`. Everything except `GET /health` requires Sanctum authentication.

| Method | Endpoint | Purpose |
|---|---|---|
| `GET` | `/health` | Health check |
| CRUD | `/courses`, `/departments`, `/rooms`, `/teachers`, `/sections`, `/time-slots` | Academic resources |
| `GET` `POST` | `/timetables`, `/timetables/{id}` | List, create and view timetables |
| `POST` | `/timetables/{id}/generate` | Queue timetable generation |
| `POST` | `/timetables/{id}/publish` | Publish a timetable |
| `GET` `POST` `DELETE` | `/timetable-entries` | Manage entries |
| `PATCH` | `/timetable-entries/{id}/move` | Move an entry (drag and drop) |
| `GET` | `/timetable-entries/{id}/conflicts` | Check an entry for clashes |

Full definitions in [`backend/routes/api.php`](backend/routes/api.php).

## Getting started

**Backend**

```bash
cd backend
composer install
# Create backend/.env with APP_KEY, your DB_* settings and a QUEUE_CONNECTION
php artisan key:generate
php artisan migrate
php artisan serve
php artisan queue:work   # in a second terminal, for timetable generation
```

**Frontend**

```bash
cd frontend
cp .env.example .env.local   # NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
npm install
npm run dev
```

## Roadmap

- Automated tests and CI
- Conflict explanations shown in the builder
- Version history for published timetables
- Calendar export

## Author

**Muhammad Talha**, backend engineer (Laravel / PHP)
[Portfolio](https://imiantalha.vercel.app) · [LinkedIn](https://www.linkedin.com/in/imiantalha) · [GitHub](https://github.com/imiantalha)
