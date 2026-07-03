import '../../core/api/api_client.dart';

/// Certificate & Document Management API surface for the mobile app (Sprint 20).
///
/// The single source of truth for official documents: versioned templates,
/// immutable generated documents (regeneration = new version), dynamic QR +
/// public verification (Identity Platform), digital-signature references (Media
/// Platform), issuance history and queued bulk generation. Students/parents view
/// & verify their certificates; permitted teachers may generate + verify. Public
/// verification needs no login and never exposes sensitive data.
class DocumentsApi {
  DocumentsApi(this._api);

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

  // ---- Public verification (no login) ----
  Future<dynamic> publicVerify(Map<String, dynamic> body) => _api.post('/public/document/verify', body: body);

  // ---- Dashboard ----
  Future<dynamic> dashboard(Map<String, dynamic> params) => _api.get('/documents/dashboard${_query(params)}');

  // ---- Configuration: categories, certificate types, versioned templates ----
  Future<dynamic> categories([Map<String, dynamic>? params]) => _api.get('/documents/categories${_query(params)}');
  Future<dynamic> createCategory(Map<String, dynamic> body) => _api.post('/documents/categories', body: body);
  Future<dynamic> certificateTypes([Map<String, dynamic>? params]) => _api.get('/documents/certificate-types${_query(params)}');
  Future<dynamic> createCertificateType(Map<String, dynamic> body) => _api.post('/documents/certificate-types', body: body);
  Future<dynamic> templates([Map<String, dynamic>? params]) => _api.get('/documents/templates${_query(params)}');
  Future<dynamic> createTemplate(Map<String, dynamic> body) => _api.post('/documents/templates', body: body);
  Future<dynamic> newTemplateVersion(int id, Map<String, dynamic> body) => _api.post('/documents/templates/$id/version', body: body);

  // ---- Generation (preview / generate / regenerate / bulk) ----
  Future<dynamic> preview(Map<String, dynamic> body) => _api.post('/documents/preview', body: body);
  Future<dynamic> generate(Map<String, dynamic> body) => _api.post('/documents/generate', body: body);
  Future<dynamic> regenerate(int id) => _api.post('/documents/history/$id/regenerate');
  Future<dynamic> bulk(Map<String, dynamic> body) => _api.post('/documents/bulk', body: body);

  // ---- History + verification ----
  Future<dynamic> history([Map<String, dynamic>? params]) => _api.get('/documents/history${_query(params)}');
  Future<dynamic> document(int id) => _api.get('/documents/history/$id');
  /// The document's QR is rendered dynamically as SVG (never stored).
  String qrUrl(int id) => '/documents/history/$id/qr';
  Future<dynamic> verify(Map<String, dynamic> body) => _api.post('/documents/verify', body: body);
}
