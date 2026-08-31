'use client';

import { useEffect, useMemo, useState } from 'react';
import { checkEntryConflicts, getTimetableEntries, publishTimetable, type TimetableEntry } from '../../../lib/timetable-api';

const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
const fallbackSlots = [
  { id: 1, label: '08:00 – 09:00' }, { id: 2, label: '09:00 – 10:00' },
  { id: 3, label: '10:00 – 11:00' }, { id: 4, label: '11:00 – 12:00' },
  { id: 5, label: '12:00 – 01:00' }, { id: 6, label: '01:00 – 02:00' },
];

export default function TimetablesPage() {
  const [entries, setEntries] = useState<TimetableEntry[]>([]);
  const [selected, setSelected] = useState<TimetableEntry | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [published, setPublished] = useState(false);

  useEffect(() => {
    getTimetableEntries(1).then(setEntries).catch(() => setError('Unable to load timetable. Check the API URL and authentication.')).finally(() => setLoading(false));
  }, []);

  const slots = useMemo(() => entries.length ? Array.from(new Map(entries.map((entry) => [entry.timeSlot.id, entry.timeSlot])).values()).sort((a,b) => a.starts_at.localeCompare(b.starts_at)) : fallbackSlots.map((slot) => ({ id: slot.id, starts_at: slot.label.slice(0,5), ends_at: slot.label.slice(8,13), day_of_week: 0 })), [entries]);
  const entryMap = useMemo(() => new Map(entries.map((entry) => [`${entry.timeSlot.day_of_week}-${entry.timeSlot.id}`, entry])), [entries]);

  async function inspect(entry: TimetableEntry) {
    setSelected(entry);
    try { const result = await checkEntryConflicts(entry.id); if (!result.valid) setError(result.conflicts.map((c: { message: string }) => c.message).join(' ')); else setError(''); } catch { setError('Could not validate this entry.'); }
  }

  async function publish() {
    try { await publishTimetable(1); setPublished(true); setError(''); } catch { setError('Unable to publish timetable.'); }
  }

  return (
    <main className="min-h-screen p-6 lg:p-10"><div className="mx-auto max-w-[1500px]">
      <div className="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"><div><p className="text-sm font-medium text-cyan-400">TIMETABLE BUILDER</p><h1 className="mt-1 text-3xl font-bold">Fall 2026 · BS Computer Science</h1><p className="mt-2 text-sm text-slate-400">{published ? 'Published schedule' : 'Draft schedule'} · Live Laravel API</p></div><div className="flex gap-2"><button className="rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Generate</button><button onClick={publish} disabled={published} className="rounded-lg bg-cyan-500 px-4 py-2 text-sm font-semibold text-slate-950 disabled:opacity-50">{published ? 'Published' : 'Publish'}</button></div></div>
      {error && <div className="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-300">{error}</div>}
      <div className="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900 shadow-2xl"><div className="min-w-[1050px]">
        <div className="grid grid-cols-[150px_repeat(5,minmax(170px,1fr))] border-b border-slate-800"><div className="p-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Time</div>{days.map((day) => <div key={day} className="border-l border-slate-800 p-4 text-sm font-semibold">{day}</div>)}</div>
        {loading ? <div className="p-10 text-center text-sm text-slate-500">Loading timetable…</div> : slots.map((slot) => <div key={slot.id} className="grid grid-cols-[150px_repeat(5,minmax(170px,1fr))] border-b border-slate-800"><div className="p-4 text-xs text-slate-500">{slot.starts_at} – {slot.ends_at}</div>{days.map((_, day) => { const entry = entryMap.get(`${day}-${slot.id}`); return <button key={day} onClick={() => entry && inspect(entry)} className="min-h-28 border-l border-slate-800 p-2 text-left hover:bg-slate-800/60">{entry ? <div className="rounded-xl border border-slate-700 bg-slate-800 p-3"><p className="text-sm font-semibold">{entry.course.name}</p><p className="mt-2 text-xs text-slate-400">{entry.teacher.first_name} {entry.teacher.last_name}</p><p className="mt-1 text-xs text-slate-500">{entry.room.name}</p></div> : <span className="text-xs text-slate-700">Available</span>}</button>; })}</div>)}
      </div></div>
      {selected && <div className="mt-4 rounded-xl border border-slate-800 bg-slate-900 p-4"><p className="text-sm font-medium">{selected.course.code} · {selected.course.name}</p><p className="mt-1 text-xs text-slate-500">{selected.section.name} · {selected.teacher.first_name} {selected.teacher.last_name} · {selected.room.name}</p></div>}
    </div></main>
  );
}
