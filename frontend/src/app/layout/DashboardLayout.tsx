/*
 * DashboardLayout — the application shell that preserves the reference layout:
 * navy sidebar + top header + main content + footer, with a mobile bottom nav.
 * Role-adaptive: the signed-in user's role drives the navigation.
 *
 * Foundation stage: renders the shell and a single active "page" placeholder.
 * Module pages mount into the content area as modules are built.
 */
import { useMemo, useState, type ReactNode } from 'react';
import { Sidebar } from './Sidebar';
import { Header } from './Header';
import { Footer } from './Footer';
import { BottomNavigation } from './BottomNavigation';
import { useAuth } from '@core/auth/AuthContext';
import { landingMenu, roleFromSlug } from '@app/navigation/menu';
import { SchoolSettingsPage } from '@features/administration/SchoolSettingsPage';
import { MasterDataPage } from '@features/administration/MasterDataPage';
import { FeatureFlagsPage } from '@features/administration/FeatureFlagsPage';
import { NumberGeneratorPage } from '@features/administration/NumberGeneratorPage';
import { GatewaysPage } from '@features/administration/GatewaysPage';
import { AcademicDashboardPage } from '@features/academic/AcademicDashboardPage';
import { AcademicYearsPage } from '@features/academic/AcademicYearsPage';
import { TermsPage } from '@features/academic/TermsPage';
import { AcademicCalendarPage } from '@features/academic/AcademicCalendarPage';
import { ClassesPage } from '@features/academic/ClassesPage';
import { SectionsPage } from '@features/academic/SectionsPage';
import { RoomsPage } from '@features/academic/RoomsPage';
import { SubjectsPage } from '@features/academic/SubjectsPage';
import { SubjectGroupsPage } from '@features/academic/SubjectGroupsPage';
import { TeacherAssignmentsPage } from '@features/academic/TeacherAssignmentsPage';
import { ClassTeachersPage } from '@features/academic/ClassTeachersPage';
import { AdmissionDashboardPage } from '@features/admissions/AdmissionDashboardPage';
import { EnquiriesPage } from '@features/admissions/EnquiriesPage';
import { ApplicationsPage } from '@features/admissions/ApplicationsPage';
import { VerificationPage } from '@features/admissions/VerificationPage';
import { ApprovalPage } from '@features/admissions/ApprovalPage';
import { EnrollmentPage } from '@features/admissions/EnrollmentPage';
import { ImportPage } from '@features/admissions/ImportPage';
import { StudentDashboardPage } from '@features/students/StudentDashboardPage';
import { StudentListPage } from '@features/students/StudentListPage';
import { StudentProfilePage } from '@features/students/StudentProfilePage';
import { StudentTimelinePage } from '@features/students/StudentTimelinePage';
import { MedicalPage } from '@features/students/MedicalPage';
import { DocumentsPage as StudentDocumentsPage } from '@features/students/DocumentsPage';
import { AcademicRecordsPage } from '@features/students/AcademicRecordsPage';
import { TransfersPage } from '@features/students/TransfersPage';
import { WithdrawalsPage } from '@features/students/WithdrawalsPage';
import { PromotionsPage } from '@features/students/PromotionsPage';
import { ImportPage as StudentImportPage } from '@features/students/ImportPage';
import { ExportPage as StudentExportPage } from '@features/students/ExportPage';
import { StaffDashboardPage } from '@features/staff/StaffDashboardPage';
import { StaffListPage } from '@features/staff/StaffListPage';
import { StaffProfilePage } from '@features/staff/StaffProfilePage';
import { QualificationsPage } from '@features/staff/QualificationsPage';
import { ExperiencePage } from '@features/staff/ExperiencePage';
import { DocumentsPage as StaffDocumentsPage } from '@features/staff/DocumentsPage';
import { TimelinePage as StaffTimelinePage } from '@features/staff/TimelinePage';
import { ImportPage as StaffImportPage } from '@features/staff/ImportPage';
import { ExportPage as StaffExportPage } from '@features/staff/ExportPage';
import { IdentityDashboardPage } from '@features/identity/IdentityDashboardPage';
import { IdentityListPage } from '@features/identity/IdentityListPage';
import { IdentityDetailsPage } from '@features/identity/IdentityDetailsPage';
import { AttendanceDashboardPage } from '@features/attendance/AttendanceDashboardPage';
import { StudentAttendancePage } from '@features/attendance/StudentAttendancePage';
import { StaffAttendancePage } from '@features/attendance/StaffAttendancePage';
import { ManualAttendancePage } from '@features/attendance/ManualAttendancePage';
import { AttendanceImportPage } from '@features/attendance/AttendanceImportPage';
import { DeviceManagementPage } from '@features/attendance/DeviceManagementPage';
import { BiometricLogsPage } from '@features/attendance/BiometricLogsPage';

interface DashboardLayoutProps {
  children?: (activeMenu: string) => ReactNode;
  schoolName?: string;
}

