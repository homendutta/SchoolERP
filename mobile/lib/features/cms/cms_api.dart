import '../../core/api/api_client.dart';

/// CMS & Public Portal API surface for the mobile app (Sprint 17).
///
/// Two surfaces: the READ-ONLY public content API (`/cms/public/*`) that the
/// static website consumes, and the RBAC-protected admin API (`/cms/*`) for
/// managing website settings, pages, notices, news, events, galleries, videos,
/// downloads, menus, forms, enquiries and submissions. Images use the Media
/// Platform; admission enquiries are captured only (Admissions is never
/// auto-written); contact forms flow through the Communication Engine. The mobile
/// app exposes the endpoints only (no UI).
class CmsApi {
  CmsApi(this._api);

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

  // ---- Public, read-only content (no auth) ----
  Future<dynamic> publicHomepage(Map<String, dynamic> params) => _api.get('/cms/public/homepage${_query(params)}');
  Future<dynamic> publicSettings(Map<String, dynamic> params) => _api.get('/cms/public/settings${_query(params)}');
  Future<dynamic> publicMenus(Map<String, dynamic> params) => _api.get('/cms/public/menus${_query(params)}');
  Future<dynamic> publicNotices(Map<String, dynamic> params) => _api.get('/cms/public/notices${_query(params)}');
  Future<dynamic> publicNews(Map<String, dynamic> params) => _api.get('/cms/public/news${_query(params)}');
  Future<dynamic> publicEvents(Map<String, dynamic> params) => _api.get('/cms/public/events${_query(params)}');
  Future<dynamic> publicGallery(Map<String, dynamic> params) => _api.get('/cms/public/gallery${_query(params)}');
  Future<dynamic> publicVideos(Map<String, dynamic> params) => _api.get('/cms/public/videos${_query(params)}');
  Future<dynamic> publicDownloads(Map<String, dynamic> params) => _api.get('/cms/public/downloads${_query(params)}');
  Future<dynamic> publicStaff(Map<String, dynamic> params) => _api.get('/cms/public/staff${_query(params)}');
  Future<dynamic> publicPage(String slug, Map<String, dynamic> params) => _api.get('/cms/public/pages/$slug${_query(params)}');

  // Public intake — contact submission + admission enquiry (enquiry only).
  Future<dynamic> submitForm(Map<String, dynamic> body) => _api.post('/cms/public/forms', body: body);
  Future<dynamic> submitEnquiry(Map<String, dynamic> body) => _api.post('/cms/public/enquiries', body: body);

  // ---- Admin dashboard + settings ----
  Future<dynamic> dashboard(Map<String, dynamic> params) => _api.get('/cms/dashboard${_query(params)}');
  Future<dynamic> settings(Map<String, dynamic> params) => _api.get('/cms/settings${_query(params)}');
  Future<dynamic> saveSettings(Map<String, dynamic> body) => _api.put('/cms/settings', body: body);

  // ---- Admin content CRUD ----
  Future<dynamic> categories([Map<String, dynamic>? params]) => _api.get('/cms/categories${_query(params)}');
  Future<dynamic> createCategory(Map<String, dynamic> body) => _api.post('/cms/categories', body: body);
  Future<dynamic> pages([Map<String, dynamic>? params]) => _api.get('/cms/pages${_query(params)}');
  Future<dynamic> createPage(Map<String, dynamic> body) => _api.post('/cms/pages', body: body);
  Future<dynamic> updatePage(int id, Map<String, dynamic> body) => _api.put('/cms/pages/$id', body: body);
  Future<dynamic> notices([Map<String, dynamic>? params]) => _api.get('/cms/notices${_query(params)}');
  Future<dynamic> createNotice(Map<String, dynamic> body) => _api.post('/cms/notices', body: body);
  Future<dynamic> updateNotice(int id, Map<String, dynamic> body) => _api.put('/cms/notices/$id', body: body);
  Future<dynamic> news([Map<String, dynamic>? params]) => _api.get('/cms/news${_query(params)}');
  Future<dynamic> createNews(Map<String, dynamic> body) => _api.post('/cms/news', body: body);
  Future<dynamic> updateNews(int id, Map<String, dynamic> body) => _api.put('/cms/news/$id', body: body);
  Future<dynamic> events([Map<String, dynamic>? params]) => _api.get('/cms/events${_query(params)}');
  Future<dynamic> createEvent(Map<String, dynamic> body) => _api.post('/cms/events', body: body);
  Future<dynamic> updateEvent(int id, Map<String, dynamic> body) => _api.put('/cms/events/$id', body: body);
  Future<dynamic> gallery([Map<String, dynamic>? params]) => _api.get('/cms/gallery${_query(params)}');
  Future<dynamic> createGallery(Map<String, dynamic> body) => _api.post('/cms/gallery', body: body);
  Future<dynamic> updateGallery(int id, Map<String, dynamic> body) => _api.put('/cms/gallery/$id', body: body);
  Future<dynamic> videos([Map<String, dynamic>? params]) => _api.get('/cms/videos${_query(params)}');
  Future<dynamic> createVideo(Map<String, dynamic> body) => _api.post('/cms/videos', body: body);
  Future<dynamic> downloads([Map<String, dynamic>? params]) => _api.get('/cms/downloads${_query(params)}');
  Future<dynamic> createDownload(Map<String, dynamic> body) => _api.post('/cms/downloads', body: body);
  Future<dynamic> menus([Map<String, dynamic>? params]) => _api.get('/cms/menus${_query(params)}');
  Future<dynamic> createMenu(Map<String, dynamic> body) => _api.post('/cms/menus', body: body);
  Future<dynamic> forms([Map<String, dynamic>? params]) => _api.get('/cms/forms${_query(params)}');
  Future<dynamic> createForm(Map<String, dynamic> body) => _api.post('/cms/forms', body: body);

  // ---- Enquiries + submissions (captured from the public site) ----
  Future<dynamic> enquiries([Map<String, dynamic>? params]) => _api.get('/cms/enquiries${_query(params)}');
  Future<dynamic> updateEnquiry(int id, Map<String, dynamic> body) => _api.put('/cms/enquiries/$id', body: body);
  Future<dynamic> submissions([Map<String, dynamic>? params]) => _api.get('/cms/submissions${_query(params)}');
  Future<dynamic> updateSubmission(int id, Map<String, dynamic> body) => _api.put('/cms/submissions/$id', body: body);
}
