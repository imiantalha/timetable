'use client';

import type { TimetableEntry } from '../../../lib/timetable-api';

export type GridSlot = { id: number; starts_at: string; ends_at: string; day_of_week: number };

export default function TimetableGrid({ days, slots, entries, onMove, onSelect }: { days: string[]; slots: GridSlot[]; entries: TimetableEntry[]; onMove: (entry: TimetableEntry, slot: GridSlot) => void; onSelect: (entry: TimetableEntry) => void }) {
  const map = new Map(entries.map((entry) => [`${entry.timeSlot.day_of_week}-${entry.timeSlot.id}`, entry]));
  return <div className="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900 shadow-2xl"><div className="min-w-[1050px]">
    <div className="grid grid-cols-[150px_repeat(5,minmax(170px,1fr))] border-b border-slate-800"><div className="p-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Time</div>{days.map((day) => <div key={day} className="border-l border-slate-800 p-4 text-sm font-semibold">{day}</div>)}</div>
    {slots.map((slot) => <div key={slot.id} className="grid grid-cols-[150px_repeat(5,minmax(170px,1fr))] border-b border-slate-800 last:border-b-0"><div className="p-4 text-xs text-slate-500">{slot.starts_at} – {slot.ends_at}</div>{days.map((_, day) => { const entry = map.get(`${day}-${slot.id}`); return <div key={day} onDragOver={(e) => e.preventDefault()} onDrop={(e) => { const id = Number(e.dataTransfer.getData('entry-id')); const dragged = entries.find((item) => item.id === id); if (dragged) onMove(dragged, { ...slot, day_of_week: day }); }} className="min-h-28 border-l border-slate-800 p-2 transition hover:bg-cyan-500/5">{entry ? <button draggable onDragStart={(e) => e.dataTransfer.setData('entry-id', String(entry.id))} onClick={() => onSelect(entry)} className="w-full rounded-xl border border-slate-700 bg-slate-800 p-3 text-left cursor-grab active:cursor-grabbing"><p className="text-sm font-semibold">{entry.course.name}</p><p className="mt-2 text-xs text-slate-400">{entry.teacher.first_name} {entry.teacher.last_name}</p><p className="mt-1 text-xs text-slate-500">{entry.room.name}</p></button> : <span className="text-xs text-slate-700">Drop here</span>}</div>; })}</div>)}
  </div></div>;
}
