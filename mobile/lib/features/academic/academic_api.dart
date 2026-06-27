import '../../core/api/api_client.dart';

/// Academic module API surface for the mobile app.
///
/// Per the Sprint 3 scope the mobile app does NOT build Academic screens — it
/// only exposes the endpoints so other features (and future screens) can read
/// the academic structure. Every call returns the decoded `data` envelope from
/// the shared [ApiClient].
class AcademicApi {
  AcademicApi(this._api);

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

  // --- Academic Years ---
  Future<dynamic> listAcademicYears([Map<String, dynamic>? params]) =>
      _api.get('/academic/academic-years${_query(params)}');
  Future<dynamic> createAcademicYear(Map<String, dynamic> body) =>
      _api.post('/academic/academic-years', body: body);
  Future<dynamic> updateAcademicYear(int id, Map<String, dynamic> body) =>
      _api.put('/academic/academic-years/$id', body: body);
  Future<dynamic> setCurrentAcademicYear(int id) =>
      _api.post('/academic/academic-years/$id/set-current');

  // --- Terms ---
  Future<dynamic> listTerms([Map<String, dynamic>? params]) => _api.get('/academic/terms${_query(params)}');

  // --- Academic Calendar (calendars, events, holiday types) ---
  Future<dynamic> listCalendars([Map<String, dynamic>? params]) =>
      _api.get('/academic/academic-calendar/calendars${_query(params)}');
  Future<dynamic> listCalendarEvents([Map<String, dynamic>? params]) =>
      _api.get('/academic/academic-calendar/events${_query(params)}');
  Future<dynamic> listHolidayTypes([Map<String, dynamic>? params]) =>
      _api.get('/academic/academic-calendar/holiday-types${_query(params)}');

  // --- Classes & Sections & Rooms ---
  Future<dynamic> listClasses([Map<String, dynamic>? params]) =>
      _api.get('/academic/classes${_query(params)}');
  Future<dynamic> listSections([Map<String, dynamic>? params]) =>
      _api.get('/academic/sections${_query(params)}');
  Future<dynamic> listRooms([Map<String, dynamic>? params]) => _api.get('/academic/rooms${_query(params)}');

  // --- Subjects & Subject Groups ---
  Future<dynamic> listSubjects([Map<String, dynamic>? params]) =>
      _api.get('/academic/subjects${_query(params)}');
  Future<dynamic> listSubjectGroups([Map<String, dynamic>? params]) =>
      _api.get('/academic/subject-groups${_query(params)}');

  // --- Teacher & Class-Teacher assignments ---
  Future<dynamic> listTeacherSubjectAssignments([Map<String, dynamic>? params]) =>
      _api.get('/academic/teacher-subject-assignments${_query(params)}');
  Future<dynamic> listClassTeachers([Map<String, dynamic>? params]) =>
      _api.get('/academic/class-teachers${_query(params)}');
  Future<dynamic> classTeacherHistory(Map<String, dynamic> params) =>
      _api.get('/academic/class-teachers/history${_query(params)}');
  Future<dynamic> assignClassTeacher(Map<String, dynamic> body) =>
      _api.post('/academic/class-teachers', body: body);
}
