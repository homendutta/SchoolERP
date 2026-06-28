/* Staff Attendance — read + correct, scoped to staff owners. */
import { AttendanceListView } from './AttendanceListView';

export function StaffAttendancePage() {
  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-id-card-clip text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Staff Attendance</h2>
      </div>
      <AttendanceListView kind="staff" />
    </div>
  );
}
