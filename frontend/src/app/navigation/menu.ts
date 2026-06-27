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
  attendance: { id: 'attendance', label: 'Attendance', icon: 'clipboard-check', group: 'daily' },
  notices: { id: 'notices', label: 'School Notices', icon: 'bullhorn', group: 'daily' },
  calendar: { id: 'calendar', label: 'Calendar', icon: 'calendar-day', group: 'daily' },
  lessonPlans: { id: 'lessonPlans', label: 'Lesson Planning', icon: 'clipboard-list', group: 'daily' },
  logbook: { id: 'logbook', label: 'Teaching Logbook', icon: 'book-open', group: 'daily' },
  ptm: { id: 'ptm', label: 'PTM', icon: 'comments', group: 'daily' },
  substitutes: { id: 'substitutes', label: 'Substitutes', icon: 'user-times', group: 'daily' },

  exams: { id: 'exams', label: 'Exams', icon: 'file-alt', group: 'academic' },
  marks: { id: 'marks', label: 'Results / Marks', icon: 'graduation-cap', group: 'academic' },
  hallTickets: { id: 'hallTickets', label: 'Hall Tickets', icon: 'id-card-alt', group: 'academic' },
  conduct: { id: 'conduct', label: 'Conduct', icon: 'award', group: 'academic' },
  discipline: { id: 'discipline', label: 'Discipline', icon: 'gavel', group: 'academic' },
  activities: { id: 'activities', label: 'Activities', icon: 'trophy', group: 'academic' },

  admissions: { id: 'admissions', label: 'Admissions', icon: 'file-signature', group: 'records' },
  students: { id: 'students', label: 'Students', icon: 'user-graduate', group: 'records' },
  parents: { id: 'parents', label: 'Parents', icon: 'users', group: 'records' },
  classes: { id: 'classes', label: 'Classes & Sections', icon: 'school', group: 'records' },
  subjects: { id: 'subjects', label: 'Subjects', icon: 'book', group: 'records' },
  assignments: { id: 'assignments', label: 'Teachers', icon: 'user-plus', group: 'records' },
  assets: { id: 'assets', label: 'Assets', icon: 'desktop', group: 'records' },
  inventory: { id: 'inventory', label: 'Stock / Inventory', icon: 'boxes', group: 'records' },

  feeStructure: { id: 'feeStructure', label: 'Fee Structure', icon: 'rupee-sign', group: 'finance' },
  feePayments: { id: 'feePayments', label: 'Fees Collection', icon: 'money-bill-wave', group: 'finance' },
  accounts: { id: 'accounts', label: 'Daily Accounts', icon: 'wallet', group: 'finance' },

  complaints: { id: 'complaints', label: 'Complaints', icon: 'comment-dots', group: 'support' },
  helpdesk: { id: 'helpdesk', label: 'Helpdesk', icon: 'headset', group: 'support' },
  documents: { id: 'documents', label: 'Documents', icon: 'folder-open', group: 'support' },

  users: { id: 'users', label: 'Users / Staff', icon: 'users-cog', group: 'admin' },
  roles: { id: 'roles', label: 'Roles & Permissions', icon: 'user-shield', group: 'admin' },
  schoolSettings: { id: 'schoolSettings', label: 'School Settings', icon: 'school', group: 'admin' },
  masterData: { id: 'masterData', label: 'Master Data', icon: 'database', group: 'admin' },
  numberGenerator: { id: 'numberGenerator', label: 'Number Generator', icon: 'hashtag', group: 'admin' },
  featureFlags: { id: 'featureFlags', label: 'Feature Flags', icon: 'toggle-on', group: 'admin' },
  gateways: { id: 'gateways', label: 'Gateways', icon: 'plug', group: 'admin' },

  account: { id: 'account', label: 'My Account', icon: 'user-circle', group: 'account' },
  settings: { id: 'settings', label: 'Settings', icon: 'cog', group: 'account' },
  about: { id: 'about', label: 'About App', icon: 'info-circle', group: 'account' },
};

/**
 * Per-role ordered menu priority (mirrors the reference MENU_PRIORITY). Drives
 * the order in which a role sees items. Final visibility is enforced by RBAC.
 */
export const MENU_PRIORITY: Record<Role, string[]> = {
  super_admin: ['dashboard', 'reports', 'users', 'roles', 'schoolSettings', 'masterData', 'numberGenerator', 'featureFlags', 'gateways', 'settings', 'about'],
  admin: [
    'dashboard', 'reports', 'admissions', 'students', 'classes', 'timetable',
    'attendance', 'substitutes', 'exams', 'marks', 'hallTickets', 'feePayments',
    'accounts', 'feeStructure', 'notices', 'ptm', 'calendar', 'discipline',
    'conduct', 'activities', 'lessonPlans', 'logbook', 'helpdesk', 'complaints',
    'documents', 'parents', 'assets', 'inventory', 'subjects', 'assignments',
    'schoolSettings', 'masterData', 'numberGenerator', 'featureFlags', 'gateways',
    'users', 'roles', 'account', 'settings', 'about',
  ],
  supervisor: [
    'dashboard', 'reports', 'attendance', 'substitutes', 'marks', 'hallTickets',
    'exams', 'notices', 'ptm', 'calendar', 'discipline', 'conduct', 'helpdesk',
    'complaints', 'lessonPlans', 'logbook', 'activities', 'students', 'parents',
    'classes', 'subjects', 'assignments', 'documents', 'timetable', 'account', 'about',
  ],
  accountant: [
    'dashboard', 'feePayments', 'feeStructure', 'accounts', 'reports',
    'students', 'documents', 'account', 'about',
  ],
  clerk: [
    'admissions', 'students', 'parents', 'feePayments', 'accounts', 'feeStructure',
    'reports', 'attendance', 'helpdesk', 'complaints', 'documents', 'assets',
    'inventory', 'timetable', 'classes', 'notices', 'calendar', 'account', 'about',
  ],
  receptionist: [
    'dashboard', 'admissions', 'students', 'parents', 'notices', 'calendar',
    'helpdesk', 'documents', 'account', 'about',
  ],
  teacher: [
    'timetable', 'attendance', 'substitutes', 'marks', 'hallTickets', 'exams',
    'lessonPlans', 'ptm', 'logbook', 'students', 'discipline', 'conduct',
    'activities', 'notices', 'reports', 'calendar', 'documents', 'complaints',
    'classes', 'subjects', 'account', 'about',
  ],
  student: [
    'dashboard', 'timetable', 'attendance', 'marks', 'hallTickets', 'exams',
    'notices', 'calendar', 'feePayments', 'feeStructure', 'activities', 'conduct',
    'discipline', 'documents', 'helpdesk', 'complaints', 'account', 'about',
  ],
  parent: [
    'dashboard', 'notices', 'ptm', 'calendar', 'timetable', 'attendance', 'marks',
    'hallTickets', 'exams', 'feePayments', 'feeStructure', 'conduct', 'discipline',
    'activities', 'documents', 'helpdesk', 'complaints', 'account', 'about',
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
