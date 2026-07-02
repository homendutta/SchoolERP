import '../../core/api/api_client.dart';

/// Hostel API surface for the mobile app.
///
/// Students occupy beds (never rooms directly); a bed is single-occupant;
/// allocation/transfer history is preserved. Fees are collected by Finance;
/// notifications go through Communication. The mobile app exposes the endpoints
/// only (no UI in this sprint).
class HostelApi {
  HostelApi(this._api);

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

  Future<dynamic> dashboard(Map<String, dynamic> params) => _api.get('/hostel/dashboard${_query(params)}');
  Future<dynamic> occupancy(Map<String, dynamic> params) => _api.get('/hostel/occupancy${_query(params)}');

  // ---- Structure (hostels → buildings → floors → rooms → beds) ----
  Future<dynamic> hostels([Map<String, dynamic>? params]) => _api.get('/hostel/hostels${_query(params)}');
  Future<dynamic> createHostel(Map<String, dynamic> body) => _api.post('/hostel/hostels', body: body);
  Future<dynamic> buildings([Map<String, dynamic>? params]) => _api.get('/hostel/buildings${_query(params)}');
  Future<dynamic> createBuilding(Map<String, dynamic> body) => _api.post('/hostel/buildings', body: body);
  Future<dynamic> floors([Map<String, dynamic>? params]) => _api.get('/hostel/floors${_query(params)}');
  Future<dynamic> createFloor(Map<String, dynamic> body) => _api.post('/hostel/floors', body: body);
  Future<dynamic> rooms([Map<String, dynamic>? params]) => _api.get('/hostel/rooms${_query(params)}');
  Future<dynamic> createRoom(Map<String, dynamic> body) => _api.post('/hostel/rooms', body: body);
  Future<dynamic> beds([Map<String, dynamic>? params]) => _api.get('/hostel/beds${_query(params)}');
  Future<dynamic> createBed(Map<String, dynamic> body) => _api.post('/hostel/beds', body: body);

  // ---- Allocation (bed single-occupant) + transfers ----
  Future<dynamic> allocations([Map<String, dynamic>? params]) => _api.get('/hostel/allocations${_query(params)}');
  Future<dynamic> allocate(Map<String, dynamic> body) => _api.post('/hostel/allocations', body: body);
  Future<dynamic> checkout(int id) => _api.post('/hostel/allocations/$id/checkout');
  Future<dynamic> transfers([Map<String, dynamic>? params]) => _api.get('/hostel/transfers${_query(params)}');
  Future<dynamic> transfer(Map<String, dynamic> body) => _api.post('/hostel/transfers', body: body);

  // ---- Wardens (Staff) ----
  Future<dynamic> wardens([Map<String, dynamic>? params]) => _api.get('/hostel/wardens${_query(params)}');
  Future<dynamic> assignWarden(Map<String, dynamic> body) => _api.post('/hostel/wardens', body: body);

  // ---- Visitors (ID proof via Media) ----
  Future<dynamic> visitors([Map<String, dynamic>? params]) => _api.get('/hostel/visitors${_query(params)}');
  Future<dynamic> createVisitor(Map<String, dynamic> body) => _api.post('/hostel/visitors', body: body);
  Future<dynamic> updateVisitor(int id, Map<String, dynamic> body) => _api.put('/hostel/visitors/$id', body: body);

  // ---- Maintenance requests ----
  Future<dynamic> maintenance([Map<String, dynamic>? params]) => _api.get('/hostel/maintenance${_query(params)}');
  Future<dynamic> createMaintenance(Map<String, dynamic> body) => _api.post('/hostel/maintenance', body: body);
  Future<dynamic> updateMaintenance(int id, Map<String, dynamic> body) => _api.put('/hostel/maintenance/$id', body: body);

  // ---- Fees (defined here; collected by Finance) ----
  Future<dynamic> fees([Map<String, dynamic>? params]) => _api.get('/hostel/fees${_query(params)}');
  Future<dynamic> createFee(Map<String, dynamic> body) => _api.post('/hostel/fees', body: body);
}
