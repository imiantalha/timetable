# Smart Timetable

A university timetable planner built with a Laravel 13 REST API and a Next.js 16 dashboard. It manages academic resources, generates draft schedules, detects conflicts and supports drag-and-drop rescheduling.

## Features

- **Academic resources:** departments, courses, teachers, rooms, sections and time slots
- **Timetable generation:** expands each course into its configured weekly sessions and places sessions into compatible time slots
- **Hard constraints:** teacher, room and section conflicts, room capacity, resource availability, break periods and course duration
- **Draft lifecycle:** generate and edit draft timetables, then publish them
- **Authentication:** Sanctum bearer-token login, current-user and logout endpoints
- **Frontend builder:** select a timetable, configure generation, view the actual institution time slots and drag entries to valid day-specific slots

The API generation endpoint currently runs synchronously. A separate queued generator/job implementation is present, but is not yet exposed through an asynchronous API workflow.

## Tech stack

| Area | Technology |
|---|---|
| Backend | PHP 8.3+, Laravel 13, Sanctum |
| Database | PostgreSQL |
| Frontend | Next.js 16, React 19, TypeScript 5, Tailwind CSS 4 |
| API | REST under /api/v1 |

## Project structure

~~~text
.
├── backend/    # Laravel API, models, migrations and scheduling domain
├── frontend/   # Next.js admin dashboard
└── docs/       # Architecture and database notes
~~~

Scheduling logic lives in backend/app/Domain/Scheduling, separate from HTTP controllers.

## API

All endpoints are under /api/v1.

| Method | Endpoint | Purpose | Auth |
|---|---|---|---|
| GET | /health | Health check | Public |
| POST | /login | Create a Sanctum token | Public, rate limited |
| GET | /me | Current authenticated user | Required |
| POST | /logout | Revoke current token | Required |
| CRUD | /courses, /departments, /rooms, /teachers, /sections, /time-slots | Manage academic resources | Required |
| GET / POST | /timetables | List and create timetables | Required |
| GET | /timetables/{id} | View a timetable | Required |
| POST | /timetables/{id}/generate | Generate a draft timetable | Admin |
| POST | /timetables/{id}/publish | Publish a draft timetable | Admin |
| GET / POST / DELETE | /timetable-entries | List, create and delete entries | Required / Admin for writes |
| PATCH | /timetable-entries/{id}/move | Move an entry to a valid slot | Admin |
| GET | /timetable-entries/{id}/conflicts | Check entry conflicts | Required |

## Getting started

### Backend

~~~bash
cd backend
composer install
cp .env.example .env
# Set DB_HOST, DB_DATABASE, DB_USERNAME and DB_PASSWORD for PostgreSQL.
php artisan key:generate
php artisan migrate
php artisan serve
~~~

The current generation endpoint runs synchronously, so a queue worker is not required for the dashboard flow.

### Frontend

~~~bash
cd frontend
cp .env.example .env.local
npm install
npm run dev
~~~

Set NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1 in frontend/.env.local.

### Validation

~~~bash
cd backend
php artisan test
composer lint

cd ../frontend
npm run lint
npm run build
~~~

## Roadmap

- Asynchronous generation API and job-status endpoint
- Automated scheduling-domain tests and CI
- Timetable creation and login screens in the dashboard
- Conflict explanations and version history
- Calendar export

## Author

**Muhammad Talha** · [Portfolio](https://imiantalha.vercel.app) · [LinkedIn](https://www.linkedin.com/in/imiantalha) · [GitHub](https://github.com/imiantalha)
