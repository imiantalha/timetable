'use client';

import { useEffect, useState } from 'react';
import {
  getCourses,
  getDepartments,
  getRooms,
  getSections,
  getTeachers,
  getTimeSlots,
  type GenerationAssignment,
  type ResourceOption,
} from '../../../lib/timetable-api';

export type GenerationConfigValue = {
  assignments: GenerationAssignment[];
  timeSlotIds: number[];
};

export default function GenerationConfig({
  onGenerate,
  disabled = false,
}: {
  onGenerate: (config: GenerationConfigValue) => void;
  disabled?: boolean;
}) {
  const [departments, setDepartments] = useState<ResourceOption[]>([]);
  const [sections, setSections] = useState<ResourceOption[]>([]);
  const [courses, setCourses] = useState<ResourceOption[]>([]);
  const [teachers, setTeachers] = useState<ResourceOption[]>([]);
  const [rooms, setRooms] = useState<ResourceOption[]>([]);
  const [timeSlots, setTimeSlots] = useState<ResourceOption[]>([]);
  const [departmentId, setDepartmentId] = useState<number | null>(null);
  const [sectionId, setSectionId] = useState<number | null>(null);
  const [courseIds, setCourseIds] = useState<number[]>([]);
  const [teacherId, setTeacherId] = useState<number | null>(null);
  const [roomId, setRoomId] = useState<number | null>(null);
  const [timeSlotIds, setTimeSlotIds] = useState<number[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    Promise.all([getDepartments(), getRooms(), getTimeSlots()])
      .then(([departmentOptions, roomOptions, slotOptions]) => {
        setDepartments(departmentOptions);
        setRooms(roomOptions);
        setTimeSlots(slotOptions);
      })
      .catch(() => setError('Unable to load generation resources. Check the API connection.'))
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    if (!departmentId) return;

    let active = true;

    Promise.all([getSections(departmentId), getCourses(departmentId), getTeachers(departmentId)])
      .then(([sectionOptions, courseOptions, teacherOptions]) => {
        if (!active) return;
        setSections(sectionOptions);
        setCourses(courseOptions);
        setTeachers(teacherOptions);
      })
      .catch(() => {
        if (active) setError('Unable to load department resources.');
      });

    return () => {
      active = false;
    };
  }, [departmentId]);

  function changeDepartment(nextDepartmentId: number | null) {
    setDepartmentId(nextDepartmentId);
    setSections([]);
    setCourses([]);
    setTeachers([]);
    setSectionId(null);
    setCourseIds([]);
    setTeacherId(null);
    setError('');
  }

  const toggleCourse = (id: number) => {
    setCourseIds((current) =>
      current.includes(id) ? current.filter((item) => item !== id) : [...current, id],
    );
  };

  const toggleSlot = (id: number) => {
    setTimeSlotIds((current) =>
      current.includes(id) ? current.filter((item) => item !== id) : [...current, id],
    );
  };

  const canGenerate =
    !disabled &&
    sectionId !== null &&
    teacherId !== null &&
    roomId !== null &&
    courseIds.length > 0 &&
    timeSlotIds.length > 0;

  function submit() {
    if (!canGenerate || sectionId === null || teacherId === null || roomId === null) return;

    onGenerate({
      assignments: courseIds.map((courseId) => ({
        course_id: courseId,
        teacher_id: teacherId,
        section_id: sectionId,
        room_id: roomId,
      })),
      timeSlotIds,
    });
  }

  if (loading) {
    return (
      <section className="mb-5 rounded-2xl border border-slate-800 bg-slate-900 p-5 text-sm text-slate-500">
        Loading generation resources…
      </section>
    );
  }

  return (
    <section className="mb-5 rounded-2xl border border-slate-800 bg-slate-900 p-5">
      <div className="mb-5">
        <p className="text-xs font-semibold uppercase tracking-wider text-cyan-400">
          Generation configuration
        </p>
        <h2 className="mt-1 text-lg font-semibold">Choose scheduling resources</h2>
        <p className="mt-1 text-sm text-slate-500">
          Each selected course will be assigned to the selected section, teacher, and room.
        </p>
      </div>

      {error && <p role="alert" className="mb-4 text-sm text-red-300">{error}</p>}

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Select label="Department" value={departmentId} onChange={changeDepartment} options={departments} />
        <Select label="Section" value={sectionId} onChange={setSectionId} options={sections} disabled={!departmentId} />
        <Select label="Teacher" value={teacherId} onChange={setTeacherId} options={teachers} disabled={!departmentId} />
        <Select label="Room" value={roomId} onChange={setRoomId} options={rooms} />
        <MultiSelect label="Courses" options={courses} selected={courseIds} onToggle={toggleCourse} />
        <MultiSelect label="Available time slots" options={timeSlots} selected={timeSlotIds} onToggle={toggleSlot} />
      </div>

      <button
        type="button"
        disabled={!canGenerate}
        onClick={submit}
        className="mt-5 rounded-lg bg-cyan-500 px-4 py-2 text-sm font-semibold text-slate-950 disabled:opacity-40"
      >
        Generate selected courses
      </button>
    </section>
  );
}

function Select({
  label,
  value,
  onChange,
  options,
  disabled = false,
}: {
  label: string;
  value: number | null;
  onChange: (value: number | null) => void;
  options: ResourceOption[];
  disabled?: boolean;
}) {
  return (
    <label className="text-xs text-slate-500">
      {label}
      <select
        disabled={disabled}
        value={value ?? ''}
        onChange={(event) => onChange(event.target.value ? Number(event.target.value) : null)}
        className="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white disabled:opacity-40"
      >
        <option value="">Select {label.toLowerCase()}</option>
        {options.map((option) => (
          <option key={option.id} value={option.id}>{option.name}</option>
        ))}
      </select>
    </label>
  );
}

function MultiSelect({
  label,
  options,
  selected,
  onToggle,
}: {
  label: string;
  options: ResourceOption[];
  selected: number[];
  onToggle: (id: number) => void;
}) {
  return (
    <fieldset>
      <legend className="text-xs text-slate-500">{label}</legend>
      <div className="mt-1 max-h-36 overflow-auto rounded-lg border border-slate-700 bg-slate-950 p-2">
        {options.length === 0 ? (
          <p className="px-2 py-1.5 text-xs text-slate-600">No resources available.</p>
        ) : options.map((option) => (
          <label key={option.id} className="flex items-center gap-2 rounded px-2 py-1.5 text-xs text-slate-300 hover:bg-slate-900">
            <input
              type="checkbox"
              checked={selected.includes(option.id)}
              onChange={() => onToggle(option.id)}
            />
            {option.name}
          </label>
        ))}
      </div>
    </fieldset>
  );
}
