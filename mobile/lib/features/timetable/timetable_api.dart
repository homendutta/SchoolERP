import '../../core/api/api_client.dart';

/// Timetable API surface for the mobile app.
///
/// The class timetable is the single source of truth for the academic schedule;
/// teacher and room timetables are derived from it server-side, and writes run
/// clash detection. The mobile app exposes the endpoints only (no UI in this
/// sprint).
class TimetableApi {
  TimetableApi(this._api);

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
      _api.get('/timetable/dashboard${_query(params)}');

  // ---- Periods (configurable bell schedule) ----
  Future<dynamic> periods([Map<String, dynamic>? params]) =>
      _api.get('/timetable/periods${_query(params)}');
  Future<dynamic> createPeriod(Map<String, dynamic> body) => _api.post('/timetable/periods', body: body);
  Future<dynamic> updatePeriod(int id, Map<String, dynamic> body) => _api.put('/timetable/periods/$id', body: body);
  Future<dynamic> deletePeriod(int id) => _api.delete('/timetable/periods/$id');

  // ---- Working days (configurable per school) ----
  Future<dynamic> workingDays([Map<String, dynamic>? params]) =>
      _api.get('/timetable/working-days${_query(params)}');
  Future<dynamic> syncWorkingDays(Map<String, dynamic> body) =>
      _api.post('/timetable/working-days/sync', body: body);

  // ---- Templates + copy ----
  Future<dynamic> templates([Map<String, dynamic>? params]) =>
      _api.get('/timetable/templates${_query(params)}');
  Future<dynamic> createTemplate(Map<String, dynamic> body) => _api.post('/timetable/templates', body: body);
  Future<dynamic> updateTemplate(int id, Map<String, dynamic> body) => _api.put('/timetable/templates/$id', body: body);
  Future<dynamic> deleteTemplate(int id) => _api.delete('/timetable/templates/$id');

  /// Copy a timetable from one academic year / template into another.
  Future<dynamic> copyTimetable(Map<String, dynamic> body) =>
      _api.post('/timetable/templates/copy', body: body);

  // ---- Master class timetable (writes run clash detection) ----
  Future<dynamic> classTimetable([Map<String, dynamic>? params]) =>
      _api.get('/timetable/classes${_query(params)}');
  Future<dynamic> classGrid(Map<String, dynamic> params) =>
      _api.get('/timetable/classes/grid${_query(params)}');
  Future<dynamic> saveSlot(Map<String, dynamic> body) => _api.post('/timetable/classes', body: body);
  Future<dynamic> updateSlot(int id, Map<String, dynamic> body) => _api.put('/timetable/classes/$id', body: body);
  Future<dynamic> deleteSlot(int id) => _api.delete('/timetable/classes/$id');

  // ---- Derived teacher / room timetables ----
  Future<dynamic> teacherWorkloadOverview(Map<String, dynamic> params) =>
      _api.get('/timetable/teachers${_query(params)}');
  Future<dynamic> teacherTimetable(int teacherId, Map<String, dynamic> params) =>
      _api.get('/timetable/teachers/$teacherId${_query(params)}');
  Future<dynamic> roomTimetable(int roomId, Map<String, dynamic> params) =>
      _api.get('/timetable/rooms/$roomId${_query(params)}');

  // ---- Substitutions (separate records — never modify the master) ----
  Future<dynamic> substitutions([Map<String, dynamic>? params]) =>
      _api.get('/timetable/substitutions${_query(params)}');
  Future<dynamic> createSubstitution(Map<String, dynamic> body) =>
      _api.post('/timetable/substitutions', body: body);
  Future<dynamic> updateSubstitution(int id, Map<String, dynamic> body) =>
      _api.put('/timetable/substitutions/$id', body: body);
  Future<dynamic> deleteSubstitution(int id) => _api.delete('/timetable/substitutions/$id');

  // ---- Special events (overrides — stored separately) ----
  Future<dynamic> specialEvents([Map<String, dynamic>? params]) =>
      _api.get('/timetable/special-events${_query(params)}');
  Future<dynamic> createSpecialEvent(Map<String, dynamic> body) =>
      _api.post('/timetable/special-events', body: body);
  Future<dynamic> updateSpecialEvent(int id, Map<String, dynamic> body) =>
      _api.put('/timetable/special-events/$id', body: body);
  Future<dynamic> deleteSpecialEvent(int id) => _api.delete('/timetable/special-events/$id');
}
