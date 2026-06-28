import '../../core/api/api_client.dart';

/// Staff module API surface for the mobile app.
///
/// Per the Sprint 6 scope the mobile app builds no Staff screens — it only
/// exposes the endpoints so future mobile features can read/maintain employees.
/// Every call returns the decoded `data` envelope.
class StaffApi {
  StaffApi(this._api);

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

  // --- Dashboard ---
  Future<dynamic> dashboard([Map<String, dynamic>? params]) =>
      _api.get('/staff/dashboard${_query(params)}');

  // --- Staff (created here — Staff Management owns creation) ---
  Future<dynamic> list([Map<String, dynamic>? params]) => _api.get('/staff${_query(params)}');
  Future<dynamic> getStaff(int id) => _api.get('/staff/$id');
  Future<dynamic> createStaff(Map<String, dynamic> body) => _api.post('/staff', body: body);
  Future<dynamic> updateStaff(int id, Map<String, dynamic> body) => _api.put('/staff/$id', body: body);

  // --- Qualifications ---
  Future<dynamic> qualifications([Map<String, dynamic>? params]) =>
      _api.get('/staff-qualifications${_query(params)}');
  Future<dynamic> addQualification(Map<String, dynamic> body) =>
      _api.post('/staff-qualifications', body: body);

  // --- Experience ---
  Future<dynamic> experience([Map<String, dynamic>? params]) =>
      _api.get('/staff-experience${_query(params)}');
  Future<dynamic> addExperience(Map<String, dynamic> body) =>
      _api.post('/staff-experience', body: body);

  // --- Documents ---
  Future<dynamic> documents([Map<String, dynamic>? params]) =>
      _api.get('/staff-documents${_query(params)}');
  Future<dynamic> addDocument(Map<String, dynamic> body) => _api.post('/staff-documents', body: body);

  // --- Timeline ---
  Future<dynamic> timeline(int staffId) => _api.get('/staff-timeline?staff_id=$staffId');

  // --- Import ---
  Future<dynamic> validateImport(List<Map<String, dynamic>> rows) =>
      _api.post('/staff-import/validate', body: {'rows': rows});
  Future<dynamic> executeImport(List<Map<String, dynamic>> rows) =>
      _api.post('/staff-import/execute', body: {'rows': rows});
}
