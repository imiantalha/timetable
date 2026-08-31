import axios from 'axios';

const api = axios.create({ baseURL: process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api/v1' });

api.interceptors.request.use((config) => {
  if (typeof window !== 'undefined') {
    const token = localStorage.getItem('auth_token');
    if (token) config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export type TimetableEntry = {
  id: number;
  course: { id: number; name: string; code: string };
  teacher: { id: number; first_name: string; last_name: string };
  section: { id: number; name: string; code: string };
  room: { id: number; name: string; code: string };
  timeSlot: { id: number; day_of_week: number; starts_at: string; ends_at: string };
};

export async function getTimetableEntries(timetableId: number) {
  const { data } = await api.get(`/timetable-entries?timetable_id=${timetableId}`);
  return data.data;
}

export async function checkEntryConflicts(entryId: number) {
  const { data } = await api.get(`/timetable-entries/${entryId}/conflicts`);
  return data.data;
}

export async function generateTimetable(timetableId: number, assignments: unknown[], timeSlotIds: number[]) {
  const { data } = await api.post(`/timetables/${timetableId}/generate`, { assignments, time_slot_ids: timeSlotIds });
  return data.data;
}

export async function publishTimetable(timetableId: number) {
  const { data } = await api.post(`/timetables/${timetableId}/publish`);
  return data.data;
}
