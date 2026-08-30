# Smart Timetable

A full-stack academic timetable and scheduling platform for managing courses, teachers, sections, rooms, time slots, conflict detection, timetable publishing, and automated schedule generation.

## Planned Stack

- **Backend:** Laravel 13, PHP 8.3+, REST API, Sanctum
- **Frontend:** Next.js, React, TypeScript, Tailwind CSS
- **Database:** PostgreSQL
- **Infrastructure:** Redis, Docker, GitHub Actions

## Architecture

```text
Next.js / React
       │
       │ REST API /api/v1
       ▼
Laravel 13
       │
   ┌───┴────┐
   ▼        ▼
PostgreSQL Redis
```

## Core Domains

- Authentication and role-based access control
- Institutions, departments, courses, teachers, students and sections
- Rooms, time slots and availability
- Timetable management and publishing
- Conflict detection and validation
- Automatic constraint-based timetable generation
- Notifications, calendar export and analytics

## Development Roadmap

1. Project foundation
2. Database and domain model
3. Authentication and RBAC
4. Academic resource management
5. Timetable and conflict engine
6. Next.js scheduling interface
7. Automatic timetable generator
8. Publishing and calendar integration
9. Notifications and analytics
10. Testing, security and production deployment

## Status

Early foundation phase.
