# 00 – Project Index: Existing School Management System

> **Scope:** Reverse-engineering map of the existing **Google Apps Script** School Management
> System. This document is a navigation index only — no redesign, no refactor, no suggestions.
> Source files analysed: `reference/original/Code.gs`, `reference/original/index.html`,
> `reference/original/database.xlsx`.

---

## 0. Technology Stack (as found)

| Layer | Technology |
|-------|-----------|
| Backend runtime | Google Apps Script (server-side `.gs`) |
| Database | Google Sheets (`SpreadsheetApp`), 36 worksheets |
| Web entry | `doGet(e)` → serves `index.html` via `HtmlService` |
| Client↔Server transport | `google.script.run` (175 call sites) |
| UI framework | React 18 (UMD, production build) + Babel Standalone (in-browser JSX) |
| Single-page shell | One `index.html` containing all CSS + JS + JSX |
| Charts | Chart.js 4.4.0 (14 chart instances via `useDashChart`) |
| Data tables | jQuery 3.7.1 + DataTables 1.13.7 (+ Buttons, Responsive) — 35 inits |
| Export / PDF | JSZip 3.10.1, pdfmake 0.2.7 (hall tickets, fee receipts, table export) |
| Dialogs / alerts | SweetAlert2 v11 (439 `Swal.fire` calls) |
| Icons | Font Awesome 6.5.1 |
| Client data cache | Custom SWR-like layer (`useSWR`, `swrFetcher`, `swrMutate`, `swrRevalidate`) |
| Session | `localStorage` (`userSession`, 60-minute TTL) |

---

## 1. Project Statistics

| Metric | Count |
|--------|-------|
| Total lines in `Code.gs` | **26,678** |
| Total lines in `index.html` | **52,011** |
| Combined source lines | **78,689** |
| Apps Script function declarations | **412** (≈46 nested inline helpers included) |
| Apps Script logical sections | **63** (`// ===` banner markers) |
| React components (`function PascalCase`) | **109** |
| → Page/feature Views (`*View`) | **35** |
| → Modals / dialogs (`*Modal`) | **51** |
| → Role dashboards (`*Dashboard`) | **6** + 1 router (`DashboardView`) |
| → Shared/layout/util components | **16** |
| Google Sheet worksheets referenced | **36** |
| Apps Script helper functions | **≈120** (45 `can*`, 17 `next*`, 27 `rowTo*`, 13 `validate*`, + misc) |
| Read endpoints (`get*`) | **101** |
| Write endpoints (`add*` / `update*` / `delete*`) | **87** |
| Dialogs/modals | 51 React modals + 439 `Swal.fire` alert dialogs |
| HTML `<form>` elements | **32** |
| HTML `<table>` elements | **53** (35 DataTables-enhanced) |
| Charts (`useDashChart`) | **14** |
| Reports | **10** core report types (+ drill-down report endpoints) |
| `google.script.run` call sites | **175** |
| Event handlers | 428 `onClick`, 585 `onChange`, 32 `onSubmit` |

---

## 2. Code.gs Index

`Code.gs` is organised into banner-delimited sections (`// ===== Name =====`).
Listed below in file order.

### Configuration
**Lines:** 7–1066
**Functions:** _(declarations only — sheet-name constants + column-header arrays)_
**Purpose:** 36 sheet-name constants, `*_HEADERS` column definitions for every worksheet, default logo.

### Web App Entry
**Lines:** 1067–1079
**Functions:** `doGet()`
**Purpose:** Serves `index.html` via `HtmlService`; app bootstrap.

### Helpers
**Lines:** 1080–1998
**Functions (selected of ~120):** `getSheet`, `nowIso`, `todayStr`, `isAdmin`, 45× `can*` permission gates, 17× `next*` ID generators, `getUsersMap`, `getClassesMap`, `getSubjectsMap`, `computeGrade`, `resolvePolymorphicName`, `generatePrefixedCode`, `recomputeClassStrength`, `rowToUser`, `toIso`.
**Purpose:** Shared utilities — sheet access, ID sequencing, role-based read/write permission checks, lookup maps, formatting, polymorphic name resolution.

### Init Sheets
**Lines:** 1999–2295
**Functions:** `initializeSheets()`
**Purpose:** Creates all worksheets with headers on first run.

### Auth
**Lines:** 2296–2659
**Functions:** `resetAdminPassword`, `authenticateUser`, `tryStaffAuth`, `tryStudentAuth`, `tryParentAuth`, `_studentSelfInfo`, `_parentSelfInfo`, `_syncStudentPassword`, `_syncParentPassword`.
**Purpose:** Multi-identity login (staff / student / parent), self-service password sync.

### Users CRUD (admin only)
**Lines:** 2660–2857
**Functions:** `getAllUsers`, `addUser`, `updateUser`, `deleteUser`.
**Purpose:** Staff/user account management.

### My Account (self)
**Lines:** 2858–2928
**Functions:** `getMyAccount`, `updateMyAccount`.
**Purpose:** Logged-in user self-profile read/update.

### Dashboard Stats (admin only)
**Lines:** 2929–3216
**Functions:** `getDashboardStats`.
**Purpose:** Aggregate KPI counts for admin dashboard.

