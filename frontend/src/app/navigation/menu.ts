/*
 * Navigation model — preserves the reference Apps Script application's sidebar:
 * the same menu catalog, the same functional grouping, and the same per-role
 * ordering. The actual permission-driven visibility is resolved server-side
 * (RBAC); this model provides the catalog and role priority for the shell.
 *
 * Foundation stage: catalog + grouping only. Module pages are added as modules
 * are built; here every item routes into the shell placeholder.
 */

export type Role =
  | 'super_admin'
  | 'admin'
  | 'supervisor'
  | 'accountant'
  | 'clerk'
  | 'receptionist'
  | 'teacher'
  | 'student'
  | 'parent';

export type MenuGroup =
  | 'overview'
  | 'daily'
  | 'academic'
  | 'records'
  | 'finance'
  | 'support'
  | 'admin'
  | 'account';

export interface MenuItem {
  id: string;
  label: string;
  icon: string; // Font Awesome icon name (without the "fa-" prefix)
  group: MenuGroup;
}

export const GROUP_LABELS: Record<MenuGroup, string> = {
  overview: 'Overview',
  daily: 'Daily',
  academic: 'Academic',
  records: 'Records',
  finance: 'Finance',
  support: 'Support',
  admin: 'Administration',
  account: 'Profile',
};

