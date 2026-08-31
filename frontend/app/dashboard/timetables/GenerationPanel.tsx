'use client';

import { useState } from 'react';
import { generateTimetable } from '../../../lib/timetable-api';

export default function GenerationPanel({ timetableId = 1, timeSlotIds = [] }: { timetableId?: number; timeSlotIds?: number[] }) {
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState<{ status?: string; score?: number; created_count?: number; unplaced_count?: number; message?: string } | null>(null);

  async function generate() {
    setLoading(true); setResult(null);
    try { setResult(await generateTimetable(timetableId, [], timeSlotIds)); }
    catch (error: any) { setResult({ status: 'failed', message: error?.response?.data?.message ?? 'Generation failed. Please check the timetable constraints.' }); }
    finally { setLoading(false); }
  }

  return <section className="mb-5 rounded-2xl border border-slate-800 bg-slate-900 p-5">
    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div><p className="text-xs font-semibold uppercase tracking-wider text-cyan-400">Automatic generation</p><h2 className="mt-1 text-lg font-semibold">Generate a conflict-aware draft</h2><p className="mt-1 text-sm text-slate-500">Build a draft using the backend scheduling constraints.</p></div>
      <button onClick={generate} disabled={loading} className="rounded-lg bg-cyan-500 px-4 py-2 text-sm font-semibold text-slate-950 disabled:opacity-50">{loading ? 'Generating…' : 'Generate draft'}</button>
    </div>
    {result && <div className="mt-5 grid gap-3 sm:grid-cols-3"><div className="rounded-xl bg-slate-950 p-3"><p className="text-xs text-slate-500">Status</p><p className="mt-1 text-sm font-medium">{result.status ?? 'completed'}</p></div><div className="rounded-xl bg-slate-950 p-3"><p className="text-xs text-slate-500">Score</p><p className="mt-1 text-sm font-medium">{result.score ?? '—'}</p></div><div className="rounded-xl bg-slate-950 p-3"><p className="text-xs text-slate-500">Unplaced</p><p className="mt-1 text-sm font-medium">{result.unplaced_count ?? '—'}</p></div>{result.message && <p className="sm:col-span-3 text-sm text-red-300">{result.message}</p>}</div>}
  </section>;
}