### Classes CRUD
**Lines:** 3217–4091
**Functions:** `getAllClasses`, `getActiveTeachers`, `emailClassesReport`, `validAcademicYear`, `formatPeriodLabel`/`formatAcademicYear`/`formatTimeHHMM`, date-cell helpers (`writeTextCell`, `pinColumnAsText`, `pinAllDateColumns`, `repairAllDatesToISO`), `validateClassFields`, `classExists`, `addClass`, `updateClass`, `deleteClass`.
**Purpose:** Class & section management + shared date-formatting/repair utilities.

### Subjects CRUD
**Lines:** 4092–4522
**Functions:** `getAllSubjects`, `subjectExists`, `validateSubjectMarksFields`, `addSubject`, `updateSubject`, `deleteSubject`, `getSubjectsForClass`.
**Purpose:** Subject definitions per class (theory/practical marks split).

### Teacher Assignments CRUD
**Lines:** 4523–4892
**Functions:** `getAllAssignments`, `assignmentExists`, `getAllTeachersWithAssignments`, `addAssignmentsBulk`, `deleteAssignment`.
**Purpose:** Teacher↔class↔subject mapping (incl. class-teacher flag).

### Students CRUD
**Lines:** 4893–5272
**Functions:** `rowToStudent`, `toBasicStudent`, `getAllStudents`, `admissionNumberExists`, `aadhaarExists`, `studentRollExists`, `validateStudentIntlFields`, `addStudent`, `updateStudent`, `deleteStudent`.
**Purpose:** Student master record (63 columns incl. international/safeguarding fields).

### Auto-mirror to Users sheet
**Lines:** 5273–5949
**Functions:** `_mirrorStudentToUsers`, `_mirrorParentToUsers`, `_unmirrorByEmployeeCode`, `backfillUserMirrors`.
**Purpose:** Mirrors students/parents into Users sheet for unified login.

### Admissions
**Lines:** 5950–6758
**Functions:** `isAdminOrClerk`, `canViewReports`, `generateRegistrationNumber`, `nextAdmissionId`, `rowToAdmission`, `getAllAdmissions`, `_validateRegistrationFields`, `addRegistration`, `updateRegistration`, `confirmAdmission`, `enrollAdmission`, `rejectAdmission`, `_closeAdmission`, `deleteAdmission`.
**Purpose:** Admission pipeline: register → confirm → enroll (+ reject/cancel).

### Daily Accounts
**Lines:** 6759–7095
**Functions:** `nextAccountTxnId`, `rowToTransaction`, `getAllTransactions`, `_validateTransactionFields`, `addTransaction`, `updateTransaction`, `deleteTransaction`, `getAccountTodaySummary`.
**Purpose:** Day-book of non-fee income & expenses.

### Reports
**Lines:** 7096–8224
**Functions:** range helpers (`_dOnly`, `_reportRange`, `_inRange`), `getFeeCollectionReport`, `getOutstandingDuesReport`, `getCashBookReport`, `getIncomeExpenseReport`, `getExamsForReport`, `getExamResultReport`, `getAttendanceSummaryReport`, `getStudentRosterReport`, `getStaffListReport`, `getAdmissionsReport`, `getActivityLogReport`.
**Purpose:** 10 cross-module reports (finance, academic, roster, admissions, audit).

### Parents CRUD
**Lines:** 8225–8705
**Functions:** `rowToParent`, `getAllParents`, `parentMobileExists`, `parentEmailExists`, `validateParentFields`, `addParent`, `updateParent`, `deleteParent`.
**Purpose:** Parent/guardian master record.

### Parent ↔ Student Junction
**Lines:** 8706–8990
**Functions:** `getParentStudentLinks`, `getEligibleStudentsForLinking`, `linkParentStudent`, `unlinkParentStudent`, `setPrimaryContact`.
**Purpose:** Many-to-many parent–student linking.

### Exams CRUD
**Lines:** 8991–9507
**Functions:** `rowToExam`, `getAllExams`, `validateExamFields`, `addExam`, `updateExam`, `deleteExam`, `publishExam`, `unpublishExam`.
**Purpose:** Exam definitions + publish/unpublish results gate.

### Marks CRUD (bulk-oriented)
**Lines:** 9508–10020
**Functions:** `getMarksForExamSubject`, `validateMarkFields`, `bulkSaveMarks`, `computeMarkRanks`.
**Purpose:** Subject-wise bulk mark entry, grading, rank computation.

### Attendance CRUD (JSON-blob, daily + subject-wise)
**Lines:** 10021–10739
**Functions:** `parseAttendanceJson`, `serializeAttendanceJson`, mode/status validators, `findAttendanceRowIdx`, `getAttendanceForClassDate`, `bulkSaveAttendance`, `lockAttendance`, `getRecentAttendanceForClass`, `getSubjectsForClassId`, `migrateAttendanceToJson`.
**Purpose:** Per-(class,date,mode,subject,period) attendance as JSON status blob; locking.

### Fee Structure CRUD
**Lines:** 10740–11127
**Functions:** `rowToFeeStructure`, `getAllFeeStructures`, `feeStructureExists`, `addFeeStructure`, `updateFeeStructure`, `deleteFeeStructure`, `getFeeStructuresForClass`.
**Purpose:** Class-level fee category/amount/frequency definitions.

