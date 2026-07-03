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
import { CommunicationDashboardPage } from '@features/communication/CommunicationDashboardPage';
import { TemplatesPage as CommTemplatesPage } from '@features/communication/TemplatesPage';
import { MessageQueuePage } from '@features/communication/MessageQueuePage';
import { ScheduledMessagesPage } from '@features/communication/ScheduledMessagesPage';
import { AnnouncementsPage } from '@features/communication/AnnouncementsPage';
import { CircularsPage } from '@features/communication/CircularsPage';
import { UserPreferencesPage } from '@features/communication/UserPreferencesPage';
import { DeliveryTrackingPage } from '@features/communication/DeliveryTrackingPage';
import { LibraryDashboardPage } from '@features/library/LibraryDashboardPage';
import { CatalogPage } from '@features/library/CatalogPage';
import { AuthorsPage } from '@features/library/AuthorsPage';
import { PublishersPage } from '@features/library/PublishersPage';
import { CategoriesPage as LibCategoriesPage } from '@features/library/CategoriesPage';
import { LocationsPage } from '@features/library/LocationsPage';
import { CopiesPage } from '@features/library/CopiesPage';
import { BorrowingPage } from '@features/library/BorrowingPage';
import { ReturnsPage } from '@features/library/ReturnsPage';
import { RenewalsPage } from '@features/library/RenewalsPage';
import { ReservationsPage } from '@features/library/ReservationsPage';
import { InventoryPage } from '@features/library/InventoryPage';
import { TransportDashboardPage } from '@features/transport/TransportDashboardPage';
import { VehiclesPage } from '@features/transport/VehiclesPage';
import { RoutesPage as TransportRoutesPage } from '@features/transport/RoutesPage';
import { StopsPage } from '@features/transport/StopsPage';
import { TripsPage } from '@features/transport/TripsPage';
import { StudentAssignmentPage as TransportStudentAssignmentPage } from '@features/transport/StudentAssignmentPage';
import { DriverAssignmentPage } from '@features/transport/DriverAssignmentPage';
import { VehicleDocumentsPage } from '@features/transport/VehicleDocumentsPage';
import { MaintenanceSchedulePage } from '@features/transport/MaintenanceSchedulePage';
import { HostelDashboardPage } from '@features/hostel/HostelDashboardPage';
import { HostelsPage } from '@features/hostel/HostelsPage';
import { BuildingsPage } from '@features/hostel/BuildingsPage';
import { FloorsPage } from '@features/hostel/FloorsPage';
import { RoomsPage as HostelRoomsPage } from '@features/hostel/RoomsPage';
import { BedsPage } from '@features/hostel/BedsPage';
import { StudentAllocationPage as HostelAllocationPage } from '@features/hostel/StudentAllocationPage';
import { RoomTransfersPage } from '@features/hostel/RoomTransfersPage';
import { WardensPage } from '@features/hostel/WardensPage';
import { VisitorsPage } from '@features/hostel/VisitorsPage';
import { MaintenancePage as HostelMaintenancePage } from '@features/hostel/MaintenancePage';
import { HostelFeesPage } from '@features/hostel/HostelFeesPage';
import { InventoryDashboardPage } from '@features/inventory/InventoryDashboardPage';
import { CategoriesPage as InvCategoriesPage } from '@features/inventory/CategoriesPage';
import { ModelsPage } from '@features/inventory/ModelsPage';
import { VendorsPage } from '@features/inventory/VendorsPage';
import { AssetsPage } from '@features/inventory/AssetsPage';
import { ConsumablesPage } from '@features/inventory/ConsumablesPage';
import { StockMovementsPage } from '@features/inventory/StockMovementsPage';
import { AssetAssignmentsPage } from '@features/inventory/AssetAssignmentsPage';
import { AssetTransfersPage } from '@features/inventory/AssetTransfersPage';
import { MaintenancePage as InvMaintenancePage } from '@features/inventory/MaintenancePage';
import { WarrantyPage } from '@features/inventory/WarrantyPage';
import { VerificationPage as InvVerificationPage } from '@features/inventory/VerificationPage';
import { DisposalPage } from '@features/inventory/DisposalPage';
import { HrDashboardPage } from '@features/hr/HrDashboardPage';
import { DepartmentsPage as HrDepartmentsPage } from '@features/hr/DepartmentsPage';
import { DesignationsPage as HrDesignationsPage } from '@features/hr/DesignationsPage';
import { EmploymentPage } from '@features/hr/EmploymentPage';
import { ShiftsPage } from '@features/hr/ShiftsPage';
import { AttendancePoliciesPage } from '@features/hr/AttendancePoliciesPage';
import { LeaveTypesPage } from '@features/hr/LeaveTypesPage';
import { LeavePoliciesPage } from '@features/hr/LeavePoliciesPage';
import { LeaveRequestsPage } from '@features/hr/LeaveRequestsPage';
import { HolidaysPage } from '@features/hr/HolidaysPage';
import { PerformancePage } from '@features/hr/PerformancePage';
import { TrainingPage as HrTrainingPage } from '@features/hr/TrainingPage';
import { DisciplinePage } from '@features/hr/DisciplinePage';
import { SeparationPage } from '@features/hr/SeparationPage';
import { PayrollDashboardPage } from '@features/payroll/PayrollDashboardPage';
import { ComponentsPage as PayrollComponentsPage } from '@features/payroll/ComponentsPage';
import { StructuresPage as PayrollStructuresPage } from '@features/payroll/StructuresPage';
import { EmployeeSalaryPage } from '@features/payroll/EmployeeSalaryPage';
import { SalaryRevisionsPage } from '@features/payroll/SalaryRevisionsPage';
import { OvertimePage } from '@features/payroll/OvertimePage';
import { LoansPage } from '@features/payroll/LoansPage';
import { ArrearsPage } from '@features/payroll/ArrearsPage';
import { StatutoryPage } from '@features/payroll/StatutoryPage';
import { PayrollRunsPage } from '@features/payroll/PayrollRunsPage';
import { PayslipsPage } from '@features/payroll/PayslipsPage';
import { FinanceDashboardPage } from '@features/finance/FinanceDashboardPage';
import { FeeCategoriesPage } from '@features/finance/FeeCategoriesPage';
import { FeeMastersPage } from '@features/finance/FeeMastersPage';
import { FeeStructuresPage } from '@features/finance/FeeStructuresPage';
import { StudentFeesPage } from '@features/finance/StudentFeesPage';
import { InstallmentsPage } from '@features/finance/InstallmentsPage';
import { DiscountsPage } from '@features/finance/DiscountsPage';
import { ScholarshipsPage } from '@features/finance/ScholarshipsPage';
import { FineRulesPage } from '@features/finance/FineRulesPage';
import { PaymentsPage } from '@features/finance/PaymentsPage';
import { RefundsPage } from '@features/finance/RefundsPage';
import { AdjustmentsPage } from '@features/finance/AdjustmentsPage';
import { LedgerPage } from '@features/finance/LedgerPage';
import { DefaultersPage } from '@features/finance/DefaultersPage';
import { ExamDashboardPage } from '@features/examination/ExamDashboardPage';
import { ExamTypesPage } from '@features/examination/ExamTypesPage';
import { ExamSessionsPage } from '@features/examination/ExamSessionsPage';
import { ExamSchedulePage } from '@features/examination/ExamSchedulePage';
import { SeatingPlanPage } from '@features/examination/SeatingPlanPage';
import { ExamAttendancePage } from '@features/examination/ExamAttendancePage';
import { MarksEntryPage } from '@features/examination/MarksEntryPage';
import { GradeManagementPage } from '@features/examination/GradeManagementPage';
import { ResultsPage } from '@features/examination/ResultsPage';
import { ReportCardsPage } from '@features/examination/ReportCardsPage';
import { TabulationPage } from '@features/examination/TabulationPage';
import { TimetableDashboardPage } from '@features/timetable/TimetableDashboardPage';
import { ClassTimetablePage } from '@features/timetable/ClassTimetablePage';
import { TeacherTimetablePage } from '@features/timetable/TeacherTimetablePage';
import { RoomTimetablePage } from '@features/timetable/RoomTimetablePage';
import { PeriodManagementPage } from '@features/timetable/PeriodManagementPage';
import { WorkingDaysPage } from '@features/timetable/WorkingDaysPage';
import { TemplatesPage as TimetableTemplatesPage } from '@features/timetable/TemplatesPage';
import { SubstitutionsPage } from '@features/timetable/SubstitutionsPage';
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

    case 'commDashboard':
      return <CommunicationDashboardPage />;
    case 'commTemplates':
      return <CommTemplatesPage />;
    case 'commQueue':
      return <MessageQueuePage />;
    case 'commScheduled':
      return <ScheduledMessagesPage />;
    case 'commAnnouncements':
      return <AnnouncementsPage />;
    case 'commCirculars':
      return <CircularsPage />;
    case 'commPreferences':
      return <UserPreferencesPage />;
    case 'commTracking':
      return <DeliveryTrackingPage />;

    case 'libDashboard':
      return <LibraryDashboardPage />;
    case 'libCatalog':
      return <CatalogPage />;
    case 'libAuthors':
      return <AuthorsPage />;
    case 'libPublishers':
      return <PublishersPage />;
    case 'libCategories':
      return <LibCategoriesPage />;
    case 'libLocations':
      return <LocationsPage />;
    case 'libCopies':
      return <CopiesPage />;
    case 'libBorrowing':
      return <BorrowingPage />;
    case 'libReturns':
      return <ReturnsPage />;
    case 'libRenewals':
      return <RenewalsPage />;
    case 'libReservations':
      return <ReservationsPage />;
    case 'libInventory':
      return <InventoryPage />;

    case 'trDashboard':
      return <TransportDashboardPage />;
    case 'trVehicles':
      return <VehiclesPage />;
    case 'trRoutes':
      return <TransportRoutesPage />;
    case 'trStops':
      return <StopsPage />;
    case 'trTrips':
      return <TripsPage />;
    case 'trStudents':
      return <TransportStudentAssignmentPage />;
    case 'trDrivers':
      return <DriverAssignmentPage />;
    case 'trDocuments':
      return <VehicleDocumentsPage />;
    case 'trMaintenance':
      return <MaintenanceSchedulePage />;

    case 'hostelDashboard':
      return <HostelDashboardPage />;
    case 'hostels':
      return <HostelsPage />;
    case 'hostelBuildings':
      return <BuildingsPage />;
    case 'hostelFloors':
      return <FloorsPage />;
    case 'hostelRooms':
      return <HostelRoomsPage />;
    case 'hostelBeds':
      return <BedsPage />;
    case 'hostelAllocation':
      return <HostelAllocationPage />;
    case 'hostelTransfers':
      return <RoomTransfersPage />;
    case 'hostelWardens':
      return <WardensPage />;
    case 'hostelVisitors':
      return <VisitorsPage />;
    case 'hostelMaintenance':
      return <HostelMaintenancePage />;
    case 'hostelFees':
      return <HostelFeesPage />;

    case 'invDashboard':
      return <InventoryDashboardPage />;
    case 'invCategories':
      return <InvCategoriesPage />;
    case 'invModels':
      return <ModelsPage />;
    case 'invVendors':
      return <VendorsPage />;
    case 'invAssets':
      return <AssetsPage />;
    case 'invConsumables':
      return <ConsumablesPage />;
    case 'invMovements':
      return <StockMovementsPage />;
    case 'invAssignments':
      return <AssetAssignmentsPage />;
    case 'invTransfers':
      return <AssetTransfersPage />;
    case 'invMaintenance':
      return <InvMaintenancePage />;
    case 'invWarranty':
      return <WarrantyPage />;
    case 'invVerification':
      return <InvVerificationPage />;
    case 'invDisposal':
      return <DisposalPage />;

    case 'hrDashboard':
      return <HrDashboardPage />;
    case 'hrDepartments':
      return <HrDepartmentsPage />;
    case 'hrDesignations':
      return <HrDesignationsPage />;
    case 'hrEmployment':
      return <EmploymentPage />;
    case 'hrShifts':
      return <ShiftsPage />;
    case 'hrAttendancePolicies':
      return <AttendancePoliciesPage />;
    case 'hrLeaveTypes':
      return <LeaveTypesPage />;
    case 'hrLeavePolicies':
      return <LeavePoliciesPage />;
    case 'hrLeaveRequests':
      return <LeaveRequestsPage />;
    case 'hrHolidays':
      return <HolidaysPage />;
    case 'hrPerformance':
      return <PerformancePage />;
    case 'hrTraining':
      return <HrTrainingPage />;
    case 'hrDiscipline':
      return <DisciplinePage />;
    case 'hrSeparation':
      return <SeparationPage />;

    case 'payrollDashboard':
      return <PayrollDashboardPage />;
    case 'payrollComponents':
      return <PayrollComponentsPage />;
    case 'payrollStructures':
      return <PayrollStructuresPage />;
    case 'payrollEmployeeSalary':
      return <EmployeeSalaryPage />;
    case 'payrollRevisions':
      return <SalaryRevisionsPage />;
    case 'payrollOvertime':
      return <OvertimePage />;
    case 'payrollLoans':
      return <LoansPage />;
    case 'payrollArrears':
      return <ArrearsPage />;
    case 'payrollStatutory':
      return <StatutoryPage />;
    case 'payrollRuns':
      return <PayrollRunsPage />;
    case 'payrollPayslips':
      return <PayslipsPage />;

    case 'finDashboard':
      return <FinanceDashboardPage />;
    case 'feeCategories':
      return <FeeCategoriesPage />;
    case 'feeMasters':
      return <FeeMastersPage />;
    case 'feeStructures':
      return <FeeStructuresPage />;
    case 'studentFees':
      return <StudentFeesPage />;
    case 'finInstallments':
      return <InstallmentsPage />;
    case 'finDiscounts':
      return <DiscountsPage />;
    case 'finScholarships':
      return <ScholarshipsPage />;
    case 'finFineRules':
      return <FineRulesPage />;
    case 'finPayments':
      return <PaymentsPage />;
    case 'finRefunds':
      return <RefundsPage />;
    case 'finAdjustments':
      return <AdjustmentsPage />;
    case 'finLedger':
      return <LedgerPage />;
    case 'finDefaulters':
      return <DefaultersPage />;

    case 'examDashboard':
      return <ExamDashboardPage />;
    case 'examTypes':
      return <ExamTypesPage />;
    case 'examSessions':
      return <ExamSessionsPage />;
    case 'examSchedule':
      return <ExamSchedulePage />;
    case 'examSeating':
      return <SeatingPlanPage />;
    case 'examAttendance':
      return <ExamAttendancePage />;
    case 'marksEntry':
      return <MarksEntryPage />;
    case 'gradeManagement':
      return <GradeManagementPage />;
    case 'examResults':
      return <ResultsPage />;
    case 'reportCards':
      return <ReportCardsPage />;
    case 'tabulation':
      return <TabulationPage />;

    case 'timetableDashboard':
      return <TimetableDashboardPage />;
    case 'classTimetable':
      return <ClassTimetablePage />;
    case 'teacherTimetable':
      return <TeacherTimetablePage />;
    case 'roomTimetable':
      return <RoomTimetablePage />;
    case 'timetablePeriods':
      return <PeriodManagementPage />;
    case 'workingDays':
      return <WorkingDaysPage />;
    case 'timetableTemplates':
      return <TimetableTemplatesPage />;
    case 'substitutions':
      return <SubstitutionsPage />;

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
