/*
 * DashboardPage — mounts the application shell (DashboardLayout). The shell is
 * role-adaptive and renders the active module placeholder. Real dashboards and
 * module pages are added as their modules are built.
 */
import { DashboardLayout } from '@app/layout/DashboardLayout';

export function DashboardPage() {
  return <DashboardLayout schoolName="Asylinx School" />;
}
