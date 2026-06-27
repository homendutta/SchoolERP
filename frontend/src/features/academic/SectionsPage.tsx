/* Sections — belong to a class, with capacity validation and room assignment. */
import { useState } from 'react';
import type { AXColumn } from '@ui/ax';
import { academicApi, type Section } from './api';
import { EntityManager, statusCell, type Field } from './EntityManager';
import { useClasses, useRooms } from './useReference';

export function SectionsPage() {
  const classes = useClasses();
  const rooms = useRooms();
  const [classId, setClassId] = useState('');

  const fields: Field[] = [
    { name: 'class_id', label: 'Class', type: 'select', options: classes, required: true },
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'capacity', label: 'Capacity', type: 'number' },
    { name: 'room_id', label: 'Room', type: 'select', options: rooms },
    { name: 'display_order', label: 'Display order', type: 'number' },
  ];

  const columns: AXColumn<Section>[] = [
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'capacity', header: 'Capacity', render: (r) => r.capacity ?? '—' },
    { key: 'display_order', header: 'Order' },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Section>
      title="Sections"
      icon="table-cells"
      unitLabel="sections"
      api={academicApi.sections}
      columns={columns}
      fields={fields}
      emptyForm={{
        class_id: classId || '',
        name: '',
        capacity: null,
        room_id: null,
        display_order: 0,
      }}
      toForm={(r) => ({
        class_id: r.class_id,
        name: r.name,
        capacity: r.capacity,
        room_id: r.room_id,
        display_order: r.display_order,
      })}
      listParams={classId ? { filter: { class_id: classId } } : {}}
      filters={[
        {
          name: 'class_id',
          label: 'Class',
          options: classes,
          value: classId,
          onChange: setClassId,
        },
      ]}
      sort="display_order"
    />
  );
}
