# 07 – Examination Domain

**Product:** SchoolERP · **Domain Model** · **Version:** 1.0 · **Last updated:** 2026-06-26

> Conceptual domain model. No tables, columns, SQL, or APIs. See
> [00-domain-overview.md](00-domain-overview.md) for conventions.

---

## 1. Purpose

The Examination domain owns **assessment**: the definition of exams, the capture and processing of
**marks**, the **publication** of results (with the publish-lock that protects them), and exam-related
outputs such as **hall tickets** and **marksheets**. It turns raw scores into grades, ranks, and
publishable outcomes.

---

## 2. Responsibilities

- Define **exams** (type, term, assessment type, grading scheme, curriculum stage, weightage, duration, passing criteria) for a class.
- Capture **marks** per student per subject, including theory/practical/internal/external components.
- Compute **grades, percentages, grade points, and ranks** (tie-aware) and keep a moderation/original-mark audit.
- Govern result **publication** and the **publish-lock** (published results become visible downstream and teacher edits are frozen).
- Produce **hall tickets** and **result/marksheet** outputs.

---

## 3. Owned Business Entities

| Entity (concept) | Responsibility / natural identity | Aggregate root |
|------------------|-----------------------------------|:--------------:|
| **Exam** | An assessment for a class/year with grading rules and publish state. | ✓ |
| **Mark** | A student's score for a subject in an exam (components, grade, rank, status); unique per exam/student/subject. | ✓ |
| **Hall Ticket** | An admit-card output for a student for an exam (schedule + identity). | ✓ |
| **Result / Marksheet** | The consolidated, published outcome for a student/class in an exam. | ✓ |

> Grading scheme and term enumerations are **configuration** referenced from Foundation/Settings, not
> owned as separate entities here.

---

## 4. Referenced Entities

| Referenced (by identity) | Owning domain | Why |
|--------------------------|---------------|-----|
| **Class** | Academic | An exam targets a class. |
| **Subject** | Academic | Marks are per subject. |
| **Teacher Assignment** | Academic | Authorizes which teacher may enter marks for a class+subject. |
| **Student** | Student | Marks, hall tickets, and results are per student. |
| **Academic Year / Term / Grading Scheme** | Foundation/Academic | Scope and grading configuration. |
| **User/Account, Audit** | Foundation | Authorization; result publish and unlock are audited. |
| **Branding / Settings** | Foundation | Hall ticket and marksheet branding. |

---

## 5. Relationships

- An **Exam** belongs to one **Class** and one **academic year**; it has many **Marks** (one per student per subject).
- A **Mark** references one **Exam**, one **Student**, and one **Subject**; teacher edit is gated by the **Teacher Assignment** and the exam's publish state.
- An **Exam** in published state exposes **Results** to students/parents and **locks** its **Marks** against teacher edits until unpublished.
- A **Hall Ticket** is produced for one **Student** and one **Exam**.
- A **Result / Marksheet** consolidates a **Student's** **Marks** across subjects for an **Exam** (and feeds report-card concepts).
- **Marks** carry rank within an exam+subject (tie-aware) and an original-mark snapshot for moderation audit.

---

## 6. Business Boundaries

**Inside:** exams, marks, grading/ranking computation, publish state, hall tickets, results/marksheets.

**Outside (not owned here):**
- **Class/Subject/Teacher Assignment** — owned by Academic (Examination references them and applies the assignment-based edit rule).
- **Student** identity — owned by Student.
- **Conduct/Discipline/Activities** — owned by Student (behavioural, not assessment); a report card may *present* them but Examination does not own them.
- **Branding assets** used on outputs — owned by Foundation.

**Consistency boundaries:**
- An Exam with its Marks is one boundary; the publish-lock state change is a controlled, audited transition affecting edit-ability of all its Marks.
- A Mark's components, computed grade/percentage/rank, and original-mark snapshot form one boundary; bulk mark entry upserts within it.

---

## 7. Dependency Rules

- Examination **depends on Academic** (class/subject/assignment), **Student** (students), and **Foundation** (authorization/audit/branding).
- Examination must **not depend on** Attendance, Finance, Communication, or Asset.
- Communication **references** exam events (result published) to notify; Reports **read** exam data (results, toppers, distribution, marksheets).
- The publish-lock is owned here; downstream visibility (student/parent) is a consequence enforced via permissions/scope (Foundation) and consumed by clients/Reports.
