# Architecture

## Overview

Smart Timetable uses a decoupled frontend/backend architecture.

```text
┌──────────────────────────────┐
│ Next.js + React + TypeScript  │
│ Dashboard / Timetable UI      │
└──────────────┬───────────────┘
               │ HTTPS REST API
               ▼
┌──────────────────────────────┐
│ Laravel 13 API                │
│ Auth / Domain / Scheduling    │
└──────────────┬───────────────┘
               │
       ┌───────┴────────┐
       ▼                ▼
┌─────────────┐   ┌─────────────┐
│ PostgreSQL  │   │    Redis    │
│ Persistence │   │ Cache/Queue │
└─────────────┘   └─────────────┘
```

## Backend boundaries

The scheduling domain should keep business rules outside controllers. Controllers handle HTTP concerns; application/domain services handle timetable validation and generation.

```text
backend/app/
├── Domain/
│   ├── Academic/
│   ├── Scheduling/
│   └── Identity/
├── Http/
├── Jobs/
├── Policies/
└── Notifications/
```

## Scheduling principles

Hard constraints must never be violated: teacher, room and section overlaps, room capacity, availability, required sessions and blocked periods.

Soft constraints can be optimized: teacher gaps, student gaps, class distribution and room utilization.

The same validation rules will be shared by manual timetable editing and automatic generation.

## API

All API endpoints are versioned under `/api/v1`.

## Deployment target

The intended production topology is Next.js on Vercel and Laravel, PostgreSQL and Redis on a managed platform such as Railway. The architecture remains provider-agnostic.
