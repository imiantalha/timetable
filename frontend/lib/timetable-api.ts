import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api/v1',
});

api.interceptors.request.use((config) => {
  if (typeof window !== 'undefined') {
    const token = window.localStorage.getItem('auth_token');
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

export type ResourceOption = { id: number; name: string; code?: string };
export type GenerationAssignment = {
  course_id: number;
  teacher_id: number;
  section_id: number;
  room_id: number;
};

type ResourcePayload = {
  data?: Record<string, unknown>[] | { data?: Record<string, unknown>[] };
};

async function getOptions(resource: string, params: Record<string, number | undefined> = {}) {
  const response = await api.get<ResourcePayload | Record<string, unknown>[]>(`/${resource}`, {
    params: { per_page: 100, ...params },
  });
  const payload = response.data;
  const wrapped = payload as ResourcePayload;
  const items: Record<string, unknown>[] = Array.isArray(payload)
    ? payload
    : Array.isArray(wrapped.data)
      ? wrapped.data
      : wrapped.data?.data ?? [];

  return items
    .filter((item) => typeof item.id === 'number' && item.is_active !== false && !(resource === 'time-slots' && item.is_break === true))
    .map((item) => {
      const fullName = [item.first_name, item.last_name].filter(Boolean).join(' ');
      const dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
      const day = typeof item.day_of_week === 'number' ? dayNames[item.day_of_week] : undefined;
      const slotLabel = item.starts_at && item.ends_at
        ? `${day ? `${day} · ` : ''}${String(item.starts_at)}–${String(item.ends_at)}`
        : undefined;
      const name = item.name ?? item.title ?? slotLabel ?? (fullName || item.code) ?? `#${item.id}`;
      return {
        id: item.id as number,
        name: String(name),
        code: typeof item.code === 'string' ? item.code : undefined,
      };
    });
}

export const getDepartments = () => getOptions('departments');
export const getSections = (departmentId?: number) => getOptions('sections', { department_id: departmentId });
export const getCourses = (departmentId?: number) => getOptions('courses', { department_id: departmentId });
export const getTeachers = () => getOptions('teachers');
export const getRooms = () => getOptions('rooms');
export const getTimeSlots = () => getOptions('time-slots');

export type TimetableOption = { id: number; name: string; status: 'draft' | 'published' };

export async function getTimetables(): Promise<TimetableOption[]> {
  const { data } = await api.get<{ data?: TimetableOption[] | { data?: TimetableOption[] } }>('/timetables', {
    params: { per_page: 100 },
  });
  const payload = data.data;
  if (Array.isArray(payload)) return payload;
  return payload?.data ?? [];
}

export async function getTimetableEntries(timetableId: number): Promise<TimetableEntry[]> {
  const { data } = await api.get<{ data?: TimetableEntry[] | { data?: TimetableEntry[] } }>('/timetable-entries', {
    params: { timetable_id: timetableId, per_page: 100 },
  });
  const payload = data.data;
  if (Array.isArray(payload)) return payload;
  return payload?.data ?? [];
}

export async function checkEntryConflicts(entryId: number) {
  const { data } = await api.get(`/timetable-entries/${entryId}/conflicts`);
  return data.data as {
    valid: boolean;
    conflicts: Array<{ type: string; message: string; entry_id: number }>;
  };
}

export async function moveTimetableEntry(entryId: number, timeSlotId: number) {
  const { data } = await api.patch(`/timetable-entries/${entryId}/move`, {
    time_slot_id: timeSlotId,
  });
  return data.data as TimetableEntry;
}

export async function generateTimetable(
  timetableId: number,
  assignments: GenerationAssignment[],
  timeSlotIds: number[],
) {
  const { data } = await api.post(`/timetables/${timetableId}/generate`, {
    assignments,
    time_slot_ids: timeSlotIds,
  });
  return data.data as {
    timetable_id: number;
    created_count: number;
    unplaced_count: number;
    unplaced: Array<{ course_id: number; message: string }>;
  };
}

export async function publishTimetable(timetableId: number) {
  const { data } = await api.post(`/timetables/${timetableId}/publish`);
  return data.data;
}
