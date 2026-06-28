import '../../core/api/api_client.dart';

/// Finance & Fees API surface for the mobile app.
///
/// Fees (what is owed), Payments (what was paid) and the Ledger (accounting
/// impact) are kept as separate concepts. Receipt/transaction numbers come from
/// the Number Generator; payment methods from Master Data; the receipt QR from
/// the Identity Platform. The mobile app exposes the endpoints only (no UI in
/// this sprint).
class FinanceApi {
  FinanceApi(this._api);

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

  Future<dynamic> dashboard({int? schoolId}) => _api.get('/finance/dashboard${_query({'school_id': schoolId})}');

  // ---- Configurable definitions ----
  Future<dynamic> categories([Map<String, dynamic>? params]) => _api.get('/finance/categories${_query(params)}');
  Future<dynamic> createCategory(Map<String, dynamic> body) => _api.post('/finance/categories', body: body);
  Future<dynamic> masters([Map<String, dynamic>? params]) => _api.get('/finance/masters${_query(params)}');
  Future<dynamic> createMaster(Map<String, dynamic> body) => _api.post('/finance/masters', body: body);
  Future<dynamic> structures([Map<String, dynamic>? params]) => _api.get('/finance/structures${_query(params)}');
  Future<dynamic> createStructure(Map<String, dynamic> body) => _api.post('/finance/structures', body: body);
  Future<dynamic> updateStructure(int id, Map<String, dynamic> body) => _api.put('/finance/structures/$id', body: body);
  Future<dynamic> discounts([Map<String, dynamic>? params]) => _api.get('/finance/discounts${_query(params)}');
  Future<dynamic> scholarships([Map<String, dynamic>? params]) => _api.get('/finance/scholarships${_query(params)}');
  Future<dynamic> siblingDiscounts([Map<String, dynamic>? params]) => _api.get('/finance/sibling-discounts${_query(params)}');
  Future<dynamic> fines([Map<String, dynamic>? params]) => _api.get('/finance/fines${_query(params)}');
  Future<dynamic> installments([Map<String, dynamic>? params]) => _api.get('/finance/installments${_query(params)}');
  Future<dynamic> createInstallment(Map<String, dynamic> body) => _api.post('/finance/installments', body: body);

  // ---- Student fees + assignment + concessions ----
  Future<dynamic> studentFees([Map<String, dynamic>? params]) => _api.get('/finance/student-fees${_query(params)}');
  Future<dynamic> studentFee(int id) => _api.get('/finance/student-fees/$id');
  Future<dynamic> assignFee(Map<String, dynamic> body) => _api.post('/finance/student-fees/assign', body: body);
  Future<dynamic> applyDiscount(int id, int discountId) =>
      _api.post('/finance/student-fees/$id/discount', body: {'discount_id': discountId});
  Future<dynamic> applyScholarship(int id, int scholarshipId) =>
      _api.post('/finance/student-fees/$id/scholarship', body: {'scholarship_id': scholarshipId});
  Future<dynamic> applySibling(int id) => _api.post('/finance/student-fees/$id/sibling-discount');

  // ---- Payments + receipts ----
  Future<dynamic> payments([Map<String, dynamic>? params]) => _api.get('/finance/payments${_query(params)}');
  Future<dynamic> recordPayment(Map<String, dynamic> body) => _api.post('/finance/payments', body: body);
  Future<dynamic> receipt(int paymentId) => _api.get('/finance/payments/$paymentId/receipt');

  // ---- Refunds + adjustments ----
  Future<dynamic> refunds([Map<String, dynamic>? params]) => _api.get('/finance/refunds${_query(params)}');
  Future<dynamic> refund(Map<String, dynamic> body) => _api.post('/finance/refunds', body: body);
  Future<dynamic> adjustments([Map<String, dynamic>? params]) => _api.get('/finance/adjustments${_query(params)}');
  Future<dynamic> adjust(Map<String, dynamic> body) => _api.post('/finance/adjustments', body: body);

  // ---- Ledger, due tracking, defaulters ----
  Future<dynamic> ledger([Map<String, dynamic>? params]) => _api.get('/finance/ledger${_query(params)}');
  Future<dynamic> dueTracking(int studentId, {String? asOf}) =>
      _api.get('/finance/due-tracking${_query({'student_id': studentId, 'as_of': asOf})}');
  Future<dynamic> defaulters(Map<String, dynamic> params) => _api.get('/finance/defaulters${_query(params)}');

  // ---- Online payment gateway abstraction ----
  Future<dynamic> gateways() => _api.get('/finance/gateways');
  Future<dynamic> initiateGateway(Map<String, dynamic> body) => _api.post('/finance/gateways/initiate', body: body);
}