/** The full menu catalogue (mirrors the reference MENU_CATALOG). */
export const MENU_CATALOG: Record<string, MenuItem> = {
  dashboard: { id: 'dashboard', label: 'Dashboard', icon: 'chart-line', group: 'overview' },
  reports: { id: 'reports', label: 'Reports', icon: 'chart-pie', group: 'overview' },

  timetable: { id: 'timetable', label: 'Timetable', icon: 'calendar-alt', group: 'daily' },
  timetableDashboard: {
    id: 'timetableDashboard',
    label: 'Timetable Dashboard',
    icon: 'calendar-alt',
    group: 'daily',
  },
  classTimetable: {
    id: 'classTimetable',
    label: 'Class Timetable',
    icon: 'table-cells',
    group: 'daily',
  },
  teacherTimetable: {
    id: 'teacherTimetable',
    label: 'Teacher Timetable',
    icon: 'chalkboard-user',
    group: 'daily',
  },
  roomTimetable: {
    id: 'roomTimetable',
    label: 'Room Timetable',
    icon: 'door-open',
    group: 'daily',
  },
  timetablePeriods: {
    id: 'timetablePeriods',
    label: 'Period Management',
    icon: 'clock',
    group: 'daily',
  },
  workingDays: { id: 'workingDays', label: 'Working Days', icon: 'calendar-week', group: 'daily' },
  timetableTemplates: {
    id: 'timetableTemplates',
    label: 'Timetable Templates',
    icon: 'layer-group',
    group: 'daily',
  },
  substitutions: {
    id: 'substitutions',
    label: 'Substitutions',
    icon: 'user-clock',
    group: 'daily',
  },
  attendance: { id: 'attendance', label: 'Attendance', icon: 'clipboard-check', group: 'daily' },
  attendanceDashboard: {
    id: 'attendanceDashboard',
    label: 'Attendance Dashboard',
    icon: 'chart-line',
    group: 'daily',
  },
  studentAttendance: {
    id: 'studentAttendance',
    label: 'Student Attendance',
    icon: 'user-graduate',
    group: 'daily',
  },
  staffAttendance: {
    id: 'staffAttendance',
    label: 'Staff Attendance',
    icon: 'id-card-clip',
    group: 'daily',
  },
  manualAttendance: {
    id: 'manualAttendance',
    label: 'Manual Attendance',
    icon: 'list-check',
    group: 'daily',
  },
  attendanceImport: {
    id: 'attendanceImport',
    label: 'Attendance Import',
    icon: 'file-import',
    group: 'daily',
  },
  attendanceDevices: {
    id: 'attendanceDevices',
    label: 'Biometric Devices',
    icon: 'fingerprint',
    group: 'daily',
  },
  biometricLogs: {
    id: 'biometricLogs',
    label: 'Biometric Logs',
    icon: 'microchip',
    group: 'daily',
  },
  notices: { id: 'notices', label: 'School Notices', icon: 'bullhorn', group: 'daily' },
  calendar: { id: 'calendar', label: 'Calendar', icon: 'calendar-day', group: 'daily' },
  lessonPlans: {
    id: 'lessonPlans',
    label: 'Lesson Planning',
    icon: 'clipboard-list',
    group: 'daily',
  },
  logbook: { id: 'logbook', label: 'Teaching Logbook', icon: 'book-open', group: 'daily' },
  ptm: { id: 'ptm', label: 'PTM', icon: 'comments', group: 'daily' },
  substitutes: { id: 'substitutes', label: 'Substitutes', icon: 'user-times', group: 'daily' },

  academicDashboard: {
    id: 'academicDashboard',
    label: 'Academic Dashboard',
    icon: 'graduation-cap',
    group: 'academic',
  },
  academicYears: {
    id: 'academicYears',
    label: 'Academic Years',
    icon: 'calendar-alt',
    group: 'academic',
  },
  terms: { id: 'terms', label: 'Terms', icon: 'layer-group', group: 'academic' },
  academicCalendar: {
    id: 'academicCalendar',
    label: 'Academic Calendar',
    icon: 'calendar-day',
    group: 'academic',
  },
  sections: { id: 'sections', label: 'Sections', icon: 'table-cells', group: 'academic' },
  rooms: { id: 'rooms', label: 'Rooms', icon: 'door-open', group: 'academic' },
  subjectGroups: {
    id: 'subjectGroups',
    label: 'Subject Groups',
    icon: 'object-group',
    group: 'academic',
  },
  classTeachers: {
    id: 'classTeachers',
    label: 'Class Teachers',
    icon: 'user-tie',
    group: 'academic',
  },

  exams: { id: 'exams', label: 'Exams', icon: 'file-alt', group: 'academic' },
  examDashboard: {
    id: 'examDashboard',
    label: 'Examination Dashboard',
    icon: 'graduation-cap',
    group: 'academic',
  },
  examTypes: { id: 'examTypes', label: 'Exam Types', icon: 'file-lines', group: 'academic' },
  examSessions: { id: 'examSessions', label: 'Exam Sessions', icon: 'file-pen', group: 'academic' },
  examSchedule: {
    id: 'examSchedule',
    label: 'Exam Schedule',
    icon: 'calendar-check',
    group: 'academic',
  },
  examSeating: { id: 'examSeating', label: 'Seating Plan', icon: 'chair', group: 'academic' },
  examAttendance: {
    id: 'examAttendance',
    label: 'Exam Attendance',
    icon: 'user-check',
    group: 'academic',
  },
  marksEntry: { id: 'marksEntry', label: 'Marks Entry', icon: 'pen-to-square', group: 'academic' },
  gradeManagement: {
    id: 'gradeManagement',
    label: 'Grade Management',
    icon: 'award',
    group: 'academic',
  },
  examResults: { id: 'examResults', label: 'Results', icon: 'ranking-star', group: 'academic' },
  reportCards: { id: 'reportCards', label: 'Report Cards', icon: 'id-card', group: 'academic' },
  tabulation: {
    id: 'tabulation',
    label: 'Tabulation Sheet',
    icon: 'table-list',
    group: 'academic',
  },
  marks: { id: 'marks', label: 'Results / Marks', icon: 'graduation-cap', group: 'academic' },
  hallTickets: { id: 'hallTickets', label: 'Hall Tickets', icon: 'id-card-alt', group: 'academic' },
  conduct: { id: 'conduct', label: 'Conduct', icon: 'award', group: 'academic' },
  discipline: { id: 'discipline', label: 'Discipline', icon: 'gavel', group: 'academic' },
  activities: { id: 'activities', label: 'Activities', icon: 'trophy', group: 'academic' },

  admissions: { id: 'admissions', label: 'Admissions', icon: 'file-signature', group: 'records' },
  admissionDashboard: {
    id: 'admissionDashboard',
    label: 'Admission Dashboard',
    icon: 'chart-pie',
    group: 'records',
  },
  admissionEnquiries: {
    id: 'admissionEnquiries',
    label: 'Enquiries',
    icon: 'comments',
    group: 'records',
  },
  admissionApplications: {
    id: 'admissionApplications',
    label: 'Applications',
    icon: 'file-signature',
    group: 'records',
  },
  admissionVerification: {
    id: 'admissionVerification',
    label: 'Verification',
    icon: 'clipboard-check',
    group: 'records',
  },
  admissionApproval: {
    id: 'admissionApproval',
    label: 'Approval',
    icon: 'diagram-project',
    group: 'records',
  },
  admissionEnrollment: {
    id: 'admissionEnrollment',
    label: 'Enrollment',
    icon: 'user-graduate',
    group: 'records',
  },
  admissionImport: {
    id: 'admissionImport',
    label: 'Admission Import',
    icon: 'file-import',
    group: 'records',
  },
  studentDashboard: {
    id: 'studentDashboard',
    label: 'Student Dashboard',
    icon: 'chart-line',
    group: 'records',
  },
  students: { id: 'students', label: 'Students', icon: 'user-graduate', group: 'records' },
  studentProfile: {
    id: 'studentProfile',
    label: 'Student Profile',
    icon: 'id-badge',
    group: 'records',
  },
  studentTimeline: {
    id: 'studentTimeline',
    label: 'Student Timeline',
    icon: 'timeline',
    group: 'records',
  },
  studentMedical: {
    id: 'studentMedical',
    label: 'Student Medical',
    icon: 'notes-medical',
    group: 'records',
  },
  studentDocuments: {
    id: 'studentDocuments',
    label: 'Student Documents',
    icon: 'folder-open',
    group: 'records',
  },
  studentAcademicRecords: {
    id: 'studentAcademicRecords',
    label: 'Academic Records',
    icon: 'clock-rotate-left',
    group: 'records',
  },
  studentTransfers: {
    id: 'studentTransfers',
    label: 'Transfers',
    icon: 'right-left',
    group: 'records',
  },
  studentWithdrawals: {
    id: 'studentWithdrawals',
    label: 'Withdrawals',
    icon: 'user-xmark',
    group: 'records',
  },
  studentPromotions: {
    id: 'studentPromotions',
    label: 'Promotions',
    icon: 'arrow-up-right-dots',
    group: 'records',
  },
  studentImport: {
    id: 'studentImport',
    label: 'Student Import',
    icon: 'file-import',
    group: 'records',
  },
  studentExport: {
    id: 'studentExport',
    label: 'Student Export',
    icon: 'file-export',
    group: 'records',
  },
  staffDashboard: {
    id: 'staffDashboard',
    label: 'Staff Dashboard',
    icon: 'id-card-clip',
    group: 'records',
  },
  staff: { id: 'staff', label: 'Staff', icon: 'users', group: 'records' },
  staffProfile: { id: 'staffProfile', label: 'Staff Profile', icon: 'id-badge', group: 'records' },
  staffQualifications: {
    id: 'staffQualifications',
    label: 'Qualifications',
    icon: 'graduation-cap',
    group: 'records',
  },
  staffExperience: {
    id: 'staffExperience',
    label: 'Experience',
    icon: 'briefcase',
    group: 'records',
  },
  staffDocuments: {
    id: 'staffDocuments',
    label: 'Staff Documents',
    icon: 'folder-open',
    group: 'records',
  },
  staffTimeline: {
    id: 'staffTimeline',
    label: 'Staff Timeline',
    icon: 'timeline',
    group: 'records',
  },
  staffImport: { id: 'staffImport', label: 'Staff Import', icon: 'file-import', group: 'records' },
  staffExport: { id: 'staffExport', label: 'Staff Export', icon: 'file-export', group: 'records' },
  parents: { id: 'parents', label: 'Parents', icon: 'users', group: 'records' },
  classes: { id: 'classes', label: 'Classes & Sections', icon: 'school', group: 'records' },
  subjects: { id: 'subjects', label: 'Subjects', icon: 'book', group: 'records' },
  assignments: { id: 'assignments', label: 'Teachers', icon: 'user-plus', group: 'records' },
  assets: { id: 'assets', label: 'Assets', icon: 'desktop', group: 'records' },
  inventory: { id: 'inventory', label: 'Stock / Inventory', icon: 'boxes', group: 'records' },

  feeStructure: {
    id: 'feeStructure',
    label: 'Fee Structure',
    icon: 'rupee-sign',
    group: 'finance',
  },
  feePayments: {
    id: 'feePayments',
    label: 'Fees Collection',
    icon: 'money-bill-wave',
    group: 'finance',
  },
  accounts: { id: 'accounts', label: 'Daily Accounts', icon: 'wallet', group: 'finance' },

  complaints: { id: 'complaints', label: 'Complaints', icon: 'comment-dots', group: 'support' },
  helpdesk: { id: 'helpdesk', label: 'Helpdesk', icon: 'headset', group: 'support' },
  documents: { id: 'documents', label: 'Documents', icon: 'folder-open', group: 'support' },

  users: { id: 'users', label: 'Users / Staff', icon: 'users-cog', group: 'admin' },
  roles: { id: 'roles', label: 'Roles & Permissions', icon: 'user-shield', group: 'admin' },
  schoolSettings: {
    id: 'schoolSettings',
    label: 'School Settings',
    icon: 'school',
    group: 'admin',
  },
  masterData: { id: 'masterData', label: 'Master Data', icon: 'database', group: 'admin' },
  numberGenerator: {
    id: 'numberGenerator',
    label: 'Number Generator',
    icon: 'hashtag',
    group: 'admin',
  },
  featureFlags: { id: 'featureFlags', label: 'Feature Flags', icon: 'toggle-on', group: 'admin' },
  gateways: { id: 'gateways', label: 'Gateways', icon: 'plug', group: 'admin' },
  identityDashboard: {
    id: 'identityDashboard',
    label: 'Identity Dashboard',
    icon: 'fingerprint',
    group: 'admin',
  },
  identityList: { id: 'identityList', label: 'Identities', icon: 'id-card', group: 'admin' },
  identityDetails: {
    id: 'identityDetails',
    label: 'Identity Lookup',
    icon: 'magnifying-glass',
    group: 'admin',
  },

  account: { id: 'account', label: 'My Account', icon: 'user-circle', group: 'account' },
  settings: { id: 'settings', label: 'Settings', icon: 'cog', group: 'account' },
  about: { id: 'about', label: 'About App', icon: 'info-circle', group: 'account' },
};

