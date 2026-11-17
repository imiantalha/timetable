'use client';

import { useState } from 'react';
import { generateTimetable } from '../../../lib/timetable-api';
import GenerationConfig, { type GenerationConfigValue } from './GenerationConfig';

type GenerationResult = {
  created_count: number;
  unplaced_count: number;
  unplaced: Array<{ course_id: number; message: string }>;
};

export default function GenerationPanel({ timetableId = 1, onGenerated }: { timetableId?: number; onGenerated?: () => void | Promise<void> }) {
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState<GenerationResult | null>(null);
  const [error, setError] = useState('');

  async function generate(config: GenerationConfigValue) {
    setLoading(true);
    setResult(null);
    setError('');

    try {
      const response = await generateTimetable(
        timetableId,
        config.assignments,
        config.timeSlotIds,
      );
      setResult(response as GenerationResult);
      await onGenerated?.();
    } catch (exception: unknown) {
      const message =
        typeof exception === 'object' &&
        exception !== null &&
        'response' in exception &&
        typeof exception.response === 'object' &&
        exception.response !== null &&
        'data' in exception.response &&
        typeof exception.response.data === 'object' &&
        exception.response.data !== null &&
        'message' in exception.response.data &&
        typeof exception.response.data.message === 'string'
          ? exception.response.data.message
          : 'Generation failed. Check the selected resources and try again.';
      setError(message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div>
      <GenerationConfig onGenerate={generate} disabled={loading} />
      {loading && (
        <p role="status" className="mb-4 text-sm text-cyan-400">Generating timetable…</p>
      )}
      {error && (
        <p role="alert" className="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-300">
          {error}
        </p>
      )}
      {result && (
        <div className="mb-5 grid gap-3 sm:grid-cols-2">
          <div className="rounded-xl border border-slate-800 bg-slate-900 p-3">
            <p className="text-xs text-slate-500">Created entries</p>
            <p className="mt-1 text-lg font-semibold">{result.created_count}</p>
          </div>
          <div className="rounded-xl border border-slate-800 bg-slate-900 p-3">
            <p className="text-xs text-slate-500">Unplaced courses</p>
            <p className="mt-1 text-lg font-semibold">{result.unplaced_count}</p>
          </div>
          {result.unplaced.length > 0 && (
            <ul className="sm:col-span-2 space-y-1 rounded-xl border border-amber-500/20 bg-amber-500/5 p-3 text-sm text-amber-200">
              {result.unplaced.map((item, index) => (
                <li key={`${item.course_id}-${index}`}>
                  Course #{item.course_id}: {item.message}
                </li>
              ))}
            </ul>
          )}
        </div>
      )}
    </div>
  );
}
