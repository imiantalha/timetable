'use client';

import { useCallback, useEffect, useState } from 'react';
import TimetableGrid, { type GridSlot } from './TimetableGrid';
import GenerationPanel from './GenerationPanel';
import {
  checkEntryConflicts,
  getTimetableEntries,
  getTimetables,
  getScheduleSlots,
  moveTimetableEntry,
  publishTimetable,
  type TimetableEntry,
  type TimetableOption,
} from '../../../lib/timetable-api';

const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

export default function TimetablesPage() {
  const [timetables, setTimetables] = useState<TimetableOption[]>([]);
  const [timetableId, setTimetableId] = useState<number | null>(null);
  const [entries, setEntries] = useState<TimetableEntry[]>([]);
  const [slots, setSlots] = useState<GridSlot[]>([]);
  const [selected, setSelected] = useState<TimetableEntry | null>(null);
  const [loading, setLoading] = useState(true);
  const [savingId, setSavingId] = useState<number | null>(null);
  const [error, setError] = useState('');
  const [published, setPublished] = useState(false);

  const refreshEntries = useCallback(async (id: number) => {
    setEntries(await getTimetableEntries(id));
  }, []);

  useEffect(() => {
    let active = true;

    getTimetables()
      .then((items) => {
        if (!active) return;
        setTimetables(items);
        if (items.length > 0) {
          setTimetableId(items[0].id);
          setPublished(items[0].status === 'published');
        }
      })
      .catch(() => setError('Unable to load timetables. Check the API URL and authentication.'))
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, []);

  const currentTimetable = timetables.find((item) => item.id === timetableId) ?? null;
  const institutionId = currentTimetable?.institution_id ?? null;

  useEffect(() => {
    if (timetableId === null || institutionId === null) return;

    let active = true;
    setLoading(true);
    setEntries([]);
    setSlots([]);
    setSelected(null);
    setError('');

    Promise.all([
      getTimetableEntries(timetableId),
      getScheduleSlots(institutionId),
    ])
      .then(([items, scheduleSlots]) => {
        if (!active) return;
        setEntries(items);
        setSlots(scheduleSlots);
      })
      .catch(() => {
        if (active) setError('Unable to load the timetable or its available time slots.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [timetableId, institutionId]);

  function selectTimetable(id: number) {
    const next = timetables.find((item) => item.id === id);
    setTimetableId(id);
    setPublished(next?.status === 'published');
  }

  async function moveEntry(entry: TimetableEntry, target: GridSlot) {
    const previous = entry.timeSlot;
    if (previous.id === target.id && previous.day_of_week === target.day_of_week) return;

    setError('');
    setSavingId(entry.id);
    setEntries((current) =>
      current.map((item) => item.id === entry.id ? { ...entry, timeSlot: target } : item),
    );

    try {
      const saved = await moveTimetableEntry(entry.id, target.id);
      setEntries((current) => current.map((item) => item.id === entry.id ? saved : item));
      setSelected((current) => current?.id === entry.id ? saved : current);
    } catch (exception: unknown) {
      setEntries((current) =>
        current.map((item) => item.id === entry.id ? { ...item, timeSlot: previous } : item),
      );

      const response = typeof exception === 'object' && exception !== null && 'response' in exception
        ? exception.response
        : null;
      const responseData = typeof response === 'object' && response !== null && 'data' in response
        ? response.data
        : null;
      const responseMessage = typeof responseData === 'object' && responseData !== null && 'message' in responseData
        ? responseData.message
        : null;

      setError(typeof responseMessage === 'string'
        ? responseMessage
        : 'This class cannot be moved to the selected slot.');
    } finally {
      setSavingId(null);
    }
  }

  async function inspect(entry: TimetableEntry) {
    setSelected(entry);
    try {
      const result = await checkEntryConflicts(entry.id);
      setError(result.valid ? '' : result.conflicts.map((conflict) => conflict.message).join(' '));
    } catch {
      setError('Could not validate this entry.');
    }
  }

  async function publish() {
    if (timetableId === null) return;

    try {
      await publishTimetable(timetableId);
      setPublished(true);
      setTimetables((current) => current.map((item) =>
        item.id === timetableId ? { ...item, status: 'published' } : item,
      ));
      setError('');
    } catch {
      setError('Unable to publish timetable. Make sure it contains valid entries.');
    }
  }

  if (loading && timetables.length === 0) {
    return (
      <main className="min-h-screen p-6 lg:p-10">
        <div className="mx-auto max-w-[1500px] rounded-2xl border border-slate-800 bg-slate-900 p-12 text-center text-sm text-slate-400">
          Loading timetables…
        </div>
      </main>
    );
  }

  if (timetableId === null) {
    return (
      <main className="min-h-screen p-6 lg:p-10">
        <div className="mx-auto max-w-[900px] rounded-2xl border border-slate-800 bg-slate-900 p-8">
          <p className="text-sm font-medium text-cyan-400">TIMETABLE BUILDER</p>
          <h1 className="mt-2 text-2xl font-bold">No timetables found</h1>
          <p className="mt-2 text-sm text-slate-400">
            Create a draft timetable through the API, then return here to generate and manage its schedule.
          </p>
          {error && <p role="alert" className="mt-4 text-sm text-red-300">{error}</p>}
        </div>
      </main>
    );
  }

  return (
    <main className="min-h-screen p-6 lg:p-10">
      <div className="mx-auto max-w-[1500px]">
        <div className="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
          <div>
            <p className="text-sm font-medium text-cyan-400">TIMETABLE BUILDER</p>
            <h1 className="mt-1 text-3xl font-bold">{currentTimetable?.name ?? 'Timetable'}</h1>
            <p className="mt-2 text-sm text-slate-400">
              {published ? 'Published schedule' : 'Draft schedule'} · {savingId ? 'Saving change…' : 'Drag classes to reschedule'}
            </p>
          </div>
          <div className="flex flex-col gap-2 sm:flex-row">
            <label className="text-xs text-slate-400">
              Select timetable
              <select
                value={timetableId}
                onChange={(event) => selectTimetable(Number(event.target.value))}
                className="mt-1 block min-w-56 rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white"
              >
                {timetables.map((item) => (
                  <option key={item.id} value={item.id}>{item.name}</option>
                ))}
              </select>
            </label>
            <button
              onClick={publish}
              disabled={published || savingId !== null || entries.length === 0}
              className="self-end rounded-lg bg-cyan-500 px-4 py-2 text-sm font-semibold text-slate-950 disabled:opacity-50"
            >
              {published ? 'Published' : 'Publish'}
            </button>
          </div>
        </div>

        {!published && (
          <GenerationPanel
            timetableId={timetableId}
            onGenerated={() => refreshEntries(timetableId)}
          />
        )}

        {error && (
          <div role="alert" className="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-300">
            {error}
          </div>
        )}

        {loading ? (
          <div className="rounded-2xl border border-slate-800 bg-slate-900 p-12 text-center text-sm text-slate-500">
            Loading timetable…
          </div>
        ) : (
          <TimetableGrid days={days} slots={slots} entries={entries} onMove={moveEntry} onSelect={inspect} />
        )}

        {selected && (
          <div className="mt-4 rounded-xl border border-slate-800 bg-slate-900 p-4">
            <p className="text-sm font-medium">{selected.course.code} · {selected.course.name}</p>
            <p className="mt-1 text-xs text-slate-500">
              {selected.section.name} · {selected.teacher.first_name} {selected.teacher.last_name} · {selected.room.name}
            </p>
            {savingId === selected.id && (
              <p className="mt-2 text-xs text-cyan-400">Saving new time slot…</p>
            )}
          </div>
        )}
      </div>
    </main>
  );
}