### Fee Payments CRUD
**Lines:** 11128–11779
**Functions:** `rowToPayment`, `getStudentsLite`, `getFeeStructuresLite`, `getAllPayments`, `receiptNumberExists`, `addPayment`, `addPaymentsBulk`, `updatePayment`, `deletePayment`.
**Purpose:** Fee receipts, single + bulk collection.

### Discipline CRUD
**Lines:** 11780–12139
**Functions:** `rowToDiscipline`, `getAllDiscipline`, `addDiscipline`, `updateDiscipline`, `deleteDiscipline`.
**Purpose:** Disciplinary incident log.

### Conduct CRUD
**Lines:** 12140–12507
**Functions:** `normalizeSubGrade`, `rowToConduct`, `getAllConduct`, `conductExists`, `addConduct`, `updateConduct`, `deleteConduct`.
**Purpose:** Periodic conduct/behaviour grading.

### Activities CRUD
**Lines:** 12508–12801
**Functions:** `rowToActivity`, `getAllActivities`, `addActivity`, `updateActivity`, `deleteActivity`.
**Purpose:** Co-curricular activity records.

### Complaints CRUD (hard delete)
**Lines:** 12802–13113
**Functions:** `rowToComplaint`, `getAllComplaints`, `addComplaint`, `updateComplaint`, `deleteComplaint`.
**Purpose:** Complaint tracking (anonymous-capable).

### Notices CRUD
**Lines:** 13114–13469
**Functions:** `rowToNotice`, `getAllNotices`, `addNotice`, `updateNotice`, `deleteNotice`.
**Purpose:** School notices with audience targeting.

### Helpdesk Tickets CRUD (hard delete)
**Lines:** 13470–13759
**Functions:** `rowToTicket`, `helpdeskSlaHours`, `getAllHelpdeskTickets`, `addHelpdeskTicket`, `updateHelpdeskTicket`, `deleteHelpdeskTicket`.
**Purpose:** Support tickets with SLA due-by.

### Lesson Plans CRUD
**Lines:** 13760–14028
**Functions:** `rowToLessonPlan`, `getAllLessonPlans`, `addLessonPlan`, `updateLessonPlan`, `deleteLessonPlan`.
**Purpose:** Teacher lesson planning + HOD review.

### Teaching Logbook CRUD (hard delete)
**Lines:** 14029–14342
**Functions:** `rowToLogbook`, `getAllLogbookEntries`, `logbookExists`, `addLogbookEntry`, `updateLogbookEntry`, `deleteLogbookEntry`.
**Purpose:** Daily class-coverage log + homework.

### Documents CRUD (polymorphic + verification)
**Lines:** 14343–14602
**Functions:** `rowToDocument`, `getAllDocuments`, `addDocument`, `verifyDocument`, `deleteDocument`.
**Purpose:** File metadata attached to any entity, with verification.

### File Upload
**Lines:** 14603–14646
**Functions:** `getAssetsFolder`, `uploadProfileImage`.
**Purpose:** Drive-backed image/file upload.

### User Settings (theme/colors/photo)
**Lines:** 14647–14699
**Functions:** `updateUserSettings`, `getUserSettings`.
**Purpose:** Per-user theme/color/photo persistence.

### Logs
**Lines:** 14700–14710
**Functions:** `addLog`.
**Purpose:** Activity/audit logging.

### School Calendar
**Lines:** 14711–14979
**Functions:** `canReadCalendar`, `canWriteCalendar`, `rowToCalendarEvent`, `validateCalendarPayload`, `getCalendarEvents`, `addCalendarEvent`, `updateCalendarEvent`, `deleteCalendarEvent`.
**Purpose:** Holiday/event calendar (blocks attendance).

### Hall Ticket
**Lines:** 14980–15175
**Functions:** `getHallTicketData`.
**Purpose:** Exam hall-ticket data assembly.

### Setup Entrypoints
**Lines:** 15176–15180
**Functions:** `setup`.
**Purpose:** One-time setup trigger.

### School Settings (system-wide config)
**Lines:** 15181–15350
**Functions:** `getSchoolSettings`, `defaultSchoolSettings`, `updateSchoolSettings`.
**Purpose:** Single-row global config (name, year, currency, working days).

### School Periods CRUD
**Lines:** 15351–15581
**Functions:** `rowToPeriod`, `getAllPeriods`, `periodExists`, `addPeriod`, `updatePeriod`, `deletePeriod`.
**Purpose:** Bell-schedule period definitions per day-type.

### Timetable CRUD + Queries
**Lines:** 15582–20788
**Functions:** `rowToTimetable`, `timetableSlotExists`, `teacherSlotConflict`, `getTimetableForClass`, `getTimetableForTeacher`, `validateTimetableEntry`, `addTimetableEntry`, `updateTimetableEntry`, `deleteTimetableEntry`, `copyTimetable`, `setupDemoData` (+ nested `buildAttRow`), `seedTimetableDemo`.
**Purpose:** Class/teacher timetable + conflict detection; large demo-data seeders.

