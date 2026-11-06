import axios from 'axios';

export const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api/v1',
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
});

api.interceptors.request.use((config) => {
  if (typeof window !== 'undefined') {
    const token = window.localStorage.getItem('auth_token');
    if (token) config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export type Paginated<T> = { data: { data: T[]; current_page: number; last_page: number; total: number } };

export type TimetableEntry = {
  id: number;
  course: { id: number; name: string; code: string };
  teacher: { id: number; first_name: string; last_name: string };
  section: { id: number; name: string; code: string };
  room: { id: number; name: string; code: string };
  timeSlot: { id: number; day_of_week: number; starts_at: string; ends_at: string };
};

export async function getTimetableEntries(timetableId: number) {
  const response = await api.get<Paginated<TimetableEntry>>('/timetable-entries', { params: { timetable_id: timetableId, per_page: 100 } });
  return response.data.data;
}

export async function checkEntryConflicts(entryId: number) {
  const response = await api.get(`/timetable-entries/${entryId}/conflicts`);
  return response.data.data as { valid: boolean; conflicts: Array<{ type: string; message: string; entry_id: number }> };
}

export async function generateTimetable(timetableId: number, assignments: unknown[], timeSlotIds: number[]) {
  const response = await api.post(`/timetables/${timetableId}/generate`, { assignments, time_slot_ids: timeSlotIds });
  return response.data.data;
}

export async function publishTimetable(timetableId: number) {
  const response = await api.post(`/timetables/${timetableId}/publish`);
  return response.data.data;
}
