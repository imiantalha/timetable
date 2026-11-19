'use client';

import type { TimetableEntry, ScheduleSlot } from '../../../lib/timetable-api';

export type GridSlot = ScheduleSlot;

export default function TimetableGrid({
  days,
  slots,
  entries,
  onMove,
  onSelect,
}: {
  days: string[];
  slots: GridSlot[];
  entries: TimetableEntry[];
  onMove: (entry: TimetableEntry, slot: GridSlot) => void;
  onSelect: (entry: TimetableEntry) => void;
}) {
  const entriesByCell = new Map(
    entries.map((entry) => [`${entry.timeSlot.day_of_week}-${entry.timeSlot.id}`, entry]),
  );
  const slotsByCell = new Map(
    slots.map((slot) => [`${slot.day_of_week}-${slot.starts_at}-${slot.ends_at}`, slot]),
  );
  const timeRanges = Array.from(
    new Set(slots.map((slot) => `${slot.starts_at}|${slot.ends_at}`)),
  ).sort((a, b) => a.localeCompare(b));

  return (
    <div className="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900 shadow-2xl">
      <div className="min-w-[1050px]">
        <div className="grid grid-cols-[150px_repeat(5,minmax(170px,1fr))] border-b border-slate-800">
          <div className="p-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Time</div>
          {days.map((day) => (
            <div key={day} className="border-l border-slate-800 p-4 text-sm font-semibold">{day}</div>
          ))}
        </div>

        {timeRanges.map((range) => {
          const [startsAt, endsAt] = range.split('|');

          return (
            <div key={range} className="grid grid-cols-[150px_repeat(5,minmax(170px,1fr))] border-b border-slate-800 last:border-b-0">
              <div className="p-4 text-xs text-slate-500">{startsAt} – {endsAt}</div>
              {days.map((dayName, day) => {
                const slot = slotsByCell.get(`${day}-${startsAt}-${endsAt}`);
                const entry = slot ? entriesByCell.get(`${day}-${slot.id}`) : undefined;

                return (
                  <div
                    key={dayName}
                    onDragOver={(event) => {
                      if (slot) event.preventDefault();
                    }}
                    onDrop={(event) => {
                      if (!slot) return;
                      const id = Number(event.dataTransfer.getData('entry-id'));
                      const dragged = entries.find((item) => item.id === id);
                      if (dragged) onMove(dragged, slot);
                    }}
                    className={[
                      'min-h-28 border-l border-slate-800 p-2 transition',
                      slot ? 'hover:bg-cyan-500/5' : 'bg-slate-950/30',
                    ].join(' ')}
                  >
                    {entry ? (
                      <button
                        draggable
                        onDragStart={(event) => event.dataTransfer.setData('entry-id', String(entry.id))}
                        onClick={() => onSelect(entry)}
                        className="w-full cursor-grab rounded-xl border border-slate-700 bg-slate-800 p-3 text-left active:cursor-grabbing"
                      >
                        <p className="text-sm font-semibold">{entry.course.name}</p>
                        <p className="mt-2 text-xs text-slate-400">
                          {entry.teacher.first_name} {entry.teacher.last_name}
                        </p>
                        <p className="mt-1 text-xs text-slate-500">{entry.room.name}</p>
                      </button>
                    ) : slot ? (
                      <span className="text-xs text-slate-700">Drop here</span>
                    ) : null}
                  </div>
                );
              })}
            </div>
          );
        })}
      </div>
    </div>
  );
}