### PTM (Parent-Teacher Meeting)
**Lines:** 20789–21586
**Functions:** PTM permission gates, `getParentIdFromLogin`, `minutesBetween`, `ptmSlotOverlap`, `rowToPtmSlot`, `rowToPtmBooking`, `getPtmSlotsMap`, `getParentsLiteMap`, `countSlotBookings`, `getPtmSlots`, `getMyPtmSlots`, `validatePtmSlotPayload`, `addPtmSlot`, `updatePtmSlot`, `deletePtmSlot`, `bookPtmSlot`, `cancelPtmBooking`, `completePtmBooking`, `getMyPtmBookings`.
**Purpose:** PTM slot creation, parent booking, completion notes/rating.

### Substitutes
**Lines:** 21587–22146
**Functions:** substitute permission gates, `parseSubAllocations`, `rowToSubstitute`, `substituteExists`, `validateSubAllocations`, `getSubstitutes`, `getMySubstituteAssignments`, `addSubstitute`, `updateSubstitute`, `deleteSubstitute`, `getTeacherTimetableForDate`, `getAvailableTeachersForSlot`.
**Purpose:** Absent-teacher substitution allocation.

### Assets
**Lines:** 22147–22671
**Functions:** asset permission gates, `rowToAsset`, `assetTagExists`, `validateAssetPayload`, `getAllAssets`, `addAsset`, `updateAsset`, `deleteAsset`, `rowToMaintenance`, `validateMaintenancePayload`, `getAssetMaintenanceHistory`, `addMaintenanceRecord`, `updateMaintenanceRecord`, `deleteMaintenanceRecord`.
**Purpose:** Fixed-asset register + maintenance history.

### Stock / Inventory
**Lines:** 22672–23112
**Functions:** stock permission gates, `rowToStockItem`, `rowToStockTxn`, `itemCodeExists`, `validateStockItemPayload`, `getAllStockItems`, `addStockItem`, `updateStockItem`, `deleteStockItem`, `recordStockTransaction`, `getStockTransactionHistory`, `getReorderAlerts`.
**Purpose:** Consumable inventory + in/out transactions + reorder alerts.

### Student Drill-down (fees / attendance / parents)
**Lines:** 23113–23312
**Functions:** `getStudentFeeSummary`, `getStudentAttendanceReport`.
**Purpose:** Per-student finance & attendance drill-downs.

### Sidebar Badge Counts (cached 5 min)
**Lines:** 23313–23458
**Functions:** `getSidebarBadges`, `computeSidebarBadges`.
**Purpose:** Cached unread/pending counts for sidebar badges.

### Role-aware Dashboard Endpoints
**Lines:** 23459–24987
**Functions:** `getStudentDashboardData`, `getParentDashboardData`, `getSupervisorDashboardData`, `getClerkDashboardData`, `getTeacherDashboardData`, `getAdminDashboardEnrich`, `getAdminDashboardCharts`.
**Purpose:** Per-role dashboard data (KPIs, alerts, birthdays, capacity, charts).

### Discipline Drill-downs
**Lines:** 24988–25094
**Functions:** `getStudentDisciplineHistory`, `toggleDisciplineParentNotified`.
**Purpose:** Student discipline history + parent-notified toggle.

### Fee Payment Drill-downs
**Lines:** 25095–25283
**Functions:** `emailFeeReceipt`, `refundPayment`.
**Purpose:** Email receipt + refund handling.

### Exam Drill-downs
**Lines:** 25284–25653
**Functions:** `getExamToppers`, `getExamDistribution`, `getExamClassMarksheet`.
**Purpose:** Exam analytics (toppers, distribution, marksheet).

### Teacher Drill-downs
**Lines:** 25654–25802
**Functions:** `getTeacherTodaySchedule`, `getTeacherAssignments`, `getTeacherRecentLogbook`.
**Purpose:** Teacher self-service schedule/assignments/logbook.

### Admin / Misc Endpoints
**Lines:** 25803–26200
**Functions:** `adminResetUserPassword`, `getStudentResults`, `getParentFeesSummary`, `getStudentParents`.
**Purpose:** Admin password reset + student/parent result & fee summaries.

### Monthly Fee Dues (auto-generated)
**Lines:** 26201–26678
**Functions:** `_ensureFeeDuesSheet`, `generateStudentDues`, `backfillAllDues`, `generateDuesForCurrentMonth`, `_resolveSelfStudentId`, `_resolveParentChildrenIds`, `getViewerScope`, `getStudentMonthlyDues`, `payMonthlyDues`.
**Purpose:** Auto-generate monthly dues from admission month; pay dues.

---

## 3. index.html Index

`index.html` = head/CSS (lines 1–4684) + one `<script type="text/babel">` (4689–52009)
containing the entire React application. Major regions below.

### Region A — Head, Libraries & CSS
**Lines:** 1–4684
**Contents:** CDN script/link tags (React, Babel, Chart.js, jQuery/DataTables, SweetAlert2, pdfmake, Font Awesome); CSS variables (navy theme); all component styles, dark-mode, responsive rules.

### Region B — Client Utilities & Data Layer
**Lines:** 4689–4899
**Functions:** `dtPdfButton`, `gsrCall`, `swrFetcher`, `swrRevalidate`, `swrMutate`, `swrRevalidateActive`, `useSWR`.
**Backend called:** all via `google.script.run` wrapper.
**Purpose:** `google.script.run` promise wrapper + custom SWR cache hook used by every View.

