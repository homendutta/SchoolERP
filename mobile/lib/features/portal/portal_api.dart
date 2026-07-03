import '../../core/api/api_client.dart';

/// Parent / Student / Teacher Portal API surface for the mobile app (Sprint 18).
///
/// The portal is a pure CONSUMER of the ERP — every endpoint delegates to the
/// owning module and is isolated to the caller's own data (parents → linked
/// children, students → self, teachers → their responsibilities). Online fee
/// payment reuses the Finance Payment Engine + Gateway abstraction; parents may
/// pay for multiple children in one transaction; Finance stays the source of
/// truth. The app exposes the endpoints only (no UI redesign).
class PortalApi {
  PortalApi(this._api);

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

  // ---- Auth (reuses the Identity/Auth system) ----
  Future<dynamic> login(Map<String, dynamic> body) => _api.post('/portal/login', body: body);
  Future<dynamic> logout() => _api.post('/portal/logout');
  Future<dynamic> me() => _api.get('/portal/me');
  Future<dynamic> changePassword(Map<String, dynamic> body) => _api.post('/portal/change-password', body: body);

  // ---- Dashboard + shared feeds ----
  Future<dynamic> dashboard() => _api.get('/portal/dashboard');
  Future<dynamic> messages() => _api.get('/portal/messages');
  Future<dynamic> downloads() => _api.get('/portal/downloads');

  // ---- Student-scoped reads (isolation enforced server-side) ----
  Future<dynamic> attendance(int studentId) => _api.get('/portal/attendance${_query({'student_id': studentId})}');
  Future<dynamic> examinations(int studentId, [int? sessionId]) =>
      _api.get('/portal/examinations${_query({'student_id': studentId, 'session_id': sessionId})}');
  Future<dynamic> library(int studentId) => _api.get('/portal/library${_query({'student_id': studentId})}');
  Future<dynamic> transport(int studentId) => _api.get('/portal/transport${_query({'student_id': studentId})}');
  Future<dynamic> hostel(int studentId) => _api.get('/portal/hostel${_query({'student_id': studentId})}');
  Future<dynamic> timetable([int? studentId]) => _api.get('/portal/timetable${_query({'student_id': studentId})}');

  // ---- Profile ----
  Future<dynamic> profile() => _api.get('/portal/profile');
  Future<dynamic> updateProfile(Map<String, dynamic> body) => _api.put('/portal/profile', body: body);

  // ---- Finance: fees, history, receipts, online payment (parents + students) ----
  Future<dynamic> fees(int studentId) => _api.get('/portal/fees${_query({'student_id': studentId})}');
  Future<dynamic> feeHistory(int studentId) => _api.get('/portal/fees/history${_query({'student_id': studentId})}');
  Future<dynamic> receipt(int paymentId) => _api.get('/portal/fees/receipt/$paymentId');
  Future<dynamic> paymentGateways() => _api.get('/portal/payment-gateways');

  /// Pay one or more children's fees in a single transaction.
  /// `items`: `[{ 'student_id': int, 'amount': num, 'reference'?: String }]`.
  Future<dynamic> payFees(List<Map<String, dynamic>> items, {String? gateway}) =>
      _api.post('/portal/fees/pay', body: {'items': items, if (gateway != null) 'gateway': gateway});
}
