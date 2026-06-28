/* Student Attendance — read + correct, scoped to student owners. */
import { AttendanceListView } from './AttendanceListView';

export function StudentAttendancePage() {
  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-user-graduate text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Student Attendance</h2>
      </div>
      <AttendanceListView kind="student" />
    </div>
  );
}
