/* Student Documents — upload through the shared Media pipeline (media_id only,
 * never paths). Document type comes from Master Data. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXConfirm, AXSelect, AXTable, useConfirm, type AXColumn } from '@ui/ax';
import { tokenStore } from '@core/api/client';
import { useMasterValues } from '@features/academic/useReference';
import { studentsApi, type StudentDoc } from './api';
import { StudentPicker, useStudentList } from './StudentPicker';

async function uploadMedia(file: File): Promise<number> {
  const body = new FormData();
  body.append('file', file);
  body.append('collection', 'student-documents');
  const token = tokenStore.get();
  const res = await fetch('/api/v1/media/upload', {
    method: 'POST',
    headers: { Accept: 'application/json', ...(token ? { Authorization: `Bearer ${token}` } : {}) },
    body,
  });
  const env = await res.json();
  if (!res.ok || env.success === false) throw new Error(env.message ?? 'Upload failed');
  return env.data.id as number;
}

export function DocumentsPage() {
  const { user } = useAuth();
  const students = useStudentList();
  const docTypes = useMasterValues('document_types');
  const [id, setId] = useState('');
  const [docs, setDocs] = useState<StudentDoc[]>([]);
  const [typeId, setTypeId] = useState('');
  const [title, setTitle] = useState('');
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const { confirmProps, ask } = useConfirm();

  const load = () => {
    if (id) studentsApi.documents.list(Number(id)).then((r) => setDocs(r.data));
  };
  useEffect(() => {
    setDocs([]);
    if (id) load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id]);

  const onFile = async (file: File | null) => {
    if (!file || !id) return;
    setBusy(true);
    setError(null);
    try {
      const mediaId = await uploadMedia(file);
      await studentsApi.documents.create({
        school_id: user?.school_id,
        student_id: Number(id),
        document_type_id: typeId ? Number(typeId) : null,
        media_id: mediaId,
        title: title || file.name,
      });
      setTitle('');
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed.');
    } finally {
      setBusy(false);
    }
  };

  const columns: AXColumn<StudentDoc>[] = [
    {
      key: 'title',
      header: 'Title',
      render: (r) => (
        <span className="font-medium">{r.title ?? r.document_type?.label ?? `#${r.id}`}</span>
      ),
    },
    { key: 'type', header: 'Type', render: (r) => r.document_type?.label ?? '—' },
    {
      key: 'media',
      header: 'File',
      render: (r) =>
        r.media?.url ? (
          <a
            href={r.media.url}
            className="text-[var(--navy-accent)]"
            target="_blank"
            rel="noreferrer"
          >
            <i className="fas fa-download" />
          </a>
        ) : (
          '—'
        ),
    },
    {
      key: 'actions',
      header: '',
      className: 'text-right',
      render: (r) => (
        <button
          onClick={() =>
            ask('Remove this document?', () => studentsApi.documents.remove(r.id).then(load))
          }
          className="text-gray-500 hover:text-[var(--danger)]"
        >
          <i className="fas fa-trash" />
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-folder-open text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Documents</h2>
        </div>
        <StudentPicker value={id} onChange={setId} students={students} />
      </div>

      {id && (
        <>
          <div className="erp-card flex flex-wrap items-end gap-3">
            <div className="w-56">
              <AXSelect
                label="Document type"
                value={typeId}
                onChange={(e) => setTypeId(e.target.value)}
                options={[{ value: '', label: '—' }, ...docTypes]}
              />
            </div>
            <label className="block flex-1">
              <span className="mb-1 block text-sm font-medium text-gray-700">Upload file</span>
              <input
                type="file"
                disabled={busy}
                onChange={(e) => onFile(e.target.files?.[0] ?? null)}
                className="block w-full text-sm"
              />
            </label>
            {busy && <AXBadge tone="amber">Uploading…</AXBadge>}
          </div>
          {error && (
            <div className="rounded-md bg-red-50 px-3 py-2 text-sm text-[var(--danger)]">
              {error}
            </div>
          )}
          <AXTable columns={columns} rows={docs} rowKey={(r) => r.id} empty="No documents." />
        </>
      )}
      <AXConfirm {...confirmProps} />
    </div>
  );
}