### Region C — Shared UI Primitives
**Lines:** 4900–5240
**Components:** `TableSkeleton`, `SearchableDropdown`, `SearchableMultiSelect`, `StatusPipeline`.
**Purpose:** Reusable form/loader/pipeline widgets.

### Region D — Authentication
**Lines:** 5241–5723
**Components:** `LoginPage`.
**Forms:** login (multi-identity). **Backend:** `authenticateUser`.
**Purpose:** Login screen with school branding.

### Region E — Navigation Shell
**Lines:** 5433–5960
**Constants:** `MENU_CATALOG`, `MENU_PRIORITY` (per-role ordering), `GROUP_LABELS`, `SIDEBAR_BADGE_STYLE`; `buildSidebarItems`.
**Components:** `Sidebar`, `BottomNavigation`.
**Backend:** `getSidebarBadges`.
**Purpose:** Role-filtered sidebar + mobile bottom nav.

### Region F — Dashboards
**Lines:** 5961–8804
**Components:** `DashboardView` (role router), `DashKpi`, `TodayAccountsCard`, `DashSkeleton`, `useDashChart` (Chart.js hook), `AdminDashboard`, `StudentDashboard`, `ParentDashboard`, `SupervisorDashboard`, `ClerkDashboard`, `TeacherDashboard`.
**Charts:** 14 Chart.js canvases. **Backend:** `getAdminDashboardEnrich/Charts`, `getStudent/Parent/Supervisor/Clerk/TeacherDashboardData`, `getAccountTodaySummary`.
**Purpose:** Six role-specific dashboards with KPIs, charts, alerts.

### Region G — Users / Staff
**Lines:** 8805–10862
**Components:** `UsersView`; modals `TeacherTodayScheduleModal`, `TeacherAssignmentsListModal`, `TeacherRecentLogbookModal`, `UserModal`.
**Tables:** users DataTable. **Forms:** user add/edit.
**Backend:** `getAllUsers`, `addUser`, `updateUser`, `deleteUser`, `adminResetUserPassword`, teacher drill-downs.

### Region H — Admissions
**Lines:** 10863–13123
**Components:** `AdmissionsView`; modals `RegistrationModal`, `ConfirmAdmissionModal`, `EnrollStudentModal`.
**Forms:** registration, confirm, enroll. **Pipeline:** `StatusPipeline`.
**Backend:** `getAllAdmissions`, `addRegistration`, `confirmAdmission`, `enrollAdmission`, `rejectAdmission`.

### Region I — Reports
**Lines:** 13124–13609
**Constants:** `REPORT_GROUP_META`, `REPORTS` (10 report configs).
**Components:** `ReportsView`, `ReportRunner`.
**Tables:** dynamic per-report DataTable with export.
**Backend:** the 10 `get*Report` endpoints.

### Region J — Classes
**Lines:** 13610–15385
**Components:** `ClassesView`; modals `ClassTodayTimetableModal`, `ClassModal`.
**Backend:** `getAllClasses`, `addClass`, `updateClass`, `deleteClass`, `getTimetableForClass`.

### Region K — Subjects
**Lines:** 15386–16405
**Components:** `SubjectsView`, `SubjectModal`.
**Backend:** `getAllSubjects`, `addSubject`, `updateSubject`, `deleteSubject`.

### Region L — Teacher Assignments
**Lines:** 16406–17784
**Components:** `TeacherAssignmentsView`, `TeacherDetailModal`.
**Backend:** `getAllTeachersWithAssignments`, `addAssignmentsBulk`, `deleteAssignment`.

### Region M — Students
**Lines:** 17785–22435
**Components:** `StudentsView`; modals `StudentFormModal`, `StudentDetailModal`, `StudentFeesModal`, `StudentAttendanceModal`, `StudentParentsModal`, `StudentResultsModal`.
**Forms:** 63-field student form. **Tables:** students DataTable.
**Backend:** `getAllStudents`, `addStudent`, `updateStudent`, `deleteStudent`, `getStudentFeeSummary`, `getStudentAttendanceReport`, `getStudentParents`, `getStudentResults`.

### Region N — Parents
**Lines:** 22436–25008
**Components:** `ParentsView`; modals `LinkedStudentsModal`, `ParentFeesModal`, `ParentModal`.
**Backend:** `getAllParents`, `addParent`, `updateParent`, `deleteParent`, `getParentStudentLinks`, `linkParentStudent`, `getParentFeesSummary`.

### Region O — Exams
**Lines:** 25009–27553
**Components:** `ExamsView`; modals `ExamToppersModal`, `ExamDistributionModal`, `ExamClassMarksheetModal`, `ExamModal`.
**Charts:** distribution chart. **Backend:** `getAllExams`, `addExam`, `updateExam`, `deleteExam`, `publishExam`, `getExamToppers`, `getExamDistribution`, `getExamClassMarksheet`.

### Region P — Marks
**Lines:** 27554–29142
**Components:** `MarksView`, `MarksEntryModal`.
**Forms:** bulk mark grid. **Backend:** `getMarksForExamSubject`, `bulkSaveMarks`, `computeMarkRanks`.

