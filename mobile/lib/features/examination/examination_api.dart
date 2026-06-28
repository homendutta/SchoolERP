import '../../core/api/api_client.dart';

/// Examination API surface for the mobile app.
///
/// Covers the full examination lifecycle. Optional/elective subjects are honoured
/// server-side: a student is only ever marked/graded on their assigned subjects,
/// and report cards never show subjects a student did not take. The mobile app
/// exposes the endpoints only (no UI in this sprint).
class ExaminationApi {
  ExaminationApi(this._api);

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

  /// Dashboard widgets + charts.
  Future<dynamic> dashboard(Map<String, dynamic> params) =>
      _api.get('/examinations/dashboard${_query(params)}');

  // ---- Configurable masters: types, components, grades, report-card templates ----
  Future<dynamic> types([Map<String, dynamic>? params]) => _api.get('/examinations/types${_query(params)}');
  Future<dynamic> createType(Map<String, dynamic> body) => _api.post('/examinations/types', body: body);
  Future<dynamic> updateType(int id, Map<String, dynamic> body) => _api.put('/examinations/types/$id', body: body);
  Future<dynamic> deleteType(int id) => _api.delete('/examinations/types/$id');

  Future<dynamic> components([Map<String, dynamic>? params]) => _api.get('/examinations/components${_query(params)}');
  Future<dynamic> createComponent(Map<String, dynamic> body) => _api.post('/examinations/components', body: body);

  Future<dynamic> grades([Map<String, dynamic>? params]) => _api.get('/examinations/grades${_query(params)}');
  Future<dynamic> createGrade(Map<String, dynamic> body) => _api.post('/examinations/grades', body: body);

  Future<dynamic> reportCardTemplates([Map<String, dynamic>? params]) =>
      _api.get('/examinations/report-card-templates${_query(params)}');

  // ---- Sessions + lifecycle ----
  Future<dynamic> sessions([Map<String, dynamic>? params]) => _api.get('/examinations/sessions${_query(params)}');
  Future<dynamic> createSession(Map<String, dynamic> body) => _api.post('/examinations/sessions', body: body);
  Future<dynamic> updateSession(int id, Map<String, dynamic> body) => _api.put('/examinations/sessions/$id', body: body);
  Future<dynamic> deleteSession(int id) => _api.delete('/examinations/sessions/$id');
  Future<dynamic> assignSubjects(int id) => _api.post('/examinations/sessions/$id/assign-subjects');
  Future<dynamic> processResults(int id) => _api.post('/examinations/sessions/$id/process');
  Future<dynamic> publishResults(int id) => _api.post('/examinations/sessions/$id/publish');

  // ---- Subject mapping (+ per-student elective assignment) ----
  Future<dynamic> subjects([Map<String, dynamic>? params]) => _api.get('/examinations/subjects${_query(params)}');
  Future<dynamic> mapSubject(Map<String, dynamic> body) => _api.post('/examinations/subjects', body: body);
  Future<dynamic> subjectStudents(int id) => _api.get('/examinations/subjects/$id/students');
  Future<dynamic> assignStudent(int id, int studentId) =>
      _api.post('/examinations/subjects/$id/assign-student', body: {'student_id': studentId});
  Future<dynamic> unassignStudent(int id, int studentId) =>
      _api.post('/examinations/subjects/$id/unassign-student', body: {'student_id': studentId});

  // ---- Schedule (clash detection) + invigilators + seating ----
  Future<dynamic> schedules([Map<String, dynamic>? params]) => _api.get('/examinations/schedules${_query(params)}');
  Future<dynamic> createSchedule(Map<String, dynamic> body) => _api.post('/examinations/schedules', body: body);
  Future<dynamic> invigilators([Map<String, dynamic>? params]) => _api.get('/examinations/invigilators${_query(params)}');
  Future<dynamic> assignInvigilator(Map<String, dynamic> body) => _api.post('/examinations/invigilators', body: body);
  Future<dynamic> seating([Map<String, dynamic>? params]) => _api.get('/examinations/seating${_query(params)}');
  Future<dynamic> allocateSeat(Map<String, dynamic> body) => _api.post('/examinations/seating', body: body);

  // ---- Exam attendance (separate from daily) ----
  Future<dynamic> examAttendance([Map<String, dynamic>? params]) => _api.get('/examinations/attendance${_query(params)}');
  Future<dynamic> markExamAttendance(Map<String, dynamic> body) => _api.post('/examinations/attendance', body: body);

  // ---- Marks (manual + import) ----
  Future<dynamic> marks(int examSubjectId) => _api.get('/examinations/marks${_query({'exam_subject_id': examSubjectId})}');
  Future<dynamic> saveMarks(Map<String, dynamic> body) => _api.post('/examinations/marks', body: body);
  Future<dynamic> importMarksValidate(List<Map<String, dynamic>> rows) =>
      _api.post('/examinations/marks/import/validate', body: {'rows': rows});
  Future<dynamic> importMarksExecute(List<Map<String, dynamic>> rows) =>
      _api.post('/examinations/marks/import/execute', body: {'rows': rows});

  // ---- Results + report cards + tabulation + promotion readiness ----
  Future<dynamic> results([Map<String, dynamic>? params]) => _api.get('/examinations/results${_query(params)}');
  Future<dynamic> reportCard(int sessionId, int studentId) =>
      _api.get('/examinations/report-cards${_query({'exam_session_id': sessionId, 'student_id': studentId})}');
  Future<dynamic> tabulation(Map<String, dynamic> params) => _api.get('/examinations/tabulation${_query(params)}');
  Future<dynamic> promotionReadiness(Map<String, dynamic> params) =>
      _api.get('/examinations/promotion-readiness${_query(params)}');
}