/**
 * Per-role ordered menu priority (mirrors the reference MENU_PRIORITY). Drives
 * the order in which a role sees items. Final visibility is enforced by RBAC.
 */
export const MENU_PRIORITY: Record<Role, string[]> = {
  super_admin: [
    'dashboard',
    'reports',
    'academicDashboard',
    'academicYears',
    'terms',
    'academicCalendar',
    'classes',
    'sections',
    'rooms',
    'subjects',
    'subjectGroups',
    'assignments',
    'classTeachers',
    'examDashboard',
    'examTypes',
    'examSessions',
    'examSchedule',
    'examSeating',
    'examAttendance',
    'marksEntry',
    'gradeManagement',
    'examResults',
    'reportCards',
    'tabulation',
    'admissionDashboard',
    'admissionEnquiries',
    'admissionApplications',
    'admissionVerification',
    'admissionApproval',
    'admissionEnrollment',
    'admissionImport',
    'studentDashboard',
    'students',
    'studentProfile',
    'studentTimeline',
    'studentMedical',
    'studentDocuments',
    'studentAcademicRecords',
    'studentTransfers',
    'studentWithdrawals',
    'studentPromotions',
    'studentImport',
    'studentExport',
    'staffDashboard',
    'staff',
    'staffProfile',
    'staffQualifications',
    'staffExperience',
    'staffDocuments',
    'staffTimeline',
    'staffImport',
    'staffExport',
    'timetableDashboard',
    'classTimetable',
    'teacherTimetable',
    'roomTimetable',
    'timetablePeriods',
    'workingDays',
    'timetableTemplates',
    'substitutions',
    'attendanceDashboard',
    'studentAttendance',
    'staffAttendance',
    'manualAttendance',
    'attendanceImport',
    'attendanceDevices',
    'biometricLogs',
    'users',
    'roles',
    'schoolSettings',
    'masterData',
    'numberGenerator',
    'featureFlags',
    'gateways',
    'identityDashboard',
    'identityList',
    'identityDetails',
    'settings',
    'about',
  ],
  admin: [
    'dashboard',
    'reports',
    'admissionDashboard',
    'admissionEnquiries',
    'admissionApplications',
    'admissionVerification',
    'admissionApproval',
    'admissionEnrollment',
    'admissionImport',
    'studentDashboard',
    'students',
    'studentProfile',
    'studentTimeline',
    'studentMedical',
    'studentDocuments',
    'studentAcademicRecords',
    'studentTransfers',
    'studentWithdrawals',
    'studentPromotions',
    'studentImport',
    'studentExport',
    'staffDashboard',
    'staff',
    'staffProfile',
    'staffQualifications',
    'staffExperience',
    'staffDocuments',
    'staffTimeline',
    'staffImport',
    'staffExport',
    'academicDashboard',
    'academicYears',
    'terms',
    'academicCalendar',
    'classes',
    'sections',
    'rooms',
    'subjects',
    'subjectGroups',
    'assignments',
    'classTeachers',
    'timetable',
    'timetableDashboard',
    'classTimetable',
    'teacherTimetable',
    'roomTimetable',
    'timetablePeriods',
    'workingDays',
    'timetableTemplates',
    'substitutions',
    'attendanceDashboard',
    'studentAttendance',
    'staffAttendance',
    'manualAttendance',
    'attendanceImport',
    'attendanceDevices',
    'biometricLogs',
    'attendance',
    'substitutes',
    'examDashboard',
    'examTypes',
    'examSessions',
    'examSchedule',
    'examSeating',
    'examAttendance',
    'marksEntry',
    'gradeManagement',
    'examResults',
    'reportCards',
    'tabulation',
    'exams',
    'marks',
    'hallTickets',
    'feePayments',
    'accounts',
    'feeStructure',
    'notices',
    'ptm',
    'calendar',
    'discipline',
    'conduct',
    'activities',
    'lessonPlans',
    'logbook',
    'helpdesk',
    'complaints',
    'documents',
    'parents',
    'assets',
    'inventory',
    'schoolSettings',
    'masterData',
    'numberGenerator',
    'featureFlags',
    'gateways',
    'identityDashboard',
    'identityList',
    'identityDetails',
    'users',
    'roles',
    'account',
    'settings',
    'about',
  ],
  supervisor: [
    'dashboard',
    'reports',
    'attendance',
    'substitutes',
    'marks',
    'hallTickets',
    'exams',
    'notices',
    'ptm',
    'calendar',
    'discipline',
    'conduct',
    'helpdesk',
    'complaints',
    'lessonPlans',
    'logbook',
    'activities',
    'students',
    'parents',
    'classes',
    'subjects',
    'assignments',
    'documents',
    'timetable',
    'account',
    'about',
  ],
  accountant: [
    'dashboard',
    'feePayments',
    'feeStructure',
    'accounts',
    'reports',
    'students',
    'documents',
    'account',
    'about',
  ],
  clerk: [
    'admissions',
    'students',
    'parents',
    'feePayments',
    'accounts',
    'feeStructure',
    'reports',
    'attendance',
    'helpdesk',
    'complaints',
    'documents',
    'assets',
    'inventory',
    'timetable',
    'classes',
    'notices',
    'calendar',
    'account',
    'about',
  ],
  receptionist: [
    'dashboard',
    'admissions',
    'students',
    'parents',
    'notices',
    'calendar',
    'helpdesk',
    'documents',
    'account',
    'about',
  ],
  teacher: [
    'timetable',
    'attendance',
    'substitutes',
    'marks',
    'hallTickets',
    'exams',
    'lessonPlans',
    'ptm',
    'logbook',
    'students',
    'discipline',
    'conduct',
    'activities',
    'notices',
    'reports',
    'calendar',
    'documents',
    'complaints',
    'classes',
    'subjects',
    'account',
    'about',
  ],
  student: [
    'dashboard',
    'timetable',
    'attendance',
    'marks',
    'hallTickets',
    'exams',
    'notices',
    'calendar',
    'feePayments',
    'feeStructure',
    'activities',
    'conduct',
    'discipline',
    'documents',
    'helpdesk',
    'complaints',
    'account',
    'about',
  ],
  parent: [
    'dashboard',
    'notices',
    'ptm',
    'calendar',
    'timetable',
    'attendance',
    'marks',
    'hallTickets',
    'exams',
    'feePayments',
    'feeStructure',
    'conduct',
    'discipline',
    'activities',
    'documents',
    'helpdesk',
    'complaints',
    'account',
    'about',
  ],
};

