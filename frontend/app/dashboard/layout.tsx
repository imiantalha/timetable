import Link from 'next/link';

const navigation = [
  ['Overview', '/dashboard'],
  ['Courses', '/dashboard/courses'],
  ['Teachers', '/dashboard/teachers'],
  ['Sections', '/dashboard/sections'],
  ['Rooms', '/dashboard/rooms'],
  ['Time Slots', '/dashboard/time-slots'],
  ['Timetables', '/dashboard/timetables'],
];

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen bg-slate-950 text-white lg:flex">
      <aside className="border-b border-slate-800 bg-slate-900 lg:min-h-screen lg:w-64 lg:border-b-0 lg:border-r">
        <div className="p-6">
          <Link href="/dashboard" className="text-xl font-bold">Time<span className="text-cyan-400">Table</span></Link>
          <nav className="mt-8 space-y-1">
            {navigation.map(([label, href]) => <Link key={href} href={href} className="block rounded-lg px-3 py-2.5 text-sm text-slate-400 transition hover:bg-slate-800 hover:text-white">{label}</Link>)}
          </nav>
        </div>
      </aside>
      <div className="flex-1">{children}</div>
    </div>
  );
}
