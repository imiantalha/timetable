'use client';

import { useEffect, useMemo, useState } from 'react';
import TimetableGrid, { type GridSlot } from './TimetableGrid';
import GenerationPanel from './GenerationPanel';
import { checkEntryConflicts, getTimetableEntries, moveTimetableEntry, publishTimetable, type TimetableEntry } from '../../../lib/timetable-api';

const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
const fallbackSlots: GridSlot[] = [
  { id: 1, starts_at: '08:00', ends_at: '09:00', day_of_week: 0 }, { id: 2, starts_at: '09:00', ends_at: '10:00', day_of_week: 0 },
  { id: 3, starts_at: '10:00', ends_at: '11:00', day_of_week: 0 }, { id: 4, starts_at: '11:00', ends_at: '12:00', day_of_week: 0 },
  { id: 5, starts_at: '12:00', ends_at: '13:00', day_of_week: 0 }, { id: 6, starts_at: '13:00', ends_at: '14:00', day_of_week: 0 },
];

export default function TimetablesPage() {
  const [entries, setEntries] = useState<TimetableEntry[]>([]);
  const [selected, setSelected] = useState<TimetableEntry | null>(null);
  const [loading, setLoading] = useState(true);
  const [savingId, setSavingId] = useState<number | null>(null);
  const [error, setError] = useState('');
  const [published, setPublished] = useState(false);

  useEffect(() => { getTimetableEntries(1).then(setEntries).catch(() => setError('Unable to load timetable. Check the API URL and authentication.')).finally(() => setLoading(false)); }, []);

  const slots = useMemo<GridSlot[]>(() => entries.length ? Array.from(new Map(entries.map((entry) => [entry.timeSlot.id, entry.timeSlot])).values()).sort((a, b) => a.starts_at.localeCompare(b.starts_at)) : fallbackSlots, [entries]);

  async function moveEntry(entry: TimetableEntry, target: GridSlot) {
    const previous = entry.timeSlot; if (previous.id === target.id && previous.day_of_week === target.day_of_week) return;
    setError(''); setSavingId(entry.id); setEntries((current) => current.map((item) => item.id === entry.id ? { ...entry, timeSlot: target } : item));
    try { const saved = await moveTimetableEntry(entry.id, target.id); setEntries((current) => current.map((item) => item.id === entry.id ? saved : item)); setSelected((current) => current?.id === entry.id ? saved : current); }
    catch (exception: any) { setEntries((current) => current.map((item) => item.id === entry.id ? { ...item, timeSlot: previous } : item)); setError(exception?.response?.data?.message ?? exception?.response?.data?.errors?.schedule?.[0]?.message ?? 'This class cannot be moved to the selected slot.'); }
    finally { setSavingId(null); }
  }

  async function inspect(entry: TimetableEntry) { setSelected(entry); try { const result = await checkEntryConflicts(entry.id); if (!result.valid) setError(result.conflicts.map((c: { message: string }) => c.message).join(' ')); else setError(''); } catch { setError('Could not validate this entry.'); } }
  async function publish() { try { await publishTimetable(1); setPublished(true); setError(''); } catch { setError('Unable to publish timetable.'); } }

  return <main className="min-h-screen p-6 lg:p-10"><div className="mx-auto max-w-[1500px]">
    <div className="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"><div><p className="text-sm font-medium text-cyan-400">TIMETABLE BUILDER</p><h1 className="mt-1 text-3xl font-bold">Fall 2026 · BS Computer Science</h1><p className="mt-2 text-sm text-slate-400">{published ? 'Published schedule' : 'Draft schedule'} · {savingId ? 'Saving change…' : 'Drag classes to reschedule'}</p></div><button onClick={publish} disabled={published || savingId !== null} className="rounded-lg bg-cyan-500 px-4 py-2 text-sm font-semibold text-slate-950 disabled:opacity-50">{published ? 'Published' : 'Publish'}</button></div>
    <GenerationPanel timetableId={1} timeSlotIds={slots.map((slot) => slot.id)} />
    {error && <div className="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-300">{error}</div>}
    {loading ? <div className="rounded-2xl border border-slate-800 bg-slate-900 p-12 text-center text-sm text-slate-500">Loading timetable…</div> : <TimetableGrid days={days} slots={slots} entries={entries} onMove={moveEntry} onSelect={inspect} />}
    {selected && <div className="mt-4 rounded-xl border border-slate-800 bg-slate-900 p-4"><p className="text-sm font-medium">{selected.course.code} · {selected.course.name}</p><p className="mt-1 text-xs text-slate-500">{selected.section.name} · {selected.teacher.first_name} {selected.teacher.last_name} · {selected.room.name}</p>{savingId === selected.id && <p className="mt-2 text-xs text-cyan-400">Saving new time slot…</p>}</div>}
  </div></main>;
}
