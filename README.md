# Smart Timetable

> A full-stack academic timetable management and scheduling platform built to make complex university scheduling easier to **create, validate, optimize, review, and publish**.

[![Backend](https://img.shields.io/badge/Backend-Laravel%2013-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com/)
[![Frontend](https://img.shields.io/badge/Frontend-Next.js-000000?style=flat-square&logo=next.js&logoColor=white)](https://nextjs.org/)
[![Language](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Database](https://img.shields.io/badge/Database-PostgreSQL-4169E1?style=flat-square&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![TypeScript](https://img.shields.io/badge/Frontend-TypeScript-3178C6?style=flat-square&logo=typescript&logoColor=white)](https://www.typescriptlang.org/)

## Why Smart Timetable?

University timetable scheduling is more than placing courses into empty cells. A usable system must account for **teacher availability, room availability, sections, time slots, conflicts, constraints, manual adjustments, and publishing workflows**.

Smart Timetable is being designed around those real-world rules, with a Laravel API powering the scheduling domain and a Next.js interface providing an interactive administration experience.

## ✨ Core Features

- 🔐 Authentication and role-based access control
- 🏫 Institution and department management
- 📚 Course and section management
- 👨‍🏫 Teacher management and availability
- 🏢 Room management and availability
- 🕐 Time-slot management
- 📅 Interactive timetable grid
- 🔍 Conflict detection and validation
- 🖱️ Drag-and-drop timetable editing
- ↩️ Optimistic updates with rollback on failed moves
- 🤖 Constraint-aware timetable generation
- 📝 Draft timetable review
- 🚀 Timetable publishing workflow
- 🔔 Notifications and calendar integrations planned
- 📊 Scheduling analytics planned

## 🏗️ Architecture

```text
┌──────────────────────────────────────────────┐
│                 Next.js / React              │
│        Scheduling UI • State • UX            │
└──────────────────────┬───────────────────────┘
                       │
                       │ REST API /api/v1
                       ▼
┌──────────────────────────────────────────────┐
│                  Laravel 13                  │
│ Auth • RBAC • Domain Logic • Validation      │
│ Timetable • Conflicts • Generation            │
└──────────────┬─────────────────┬─────────────┘
               │                 │
               ▼                 ▼
       ┌──────────────┐   ┌──────────────┐
       │ PostgreSQL   │   │    Redis     │
       │ Domain Data  │   │ Queue/Cache  │
       └──────────────┘   └──────────────┘

              Docker + GitHub Actions
```

## 🧠 Scheduling Model

The system separates the scheduling problem into explicit resources and constraints:

```text
Department
    ↓
Section ───────┐
    ↓          │
  Courses      │
    ↓          │
 Teachers      │
    ↓          │
  Rooms        │
    ↓          │
 Time Slots ───┘
    ↓
Conflict Validation
    ↓
Generated Draft
    ↓
Manual Review / Drag & Drop
    ↓
Publish
```

This structure allows the application to support both **automatic generation** and **human-controlled timetable adjustments**.

## 🛠️ Tech Stack

### Backend

- Laravel 13
- PHP 8.3+
- RESTful API
- Laravel Sanctum
- OOP / MVC / SOLID-oriented architecture
- Validation and authorization
- Queues and background processing

### Frontend

- Next.js
- React
- TypeScript
- Tailwind CSS
- Interactive scheduling UI

### Data & Infrastructure

- PostgreSQL
- Redis
- Docker
- GitHub Actions

## 📁 Core Domains

| Domain | Responsibility |
| --- | --- |
| Authentication | Login, authentication, authorization and roles |
| Institutions | Institutions and academic structure |
| Departments | Academic department organization |
| Courses | Course definitions and scheduling requirements |
| Teachers | Faculty assignment and availability |
| Sections | Student groups and academic sections |
| Rooms | Room capacity, type and availability |
| Time Slots | Scheduling periods and working hours |
| Timetable | Draft schedules, entries and publishing |
| Conflict Engine | Detect and prevent invalid schedules |
| Generator | Build schedules from selected resources and constraints |

## 🚦 Current Development Status

**Active development — foundation and scheduling workflow are being implemented incrementally.**

Implemented/in progress areas include the timetable API, resource configuration, conflict validation, interactive grid editing, drag-and-drop persistence, and generation workflow UI.

The project deliberately uses separate feature branches for major modules so each capability can be developed and reviewed independently.

## 🗺️ Roadmap

- [x] Project foundation
- [x] Core timetable UI foundation
- [x] Drag-and-drop timetable editing
- [x] Move persistence and rollback handling
- [x] Generation configuration UI
- [x] Backend resource API integration
- [ ] Complete automatic constraint solver
- [ ] Generation progress / queued jobs
- [ ] Advanced conflict explanations
- [ ] Timetable versioning and audit history
- [ ] Calendar export (ICS)
- [ ] Notifications
- [ ] Scheduling analytics
- [ ] Comprehensive automated test suite
- [ ] Dockerized production deployment
- [ ] CI/CD hardening

## 🔄 Example Workflow

```text
Admin creates academic resources
            ↓
Selects courses, teachers, rooms & time slots
            ↓
Generates a timetable draft
            ↓
Conflict engine validates the schedule
            ↓
Admin reviews generated timetable
            ↓
Drag & drop to make adjustments
            ↓
Changes are persisted with rollback protection
            ↓
Publish timetable
```

## 🎯 Engineering Goals

Smart Timetable is also an engineering project focused on demonstrating practical software development patterns:

- Clear REST API contracts
- Separation of frontend and backend responsibilities
- Domain-driven scheduling rules
- Validation before persistence
- Safe optimistic UI updates
- Conflict-aware business logic
- Scalable background generation
- Testable application services
- Production-oriented error handling
- Maintainable modular architecture

## 🚀 Development Setup

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

### Frontend

```bash
cd frontend
npm install
npm run dev
```

Set the frontend API URL in `.env.local`:

```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
```

## 📌 Project Status

This repository is an **actively evolving engineering project**. Some roadmap items are intentionally marked as planned rather than presented as completed functionality.

## 👨‍💻 Author

**Muhammad Talha** — Backend-focused Software Engineer working with Laravel, REST APIs, databases, React and Next.js.

- GitHub: https://github.com/imiantalha
- Portfolio: https://imiantalha.vercel.app/
