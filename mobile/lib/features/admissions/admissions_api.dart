import '../../core/api/api_client.dart';

/// Admissions module API surface for the mobile app.
///
/// Per the Sprint 4 scope the mobile app builds no Admission UI yet — it only
/// exposes the endpoints so future screens (and other features) can drive the
/// admission workflow. Every call returns the decoded `data` envelope.
class AdmissionsApi {
  AdmissionsApi(this._api);

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
      _api.get('/admissions/dashboard${_query(params)}');

  // --- Enquiries ---
  Future<dynamic> listEnquiries([Map<String, dynamic>? params]) =>
      _api.get('/admissions/enquiries${_query(params)}');
  Future<dynamic> createEnquiry(Map<String, dynamic> body) =>
      _api.post('/admissions/enquiries', body: body);

  // --- Applications ---
  Future<dynamic> listApplications([Map<String, dynamic>? params]) =>
      _api.get('/admissions/applications${_query(params)}');
  Future<dynamic> getApplication(int id) => _api.get('/admissions/applications/$id');
  Future<dynamic> createApplication(Map<String, dynamic> body) =>
      _api.post('/admissions/applications', body: body);
  Future<dynamic> submitApplication(int id) =>
      _api.post('/admissions/applications/$id/submit');

  // --- Documents ---
  Future<dynamic> listDocuments([Map<String, dynamic>? params]) =>
      _api.get('/admissions/documents${_query(params)}');
  Future<dynamic> createDocument(Map<String, dynamic> body) =>
      _api.post('/admissions/documents', body: body);

  // --- Verification ---
  Future<dynamic> verifyApplication(int id, Map<String, dynamic> body) =>
      _api.post('/admissions/verification/applications/$id', body: body);
  Future<dynamic> verifyDocument(int id, Map<String, dynamic> body) =>
      _api.post('/admissions/verification/documents/$id', body: body);
  Future<dynamic> verificationHistory(int id) =>
      _api.get('/admissions/verification/applications/$id/history');

  // --- Approval ---
  Future<dynamic> listWorkflowSteps([Map<String, dynamic>? params]) =>
      _api.get('/admissions/approval/workflow-steps${_query(params)}');
  Future<dynamic> startApproval(int id) =>
      _api.post('/admissions/approval/applications/$id/start');
  Future<dynamic> actOnStep(int stepId, Map<String, dynamic> body) =>
      _api.post('/admissions/approval/steps/$stepId/act', body: body);

  // --- Enrollment (transactional admission → student) ---
  Future<dynamic> enroll(int applicationId) => _api.post('/admissions/enroll/$applicationId');

  // --- Import ---
  Future<dynamic> validateImport(List<Map<String, dynamic>> rows) =>
      _api.post('/admissions/import/validate', body: {'rows': rows});
  Future<dynamic> executeImport(List<Map<String, dynamic>> rows) =>
      _api.post('/admissions/import/execute', body: {'rows': rows});
}
