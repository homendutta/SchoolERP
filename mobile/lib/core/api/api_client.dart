import 'dart:convert';
import 'package:http/http.dart' as http;

/// Thrown on any non-success API response.
class ApiException implements Exception {
  ApiException(this.message, this.statusCode);
  final String message;
  final int statusCode;

  @override
  String toString() => message;
}

/// Single API client — the only integration point to the Laravel API, mirroring
/// the web client. Applies the Sanctum bearer token and the standard envelope.
class ApiClient {
  ApiClient(this.baseUrl, {this.tokenProvider});

  final String baseUrl;
  String? Function()? tokenProvider;

  Future<dynamic> get(String path) => _send('GET', path);

  Future<dynamic> post(String path, {Object? body}) => _send('POST', path, body: body);

  Future<dynamic> _send(String method, String path, {Object? body}) async {
    final uri = Uri.parse('$baseUrl$path');
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    final token = tokenProvider?.call();
    if (token != null) {
      headers['Authorization'] = 'Bearer $token';
    }

    final encoded = body != null ? jsonEncode(body) : null;
    final http.Response res;
    switch (method) {
      case 'POST':
        res = await http.post(uri, headers: headers, body: encoded);
      case 'GET':
      default:
        res = await http.get(uri, headers: headers);
    }

    Map<String, dynamic>? envelope;
    try {
      envelope = jsonDecode(res.body) as Map<String, dynamic>;
    } catch (_) {
      envelope = null;
    }

    final failed = res.statusCode >= 400 || envelope?['success'] == false;
    if (failed) {
      throw ApiException(
        envelope?['message']?.toString() ?? 'Request failed (${res.statusCode}).',
        res.statusCode,
      );
    }

    return envelope?['data'];
  }
}
