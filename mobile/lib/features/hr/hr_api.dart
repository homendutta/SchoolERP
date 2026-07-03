import '../../core/api/api_client.dart';

/// Human Resources API surface for the mobile app (Sprint 16A).
///
/// Employee lifecycle: departments & designations (hierarchical; codes from the
/// Number Generator), employment HISTORY (never overwritten), employee documents
/// (Media references), shifts, attendance policies (consumed by the Attendance
/// module), leave types/policies/requests (the Leave Engine, with multi-level
/// approval + balance tracking), holidays, performance reviews, training,
/// disciplinary records and employee separation. Notifications go through the
/// Communication Engine. Payroll is Sprint 16B. The mobile app exposes the
/// endpoints only (no UI).
class HrApi {
  HrApi(this._api);

  final ApiClient _api;

  String _query(Map<String, dynamic>? params) {
    if (params == null || params.isEmpty) return '';
    final pairs = <String>[];
    params.forEach((key, value) {
      if (value == null) return;
      pairs.add('${Uri.encodeQueryComponent(key)}=${Uri.encodeQueryComponent('$value')}');
    });
    return pairs.isEmpty ? '' : '?${pairs.join('&')}';
  }

  Future<dynamic> dashboard(Map<String, dynamic> params) => _api.get('/hr/dashboard${_query(params)}');

  // ---- Departments + designations (Number Generator codes; hierarchical) ----
  Future<dynamic> departments([Map<String, dynamic>? params]) => _api.get('/hr/departments${_query(params)}');
  Future<dynamic> createDepartment(Map<String, dynamic> body) => _api.post('/hr/departments', body: body);
  Future<dynamic> updateDepartment(int id, Map<String, dynamic> body) => _api.put('/hr/departments/$id', body: body);
  Future<dynamic> designations([Map<String, dynamic>? params]) => _api.get('/hr/designations${_query(params)}');
  Future<dynamic> createDesignation(Map<String, dynamic> body) => _api.post('/hr/designations', body: body);
  Future<dynamic> updateDesignation(int id, Map<String, dynamic> body) => _api.put('/hr/designations/$id', body: body);

  // ---- Employment history (every change creates a new record) + documents ----
  Future<dynamic> employment([Map<String, dynamic>? params]) => _api.get('/hr/employment${_query(params)}');
  Future<dynamic> createEmployment(Map<String, dynamic> body) => _api.post('/hr/employment', body: body);
  Future<dynamic> employeeDocuments([Map<String, dynamic>? params]) => _api.get('/hr/employee-documents${_query(params)}');
  Future<dynamic> createEmployeeDocument(Map<String, dynamic> body) => _api.post('/hr/employee-documents', body: body);

  // ---- Shifts + attendance policies (consumed by Attendance) ----
  Future<dynamic> shifts([Map<String, dynamic>? params]) => _api.get('/hr/shifts${_query(params)}');
  Future<dynamic> createShift(Map<String, dynamic> body) => _api.post('/hr/shifts', body: body);
  Future<dynamic> updateShift(int id, Map<String, dynamic> body) => _api.put('/hr/shifts/$id', body: body);
  Future<dynamic> attendancePolicies([Map<String, dynamic>? params]) => _api.get('/hr/attendance-policies${_query(params)}');
  Future<dynamic> createAttendancePolicy(Map<String, dynamic> body) => _api.post('/hr/attendance-policies', body: body);

  // ---- Leave configuration ----
  Future<dynamic> leaveTypes([Map<String, dynamic>? params]) => _api.get('/hr/leave-types${_query(params)}');
  Future<dynamic> createLeaveType(Map<String, dynamic> body) => _api.post('/hr/leave-types', body: body);
  Future<dynamic> leavePolicies([Map<String, dynamic>? params]) => _api.get('/hr/leave-policies${_query(params)}');
  Future<dynamic> createLeavePolicy(Map<String, dynamic> body) => _api.post('/hr/leave-policies', body: body);

  // ---- Leave requests — writes go through the Leave Engine ----
  Future<dynamic> leaveRequests([Map<String, dynamic>? params]) => _api.get('/hr/leave-requests${_query(params)}');
  Future<dynamic> leaveRequest(int id) => _api.get('/hr/leave-requests/$id');
  Future<dynamic> applyLeave(Map<String, dynamic> body) => _api.post('/hr/leave-requests', body: body);
  Future<dynamic> approveLeave(int id, [Map<String, dynamic>? body]) => _api.post('/hr/leave-requests/$id/approve', body: body ?? {});
  Future<dynamic> rejectLeave(int id, [Map<String, dynamic>? body]) => _api.post('/hr/leave-requests/$id/reject', body: body ?? {});
  Future<dynamic> cancelLeave(int id) => _api.post('/hr/leave-requests/$id/cancel');
  Future<dynamic> leaveBalances([Map<String, dynamic>? params]) => _api.get('/hr/leave-balances${_query(params)}');

  // ---- Holidays ----
  Future<dynamic> holidays([Map<String, dynamic>? params]) => _api.get('/hr/holidays${_query(params)}');
  Future<dynamic> createHoliday(Map<String, dynamic> body) => _api.post('/hr/holidays', body: body);

  // ---- Performance + training + discipline ----
  Future<dynamic> performance([Map<String, dynamic>? params]) => _api.get('/hr/performance${_query(params)}');
  Future<dynamic> createPerformanceReview(Map<String, dynamic> body) => _api.post('/hr/performance', body: body);
  Future<dynamic> training([Map<String, dynamic>? params]) => _api.get('/hr/training${_query(params)}');
  Future<dynamic> createTraining(Map<String, dynamic> body) => _api.post('/hr/training', body: body);
  Future<dynamic> assignTraining(int id, Map<String, dynamic> body) => _api.post('/hr/training/$id/participants', body: body);
  Future<dynamic> discipline([Map<String, dynamic>? params]) => _api.get('/hr/discipline${_query(params)}');
  Future<dynamic> createDisciplinary(Map<String, dynamic> body) => _api.post('/hr/discipline', body: body);

  // ---- Employee separation (never deletes the employee) ----
  Future<dynamic> separation([Map<String, dynamic>? params]) => _api.get('/hr/separation${_query(params)}');
  Future<dynamic> createSeparation(Map<String, dynamic> body) => _api.post('/hr/separation', body: body);
}
