# Architecture

## Overview

Smart Timetable separates the Next.js dashboard from the Laravel API and scheduling domain.

~~~text
Next.js 16 dashboard
  ├── Timetable selector
  ├── Generation configuration
  └── Day-specific drag-and-drop grid
          │ HTTP / JSON
          ▼
Laravel 13 API (/api/v1)
  ├── Sanctum authentication
  ├── Institution access middleware
  ├── Resource and timetable controllers
  └── Domain/Scheduling
        ├── GenerationPlan
        ├── TimetableGenerator
        ├── OptimizedTimetableGenerator
        ├── AvailabilityConstraint
        ├── ConflictDetector
        └── ScheduleScorer
          │
          ▼
      PostgreSQL
~~~

## Backend boundaries

Controllers validate HTTP input and enforce timetable lifecycle rules. Scheduling rules live in backend/app/Domain/Scheduling so generation, manual entry and drag-and-drop operations can share the same constraints.

The EnsureInstitutionAccess middleware protects route-bound resources. Collection endpoints and write requests also apply institution-level scoping.

## Scheduling principles

Hard constraints include teacher, room and section overlaps, room capacity, resource availability, break periods, course duration and required weekly sessions. Time overlap detection compares actual time ranges, so two different slot IDs cannot hide a clash.

The synchronous API generation endpoint runs inside a database transaction. An optimized generator and queue job are also present, but the current dashboard endpoint does not yet dispatch asynchronous jobs.

## Runtime configuration

The backend defaults to PostgreSQL, file cache and synchronous queue execution for local development. Production deployments can configure managed PostgreSQL, Redis cache and a durable queue through environment settings.

## Deployment target

The intended production topology is Next.js on Vercel with Laravel and PostgreSQL on a managed application platform. The API remains provider-agnostic.
