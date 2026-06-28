import '../../core/api/api_client.dart';

/// Attendance API surface for the mobile app.
///
/// One Attendance Engine, three sources (manual / import / biometric). People
/// are matched by Platform Identity Number — never by student/staff id. The
/// mobile app exposes the endpoints only (no UI in this sprint).
class AttendanceApi {
  AttendanceApi(this._api);

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

  /// Dashboard widgets + charts. [type] is 'student' or 'staff'.
  Future<dynamic> dashboard(String type, {int? schoolId}) =>
      _api.get('/attendance/dashboard${_query({'type': type, 'school_id': schoolId})}');

  /// Student attendance records (unified engine, scoped to students).
  Future<dynamic> student([Map<String, dynamic>? params]) =>
      _api.get('/attendance/student${_query(params)}');

  /// Staff attendance records (unified engine, scoped to staff).
  Future<dynamic> staff([Map<String, dynamic>? params]) =>
      _api.get('/attendance/staff${_query(params)}');

  /// Mark attendance manually (bulk). Body: { school_id, date, session_id,
  /// entries: [{ identity_id | identity_number, status }] }.
  Future<dynamic> mark(Map<String, dynamic> body) =>
      _api.post('/attendance/manual', body: body);

  /// Correct an attendance record through the authorized workflow.
  Future<dynamic> correct(int id, Map<String, dynamic> body) =>
      _api.put('/attendance/manual/$id', body: body);

  /// Import — validate rows (Upload → Validate → Preview → Import → Summary).
  Future<dynamic> importValidate(List<Map<String, dynamic>> rows) =>
      _api.post('/attendance/import/validate', body: {'rows': rows});

  /// Import — execute rows.
  Future<dynamic> importExecute(List<Map<String, dynamic>> rows) =>
      _api.post('/attendance/import/execute', body: {'rows': rows});

  /// Biometric devices (multiple per school).
  Future<dynamic> devices([Map<String, dynamic>? params]) =>
      _api.get('/attendance/devices${_query(params)}');

  Future<dynamic> createDevice(Map<String, dynamic> body) =>
      _api.post('/attendance/devices', body: body);

  Future<dynamic> updateDevice(int id, Map<String, dynamic> body) =>
      _api.put('/attendance/devices/$id', body: body);

  Future<dynamic> deleteDevice(int id) => _api.delete('/attendance/devices/$id');

  /// Real-time biometric event ingestion (vendor-independent). Body carries the
  /// Device, Identity Number, Timestamp and Direction; raw vendor payloads are
  /// normalized by the connector layer server-side.
  Future<dynamic> biometricEvent(Map<String, dynamic> body) =>
      _api.post('/attendance/biometric/events', body: body);

  /// Immutable biometric audit logs (read-only).
  Future<dynamic> biometricLogs([Map<String, dynamic>? params]) =>
      _api.get('/attendance/biometric/logs${_query(params)}');
}
