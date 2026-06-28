import '../../core/api/api_client.dart';

/// Students module API surface for the mobile app.
///
/// Per the Sprint 5 scope the mobile app builds no Student screens — it only
/// exposes the endpoints so future mobile features can drive the student
/// lifecycle. Every call returns the decoded `data` envelope.
class StudentsApi {
  StudentsApi(this._api);

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
      _api.get('/students/dashboard${_query(params)}');

  // --- Search / profile ---
  Future<dynamic> list([Map<String, dynamic>? params]) => _api.get('/students${_query(params)}');
  Future<dynamic> getStudent(int id) => _api.get('/students/$id');
  Future<dynamic> updateStudent(int id, Map<String, dynamic> body) => _api.put('/students/$id', body: body);
  Future<dynamic> idCard(int id) => _api.get('/students/$id/id-card');
  Future<dynamic> qr(int id) => _api.get('/students/$id/qr');

  // --- Timeline ---
  Future<dynamic> timeline(int studentId) => _api.get('/student-timeline?student_id=$studentId');

  // --- Medical ---
  Future<dynamic> updateMedical(int id, Map<String, dynamic> body) =>
      _api.put('/student-medical/$id', body: body);

  // --- Documents ---
  Future<dynamic> documents([Map<String, dynamic>? params]) =>
      _api.get('/student-documents${_query(params)}');
  Future<dynamic> addDocument(Map<String, dynamic> body) => _api.post('/student-documents', body: body);

  // --- Academic records (immutable history) ---
  Future<dynamic> academicRecords([Map<String, dynamic>? params]) =>
      _api.get('/student-academic-records${_query(params)}');

  // --- Transfers / withdrawals / promotion ---
  Future<dynamic> transfers([Map<String, dynamic>? params]) =>
      _api.get('/student-transfer${_query(params)}');
  Future<dynamic> transfer(int id, Map<String, dynamic> body) =>
      _api.post('/student-transfer/$id', body: body);
  Future<dynamic> withdrawals([Map<String, dynamic>? params]) =>
      _api.get('/student-withdrawal${_query(params)}');
  Future<dynamic> withdraw(int id, Map<String, dynamic> body) =>
      _api.post('/student-withdrawal/$id', body: body);
  Future<dynamic> promote(int id, Map<String, dynamic> body) =>
      _api.post('/student-promotion/$id', body: body);

  // --- Import (migration mode) ---
  Future<dynamic> validateImport(List<Map<String, dynamic>> rows) =>
      _api.post('/student-import/validate', body: {'rows': rows});
  Future<dynamic> executeImport(List<Map<String, dynamic>> rows) =>
      _api.post('/student-import/execute', body: {'rows': rows});
}