### Region Q — Attendance
**Lines:** 29143–30377
**Components:** `AttendanceView`.
**Forms:** daily/subject-wise grid. **Backend:** `getAttendanceForClassDate`, `bulkSaveAttendance`, `lockAttendance`, `getRecentAttendanceForClass`.

### Region R — Finance (Fee Structure, Accounts, Fee Payments)
**Lines:** 30378–34585
**Components:** `FeeStructureView`, `FeeStructureModal`, `AccountsView`, `TransactionModal`, `FeePaymentsView`, `FeeReceiptA4Modal`, `FeePaymentModal`.
**Forms:** fee structure, day-book txn, fee receipt. **PDF:** A4 receipt.
**Backend:** fee-structure CRUD, transaction CRUD, `getAllPayments`, `addPayment`, `addPaymentsBulk`, `emailFeeReceipt`, `refundPayment`, `getStudentMonthlyDues`, `payMonthlyDues`.

### Region S — Discipline & Conduct
**Lines:** 34586–36696
**Components:** `DisciplineView`, `StudentDisciplineHistoryModal`, `DisciplineModal`, `ConductView`, `ConductModal`.
**Backend:** discipline/conduct CRUD, `getStudentDisciplineHistory`, `toggleDisciplineParentNotified`.

### Region T — Activities & Complaints
**Lines:** 36697–37923
**Components:** `ActivitiesView`, `ActivityModal`, `ComplaintsView`, `ComplaintModal`.
**Backend:** activities CRUD, complaints CRUD.

### Region U — Calendar
**Lines:** 37924–39348
**Components:** `CalendarView`, `CalendarEventModal`.
**Backend:** `getCalendarEvents`, `addCalendarEvent`, `updateCalendarEvent`, `deleteCalendarEvent`.

### Region V — Hall Tickets
**Lines:** 39349–39888
**Components:** `HallTicketsView`; helpers `buildHallTicketPdfDoc`, `downloadHallTicket`.
**PDF:** hall ticket. **Backend:** `getHallTicketData`.

### Region W — Notices & Helpdesk
**Lines:** 39889–41441
**Components:** `NoticesView`, `NoticeModal`, `HelpdeskView`, `HelpdeskModal`.
**Backend:** notices CRUD, helpdesk CRUD.

### Region X — Lesson Plans & Logbook
**Lines:** 41442–42788
**Components:** `LessonPlansView`, `LessonPlanModal`, `LogbookView`, `LogbookModal`.
**Backend:** lesson-plan CRUD, logbook CRUD.

### Region Y — Documents
**Lines:** 42789–43420
**Components:** `DocumentsView`, `DocumentModal`.
**Backend:** `getAllDocuments`, `addDocument`, `verifyDocument`, `deleteDocument`, `uploadProfileImage`.

### Region Z — Timetable & Periods
**Lines:** 43421–45276
**Components:** `TimetableGrid`, `TimetableNowWidget`, `TimetableEntryModal`, `PeriodModal`, `PeriodsSetupView`, `TimetableView`, `CopyTimetableModal`; helpers `ttSubjectColor`, `ttNowHHMM`, `ttIsNowInPeriod`.
**Backend:** timetable CRUD, periods CRUD, `getTimetableForClass/Teacher`, `copyTimetable`.

### Region AA — Account, Settings & About
**Lines:** 45277–46613
**Components:** `AccountView`, `SettingsView`, `SchoolSettingsForm`, `AboutView`.
**Backend:** `getMyAccount`, `updateMyAccount`, `getUserSettings`, `updateUserSettings`, `getSchoolSettings`, `updateSchoolSettings`.

### Region AB — PTM
**Lines:** 46614–47842
**Components:** `PtmView`, `PtmSlotModal`, `PtmBookingModal`.
**Backend:** `getPtmSlots`, `getMyPtmSlots`, `addPtmSlot`, `bookPtmSlot`, `cancelPtmBooking`, `completePtmBooking`, `getMyPtmBookings`.

### Region AC — Substitutes
**Lines:** 47843–48677
**Components:** `SubstitutesView`, `SubstituteModal`.
**Backend:** `getSubstitutes`, `addSubstitute`, `updateSubstitute`, `deleteSubstitute`, `getTeacherTimetableForDate`, `getAvailableTeachersForSlot`.

### Region AD — Assets & Inventory
**Lines:** 48678–50796
**Components:** `AssetsView`, `AssetModal`, `AssetMaintenanceModal`, `InventoryView`, `StockItemModal`, `StockTransactionModal`, `StockHistoryModal`.
**Backend:** assets CRUD + maintenance, stock CRUD + transactions + reorder alerts.

### Region AE — Application Shell
**Lines:** 50797–52009
**Components:** `AboutView`, `MainContent` (role-gated view router), `Dashboard` (layout: Sidebar + Header + MainContent + BottomNav), `App` (auth/session/theme root).
**Render:** `ReactDOM.createRoot(...).render(<App/>)`.

---

## 4. Google Sheets Inventory

36 worksheets (confirmed identical between `Code.gs` Config constants and `database.xlsx`).
CRUD column shows endpoints found in `Code.gs` (R=read, C=create, U=update, D=delete).

