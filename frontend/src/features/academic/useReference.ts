/* Small hooks that load reference lists (years, classes, subjects, rooms,
 * teachers, master-data values) and expose them as AX select options. */
import { useEffect, useState } from 'react';
import { apiClient, apiPage } from '@core/api/client';
import { academicApi, qs } from './api';
import type { FieldOption } from './EntityManager';

type Option = FieldOption;

function useList<T>(loader: () => Promise<T[]>, map: (item: T) => Option, deps: unknown[] = []) {
  const [options, setOptions] = useState<Option[]>([]);
  useEffect(() => {
    let active = true;
    loader().then((items) => {
      if (active) setOptions(items.map(map));
    });
    return () => {
      active = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, deps);
  return options;
}

export const useYears = () =>
  useList(
    () => academicApi.years.list({ per_page: 100, sort: 'start_date' }).then((r) => r.data),
    (y) => ({ value: String(y.id), label: y.name })
  );

export const useClasses = () =>
  useList(
    () => academicApi.classes.list({ per_page: 100, sort: 'display_order' }).then((r) => r.data),
    (c) => ({ value: String(c.id), label: c.name })
  );

export const useSubjects = () =>
  useList(
    () => academicApi.subjects.list({ per_page: 200, sort: 'display_order' }).then((r) => r.data),
    (s) => ({ value: String(s.id), label: `${s.code} — ${s.name}` })
  );

export const useRooms = () =>
  useList(
    () => academicApi.rooms.list({ per_page: 200 }).then((r) => r.data),
    (r) => ({ value: String(r.id), label: r.name })
  );

export const useCalendars = () =>
  useList(
    () => academicApi.calendars.list({ per_page: 100 }).then((r) => r.data),
    (c) => ({ value: String(c.id), label: c.name })
  );

export const useHolidayTypes = () =>
  useList(
    () => academicApi.holidayTypes.list({ per_page: 100 }).then((r) => r.data),
    (h) => ({ value: String(h.id), label: h.name })
  );

export const useAllSections = () =>
  useList(
    () => academicApi.sections.list({ per_page: 500 }).then((r) => r.data),
    (s) => ({ value: String(s.id), label: s.name })
  );

/** Sections for a given class (empty when no class chosen). */
export function useSections(classId: string) {
  return useList(
    () =>
      classId
        ? academicApi.sections
            .list({ filter: { class_id: classId }, per_page: 200 })
            .then((r) => r.data)
        : Promise.resolve([]),
    (s) => ({ value: String(s.id), label: s.name }),
    [classId]
  );
}

interface UserRow {
  id: number;
  name: string;
}

/** Staff/teachers picker (uses the shared Users endpoint). */
export const useTeachers = () =>
  useList(
    () => apiPage<UserRow>('/users?per_page=100').then((r) => r.data),
    (u) => ({ value: String(u.id), label: u.name })
  );

interface MasterValue {
  id: number;
  label: string;
}
interface MasterType {
  id: number;
  slug: string;
}

/** Master-data values for a type slug (e.g. room_types, subject_types). */
export function useMasterValues(typeSlug: string) {
  return useList(
    async () => {
      const types = await apiPage<MasterType>('/admin/master-data/types?per_page=100');
      const type = types.data.find((t) => t.slug === typeSlug);
      if (!type) return [];
      const values = await apiClient.get<MasterValue[]>(
        `/admin/master-data/values?${qs({ filter: { type_id: type.id }, per_page: 200 })}`
      );
      return Array.isArray(values) ? values : [];
    },
    (v) => ({ value: String(v.id), label: v.label }),
    [typeSlug]
  );
}
