# Smart Timetable

Smart Timetable is a university timetable management project with a Laravel REST API and a Next.js frontend. It is being developed to model academic resources, manage timetable data, validate scheduling constraints, and provide a foundation for timetable generation.

> **Status:** Active development. The repository contains a working backend foundation and an early frontend scaffold. Automatic timetable optimization and a complete end-to-end user experience should not yet be considered production-ready.

## Tech stack

| Area | Technology |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13 |
| API authentication middleware | Laravel Sanctum |
| Frontend | Next.js 16, React 19, TypeScript 5 |
| API style | Versioned REST API |
| Backend quality tools | PHPUnit 12, Laravel Pint |

Database, cache, queue, and deployment services are environment-dependent. Refer to the application configuration and [architecture notes](docs/architecture.md) before choosing or provisioning infrastructure.

## Repository structure

```text
.
├── backend/       # Laravel API, domain logic, migrations, and tests
├── frontend/      # Next.js App Router application
└── docs/          # Architecture and project documentation
```

## Backend domains

The API models the academic resources needed to build a timetable:

- Departments
- Courses
- Teachers
- Rooms
- Sections
- Time slots
- Timetables and timetable entries

The backend also contains foundations for timetable generation and conflict checking. The exact implementation status of a workflow should be verified in its controller, service, job, and tests rather than inferred from the existence of a route or class.

## Architecture

The frontend and backend are separate applications. The frontend communicates with the Laravel API over HTTP using JSON.

```text
┌──────────────────────────────┐
│ Next.js / React / TypeScript │
│ Web application              │
└──────────────┬───────────────┘
               │ HTTP / JSON
               ▼
┌──────────────────────────────┐
│ Laravel 13 REST API          │
│ Authentication / Validation  │
│ Academic resources           │
│ Timetable domain              │
└──────────────────────────────┘
```

See [docs/architecture.md](docs/architecture.md) for domain boundaries, scheduling principles, and the intended deployment topology.

## Getting started

### Prerequisites

- PHP 8.3 or later
- Composer
- Node.js and npm
- A database supported by the backend's Laravel configuration

### Backend

Install the PHP dependencies:

```bash
cd backend
composer install
```

Create a local `.env` file with the application and database settings required for your environment. Then generate the application key, run migrations, and start the API:

```bash
php artisan key:generate
php artisan migrate
php artisan serve
```

The repository currently does not include a tracked `backend/.env.example`, so do not assume that a copy-from-example command is available.

### Frontend

In a separate terminal:

```bash
cd frontend
npm install
npm run dev
```

Next.js prints the local development URL after the server starts. If the frontend needs to call the API, configure its API base URL using the configuration expected by the current frontend code.

## API overview

The API is versioned under `/api/v1`. The health endpoint is public; the academic-resource and timetable routes shown below are grouped behind Laravel's `auth:sanctum` middleware.

| Method | Endpoint | Access / purpose |
| --- | --- | --- |
| GET | `/api/v1/health` | Public health check |
| GET, POST | `/api/v1/courses` | List and create courses |
| GET, PUT/PATCH, DELETE | `/api/v1/courses/{course}` | Read, update, and delete a course |
| GET, POST | `/api/v1/departments` | List and create departments |
| GET, PUT/PATCH, DELETE | `/api/v1/departments/{department}` | Read, update, and delete a department |
| GET, POST | `/api/v1/rooms` | List and create rooms |
| GET, PUT/PATCH, DELETE | `/api/v1/rooms/{room}` | Read, update, and delete a room |
| GET, POST | `/api/v1/teachers` | List and create teachers |
| GET, PUT/PATCH, DELETE | `/api/v1/teachers/{teacher}` | Read, update, and delete a teacher |
| GET, POST | `/api/v1/sections` | List and create sections |
| GET, PUT/PATCH, DELETE | `/api/v1/sections/{section}` | Read, update, and delete a section |
| GET, POST | `/api/v1/time-slots` | List and create time slots |
| GET, PUT/PATCH, DELETE | `/api/v1/time-slots/{time_slot}` | Read, update, and delete a time slot |
| GET, POST | `/api/v1/timetables` | List and create timetables |
| GET | `/api/v1/timetables/{timetable}` | Read a timetable |
| POST | `/api/v1/timetables/{timetable}/publish` | Publish a timetable |
| POST | `/api/v1/timetables/{timetable}/generate` | Start timetable generation |
| GET, POST | `/api/v1/timetable-entries` | List and create timetable entries |
| GET, DELETE | `/api/v1/timetable-entries/{timetable_entry}` | Read and delete an entry |
| GET | `/api/v1/timetable-entries/{timetable_entry}/conflicts` | Check entry conflicts |

This table reflects the route declarations in [backend/routes/api.php](backend/routes/api.php). It does not imply that every workflow is complete. In particular, an authentication/login endpoint is not declared in that API route file; configure and verify the applicable Sanctum authentication flow before calling protected endpoints.

## Development commands

Run backend checks from `backend/`:

```bash
composer test
composer lint
```

Run frontend checks from `frontend/`:

```bash
npm run lint
npm run build
```

## Development roadmap

The project is intended to grow toward the following capabilities:

- Constraint-aware automatic timetable generation
- Conflict detection, validation, and useful explanations
- An interactive timetable editor
- Timetable publishing and version history
- Audit trails and notifications
- Calendar export and analytics
- Automated deployment and CI/CD

These are development goals, not a statement that each feature is already complete.

## Engineering principles

- Keep HTTP concerns in controllers and business rules in domain or application services.
- Validate input at system boundaries.
- Treat teacher, room, and section overlaps as hard scheduling constraints.
- Separate hard constraints from soft preferences used during optimization.
- Keep manual editing and automatic generation aligned around shared scheduling rules.
- Document implementation status and known limitations alongside changes.

## Contributing

1. Create a focused feature or fix branch.
2. Keep changes scoped to the relevant application.
3. Run the available checks for the area you changed.
4. Update documentation when routes, setup steps, or feature status change.
5. Describe implementation status and known limitations in your pull request.

## Author

**Muhammad Talha** — Software Engineer focused on backend development with PHP and Laravel.

- GitHub: [@imiantalha](https://github.com/imiantalha)
- Portfolio: [imiantalha.vercel.app](https://imiantalha.vercel.app/)
