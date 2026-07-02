import '../../core/api/api_client.dart';

/// Library API surface for the mobile app.
///
/// Catalog is never borrowed — physical copies are. Borrowers are resolved
/// through the Identity Platform (borrow/reserve take an identity number). The
/// mobile app exposes the endpoints only (no UI in this sprint).
class LibraryApi {
  LibraryApi(this._api);

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
      _api.get('/library/dashboard${_query(params)}');

  // ---- Catalog + reference CRUD ----
  Future<dynamic> catalog([Map<String, dynamic>? params]) => _api.get('/library/catalog${_query(params)}');
  Future<dynamic> createBook(Map<String, dynamic> body) => _api.post('/library/catalog', body: body);
  Future<dynamic> updateBook(int id, Map<String, dynamic> body) => _api.put('/library/catalog/$id', body: body);
  Future<dynamic> deleteBook(int id) => _api.delete('/library/catalog/$id');

  Future<dynamic> authors([Map<String, dynamic>? params]) => _api.get('/library/authors${_query(params)}');
  Future<dynamic> publishers([Map<String, dynamic>? params]) => _api.get('/library/publishers${_query(params)}');
  Future<dynamic> categories([Map<String, dynamic>? params]) => _api.get('/library/categories${_query(params)}');
  Future<dynamic> locations([Map<String, dynamic>? params]) => _api.get('/library/locations${_query(params)}');
  Future<dynamic> fineRules([Map<String, dynamic>? params]) => _api.get('/library/fine-rules${_query(params)}');

  // ---- Physical copies (each with its own Identity) ----
  Future<dynamic> copies([Map<String, dynamic>? params]) => _api.get('/library/copies${_query(params)}');
  Future<dynamic> createCopy(Map<String, dynamic> body) => _api.post('/library/copies', body: body);
  Future<dynamic> updateCopy(int id, Map<String, dynamic> body) => _api.put('/library/copies/$id', body: body);
  Future<dynamic> deleteCopy(int id) => _api.delete('/library/copies/$id');

  // ---- Circulation (borrower resolved via Identity Number) ----
  Future<dynamic> borrowings([Map<String, dynamic>? params]) => _api.get('/library/borrowings${_query(params)}');
  Future<dynamic> borrowing(int id) => _api.get('/library/borrowings/$id');
  Future<dynamic> borrow(Map<String, dynamic> body) => _api.post('/library/borrow', body: body);
  Future<dynamic> returnCopy(Map<String, dynamic> body) => _api.post('/library/return', body: body);
  Future<dynamic> renew(Map<String, dynamic> body) => _api.post('/library/renew', body: body);

  // ---- Reservations ----
  Future<dynamic> reservations([Map<String, dynamic>? params]) => _api.get('/library/reservations${_query(params)}');
  Future<dynamic> reserve(Map<String, dynamic> body) => _api.post('/library/reservations', body: body);
  Future<dynamic> cancelReservation(int id) => _api.post('/library/reservations/$id/cancel');

  // ---- Inventory verification ----
  Future<dynamic> inventory([Map<String, dynamic>? params]) => _api.get('/library/inventory${_query(params)}');
  Future<dynamic> recordInventory(Map<String, dynamic> body) => _api.post('/library/inventory', body: body);
  Future<dynamic> inventoryReport(Map<String, dynamic> params) => _api.get('/library/inventory/report${_query(params)}');

  // ---- Settings ----
  Future<dynamic> settings(Map<String, dynamic> params) => _api.get('/library/settings${_query(params)}');
  Future<dynamic> updateSettings(Map<String, dynamic> body) => _api.put('/library/settings', body: body);
}
