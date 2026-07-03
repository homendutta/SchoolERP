/* Document Templates — versioned. Editing metadata; a new version is created via
 * the row action (old versions preserved). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import {
  EntityManager,
  statusCell,
  type Field,
  type FieldOption,
} from '@features/academic/EntityManager';
import { ORIENTATIONS, PAPER_SIZES, documentsApi, type Ref } from './api';

export function TemplatesPage() {
  const { user } = useAuth();
  const [types, setTypes] = useState<FieldOption[]>([]);

  useEffect(() => {
    documentsApi.certificateTypes
      .list({ filter: { school_id: user?.school_id }, per_page: 200 })
      .then((r) => setTypes(r.data.map((t) => ({ value: String(t.id), label: String(t.name) }))));
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'name', label: 'Template name', type: 'text', required: true },
    { name: 'code', label: 'Code', type: 'text' },
    {
      name: 'certificate_type_id',
      label: 'Certificate type',
      type: 'select',
      options: [{ value: '', label: '—' }, ...types],
    },
    { name: 'html', label: 'HTML body (use {{student.name}} …)', type: 'text' },
    { name: 'header', label: 'Header', type: 'text' },
    { name: 'footer', label: 'Footer', type: 'text' },
    { name: 'logo_media_id', label: 'Logo (Media id)', type: 'number' },
    { name: 'watermark_media_id', label: 'Watermark (Media id)', type: 'number' },
    {
      name: 'orientation',
      label: 'Orientation',
      type: 'select',
      options: ORIENTATIONS.map((o) => ({ value: o, label: o })),
    },
    {
      name: 'paper_size',
      label: 'Paper size',
      type: 'select',
      options: PAPER_SIZES.map((p) => ({ value: p, label: p })),
    },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Template',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'code', header: 'Code', render: (r) => String(r.code ?? '—') },
    { key: 'version', header: 'Version', render: (r) => `v${String(r.version ?? 1)}` },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Templates"
      icon="file-code"
      unitLabel="templates"
      api={documentsApi.templates}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        code: '',
        certificate_type_id: '',
        html: '',
        header: '',
        footer: '',
        logo_media_id: '',
        watermark_media_id: '',
        orientation: 'portrait',
        paper_size: 'a4',
      }}
      toForm={(r) => ({
        name: r.name,
        code: r.code ?? '',
        certificate_type_id: r.certificate_type_id ? String(r.certificate_type_id) : '',
        html: r.html ?? '',
        header: r.header ?? '',
        footer: r.footer ?? '',
        logo_media_id: (r.logo_media_id as number) ?? '',
        watermark_media_id: (r.watermark_media_id as number) ?? '',
        orientation: String(r.orientation ?? 'portrait'),
        paper_size: String(r.paper_size ?? 'a4'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search templates…"
      sort="name"
      rowExtras={(r, reload) => (
        <button
          title="Create a new version"
          className="hover:text-[var(--navy-primary)]"
          onClick={() => documentsApi.templateVersion(r.id, {}).then(reload)}
        >
          <i className="fas fa-code-branch" />
        </button>
      )}
    />
  );
}
