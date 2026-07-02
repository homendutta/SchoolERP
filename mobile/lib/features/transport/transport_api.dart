import '../../core/api/api_client.dart';

/// Transport API surface for the mobile app.
///
/// Students are assigned to a route + stop (never a vehicle — the vehicle is
/// determined via the trip). Fees are collected by Finance; notifications go
/// through Communication. The mobile app exposes the endpoints only (no UI in
/// this sprint).
class TransportApi {
  TransportApi(this._api);

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

  Future<dynamic> dashboard(Map<String, dynamic> params) =>
      _api.get('/transport/dashboard${_query(params)}');

  // ---- Vehicles (number from the Number Generator; Media docs) ----
  Future<dynamic> vehicles([Map<String, dynamic>? params]) => _api.get('/transport/vehicles${_query(params)}');
  Future<dynamic> createVehicle(Map<String, dynamic> body) => _api.post('/transport/vehicles', body: body);
  Future<dynamic> updateVehicle(int id, Map<String, dynamic> body) => _api.put('/transport/vehicles/$id', body: body);
  Future<dynamic> deleteVehicle(int id) => _api.delete('/transport/vehicles/$id');

  // ---- Routes + stops ----
  Future<dynamic> routes([Map<String, dynamic>? params]) => _api.get('/transport/routes${_query(params)}');
  Future<dynamic> route(int id) => _api.get('/transport/routes/$id');
  Future<dynamic> createRoute(Map<String, dynamic> body) => _api.post('/transport/routes', body: body);
  Future<dynamic> stops([Map<String, dynamic>? params]) => _api.get('/transport/stops${_query(params)}');
  Future<dynamic> createStop(Map<String, dynamic> body) => _api.post('/transport/stops', body: body);

  // ---- Trips ----
  Future<dynamic> trips([Map<String, dynamic>? params]) => _api.get('/transport/trips${_query(params)}');
  Future<dynamic> createTrip(Map<String, dynamic> body) => _api.post('/transport/trips', body: body);
  Future<dynamic> updateTrip(int id, Map<String, dynamic> body) => _api.put('/transport/trips/$id', body: body);

  // ---- Drivers / attendants (Staff) ----
  Future<dynamic> drivers([Map<String, dynamic>? params]) => _api.get('/transport/drivers${_query(params)}');
  Future<dynamic> assignDriver(Map<String, dynamic> body) => _api.post('/transport/drivers', body: body);
  Future<dynamic> removeDriver(int id) => _api.delete('/transport/drivers/$id');

  // ---- Vehicle documents (Media references) ----
  Future<dynamic> documents([Map<String, dynamic>? params]) => _api.get('/transport/documents${_query(params)}');
  Future<dynamic> createDocument(Map<String, dynamic> body) => _api.post('/transport/documents', body: body);

  // ---- Student assignments (route + stop; capacity enforced) ----
  Future<dynamic> students([Map<String, dynamic>? params]) => _api.get('/transport/students${_query(params)}');
  Future<dynamic> assignStudent(Map<String, dynamic> body) => _api.post('/transport/students', body: body);
  Future<dynamic> cancelAssignment(int id) => _api.post('/transport/students/$id/cancel');

  // ---- Fees (defined here; collected by Finance) ----
  Future<dynamic> fees([Map<String, dynamic>? params]) => _api.get('/transport/fees${_query(params)}');
  Future<dynamic> createFee(Map<String, dynamic> body) => _api.post('/transport/fees', body: body);

  // ---- Maintenance schedules ----
  Future<dynamic> maintenance([Map<String, dynamic>? params]) => _api.get('/transport/maintenance${_query(params)}');
  Future<dynamic> createMaintenance(Map<String, dynamic> body) => _api.post('/transport/maintenance', body: body);
}