export interface SidebarGroup {
  group: MenuGroup;
  label: string;
  items: MenuItem[];
}

/** Build the grouped sidebar for a role, in the role's priority order. */
export function buildSidebar(role: Role): SidebarGroup[] {
  const order = MENU_PRIORITY[role] ?? MENU_PRIORITY.admin;
  const groups: SidebarGroup[] = [];
  const index = new Map<MenuGroup, SidebarGroup>();

  for (const id of order) {
    const item = MENU_CATALOG[id];
    if (!item) continue;
    let g = index.get(item.group);
    if (!g) {
      g = { group: item.group, label: GROUP_LABELS[item.group], items: [] };
      index.set(item.group, g);
      groups.push(g);
    }
    g.items.push(item);
  }
  return groups;
}

/** Role-appropriate landing menu id (mirrors the reference landing rules). */
export function landingMenu(role: Role): string {
  if (role === 'admin' || role === 'super_admin') return 'dashboard';
  if (role === 'clerk' || role === 'teacher') return 'students';
  if (role === 'student' || role === 'parent') return 'notices';
  return 'dashboard';
}

/** Map a backend role slug (from /auth/me) to the menu Role. */
export function roleFromSlug(slug: string | undefined): Role {
  switch (slug) {
    case 'super_admin':
      return 'super_admin';
    case 'administrator':
    case 'admin':
      return 'admin';
    case 'supervisor':
      return 'supervisor';
    case 'accountant':
      return 'accountant';
    case 'clerk':
      return 'clerk';
    case 'receptionist':
      return 'receptionist';
    case 'teacher':
      return 'teacher';
    case 'student':
      return 'student';
    case 'parent':
      return 'parent';
    default:
      return 'admin';
  }
}