export function DashboardLayout({ children, schoolName }: DashboardLayoutProps) {
  const { user } = useAuth();
  // The signed-in user's primary role drives the dynamic, role-adaptive sidebar.
  const role = roleFromSlug(user?.roles?.[0]);

  const [collapsed, setCollapsed] = useState(false);
  const [mobileSidebar, setMobileSidebar] = useState(false);
  const initial = useMemo(() => landingMenu(role), [role]);
  const [activeMenu, setActiveMenu] = useState(initial);

  const select = (id: string) => {
    setActiveMenu(id);
    setMobileSidebar(false);
  };

  return (
    <div className="flex h-screen overflow-hidden bg-[#f5f5f5]">
      {/* Desktop sidebar */}
      <div className="hidden md:block">
        <Sidebar
          role={role}
          activeMenu={activeMenu}
          onSelect={select}
          collapsed={collapsed}
          schoolName={schoolName}
        />
      </div>

      {/* Mobile sidebar drawer */}
      {mobileSidebar && (
        <div className="fixed inset-0 z-40 md:hidden">
          <div className="absolute inset-0 bg-black/40" onClick={() => setMobileSidebar(false)} />
          <div className="absolute inset-y-0 left-0">
            <Sidebar
              role={role}
              activeMenu={activeMenu}
              onSelect={select}
              collapsed={false}
              schoolName={schoolName}
            />
          </div>
        </div>
      )}

      {/* Main column */}
      <div className="flex min-w-0 flex-1 flex-col">
        <Header
          activeMenu={activeMenu}
          schoolName={schoolName}
          onToggleSidebar={() =>
            window.matchMedia('(min-width: 768px)').matches
              ? setCollapsed((c) => !c)
              : setMobileSidebar(true)
          }
        />
        <main className="flex-1 overflow-y-auto p-4 pb-24 md:pb-4">
          {children ? children(activeMenu) : renderContent(activeMenu)}
        </main>
        <Footer />
      </div>

      <BottomNavigation
        role={role}
        activeMenu={activeMenu}
        onSelect={select}
        onOpenMenu={() => setMobileSidebar(true)}
      />
    </div>
  );
}

/** Route the active menu id to its Administration page (others: placeholder). */
function renderContent(activeMenu: string) {
  switch (activeMenu) {
    case 'schoolSettings':
      return <SchoolSettingsPage />;
    case 'masterData':
      return <MasterDataPage />;
    case 'numberGenerator':
      return <NumberGeneratorPage />;
    case 'featureFlags':
      return <FeatureFlagsPage />;
    case 'gateways':
      return <GatewaysPage />;
    // Academic module
    case 'academicDashboard':
      return <AcademicDashboardPage />;
    case 'academicYears':
      return <AcademicYearsPage />;
    case 'terms':
      return <TermsPage />;
    case 'academicCalendar':
      return <AcademicCalendarPage />;
    case 'classes':
      return <ClassesPage />;
    case 'sections':
      return <SectionsPage />;
    case 'rooms':
      return <RoomsPage />;
    case 'subjects':
      return <SubjectsPage />;
    case 'subjectGroups':
      return <SubjectGroupsPage />;
    case 'assignments':
      return <TeacherAssignmentsPage />;
    case 'classTeachers':
      return <ClassTeachersPage />;
    // Admissions module
    case 'admissionDashboard':
      return <AdmissionDashboardPage />;
    case 'admissionEnquiries':
      return <EnquiriesPage />;
    case 'admissionApplications':
      return <ApplicationsPage />;
    case 'admissionVerification':
      return <VerificationPage />;
    case 'admissionApproval':
      return <ApprovalPage />;
    case 'admissionEnrollment':
      return <EnrollmentPage />;
    case 'admissionImport':
      return <ImportPage />;
    // Students module
    case 'studentDashboard':
      return <StudentDashboardPage />;
    case 'students':
      return <StudentListPage />;
    case 'studentProfile':
      return <StudentProfilePage />;
    case 'studentTimeline':
      return <StudentTimelinePage />;
    case 'studentMedical':
      return <MedicalPage />;
    case 'studentDocuments':
      return <StudentDocumentsPage />;
    case 'studentAcademicRecords':
      return <AcademicRecordsPage />;
    case 'studentTransfers':
      return <TransfersPage />;
    case 'studentWithdrawals':
      return <WithdrawalsPage />;
    case 'studentPromotions':
      return <PromotionsPage />;
    case 'studentImport':
      return <StudentImportPage />;
    case 'studentExport':
      return <StudentExportPage />;
    // Staff module
    case 'staffDashboard':
      return <StaffDashboardPage />;
    case 'staff':
      return <StaffListPage />;
    case 'staffProfile':
      return <StaffProfilePage />;
    case 'staffQualifications':
      return <QualificationsPage />;
    case 'staffExperience':
      return <ExperiencePage />;
    case 'staffDocuments':
      return <StaffDocumentsPage />;
    case 'staffTimeline':
      return <StaffTimelinePage />;
    case 'staffImport':
      return <StaffImportPage />;
    case 'staffExport':
      return <StaffExportPage />;
    // Platform Identity Service
    case 'identityDashboard':
      return <IdentityDashboardPage />;
    case 'identityList':
      return <IdentityListPage />;
    case 'identityDetails':
      return <IdentityDetailsPage />;

    case 'attendanceDashboard':
      return <AttendanceDashboardPage />;
    case 'studentAttendance':
      return <StudentAttendancePage />;
    case 'staffAttendance':
      return <StaffAttendancePage />;
    case 'manualAttendance':
      return <ManualAttendancePage />;
    case 'attendanceImport':
      return <AttendanceImportPage />;
    case 'attendanceDevices':
      return <DeviceManagementPage />;
    case 'biometricLogs':
      return <BiometricLogsPage />;
    default:
      return <DefaultContent activeMenu={activeMenu} />;
  }
}

/** Foundation placeholder shown until the selected module is built. */
function DefaultContent({ activeMenu }: { activeMenu: string }) {
  return (
    <div className="erp-card">
      <div className="flex items-center gap-3 text-[var(--navy-primary)]">
        <i className="fas fa-cube text-lg" />
        <h2 className="text-lg font-semibold capitalize">{activeMenu}</h2>
      </div>
      <p className="mt-2 text-sm text-gray-500">
        Engineering foundation — module pages mount here as they are built. The layout, navigation,
        theme, and shell are production-ready.
      </p>
    </div>
  );
}
