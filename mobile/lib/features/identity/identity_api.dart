import '../../core/api/api_client.dart';

/// Platform Identity API surface for the mobile app.
///
/// The Identity Service is the single source of truth for QR codes and
/// barcodes. The mobile app exposes the endpoints only (no UI in this sprint);
/// future verification/scanning features reuse these.
class IdentityApi {
  IdentityApi(this._api);

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

  /// Search identities (by number, public identifier, owner, type, status).
  Future<dynamic> search([Map<String, dynamic>? params]) =>
      _api.get('/identity/search${_query(params)}');

  /// Identity details.
  Future<dynamic> get(int id) => _api.get('/identity/$id');

  /// QR payload (data only — pass format=payload). The raw image is served as
  /// SVG at `/identity/$id/qr` and `/identity/$id/barcode`.
  Future<dynamic> qrPayload(int id) => _api.get('/identity/$id/qr?format=payload');

  /// Regenerate derived QR/barcode data (immutable fields preserved).
  Future<dynamic> regenerate(int id) => _api.post('/identity/regenerate', body: {'identity_id': id});

  /// Enable / disable an identity.
  Future<dynamic> setStatus(int id, String status) =>
      _api.post('/identity/$id/status', body: {'status': status});
}
