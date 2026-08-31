'use client';

import { useMemo, useState } from 'react';

const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
const slots = [
  { id: 1, label: '08:00 – 09:00' },
  { id: 2, label: '09:00 – 10:00' },
  { id: 3, label: '10:00 – 11:00' },
  { id: 4, label: '11:00 – 12:00' },
  { id: 5, label: '12:00 – 01:00' },
  { id: 6, label: '01:00 – 02:00' },
];

const initialEntries = [
  { day: 0, slot: 1, course: 'Database Systems', teacher: 'A. Khan', room: 'Lab 01', color: 'cyan' },
  { day: 0, slot: 3, course: 'Software Engineering', teacher: 'M. Ahmed', room: 'Room 204', color: 'violet' },
  { day: 1, slot: 2, course: 'Web Engineering', teacher: 'S. Ali', room: 'Lab 02', color: 'emerald' },
  { day: 2, slot: 4, course: 'Data Structures', teacher: 'A. Khan', room: 'Room 105', color: 'amber' },
];

export default function TimetablesPage() {
  const [entries, setEntries] = useState(initialEntries);
  const [selected, setSelected] = useState<{ day: number; slot: number } | null>(null);

  const entryMap = useMemo(() => new Map(entries.map((entry) => [`${entry.day}-${entry.slot}`, entry])), [entries]);

  function clearSelected() {
    if (!selected) return;
    setEntries((current) => current.filter((entry) => !(entry.day === selected.day && entry.slot === selected.slot)));
    setSelected(null);
  }

  return (
    <main className="min-h-screen p-6 lg:p-10">
      <div className="mx-auto max-w-[1500px]">
        <div className="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
          <div>
            <p className="text-sm font-medium text-cyan-400">TIMETABLE BUILDER</p>
            <h1 className="mt-1 text-3xl font-bold">Fall 2026 · BS Computer Science</h1>
            <p className="mt-2 text-sm text-slate-400">Draft schedule · Click a class to inspect or remove it.</p>
          </div>
          <div className="flex gap-2">
            <button className="rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Generate</button>
            <button className="rounded-lg bg-cyan-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-400">Publish</button>
          </div>
        </div>

        <div className="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900 shadow-2xl">
          <div className="min-w-[1050px]">
            <div className="grid grid-cols-[150px_repeat(5,minmax(170px,1fr))] border-b border-slate-800 bg-slate-900">
              <div className="p-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Time</div>
              {days.map((day) => <div key={day} className="border-l border-slate-800 p-4 text-sm font-semibold text-slate-200">{day}</div>)}
            </div>
            {slots.map((slot) => (
              <div key={slot.id} className="grid grid-cols-[150px_repeat(5,minmax(170px,1fr))] border-b border-slate-800 last:border-b-0">
                <div className="p-4 text-xs text-slate-500">{slot.label}</div>
                {days.map((_, day) => {
                  const entry = entryMap.get(`${day}-${slot.id}`);
                  const active = selected?.day === day && selected?.slot === slot.id;
                  return (
                    <button key={day} onClick={() => setSelected({ day, slot: slot.id })} className={`min-h-28 border-l border-slate-800 p-2 text-left transition ${active ? 'bg-cyan-500/10 ring-1 ring-inset ring-cyan-500' : 'hover:bg-slate-800/60'}`}>
                      {entry ? (
                        <div className="h-full rounded-xl border border-slate-700 bg-slate-800/90 p-3">
                          <p className="text-sm font-semibold text-white">{entry.course}</p>
                          <p className="mt-2 text-xs text-slate-400">{entry.teacher}</p>
                          <p className="mt-1 text-xs text-slate-500">{entry.room}</p>
                        </div>
                      ) : <span className="text-xs text-slate-700">Available</span>}
                    </button>
                  );
                })}
              </div>
            ))}
          </div>
        </div>

        {selected && (
          <div className="mt-4 flex items-center justify-between rounded-xl border border-slate-800 bg-slate-900 p-4">
            <div><p className="text-sm font-medium">Selected: {days[selected.day]} · {slots.find((slot) => slot.id === selected.slot)?.label}</p><p className="mt-1 text-xs text-slate-500">Use this panel for edit, drag/drop and conflict details.</p></div>
            <button onClick={clearSelected} className="rounded-lg border border-red-500/30 px-3 py-2 text-xs font-medium text-red-400 hover:bg-red-500/10">Remove class</button>
          </div>
        )}
      </div>
    </main>
  );
}
