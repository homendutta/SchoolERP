/* LMS Assignment/Homework Reviews — look up a student's submission history and
 * record a teacher review (comment / grade / return / approve). */
import { useState } from 'react';
import { AXBadge, AXInput, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import { REVIEW_ACTIONS, lmsApi, type Ref } from './api';

export function ReviewsPage() {
  const [lookup, setLookup] = useState({ type: 'assignment', submittable_id: '', student_id: '' });
  const [rows, setRows] = useState<Ref[]>([]);
  const [form, setForm] = useState({
    submission_id: '',
    subject_id: '',
    action: 'grade',
    marks: '',
    comment: '',
  });
  const [status, setStatus] = useState<string | null>(null);

  const load = () => {
    if (!lookup.submittable_id || !lookup.student_id) return;
    lmsApi
      .submissions({
        type: lookup.type,
        submittable_id: lookup.submittable_id,
        student_id: lookup.student_id,
      })
      .then((r) => setRows(r as Ref[]))
      .catch((e) => setStatus(e instanceof Error ? e.message : 'Lookup failed.'));
  };

  const submit = async () => {
    setStatus(null);
    try {
      await lmsApi.review({
        submission_id: Number(form.submission_id),
        subject_id: form.subject_id ? Number(form.subject_id) : undefined,
        action: form.action,
        marks: form.marks ? Number(form.marks) : undefined,
        comment: form.comment || undefined,
      });
      setStatus('Review recorded.');
      load();
    } catch (e) {
      setStatus(e instanceof Error ? e.message : 'Review failed.');
    }
  };

  const columns: AXColumn<Ref>[] = [
    {
      key: 'v',
      header: 'Version',
      render: (r) => <span className="font-medium">v{String(r.version)}</span>,
    },
    { key: 'when', header: 'Submitted', render: (r) => String(r.submitted_at ?? '—') },
    { key: 'late', header: 'Late', render: (r) => (r.is_late ? 'Yes' : 'No') },
    { key: 'status', header: 'Status', render: (r) => String(r.status) },
    { key: 'marks', header: 'Marks', render: (r) => String(r.marks ?? '—') },
    {
      key: 'act',
      header: '',
      render: (r) => (
        <button
          className="text-xs font-semibold text-[var(--navy-accent)]"
          onClick={() => setForm((f) => ({ ...f, submission_id: String(r.id) }))}
        >
          Review
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-clipboard-check text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Submission Reviews</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-40">
          <AXSelect
            label="Type"
            value={lookup.type}
            onChange={(e) => setLookup((l) => ({ ...l, type: e.target.value }))}
            options={[
              { value: 'assignment', label: 'assignment' },
              { value: 'homework', label: 'homework' },
            ]}
          />
        </div>
        <div className="w-36">
          <AXInput
            label="Item id"
            type="number"
            value={lookup.submittable_id}
            onChange={(e) => setLookup((l) => ({ ...l, submittable_id: e.target.value }))}
          />
        </div>
        <div className="w-36">
          <AXInput
            label="Student id"
            type="number"
            value={lookup.student_id}
            onChange={(e) => setLookup((l) => ({ ...l, student_id: e.target.value }))}
          />
        </div>
        <button
          onClick={load}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white"
        >
          Load submissions
        </button>
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No submissions loaded." />

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-32">
          <AXInput
            label="Submission id"
            type="number"
            value={form.submission_id}
            onChange={(e) => setForm((f) => ({ ...f, submission_id: e.target.value }))}
          />
        </div>
        <div className="w-32">
          <AXInput
            label="Subject id"
            type="number"
            value={form.subject_id}
            onChange={(e) => setForm((f) => ({ ...f, subject_id: e.target.value }))}
          />
        </div>
        <div className="w-40">
          <AXSelect
            label="Action"
            value={form.action}
            onChange={(e) => setForm((f) => ({ ...f, action: e.target.value }))}
            options={REVIEW_ACTIONS.map((a) => ({ value: a, label: a }))}
          />
        </div>
        <div className="w-28">
          <AXInput
            label="Marks"
            type="number"
            value={form.marks}
            onChange={(e) => setForm((f) => ({ ...f, marks: e.target.value }))}
          />
        </div>
        <div className="w-56">
          <AXInput
            label="Comment"
            value={form.comment}
            onChange={(e) => setForm((f) => ({ ...f, comment: e.target.value }))}
          />
        </div>
        <button
          onClick={submit}
          disabled={!form.submission_id}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          Save review
        </button>
        {status && <AXBadge tone="navy">{status}</AXBadge>}
      </div>
    </div>
  );
}
