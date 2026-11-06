import Link from 'next/link';

const resources = [
  ['Courses', 'Manage subjects and weekly sessions.', '/dashboard/courses'],
  ['Teachers', 'Manage faculty assignments and availability.', '/dashboard/teachers'],
  ['Sections', 'Manage student groups and capacity.', '/dashboard/sections'],
  ['Rooms', 'Manage classrooms and capacity.', '/dashboard/rooms'],
  ['Time Slots', 'Configure the weekly scheduling grid.', '/dashboard/time-slots'],
  ['Timetables', 'Create, generate and publish schedules.', '/dashboard/timetables'],
];

export default function DashboardPage() {
  return (
    <main className="min-h-screen bg-slate-950 px-6 py-10 text-white">
      <div className="mx-auto max-w-7xl">
        <header className="mb-10">
          <p className="mb-2 text-sm font-medium text-cyan-400">TIMETABLE ADMIN</p>
          <h1 className="text-4xl font-bold tracking-tight">Academic scheduling dashboard</h1>
          <p className="mt-3 max-w-2xl text-slate-400">Manage academic resources, generate conflict-free schedules, and publish timetables from one place.</p>
        </header>
        <section className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {resources.map(([title, description, href]) => (
            <Link key={href} href={href} className="group rounded-2xl border border-slate-800 bg-slate-900 p-6 transition hover:border-cyan-500/60 hover:bg-slate-900/80">
              <div className="mb-8 flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-400">→</div>
              <h2 className="text-lg font-semibold">{title}</h2>
              <p className="mt-2 text-sm leading-6 text-slate-400">{description}</p>
              <span className="mt-5 inline-block text-sm font-medium text-cyan-400">Open module →</span>
            </Link>
          ))}
        </section>
      </div>
    </main>
  );
}
