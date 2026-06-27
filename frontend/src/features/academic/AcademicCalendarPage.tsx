/* Academic Calendar — a reusable platform calendar service. Manages calendars,
 * their events (Working Day, Holiday, Half Day, Examination Day, School Event,
 * Special Working Day) and the school's holiday types. */
import { useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { academicApi, type AcademicCalendar, type CalendarEvent, type HolidayType } from './api';
import { EntityManager, statusCell, type Field, type FieldOption } from './EntityManager';
import { useCalendars, useHolidayTypes, useYears } from './useReference';

const EVENT_TYPES: FieldOption[] = [
  { value: 'working_day', label: 'Working Day' },
  { value: 'holiday', label: 'Holiday' },
  { value: 'half_day', label: 'Half Day' },
  { value: 'examination_day', label: 'Examination Day' },
  { value: 'school_event', label: 'School Event' },
  { value: 'special_working_day', label: 'Special Working Day' },
];
const EVENT_LABEL = Object.fromEntries(EVENT_TYPES.map((t) => [t.value, t.label]));

type Tab = 'calendars' | 'events' | 'holiday-types';

export function AcademicCalendarPage() {
  const [tab, setTab] = useState<Tab>('calendars');
  const tabs: { id: Tab; label: string; icon: string }[] = [
    { id: 'calendars', label: 'Calendars', icon: 'calendar-day' },
    { id: 'events', label: 'Events', icon: 'calendar-check' },
    { id: 'holiday-types', label: 'Holiday Types', icon: 'umbrella-beach' },
  ];

  return (
    <div className="space-y-4">
      <div className="flex gap-1 rounded-lg border border-gray-200 bg-white p-1 text-sm">
        {tabs.map((t) => (
          <button
            key={t.id}
            onClick={() => setTab(t.id)}
            className={`flex items-center gap-2 rounded-md px-3 py-2 font-medium ${tab === t.id ? 'bg-[var(--navy-primary)] text-white' : 'text-gray-600 hover:bg-gray-100'}`}
          >
            <i className={`fas fa-${t.icon}`} /> {t.label}
          </button>
        ))}
      </div>
      {tab === 'calendars' && <CalendarsTab />}
      {tab === 'events' && <EventsTab />}
      {tab === 'holiday-types' && <HolidayTypesTab />}
    </div>
  );
}

function CalendarsTab() {
  const { user } = useAuth();
  const years = useYears();
  const fields: Field[] = [
    {
      name: 'academic_year_id',
      label: 'Academic year',
      type: 'select',
      options: years,
      required: true,
    },
    { name: 'name', label: 'Name', type: 'text', required: true },
  ];
  const columns: AXColumn<AcademicCalendar>[] = [
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'status', header: 'Status', render: statusCell },
  ];
  return (
    <EntityManager<AcademicCalendar>
      title="Calendars"
      icon="calendar-day"
      unitLabel="calendars"
      api={academicApi.calendars}
      columns={columns}
      fields={fields}
      emptyForm={{ academic_year_id: '', name: '' }}
      toForm={(r) => ({ academic_year_id: r.academic_year_id, name: r.name })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
    />
  );
}

function EventsTab() {
  const calendars = useCalendars();
  const holidayTypes = useHolidayTypes();
  const [calendarId, setCalendarId] = useState('');

  const fields: Field[] = [
    {
      name: 'academic_calendar_id',
      label: 'Calendar',
      type: 'select',
      options: calendars,
      required: true,
    },
    { name: 'title', label: 'Title', type: 'text', required: true },
    {
      name: 'event_type',
      label: 'Event type',
      type: 'select',
      options: EVENT_TYPES,
      required: true,
    },
    { name: 'holiday_type_id', label: 'Holiday type', type: 'select', options: holidayTypes },
    { name: 'start_date', label: 'Start date', type: 'date', required: true },
    { name: 'end_date', label: 'End date', type: 'date' },
    { name: 'description', label: 'Description', type: 'text' },
    { name: 'is_recurring', label: 'Recurring', type: 'checkbox' },
  ];
  const columns: AXColumn<CalendarEvent>[] = [
    {
      key: 'title',
      header: 'Title',
      render: (r) => <span className="font-medium">{r.title}</span>,
    },
    {
      key: 'event_type',
      header: 'Type',
      render: (r) => <AXBadge tone="navy">{EVENT_LABEL[r.event_type] ?? r.event_type}</AXBadge>,
    },
    {
      key: 'period',
      header: 'Period',
      render: (r) => (
        <span className="text-xs text-gray-500">
          {r.start_date}
          {r.end_date ? ` → ${r.end_date}` : ''}
        </span>
      ),
    },
    { key: 'status', header: 'Status', render: statusCell },
  ];
  return (
    <EntityManager<CalendarEvent>
      title="Calendar Events"
      icon="calendar-check"
      unitLabel="events"
      api={academicApi.events}
      columns={columns}
      fields={fields}
      emptyForm={{
        academic_calendar_id: calendarId || '',
        title: '',
        event_type: 'working_day',
        holiday_type_id: null,
        start_date: '',
        end_date: '',
        description: '',
        is_recurring: false,
      }}
      toForm={(r) => ({
        academic_calendar_id: r.academic_calendar_id,
        title: r.title,
        event_type: r.event_type,
        holiday_type_id: r.holiday_type_id,
        start_date: r.start_date ?? '',
        end_date: r.end_date ?? '',
        description: r.description ?? '',
        is_recurring: r.is_recurring,
      })}
      listParams={calendarId ? { filter: { academic_calendar_id: calendarId } } : {}}
      filters={[
        {
          name: 'academic_calendar_id',
          label: 'Calendar',
          options: calendars,
          value: calendarId,
          onChange: setCalendarId,
        },
      ]}
      sort="start_date"
    />
  );
}

function HolidayTypesTab() {
  const { user } = useAuth();
  const fields: Field[] = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'color', label: 'Color (hex)', type: 'text' },
  ];
  const columns: AXColumn<HolidayType>[] = [
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    {
      key: 'color',
      header: 'Color',
      render: (r) =>
        r.color ? (
          <span className="inline-flex items-center gap-2">
            <span className="h-3 w-3 rounded-full" style={{ background: r.color }} />
            {r.color}
          </span>
        ) : (
          '—'
        ),
    },
    { key: 'status', header: 'Status', render: statusCell },
  ];
  return (
    <EntityManager<HolidayType>
      title="Holiday Types"
      icon="umbrella-beach"
      unitLabel="types"
      api={academicApi.holidayTypes}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', color: '' }}
      toForm={(r) => ({ name: r.name, color: r.color ?? '' })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
    />
  );
}
