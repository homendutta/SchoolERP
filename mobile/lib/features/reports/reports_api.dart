import '../../core/api/api_client.dart';

/// Reports & Printing Center API surface for the mobile app (Sprint 21).
///
/// The one reporting + printing platform: a code-registered catalog of report
/// definitions run through a single Reporting Engine, exported through one Export
/// Engine (CSV/Excel) and printed through one Print/PDF Engine. Large + scheduled
/// exports use queues; scheduled delivery uses the Communication Engine; every
/// export is audited. Parents/students/teachers pull their permitted reports
/// (report cards, attendance, fee receipts, class reports); the module never owns
/// business data. The mobile app exposes the endpoints only (no UI).
class ReportsApi {
  ReportsApi(this._api);

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

  Future<dynamic> dashboard(Map<String, dynamic> params) => _api.get('/reports/dashboard${_query(params)}');

  /// The reusable report catalog (definitions registered per module).
  Future<dynamic> catalog() => _api.get('/reports/catalog');

  /// Execute a report (filters / sort / group / paginate / totals).
  Future<dynamic> run(Map<String, dynamic> body) => _api.post('/reports/run', body: body);

  /// Export a report (format: csv / xlsx). Pass queue:true to process large runs on the queue.
  Future<dynamic> export(Map<String, dynamic> body) => _api.post('/reports/export', body: body);

  /// The relative URL to POST for a print-ready HTML document (browser prints to PDF).
  String printPath() => '/reports/print';

  // ---- Saved reports ----
  Future<dynamic> saved([Map<String, dynamic>? params]) => _api.get('/reports/saved${_query(params)}');
  Future<dynamic> createSaved(Map<String, dynamic> body) => _api.post('/reports/saved', body: body);
  Future<dynamic> deleteSaved(int id) => _api.delete('/reports/saved/$id');

  // ---- Scheduled reports ----
  Future<dynamic> schedules([Map<String, dynamic>? params]) => _api.get('/reports/schedules${_query(params)}');
  Future<dynamic> createSchedule(Map<String, dynamic> body) => _api.post('/reports/schedules', body: body);
  Future<dynamic> runSchedule(int id) => _api.post('/reports/schedules/$id/run');

  // ---- Export history / queue ----
  Future<dynamic> exports([Map<String, dynamic>? params]) => _api.get('/reports/exports${_query(params)}');
}