| # | Sheet | Purpose | CRUD found |
|---|-------|---------|-----------|
| 1 | `Users` | Staff/login accounts (mirrors students & parents) | R C U D |
| 2 | `Classes` | Class & section master | R C U D |
| 3 | `Subjects` | Subject per class | R C U D |
| 4 | `Teacher_Assignments` | Teacher↔class↔subject mapping | R C D (bulk) |
| 5 | `Students` | Student master (63 cols) | R C U D |
| 6 | `Parents` | Parent/guardian master | R C U D |
| 7 | `Parent_Students` | Parent–student junction | R C D |
| 8 | `Exams` | Exam definitions | R C U D + publish |
| 9 | `Marks` | Per-subject marks | R + bulk C/U |
| 10 | `Attendance` | Daily/subject-wise JSON blobs | R + bulk save + lock |
| 11 | `Fee_Structure` | Fee categories per class | R C U D |
| 12 | `Fee_Payments` | Fee receipts | R C U D + bulk + refund |
| 13 | `Fee_Dues` | Auto-generated monthly dues | R C U (auto-gen) |
| 14 | `Discipline` | Discipline incidents | R C U D |
| 15 | `Conduct` | Conduct grading | R C U D |
| 16 | `Activities` | Co-curricular records | R C U D |
| 17 | `Complaints` | Complaints (hard delete) | R C U D |
| 18 | `Notices` | School notices | R C U D |
| 19 | `Helpdesk_Tickets` | Support tickets (hard delete) | R C U D |
| 20 | `Lesson_Plans` | Lesson planning | R C U D |
| 21 | `Teaching_Logbook` | Class-coverage log (hard delete) | R C U D |
| 22 | `Documents` | Polymorphic file metadata | R C D + verify |
| 23 | `School_Periods` | Bell-schedule periods | R C U D |
| 24 | `Timetable` | Class/teacher timetable | R C U D + copy |
| 25 | `School_Settings` | Single-row global config | R U |
| 26 | `School_Calendar` | Holidays/events | R C U D |
| 27 | `PTM_Slots` | Teacher meeting slots | R C U D |
| 28 | `PTM_Bookings` | Parent slot bookings | R C U + complete/cancel |
| 29 | `Substitutes` | Absent-teacher allocations | R C U D |
| 30 | `Assets` | Fixed-asset register | R C U D |
| 31 | `Asset_Maintenance` | Asset maintenance history | R C U D |
| 32 | `Stock_Items` | Inventory items | R C U D |
| 33 | `Stock_Transactions` | Inventory in/out moves | R C |
| 34 | `Logs` | Activity/audit log | R C |
| 35 | `Admissions` | Admission pipeline | R C U D + state transitions |
| 36 | `Account_Transactions` | Day-book income/expense | R C U D |

---

## 5. Navigation Map

Sidebar is built per-role from `MENU_CATALOG` + `MENU_PRIORITY`, grouped by `GROUP_LABELS`.
Full catalogue (role visibility varies):

```
Application
├── Overview
│   ├── Dashboard
│   └── Reports
├── Daily
│   ├── Timetable
│   ├── Attendance
│   ├── School Notices
│   ├── Calendar
│   ├── Lesson Planning
│   ├── Teaching Logbook
│   ├── PTM
│   └── Substitutes
├── Academic
│   ├── Exams
│   ├── Results / Marks
│   ├── Hall Tickets
│   ├── Conduct
│   ├── Discipline
│   └── Activities
├── Records
│   ├── Admissions
│   ├── Students
│   ├── Parents
│   ├── Classes & Sections
│   ├── Subjects
│   ├── Teachers (Assignments)
│   ├── Assets
│   └── Stock / Inventory
├── Finance
│   ├── Fee Structure
│   ├── Fees Collection
│   └── Daily Accounts
├── Support
│   ├── Complaints
│   ├── Helpdesk
│   └── Documents
├── Administration
│   └── Users / Staff
└── Profile
    ├── My Account
    ├── Settings
    └── About App
```

**Per-role landing pages** (`Dashboard.initialMenu`): admin → Dashboard; clerk/teacher → Students;
student/parent → Notices; others → Classes.

**Role visibility summary** (from `MENU_PRIORITY`):
- **admin** – all modules.
- **supervisor** – academic oversight + records (no fees collection/users/settings).
- **clerk** – admissions, students, parents, fees, accounts, documents, assets/inventory.
- **teacher** – timetable, attendance, marks, exams, lesson plans, logbook, PTM, substitutes.
- **student** – read-only academics + own records; writes only helpdesk/complaints/account.
- **parent** – like student + PTM booking.

---

## 6. Function Dependency Map (high-level)

