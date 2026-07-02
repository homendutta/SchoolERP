import '../../core/api/api_client.dart';

/// Inventory & Asset API surface for the mobile app.
///
/// Physical assets each have their own permanent Identity (barcode/QR);
/// consumables never do. Assignments/transfers/verifications/disposals are
/// historical. Accounting/depreciation belong to Finance; notifications go
/// through Communication. The mobile app exposes the endpoints only (no UI).
class InventoryApi {
  InventoryApi(this._api);

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

  Future<dynamic> dashboard(Map<String, dynamic> params) => _api.get('/inventory/dashboard${_query(params)}');

  // ---- Catalog ----
  Future<dynamic> categories([Map<String, dynamic>? params]) => _api.get('/inventory/categories${_query(params)}');
  Future<dynamic> createCategory(Map<String, dynamic> body) => _api.post('/inventory/categories', body: body);
  Future<dynamic> models([Map<String, dynamic>? params]) => _api.get('/inventory/models${_query(params)}');
  Future<dynamic> createModel(Map<String, dynamic> body) => _api.post('/inventory/models', body: body);
  Future<dynamic> vendors([Map<String, dynamic>? params]) => _api.get('/inventory/vendors${_query(params)}');
  Future<dynamic> createVendor(Map<String, dynamic> body) => _api.post('/inventory/vendors', body: body);

  // ---- Physical assets (own Identity) + consumables (no Identity) ----
  Future<dynamic> assets([Map<String, dynamic>? params]) => _api.get('/inventory/assets${_query(params)}');
  Future<dynamic> asset(int id) => _api.get('/inventory/assets/$id');
  Future<dynamic> createAsset(Map<String, dynamic> body) => _api.post('/inventory/assets', body: body);
  Future<dynamic> updateAsset(int id, Map<String, dynamic> body) => _api.put('/inventory/assets/$id', body: body);

  // ---- Asset lifecycle (state machine): current state + immutable history ----
  // States: draft, ordered, received, available, assigned, reserved,
  // in_maintenance, lost, stolen, disposed. Every transition is audited +
  // written to the asset Timeline; assets are never physically deleted.
  Future<dynamic> assetLifecycle(int id) => _api.get('/inventory/assets/$id/lifecycle');
  Future<dynamic> transitionAsset(int id, Map<String, dynamic> body) =>
      _api.post('/inventory/assets/$id/lifecycle', body: body);
  Future<dynamic> consumables([Map<String, dynamic>? params]) => _api.get('/inventory/consumables${_query(params)}');
  Future<dynamic> createConsumable(Map<String, dynamic> body) => _api.post('/inventory/consumables', body: body);

  // ---- Stock movements (append-only) ----
  Future<dynamic> movements([Map<String, dynamic>? params]) => _api.get('/inventory/movements${_query(params)}');
  Future<dynamic> recordMovement(Map<String, dynamic> body) => _api.post('/inventory/movements', body: body);

  // ---- Assignments (historical) + transfers ----
  // Assignment resolves through the Platform Identity Service — never coupled to
  // Staff/Room/Hostel primary keys. Person targets pass `identity_number`;
  // non-person targets (department/room/hostel/library/laboratory) pass a
  // decoupled `target_reference`. Transfers create new records; history is never
  // overwritten.
  Future<dynamic> assignments([Map<String, dynamic>? params]) => _api.get('/inventory/assignments${_query(params)}');
  Future<dynamic> assign(Map<String, dynamic> body) => _api.post('/inventory/assignments', body: body);
  Future<dynamic> returnAsset(int id) => _api.post('/inventory/assignments/$id/return');
  Future<dynamic> transfers([Map<String, dynamic>? params]) => _api.get('/inventory/transfers${_query(params)}');
  Future<dynamic> transfer(Map<String, dynamic> body) => _api.post('/inventory/transfers', body: body);

  // ---- Maintenance (reusable Platform Maintenance Engine) + warranties ----
  // Maintenance is served by the shared Maintenance Engine (preventive/corrective/
  // emergency; scheduling, assigned staff, priority, cost, resolution, audit,
  // timeline, communication) — reusable by future modules. Inventory consumes it.
  Future<dynamic> maintenance([Map<String, dynamic>? params]) => _api.get('/inventory/maintenance${_query(params)}');
  Future<dynamic> maintenanceRecord(int id) => _api.get('/inventory/maintenance/$id');
  Future<dynamic> createMaintenance(Map<String, dynamic> body) => _api.post('/inventory/maintenance', body: body);
  Future<dynamic> updateMaintenance(int id, Map<String, dynamic> body) => _api.put('/inventory/maintenance/$id', body: body);
  Future<dynamic> warranties([Map<String, dynamic>? params]) => _api.get('/inventory/warranties${_query(params)}');
  Future<dynamic> createWarranty(Map<String, dynamic> body) => _api.post('/inventory/warranties', body: body);

  // ---- Verification + disposal (historical) ----
  Future<dynamic> verifications([Map<String, dynamic>? params]) => _api.get('/inventory/verifications${_query(params)}');
  Future<dynamic> verify(Map<String, dynamic> body) => _api.post('/inventory/verifications', body: body);
  Future<dynamic> verificationReport(Map<String, dynamic> params) => _api.get('/inventory/verifications/report${_query(params)}');
  Future<dynamic> disposals([Map<String, dynamic>? params]) => _api.get('/inventory/disposals${_query(params)}');
  Future<dynamic> dispose(Map<String, dynamic> body) => _api.post('/inventory/disposals', body: body);
}
