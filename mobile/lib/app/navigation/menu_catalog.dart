import 'package:flutter/material.dart';

/// Navigation model — mirrors the web client and the reference application:
/// the same menu catalogue, functional grouping, and per-role ordering. Final
/// visibility is enforced server-side (RBAC); this drives the adaptive shell.

enum Role {
  superAdmin,
  admin,
  supervisor,
  accountant,
  clerk,
  receptionist,
  teacher,
  student,
  parent,
}

Role roleFromString(String value) {
  switch (value) {
    case 'super_admin':
      return Role.superAdmin;
    case 'admin':
    case 'administrator':
      return Role.admin;
    case 'supervisor':
      return Role.supervisor;
    case 'accountant':
      return Role.accountant;
    case 'clerk':
      return Role.clerk;
    case 'receptionist':
      return Role.receptionist;
    case 'teacher':
      return Role.teacher;
    case 'student':
      return Role.student;
    case 'parent':
      return Role.parent;
    default:
      return Role.admin;
  }
}

class MenuItem {
  const MenuItem(this.id, this.label, this.icon, this.group);
  final String id;
  final String label;
  final IconData icon;
  final String group;
}

const Map<String, String> groupLabels = {
  'overview': 'Overview',
  'daily': 'Daily',
  'academic': 'Academic',
  'records': 'Records',
  'finance': 'Finance',
  'support': 'Support',
  'admin': 'Administration',
  'account': 'Profile',
};

const Map<String, MenuItem> menuCatalog = {
  'dashboard': MenuItem('dashboard', 'Dashboard', Icons.dashboard, 'overview'),
  'reports': MenuItem('reports', 'Reports', Icons.pie_chart, 'overview'),
  'timetable': MenuItem('timetable', 'Timetable', Icons.calendar_month, 'daily'),
  'attendance': MenuItem('attendance', 'Attendance', Icons.fact_check, 'daily'),
  'notices': MenuItem('notices', 'School Notices', Icons.campaign, 'daily'),
  'calendar': MenuItem('calendar', 'Calendar', Icons.event, 'daily'),
  'lessonPlans': MenuItem('lessonPlans', 'Lesson Planning', Icons.assignment, 'daily'),
  'logbook': MenuItem('logbook', 'Teaching Logbook', Icons.menu_book, 'daily'),
  'ptm': MenuItem('ptm', 'PTM', Icons.forum, 'daily'),
  'substitutes': MenuItem('substitutes', 'Substitutes', Icons.person_off, 'daily'),
  'exams': MenuItem('exams', 'Exams', Icons.description, 'academic'),
  'marks': MenuItem('marks', 'Results / Marks', Icons.school, 'academic'),
  'hallTickets': MenuItem('hallTickets', 'Hall Tickets', Icons.badge, 'academic'),
  'conduct': MenuItem('conduct', 'Conduct', Icons.emoji_events, 'academic'),
  'discipline': MenuItem('discipline', 'Discipline', Icons.gavel, 'academic'),
  'activities': MenuItem('activities', 'Activities', Icons.sports_soccer, 'academic'),
  'admissions': MenuItem('admissions', 'Admissions', Icons.how_to_reg, 'records'),
  'students': MenuItem('students', 'Students', Icons.groups, 'records'),
  'parents': MenuItem('parents', 'Parents', Icons.family_restroom, 'records'),
  'classes': MenuItem('classes', 'Classes & Sections', Icons.meeting_room, 'records'),
  'subjects': MenuItem('subjects', 'Subjects', Icons.book, 'records'),
  'assignments': MenuItem('assignments', 'Teachers', Icons.person_add, 'records'),
  'assets': MenuItem('assets', 'Assets', Icons.desktop_windows, 'records'),
  'inventory': MenuItem('inventory', 'Stock / Inventory', Icons.inventory_2, 'records'),
  'feeStructure': MenuItem('feeStructure', 'Fee Structure', Icons.request_quote, 'finance'),
  'feePayments': MenuItem('feePayments', 'Fees Collection', Icons.payments, 'finance'),
  'accounts': MenuItem('accounts', 'Daily Accounts', Icons.account_balance_wallet, 'finance'),
  'complaints': MenuItem('complaints', 'Complaints', Icons.report_problem, 'support'),
  'helpdesk': MenuItem('helpdesk', 'Helpdesk', Icons.support_agent, 'support'),
  'documents': MenuItem('documents', 'Documents', Icons.folder_open, 'support'),
  'users': MenuItem('users', 'Users / Staff', Icons.manage_accounts, 'admin'),
  'roles': MenuItem('roles', 'Roles & Permissions', Icons.admin_panel_settings, 'admin'),
  'account': MenuItem('account', 'My Account', Icons.account_circle, 'account'),
  'settings': MenuItem('settings', 'Settings', Icons.settings, 'account'),
  'about': MenuItem('about', 'About App', Icons.info_outline, 'account'),
};

const Map<Role, List<String>> menuPriority = {
  Role.superAdmin: ['dashboard', 'reports', 'users', 'roles', 'settings', 'about'],
  Role.admin: [
    'dashboard', 'reports', 'admissions', 'students', 'classes', 'timetable',
    'attendance', 'exams', 'marks', 'feePayments', 'accounts', 'feeStructure',
    'notices', 'ptm', 'calendar', 'discipline', 'conduct', 'documents',
    'parents', 'assets', 'inventory', 'subjects', 'assignments', 'users',
    'roles', 'account', 'settings', 'about',
  ],
  Role.supervisor: [
    'dashboard', 'reports', 'attendance', 'marks', 'exams', 'notices', 'ptm',
    'calendar', 'discipline', 'conduct', 'students', 'classes', 'account', 'about',
  ],
  Role.accountant: [
    'dashboard', 'feePayments', 'feeStructure', 'accounts', 'reports',
    'students', 'documents', 'account', 'about',
  ],
  Role.clerk: [
    'admissions', 'students', 'parents', 'feePayments', 'accounts', 'reports',
    'attendance', 'helpdesk', 'documents', 'notices', 'account', 'about',
  ],
  Role.receptionist: [
    'dashboard', 'admissions', 'students', 'parents', 'notices', 'calendar',
    'helpdesk', 'documents', 'account', 'about',
  ],
  Role.teacher: [
    'timetable', 'attendance', 'marks', 'exams', 'lessonPlans', 'ptm', 'logbook',
    'students', 'discipline', 'conduct', 'notices', 'calendar', 'account', 'about',
  ],
  Role.student: [
    'dashboard', 'timetable', 'attendance', 'marks', 'exams', 'notices',
    'calendar', 'feePayments', 'documents', 'helpdesk', 'account', 'about',
  ],
  Role.parent: [
    'dashboard', 'notices', 'ptm', 'calendar', 'timetable', 'attendance',
    'marks', 'feePayments', 'documents', 'helpdesk', 'account', 'about',
  ],
};

/// Ordered menu items for a role.
List<MenuItem> menuForRole(Role role) {
  final order = menuPriority[role] ?? menuPriority[Role.admin]!;
  return order
      .map((id) => menuCatalog[id])
      .whereType<MenuItem>()
      .toList(growable: false);
}

/// Role-appropriate landing menu id.
String landingMenu(Role role) {
  if (role == Role.admin || role == Role.superAdmin) return 'dashboard';
  if (role == Role.clerk || role == Role.teacher) return 'students';
  if (role == Role.student || role == Role.parent) return 'notices';
  return 'dashboard';
}