```
Login (LoginPage)
   ↓ google.script.run
authenticateUser() → tryStaffAuth / tryStudentAuth / tryParentAuth
   ↓ Users / Students / Parents sheets
session (localStorage) → role

Dashboard (role router: DashboardView)
   ↓
getAdminDashboardEnrich / getAdminDashboardCharts / get{Student|Parent|Supervisor|Clerk|Teacher}DashboardData
   ↓ aggregates across many sheets (Students, Fee_Payments, Attendance, Marks, ...)
   ↓
DashKpi cards + Chart.js charts + alert lists

Sidebar badges
   ↓
getSidebarBadges() → computeSidebarBadges() (5-min CacheService)
   ↓ Helpdesk_Tickets / Complaints / Discipline / PTM / Notices / Substitutes
   ↓
badge counts

Any CRUD View (e.g. StudentsView)
   ↓ useSWR → swrFetcher → google.script.run
get* endpoint → can*() permission gate → getSheet() → rowTo*() mapper
   ↓
table / cards
   ↓ (modal submit)
add*/update*/delete* → validate*() → next*Id() → sheet write → addLog()
   ↓ swrMutate (cache invalidate) → UI refresh

Fees flow
   ↓
addPayment / payMonthlyDues → Fee_Payments (+ Fee_Dues update) → generateReceiptNumber
   ↓
emailFeeReceipt (MailApp) / FeeReceiptA4Modal (pdfmake)

Admissions flow
   ↓
addRegistration → confirmAdmission → enrollAdmission
   ↓ Admissions sheet state machine → Students (on enroll) + Fee_Payments (admission fee)
```

---

## 7. React Component Map

```
App  (auth, session, theme, schoolSettings)
├── LoginPage
└── Dashboard  (layout)
    ├── Sidebar  (role menu + badges)
    ├── Header   (title + refresh + welcome)
    ├── BottomNavigation  (mobile)
    └── MainContent  (role-gated view router)
        ├── DashboardView → {Admin|Student|Parent|Supervisor|Clerk|Teacher}Dashboard
        │       └── DashKpi · TodayAccountsCard · useDashChart (Chart.js)
        ├── ReportsView → ReportRunner
        ├── UsersView → UserModal · Teacher{TodaySchedule|AssignmentsList|RecentLogbook}Modal
        ├── AdmissionsView → Registration/ConfirmAdmission/EnrollStudent Modal
        ├── ClassesView → ClassModal · ClassTodayTimetableModal
        ├── SubjectsView → SubjectModal
        ├── TeacherAssignmentsView → TeacherDetailModal
        ├── StudentsView → StudentForm/Detail/Fees/Attendance/Parents/Results Modal
        ├── ParentsView → ParentModal · LinkedStudentsModal · ParentFeesModal
        ├── ExamsView → ExamModal · ExamToppers/Distribution/ClassMarksheet Modal
        ├── MarksView → MarksEntryModal
        ├── AttendanceView
        ├── FeeStructureView → FeeStructureModal
        ├── AccountsView → TransactionModal
        ├── FeePaymentsView → FeePaymentModal · FeeReceiptA4Modal
        ├── DisciplineView → DisciplineModal · StudentDisciplineHistoryModal
        ├── ConductView → ConductModal
        ├── ActivitiesView → ActivityModal
        ├── ComplaintsView → ComplaintModal
        ├── NoticesView → NoticeModal
        ├── HelpdeskView → HelpdeskModal
        ├── LessonPlansView → LessonPlanModal
        ├── LogbookView → LogbookModal
        ├── DocumentsView → DocumentModal
        ├── CalendarView → CalendarEventModal
        ├── HallTicketsView
        ├── TimetableView → TimetableGrid · TimetableEntryModal · CopyTimetableModal
        │       PeriodsSetupView → PeriodModal · TimetableNowWidget
        ├── PtmView → PtmSlotModal · PtmBookingModal
        ├── SubstitutesView → SubstituteModal
        ├── AssetsView → AssetModal · AssetMaintenanceModal
        ├── InventoryView → StockItemModal · StockTransactionModal · StockHistoryModal
        ├── AccountView
        ├── SettingsView → SchoolSettingsForm
        └── AboutView
Shared primitives: TableSkeleton · SearchableDropdown · SearchableMultiSelect · StatusPipeline
Data layer: useSWR · swrFetcher · swrMutate · swrRevalidate · gsrCall
```

---

## 8. High-Level Data Flow

```
User Action  (click / form submit in a React component)
      ↓
React Component  (View or Modal — local state, validation)
      ↓
useSWR / swrFetcher / gsrCall   (client cache + promise wrapper)
      ↓
google.script.run.withSuccessHandler(...).<fnName>(args)
      ↓
Apps Script Function  (Code.gs)
      ├─ can*(role)            → permission gate
      ├─ validate*(payload)    → field validation
      ├─ getSheet(NAME)        → SpreadsheetApp worksheet
      ├─ next*Id / rowTo*      → ID gen / row mapping
      └─ addLog(...)           → audit trail
      ↓
Google Sheet  (read rows / append / update / soft-delete)
      ↓
Response  { success, data | message }
      ↓
withSuccessHandler  → swrMutate (invalidate cache) / setState
      ↓
UI Update  (re-render table / cards / Swal toast)
```

**Notes on conventions found:**
- Most endpoints take `(payload, currentUser, currentRole)` and return `{ success, data }` or `{ success:false, message }`.
- Soft delete (`IsDeleted`) on most master sheets; a few are hard-delete by design
  (Teacher_Assignments, Parent_Students, Marks, Complaints, Helpdesk_Tickets, Teaching_Logbook, Attendance).
- Dates are stored as ISO text; helper routines pin/repair date columns to text.
- Auth has three identities (staff/student/parent) unified through the `Users` sheet mirror.
