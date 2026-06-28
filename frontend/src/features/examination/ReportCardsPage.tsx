/* Report Cards — assembled report-card DATA (configurable template, no visual
 * designer). Only the student's ASSIGNED subjects are ever shown. */
import { useEffect, useState } from 'react';
import { AXBadge, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import type { FieldOption } from '@features/academic/EntityManager';
import { examApi, type ExamResult, type ExamSession } from './api';

interface CardSubject {
  subject: string | null;
  code: string | null;
  is_elective: boolean;
  max_marks: number;
  obtained: number;
  is_absent: boolean;
  passed: boolean;
  grade: string | null;
}

export function ReportCardsPage() {
  const [sessions, setSessions] = useState<FieldOption[]>([]);
  const [sessionId, setSessionId] = useState('');
  const [students, setStudents] = useState<ExamResult[]>([]);
  const [studentId, setStudentId] = useState('');
  const [card, setCard] = useState<Record<string, unknown> | null>(null);

  useEffect(() => {
    examApi.sessions
      .list({ per_page: 100 })
      .then((r) =>
        setSessions(r.data.map((s: ExamSession) => ({ value: String(s.id), label: s.name })))
      );
  }, []);

  useEffect(() => {
    setStudentId('');
    setCard(null);
    if (!sessionId) return;
    examApi
      .results({ filter: { exam_session_id: sessionId }, per_page: 300 })
      .then((r) => setStudents(r.data));
  }, [sessionId]);

  useEffect(() => {
    if (sessionId && studentId)
      examApi.reportCard(Number(sessionId), Number(studentId)).then(setCard);
    else setCard(null);
  }, [sessionId, studentId]);

  const subjects = (card?.subjects ?? []) as CardSubject[];
  const summary = (card?.summary ?? {}) as Record<string, unknown>;
  const student = (card?.student ?? {}) as Record<string, unknown>;
  const identity = card?.identity as Record<string, unknown> | null;
  const att = (card?.attendance_summary ?? {}) as Record<string, unknown>;

  const columns: AXColumn<CardSubject>[] = [
    {
      key: 'subject',
      header: 'Subject',
      render: (r) => (
        <span className="font-medium">
          {r.subject}
          {r.is_elective ? ' (elective)' : ''}
        </span>
      ),
    },
    {
      key: 'marks',
      header: 'Marks',
      render: (r) => (r.is_absent ? 'AB' : `${r.obtained} / ${r.max_marks}`),
    },
    { key: 'grade', header: 'Grade', render: (r) => r.grade ?? '—' },
    {
      key: 'res',
      header: '',
      render: (r) => (
        <AXBadge tone={r.passed ? 'green' : 'red'}>{r.passed ? 'Pass' : 'Fail'}</AXBadge>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-id-card text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Report Cards</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-56">
          <AXSelect
            label="Exam session"
            value={sessionId}
            onChange={(e) => setSessionId(e.target.value)}
            options={[{ value: '', label: 'Select…' }, ...sessions]}
          />
        </div>
        <div className="w-64">
          <AXSelect
            label="Student"
            value={studentId}
            onChange={(e) => setStudentId(e.target.value)}
            options={[
              { value: '', label: 'Select…' },
              ...students.map((s) => ({
                value: String(s.student_id),
                label: `${s.admission_number} — ${s.student}`,
              })),
            ]}
          />
        </div>
      </div>

      {card && (
        <div className="erp-card space-y-4">
          <div className="flex items-start justify-between">
            <div>
              <div className="text-lg font-semibold text-[var(--navy-primary)]">
                {String(student.name ?? '')}
              </div>
              <div className="text-sm text-gray-500">
                Adm. {String(student.admission_number ?? '')}
              </div>
            </div>
            {identity?.qr_url ? (
              <div className="text-right text-xs text-gray-400">
                <i className="fas fa-qrcode text-3xl text-[var(--navy-primary)]" />
                <div>{String(identity.identity_number ?? '')}</div>
              </div>
            ) : null}
          </div>

          <AXTable
            columns={columns}
            rows={subjects}
            rowKey={(r) => r.code ?? r.subject ?? ''}
            empty="No assigned subjects."
          />

          <div className="flex flex-wrap gap-3 text-sm">
            <AXBadge tone="navy">
              Total: {String(summary.total_obtained ?? 0)} / {String(summary.total_max ?? 0)}
            </AXBadge>
            <AXBadge tone="navy">Percentage: {String(summary.percentage ?? 0)}%</AXBadge>
            <AXBadge tone="navy">Grade: {String(summary.grade ?? '—')}</AXBadge>
            <AXBadge tone={summary.result_status === 'pass' ? 'green' : 'red'}>
              {String(summary.result_status ?? 'pending')}
            </AXBadge>
            {summary.rank != null && <AXBadge tone="amber">Rank: {String(summary.rank)}</AXBadge>}
            <AXBadge tone="gray">
              Exam attendance: {String(att.present ?? 0)}/{String(att.total_exams ?? 0)}
            </AXBadge>
          </div>
        </div>
      )}
    </div>
  );
}
