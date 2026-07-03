import '../../core/api/api_client.dart';

/// Learning Management System API surface for the mobile app (Sprint 19).
///
/// Portal-authenticated (no public APIs). Teachers publish lessons/materials/
/// homework/assignments/quizzes and review submissions for their assigned
/// subjects; students submit work, attempt quizzes and join discussions for their
/// own records; parents monitor their children. Files use the Media Platform;
/// homework/assignments/quizzes are INDEPENDENT of the Examination module. The
/// app reuses the existing architecture (no redesign).
class LmsApi {
  LmsApi(this._api);

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

  // ---- Dashboard + progress ----
  Future<dynamic> dashboard() => _api.get('/lms/dashboard');
  Future<dynamic> progress(int studentId) => _api.get('/lms/progress${_query({'student_id': studentId})}');

  // ---- Lessons + plans + materials (teachers publish; students view) ----
  Future<dynamic> lessonPlans([Map<String, dynamic>? params]) => _api.get('/lms/lesson-plans${_query(params)}');
  Future<dynamic> createLessonPlan(Map<String, dynamic> body) => _api.post('/lms/lesson-plans', body: body);
  Future<dynamic> lessons([Map<String, dynamic>? params]) => _api.get('/lms/lessons${_query(params)}');
  Future<dynamic> createLesson(Map<String, dynamic> body) => _api.post('/lms/lessons', body: body);
  Future<dynamic> materials([Map<String, dynamic>? params]) => _api.get('/lms/materials${_query(params)}');
  Future<dynamic> createMaterial(Map<String, dynamic> body) => _api.post('/lms/materials', body: body);

  // ---- Homework + assignments ----
  Future<dynamic> homework([Map<String, dynamic>? params]) => _api.get('/lms/homework${_query(params)}');
  Future<dynamic> createHomework(Map<String, dynamic> body) => _api.post('/lms/homework', body: body);
  Future<dynamic> assignments([Map<String, dynamic>? params]) => _api.get('/lms/assignments${_query(params)}');
  Future<dynamic> createAssignment(Map<String, dynamic> body) => _api.post('/lms/assignments', body: body);

  // ---- Student submissions (immutable versions) + teacher reviews ----
  Future<dynamic> submissionHistory(String type, int submittableId, int studentId) =>
      _api.get('/lms/submissions${_query({'type': type, 'submittable_id': submittableId, 'student_id': studentId})}');
  Future<dynamic> submit(Map<String, dynamic> body) => _api.post('/lms/submissions', body: body);
  Future<dynamic> review(Map<String, dynamic> body) => _api.post('/lms/reviews', body: body);

  // ---- Classroom resources ----
  Future<dynamic> resources([Map<String, dynamic>? params]) => _api.get('/lms/resources${_query(params)}');
  Future<dynamic> createResource(Map<String, dynamic> body) => _api.post('/lms/resources', body: body);

  // ---- Quizzes + attempts (learning quizzes, not Examination exams) ----
  Future<dynamic> quizzes([Map<String, dynamic>? params]) => _api.get('/lms/quizzes${_query(params)}');
  Future<dynamic> quiz(int id) => _api.get('/lms/quizzes/$id');
  Future<dynamic> createQuiz(Map<String, dynamic> body) => _api.post('/lms/quizzes', body: body);
  Future<dynamic> attempts(int quizId, int studentId) =>
      _api.get('/lms/attempts${_query({'quiz_id': quizId, 'student_id': studentId})}');
  Future<dynamic> attempt(Map<String, dynamic> body) => _api.post('/lms/attempts', body: body);

  // ---- Classroom discussions (+ replies + moderation) ----
  Future<dynamic> discussions([Map<String, dynamic>? params]) => _api.get('/lms/discussions${_query(params)}');
  Future<dynamic> discussion(int id) => _api.get('/lms/discussions/$id');
  Future<dynamic> createDiscussion(Map<String, dynamic> body) => _api.post('/lms/discussions', body: body);
  Future<dynamic> postReply(int discussionId, Map<String, dynamic> body) =>
      _api.post('/lms/discussions/$discussionId/posts', body: body);
  Future<dynamic> moderatePost(int postId) => _api.post('/lms/discussions/posts/$postId/moderate');
}
