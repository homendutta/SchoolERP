import '../../core/api/api_client.dart';

/// Payroll API surface for the mobile app (Sprint 16B).
///
/// Salary components/structures, employee salary assignments + revisions
/// (historical, immutable versions), overtime, loans/advances, arrears, statutory
/// components, and the idempotent Payroll Engine (runs → payslips). Payroll
/// CONSUMES HR (salary structures), Attendance and Leave (read-only) and Finance
/// (settlement recorded elsewhere) — it never edits them. A locked run is
/// immutable. Numbers come from the Number Generator; payslip QR uses the
/// Identity Platform. The mobile app exposes the endpoints only (no UI).
class PayrollApi {
  PayrollApi(this._api);

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

  Future<dynamic> dashboard(Map<String, dynamic> params) => _api.get('/payroll/dashboard${_query(params)}');

  // ---- Salary components + versioned structures ----
  Future<dynamic> components([Map<String, dynamic>? params]) => _api.get('/payroll/components${_query(params)}');
  Future<dynamic> createComponent(Map<String, dynamic> body) => _api.post('/payroll/components', body: body);
  Future<dynamic> updateComponent(int id, Map<String, dynamic> body) => _api.put('/payroll/components/$id', body: body);
  Future<dynamic> structures([Map<String, dynamic>? params]) => _api.get('/payroll/structures${_query(params)}');
  Future<dynamic> structure(int id) => _api.get('/payroll/structures/$id');
  Future<dynamic> createStructure(Map<String, dynamic> body) => _api.post('/payroll/structures', body: body);
  Future<dynamic> updateStructure(int id, Map<String, dynamic> body) => _api.put('/payroll/structures/$id', body: body);

  // ---- Employee salary assignments (historical) + revisions (immutable versions) ----
  Future<dynamic> assignments([Map<String, dynamic>? params]) => _api.get('/payroll/assignments${_query(params)}');
  Future<dynamic> createAssignment(Map<String, dynamic> body) => _api.post('/payroll/assignments', body: body);
  Future<dynamic> revisions([Map<String, dynamic>? params]) => _api.get('/payroll/revisions${_query(params)}');
  Future<dynamic> createRevision(Map<String, dynamic> body) => _api.post('/payroll/revisions', body: body);

  // ---- Overtime (approved only) + arrears ----
  Future<dynamic> overtime([Map<String, dynamic>? params]) => _api.get('/payroll/overtime${_query(params)}');
  Future<dynamic> createOvertime(Map<String, dynamic> body) => _api.post('/payroll/overtime', body: body);
  Future<dynamic> arrears([Map<String, dynamic>? params]) => _api.get('/payroll/arrears${_query(params)}');
  Future<dynamic> createArrear(Map<String, dynamic> body) => _api.post('/payroll/arrears', body: body);

  // ---- Loans / advances (Finance owns the cash movement) ----
  Future<dynamic> loans([Map<String, dynamic>? params]) => _api.get('/payroll/loans${_query(params)}');
  Future<dynamic> createLoan(Map<String, dynamic> body) => _api.post('/payroll/loans', body: body);
  Future<dynamic> approveLoan(int id) => _api.post('/payroll/loans/$id/approve');

  // ---- Statutory components (config only; never hardcoded rates) ----
  Future<dynamic> statutory([Map<String, dynamic>? params]) => _api.get('/payroll/statutory${_query(params)}');
  Future<dynamic> createStatutory(Map<String, dynamic> body) => _api.post('/payroll/statutory', body: body);

  // ---- Payroll runs — the engine processes (idempotent) and locks ----
  Future<dynamic> runs([Map<String, dynamic>? params]) => _api.get('/payroll/runs${_query(params)}');
  Future<dynamic> run(int id) => _api.get('/payroll/runs/$id');
  Future<dynamic> createRun(Map<String, dynamic> body) => _api.post('/payroll/runs', body: body);
  Future<dynamic> processRun(int id) => _api.post('/payroll/runs/$id/process');
  Future<dynamic> lockRun(int id) => _api.post('/payroll/runs/$id/lock');

  // ---- Payslips — structured data (no PDF); QR via the Identity Platform ----
  Future<dynamic> payslips([Map<String, dynamic>? params]) => _api.get('/payroll/payslips${_query(params)}');
  Future<dynamic> payslip(int id) => _api.get('/payroll/payslips/$id');
  Future<dynamic> settlePayslip(int id, Map<String, dynamic> body) => _api.post('/payroll/payslips/$id/settle', body: body);
}
