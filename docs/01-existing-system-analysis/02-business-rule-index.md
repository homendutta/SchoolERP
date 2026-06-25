# 02 – Business Rule Index (Reference Application)

> **Scope:** A catalogue of **every business rule already implemented** in the existing Google
> Apps Script School Management System. This documents *what exists* — no redesign, no new rules,
> no suggestions. Rules are extracted directly from `reference/original/Code.gs`.
>
> **Legend of categories per module:** Business Rules · Validation Rules · Status Transitions ·
> Automatic Actions · Permission Rules · Calculations · Generated IDs · Locking Rules ·
> Approval Rules · Notification Rules · Report Dependencies. (A category is omitted for a module
> when the code contains nothing for it.)

---

## 0. Cross-Cutting / Platform-Wide Rules

These apply across every module unless overridden.

### Permission Rules (global model)
- Every endpoint takes `(…, currentUser, currentRole)` and gates on a `can*(role)` helper or `isAdmin`/`isAdminOrClerk` before any read/write.
- Roles: **admin, clerk, teacher, supervisor, student, parent** (case-insensitive, trimmed).
- `isAdmin` = role `admin`. `isAdminOrClerk` = admin or clerk.
- Read vs. write are separate gates (`canRead*` / `canWrite*`); write is almost always narrower.
- **Student/parent scoping** (`getViewerScope`): students see only their own `studentId`/`classId`; parents see only linked children's ids + those children's classes; all other roles get `{all:true}`.
- **Teacher scoping**: limited to assigned classes (`getTeacherClassIds`) and assigned class+subject pairs (`getTeacherAssignmentsMap`, keyed `classId|subjectId`).

### Soft-Delete Rule
- Most sheets use an `IsDeleted` flag (`"1"` = deleted); deleted rows are skipped on every read and excluded from uniqueness checks.
- **Hard-delete (row stays, no `IsDeleted`)** by design: Teacher_Assignments, Parent_Students, Marks, Complaints, Helpdesk_Tickets, Teaching_Logbook, Attendance, Stock_Transactions, PTM bookings (status-based), Account dues.

### Generated IDs (global patterns)
- **Numeric PK**: `next*Id(sheet)` = max(existing ID in col 0) + 1 (per-sheet auto-increment).
- **Prefixed sequential codes** (`generatePrefixedCode`): `<PREFIX>-<YEAR>-<8-digit zero-padded seq>`, sequence scoped to current calendar year. Used for Complaints (`CMP`), Helpdesk (`TKT`).
- **Receipt numbers** (`generateReceiptNumber`): `RCP-<YEAR>-<8-digit seq>`.
- Module-specific formats (Admissions `REG-…`, `REGF-…`, `ADMF-…`) listed under each module.

### Automatic Actions (global)
- `addLog(user, action, details)` writes an audit row to **Logs** after virtually every create/update/delete/login.
- Date values normalised to ISO 8601 via `toIso()`; date/time columns "pinned" as text (`@` number format) to stop Sheets auto-converting them.

### Calculations (global helpers)
- `computeGrade(obtained, max, isAbsent)` → 7-band scale: ≥90 `A+`, ≥80 `A`, ≥70 `B+`, ≥60 `B`, ≥50 `C`, ≥40 `D`, else `F`; absent → `AB`.
- `computePaymentStatus(paid, expected)` → `paid` (paid ≥ expected, or expected ≤ 0), `pending` (paid ≤ 0), else `partial`.
- `validAcademicYear` → must match `YYYY-YYYY`.

---

## 1. Authentication & Session

**Business Rules**
- Unified login tries three sources in order: **Users (staff) → Students → Parents**; first match wins.
- Staff login key = `Username`; student key = `AdmissionNumber` OR `Email`; parent key = `Mobile` OR `Email` (all case-insensitive, trimmed).
- Passwords stored/compared as plain text (`String()` cast both sides) — per Apps Script constraint.
- Student & parent records are **mirrored into Users** (`Role='student'/'parent'`, linked by `EmployeeCode = STU-<id>`/`PAR-<id>`) so admin sees one login list.
- Self-service password change keeps source sheet in sync (`_syncStudentPassword`, `_syncParentPassword`).
- Recovery: `resetAdminPassword()` (run from editor) resets admin to `admin/admin123`, reactivates and un-deletes; creates admin row if missing.

**Validation / Gate Rules (login)**
- Username + password both required.
- Account must not be soft-deleted (`IsDeleted=1` → "Account no longer exists").
- Status must be `active` (else "Account is <status>").
- Wrong password → "Invalid password".

**Status Transitions** — none (auth is stateless aside from `LastLogin`).

**Automatic Actions**
- On success: stamp `LastLogin` (staff col 16, parent col 9), log "Login Success".
- On every failure path: log "Login Failed" with reason.
- Enrich payload with scoping ids (student → studentId/classId/classLabel; parent → parentId/mobile).

**Session (client-side)**
- 60-minute session TTL in `localStorage`; checked on mount + every 60s; expiry forces logout with a SweetAlert.

---

## 2. Users / Staff

**Permission Rules** — all endpoints **admin only**.

**Validation Rules**
- Required: Username, FullName, Email, Mobile, Password.
- Duplicate checks (active rows): Username unique, Email unique, EmployeeCode unique when provided (skips self on update).

**Business Rules**
- Cannot delete your own account (`username === currentUser` blocked).
- Update: password only changed when a non-blank value supplied; ProfilePhoto preserved.
- My Account self-update requires correct `CurrentPassword`; student/parent password change mirrors to source sheet.

**Status Transitions** — `Status` active/inactive/suspended; delete sets Status `inactive` + `IsDeleted=1`.

**Automatic Actions / Generated IDs**
- New users default Role `teacher`, Gender `other`, Status `active`, JoiningDate today, ProfilePhoto = default logo, theme `light`.
- ID = `nextUserId`. Logs: "User Added/Updated/Deleted", "Profile Updated".

---

## 3. Classes & Sections

**Permission Rules** — read: admin/clerk/teacher/supervisor/student/parent; **write: admin only**.

**Validation Rules**
- Required: ClassName, Section, AcademicYear.
- Lengths: ClassName ≤50, Section ≤10, ClassCode ≤20, RoomNumber ≤30, Building ≤50.
- AcademicYear must be `YYYY-YYYY`.
- Enums: GradeLevel 0–13; CurriculumStage ∈ {pyp,myp,dp,igcse,alevel,primary,secondary,senior}; MediumOfInstruction ∈ {english,french,spanish,mandarin,arabic,bilingual_en_fr,bilingual_en_zh,immersion}; SubjectStream ∈ {science,commerce,arts,vocational,general,none}; Shift ∈ {morning,afternoon,evening,full_day}.
- MaxCapacity default 30; TotalStrength default 0 (negatives coerced).
- ClassTeacherID / AssistantTeacherID (optional) must reference an **active teacher**; assistant ≠ class teacher.
- **Composite uniqueness**: (ClassName + Section + AcademicYear) on active rows.

**Calculations**
- `recomputeClassStrength(classId)` after any student CUD: counts active students in class **excluding** `transferred`/`passed_out`/deleted; writes to col 6.

**Generated IDs** — `nextClassId`.

---

## 4. Subjects

**Permission Rules** — read: admin/teacher/supervisor/student/parent (NOT clerk); **write: admin only**.

**Validation Rules**
- `validateSubjectMarksFields`: theory/practical/internal/external max-marks splits validated against subject MaxMarks; SubjectType ∈ {theory,practical,both,oral,project}; IsOptional/IsActive flags.
- **Composite uniqueness**: (SubjectCode + ClassID).
- Subject must belong to a valid class.

**Generated IDs** — `nextSubjectId`. Default MaxMarks 100.

---

## 5. Teacher Assignments

**Permission Rules** — read: admin/supervisor (full) + teacher (own only); **write: admin only**.

**Business Rules**
- Bulk add (`addAssignmentsBulk`): teacher↔class↔subject mappings created together.
- **Composite uniqueness**: (TeacherID + ClassID + SubjectID + AcademicYear).
- **Hard delete** (no IsDeleted).
- One assignment per teacher per class may carry `IsClassTeacher`.
- PeriodsPerWeek int 0–40.

**Report/Feature Dependencies** — gates timetable entry, marks entry, attendance (subject-wise), logbook, and teacher dashboards.

---

## 6. Students

**Permission Rules** — read: admin/teacher (own class)/clerk+supervisor (basic stripped view, no Aadhaar/medical/family) /student/parent (self); **write/delete: admin only** (add also called by enrollment as admin).

**Validation Rules**
- Duplicate checks: AdmissionNumber unique; AadhaarNumber unique when present; (ClassID + RollNumber + Status) unique.
- `validateStudentIntlFields` enums: EnglishProficiency (CEFR none/a1…c2/native); CurriculumTrack (ib_pyp…other); CustodyArrangement (joint/mother_only/father_only/legal_guardian/split/other); PrimaryContactParent (father/mother/guardian/both); AdmissionType (fresh/transfer/re_admission); DietaryRequirements CSV against fixed enum (defaults `none`).
- Length caps on ~15 intl fields; passport/visa/insurance expiry must be `YYYY-MM-DD`.
- ConcessionPercent 0–100; SpecialNeeds ≤300; MediaConsent 0/1.

**Automatic Actions**
- On add/update: **mirror to Users** (`_mirrorStudentToUsers`, EmployeeCode `STU-<id>`); username conflict → skip mirror (logged).
- On add/update/delete: `recomputeClassStrength`.
- On delete: soft-delete + un-mirror Users row (`_unmirrorByEmployeeCode`).

**Generated IDs** — `nextStudentId`.

---

## 7. Parents & Parent↔Student Links

**Permission Rules** — Parents read: admin/clerk/teacher (own-class mobile bridge)/supervisor/self; write: admin. Links read: admin/supervisor/student/parent; **link/unlink/setPrimary: admin only**.

**Validation Rules (Parents)**
- Mobile unique (always); Email unique when populated.
- Relation, PreferredLanguage, PreferredContactMethod enums; NotificationPreferences CSV.

**Business Rules (Links)**
- **Composite uniqueness** (ParentID + StudentID); **hard delete**.
- `getEligibleStudentsForLinking` excludes already-linked students.
- `setPrimaryContact` toggles IsPrimaryContact.

**Automatic Actions** — parents mirrored to Users (`PAR-<id>`).

---

## 8. Admissions

**Permission Rules** — all endpoints **admin/clerk only**.

**Business Rules / Status Transitions** (state machine):
```
registered ──confirm──▶ admitted ──enroll──▶ enrolled
     │                      │
     └──reject/cancel──────┘  →  rejected / cancelled
```
- **Register** (`addRegistration`): creates record in `registered`.
- **Confirm** (`confirmAdmission`): only from `registered` → `admitted`; requires Category + AdmissionFee.
- **Enroll** (`enrollAdmission`): only from `admitted` → `enrolled`; requires AllottedClassID (valid class), RollNumber, AdmissionNumber, LoginPassword, AdmissionDate, EntryPoint; applicant DOB + Category must exist.
- **Reject/Cancel** (`_closeAdmission`): only from `registered` or `admitted`; reason ≥3 chars.
- **Edit** (`updateRegistration`): only while `registered` or `admitted`.

**Validation Rules**
- Required: FirstName, LastName, Gender, DateOfBirth, AddressLine, City, State, PinCode, FatherName, FatherMobile, MotherName, AcademicYear, AdmissionType.
- AdmissionType ∈ {new,transfer,re_admission}; transfer requires PreviousSchool.
- AppliedForClassID must exist; registration/admission fee modes ∈ pay-mode enum when fee > 0.
- EntryPoint ∈ {session_start, mid_session}.

**Automatic Actions (on enroll)**
1. **Create Student** (`addStudent` as admin) → new studentId written to `LinkedStudentID`; AdmissionType remapped new→fresh.
2. **Mirror to Users** (via addStudent) → login account created.
3. **Create fee records (best-effort)**: if AdmissionFee > 0, find/create a `admission` / `one_time` Fee_Structure for the class+year, then `addPayment` for the admission fee; FeePaymentID stored. Failure here never blocks enrollment.

**Generated IDs**
- RegistrationNumber `REG-YYYY-NNNN` (`generateRegistrationNumber`).
- Registration fee receipt `REGF-YYYY-<id 4-pad>` (only if fee > 0).
- Admission fee receipt `ADMF-YYYY-<id 4-pad>` (on confirm).

---

## 9. Exams

**Permission Rules** — read: admin/teacher (own class)/supervisor/student+parent (published only); **write/publish/delete: admin only**.

**Validation Rules** (`validateExamFields`)
- Enums: Term (term1-3/semester1-2/quarter1-4); AssessmentType (summative/formative/mock/project/oral/practical); GradingScheme (percentage/cgpa_4/cgpa_10/ib_7point/letter_aff/pass_fail/cambridge_a_to_g); CurriculumStage (8 values).
- WeightagePercent 0–100; ExamDuration 0–600 (default 60); ExamCode ≤30; ApplicableSections ≤200; PassingPercentageRequired 0–100; ResultsLockedDate `YYYY-MM-DD`.

**Status Transitions / Locking** — `IsPublished` 0↔1.
- **Publish**: sets IsPublished=1, PublishedAt, PublishedBy. Effect: marks become visible to students/parents **and locked for teacher edits**.
- **Unpublish**: clears publish fields → teachers can edit marks again.

**Generated IDs** — `nextExamId`.

---

## 10. Marks

**Permission Rules** — read: admin/teacher/supervisor/student/parent; **write: admin or teacher only**.

**Locking / Approval Rules**
- Teacher may edit only if assigned to the exam's class+subject AND exam **not published** ("Ask admin to unpublish first"). Admin always edits.
- `getMarksForExamSubject` returns `canEdit` computed from assignment + publish state.

**Validation Rules** (`validateMarkFields`)
- Theory+Practical ≤ MaxMarks; Internal+External ≤ MaxMarks (0.01 tolerance).
- AttemptNumber 1–3; GradePoints capped 0–10; PercentageScored clamped 0–100; Status ∈ {draft,submitted,moderated,locked,published}; Comments ≤500; ModerationDate `YYYY-MM-DD`.
- Subject must belong to the exam's class.
- Obtained marks clamped to [0, MaxMarks]; absent forces obtained 0.

**Business Rules**
- **Bulk upsert** by composite key (ExamID + StudentID + SubjectID) — insert or update each row.
- **Hard delete** (no IsDeleted).

**Calculations / Automatic Actions**
- Grade auto-computed (`computeGrade`); PercentageScored auto-computed if not supplied.
- **OriginalMarks** audit snapshot written on first write / backfilled before overwrite (moderation audit).
- **Rank** (`computeMarkRanks`): per exam+subject, descending by MarksObtained, **ties share rank**, absent rows get blank rank.

**Generated IDs** — `nextMarkId`.

---

## 11. Attendance

**Permission Rules** — read: admin/clerk (read-only)/teacher/supervisor/student/parent; **write: admin/supervisor/teacher**; **lock/unlock: admin only**.

**Business Rules**
- Denormalised: one row per (ClassID + Date + Mode + SubjectID + PeriodNumber); statuses stored as JSON blob keyed by studentId.
- Mode ∈ {daily, subject_wise}; subject_wise requires valid SubjectID belonging to class + PeriodNumber ≥1.
- **Teacher constraints**: must be assigned to the class; can mark/edit **only today's** date; subject_wise requires class+subject assignment.
- Upsert by composite key (insert if absent, else update).

**Locking Rules**
- Row `IsLocked` flag; once locked, non-admin writes are blocked ("Ask admin to unlock first"). `lockAttendance` (admin) toggles lock + stamps LockedAt.

**Calculations**
- PresentCount counts status present/late/half_day; AbsentCount counts absent; TotalCount = entries.

**Generated IDs** — `nextAttendanceId`.

---

## 12. Fee Structure

**Permission Rules** — read: admin/clerk/student/parent; **write: admin/clerk**.

**Validation Rules**
- Required: ClassID, FeeCategory, Amount, Frequency, AcademicYear.
- FeeCategory ∈ {tuition,admission,transport,exam,library,sports,lab,annual,other}; Frequency ∈ {monthly,quarterly,half_yearly,annual,one_time}; AcademicYear `YYYY-YYYY`; Amount ≥0.
- ClassID must exist; **composite uniqueness** (Class + Category + Frequency + Year).
- DueDay 1–31 (default 10); LateFeePerDay ≥0; InstallmentCount 1–12; TaxPercent 0–100; Description ≤500.

**Generated IDs** — `nextFeeStructureId`.

---

## 13. Fee Payments

**Permission Rules** — read: admin/clerk/student/parent; **add/update/delete: admin or clerk only**; **refund: admin only**.

**Validation Rules**
- Required: StudentID, FeeStructureID, AmountPaid, PaymentDate, BillingPeriod, PaymentMode.
- PaymentMode ∈ {cash,cheque,online,upi,card,bank_transfer,dd}.
- FK: student exists; fee structure exists **and belongs to the student's class**.
- AmountPaid ≥0; ReceiptNumber unique; RefundReason ≤300; RefundDate `YYYY-MM-DD`.

**Calculations**
- expected = Amount + LateFee − Discount (floored at 0); AmountDue = max(0, expected − AmountPaid).
- PaymentStatus auto via `computePaymentStatus`; **admin may override** to paid/partial/pending/failed/refunded.
- AcademicYear denormalised from fee structure when not provided.

**Business Rules**
- **Bulk pay** (`addPaymentsBulk`): validate-all-then-write; max 50 rows; sequential receipts.
- **Refund**: amount > 0 and ≤ amount paid; reason ≥3 chars; sets PaymentStatus `refunded` + refund fields.

**Generated IDs** — `nextPaymentId`; receipt `RCP-YYYY-NNNNNNNN` (auto when blank).

**Notification Rules** — `emailFeeReceipt(paymentId)` emails the receipt (MailApp).

---

## 14. Monthly Fee Dues (auto-generated)

**Business Rules**
- One due row per (Student + FeeStructure + BillingMonth) for **monthly, active** fee structures of the student's class, generated from **admission month → current month**.
- **Idempotent**: skips months already having a due row.
- Due Status ∈ {pending, paid, partial, waived}.

**Automatic Actions**
- `generateStudentDues` auto-runs before admin/clerk reads (`getStudentMonthlyDues`).
- `backfillAllDues` / `generateDuesForCurrentMonth` (time-trigger entry) cover all active students; inactive skipped.
- Fee_Dues sheet auto-created if missing (`_ensureFeeDuesSheet`).

**Permission Rules** — read scoped (student=self, parent=linked-only, admin/clerk=any); **pay: admin/clerk only**.

**Calculations (pay)**
- `payMonthlyDues`: selected dues grouped by FeeStructureID → one Fee_Payments row per group (status `paid`); each due marked paid with PaymentID + PaidAmount + PaidDate. Already-paid dues skipped.

---

## 15. Daily Accounts (non-fee day-book)

**Permission Rules** — **admin/clerk only**.

**Validation Rules** (`_validateTransactionFields`)
- TxnType ∈ {income, expense}; PaymentMode ∈ account pay-mode enum; Category from income-set (donation/rent_received/fine/sale/grant/interest/misc_income/other) or expense-set (salary/utilities/supplies/maintenance/transport/rent_paid/marketing/events/taxes/printing/refreshments/misc_expense/other); Amount > 0; date required.

**Generated IDs** — `nextAccountTxnId`.

**Report Dependencies** — feeds Cash Book and Income & Expense reports + "today's accounts" dashboard card.

---

## 16. Discipline

**Permission Rules** — read: admin/teacher/supervisor/student/parent; **write: admin/teacher/supervisor** (teacher only for students in own class via `teacherHasStudent`).

**Validation Rules**
- Required: StudentID, IncidentDate, IncidentType, Severity, Description.
- IncidentType enum (10 values); Severity ∈ {low,medium,high,critical}; Status ∈ {open,under_review,resolved,escalated} (default open); Location enum (defaults other); WitnessNames ≤300.

**Notification Rules** — `ParentNotified` flag; `toggleDisciplineParentNotified` flips it (drill-down).

**Generated IDs** — `nextDisciplineId`. Soft delete.

---

## 17. Conduct

**Permission Rules** — read: admin/teacher/supervisor/student/parent; **write: admin/teacher/supervisor** (teacher own-class only).

**Validation Rules**
- Required: StudentID, EvaluationPeriod, PeriodLabel, AcademicYear, ConductGrade.
- EvaluationPeriod ∈ {monthly,term_1,term_2,term_3,annual}; ConductGrade ∈ {excellent,very_good,good,satisfactory,needs_improvement,poor}; AcademicYear `YYYY-YYYY`.
- Sub-grades (Punctuality/Behavior/Teamwork/Leadership) normalised (`normalizeSubGrade`).
- **Composite uniqueness**: (Student + EvaluationPeriod + PeriodLabel + AcademicYear).

**Generated IDs** — `nextConductId`. Soft delete.

---

## 18. Activities (Co-curricular)

**Permission Rules** — read: admin/teacher/supervisor/student/parent; **write: admin/teacher**.

**Validation Rules** — ActivityType (10 enum), Level (5 enum), ActivityDate, AcademicYear; CoachTeacherID FK optional.

**Generated IDs** — `nextRowId`. Soft delete.

---

## 19. Complaints

**Permission Rules** — read & write: admin/clerk/teacher/supervisor/student/parent (teacher reads own submissions only).

**Business Rules**
- Polymorphic submitter (SubmittedByType: student/parent/teacher/staff…); IsAnonymous flag hides submitter.
- **Hard delete** (no IsDeleted).

**Validation Rules** — Category (8 enum), Priority (4 enum), Status (5 enum); Subject/Description required.

**Status Transitions** — open → in_progress → resolved/closed (+ ResolvedAt stamp).

**Generated IDs** — ComplaintCode `CMP-YYYY-NNNNNNNN`.

---

## 20. Helpdesk Tickets

**Permission Rules** — read: admin/clerk/supervisor/student/parent (NOT teacher); **manage/assign: admin/clerk/supervisor**; raise: any reader (staff raise on behalf as type parent).

**Validation Rules**
- Required: RelatedStudentID (must exist), Category, Subject, Description.
- Category (7 enum); Priority ∈ {low,medium,high} (default medium); Status ∈ {open,in_progress,awaiting_response,resolved,closed}.

**Approval / SLA Rules**
- **DueBy auto-set from priority** (`helpdeskSlaHours`): urgent 4h, high 24h, medium 48h, low 72h (from now).
- ResolvedAt stamped when status resolved/closed.

**Business Rules** — **Hard delete**. Generated IDs — TicketCode `TKT-YYYY-NNNNNNNN`.

---

## 21. Notices

**Permission Rules** — read: all roles (audience-filtered); **write: admin/supervisor/teacher**.

**Business Rules**
- **Teacher restriction**: teachers may only post `class_specific` notices to a class they teach.
- **Audience filter** (`noticeAudienceMatches`): admin/clerk/supervisor see all; otherwise match on audience (all/staff/teachers/students/parents) or class_specific against viewer's class scope.

**Validation Rules**
- Required: Title, Description, NoticeType, NoticeDate, TargetAudience.
- NoticeType (9 enum); TargetAudience ∈ {all,students,teachers,parents,staff,class_specific}; Priority ∈ {low,medium,high,urgent} (default medium); class_specific requires valid TargetClassID.
- AcknowledgmentRequired / IsActive flags.

**Generated IDs** — `nextRowId`. Soft delete.

---

## 22. Lesson Plans

**Permission Rules** — read/write: admin/teacher/supervisor; **admin can delete but NOT write/update** (per section comment).

**Validation Rules** — PlanPeriod (4 enum), Status (4 enum), Start/End dates; ReviewStatus ∈ {pending,approved,rework,na}.

**Approval Rules** — ReviewedBy (HOD/Coordinator) + ReviewStatus workflow.

**Generated IDs** — `nextRowId`. Soft delete.

---

## 23. Teaching Logbook

**Permission Rules** — read: admin/teacher/supervisor/student/parent; **add/update: teachers only**.

**Business Rules**
- Teacher must be assigned to the class+subject; subject must belong to the class.
- **Composite uniqueness**: (Teacher + Class + Subject + LogDate + PeriodNumber) — compared on date-only.
- **Hard delete**; teacher may update own same-day entry.

**Validation Rules** — Required: ClassID, SubjectID, LogDate, TopicCovered; Status ∈ {completed,partial,not_taught}; StudentsPresent int ≥0.

**Generated IDs** — `nextRowId`.

---

## 24. Documents

**Permission Rules** — read: all staff + student/parent; write/verify/delete gated.

**Business Rules**
- Polymorphic (EntityType: student/parent/user/asset + EntityID).
- DocumentType (11 enum); ExpiryDate optional (drives expiry reminders); DocumentNumber for certs/passports.

**Approval Rules** — `verifyDocument` toggles IsVerified + VerifiedBy.

**Automatic Actions** — file upload to Drive ASSETS folder (`uploadProfileImage`/`getAssetsFolder`). Soft delete.

---

## 25. School Periods

**Permission Rules** — read: all; **write: admin only**.

**Validation Rules**
- StartTime/EndTime `HH:MM`; IsBreak flag; DisplayOrder.
- **Composite uniqueness**: (PeriodNumber + AcademicYear + DayType) — DayType ∈ {regular,saturday,half_day,exam}.

**Generated IDs** — `nextRowId`. Soft delete.

---

## 26. Timetable

**Permission Rules** — read: all (own-class/teacher scoped); **write: admin only**.

**Business Rules / Validation**
- DayOfWeek ∈ monday…sunday; Term ∈ {full_year,term_1,term_2,term_3}; times `HH:MM`.
- Subject must belong to the class; teacher must be **assigned** to teach that class+subject ("Add the assignment first").
- Empty slots (free period / break) allowed when both subject & teacher blank.
- **Slot uniqueness**: (Class + Day + Period + Year + Term).
- **Teacher conflict** (`teacherSlotConflict`): same teacher cannot occupy two classes at the same Day+Period+Year+Term — returns conflicting class.
- Mode ∈ {offline,online,hybrid}; MeetingLink for online/hybrid.
- `copyTimetable`: clone a class/term's grid to another.

**Generated IDs** — `nextRowId`. Soft delete.

---

## 27. PTM (Parent-Teacher Meetings)

**Permission Rules** — slots: admin/teacher (own only)/supervisor write; **booking: parents only**; complete: teacher (own slot)/admin/supervisor.

**Validation Rules (slots)**
- TeacherID + Date required; StartTime/EndTime `HH:MM` with End > Start; AcademicYear `YYYY-YYYY`; MaxBookings ≥1 (default 1); Mode ∈ {in_person,online,hybrid}; MeetingLink ≤500.
- **Slot overlap** blocked for same teacher/date (`ptmSlotOverlap`).
- Teacher may only create own slots.

**Business Rules (bookings)**
- Parent must be **linked** to the student (Parent_Students).
- Slot must exist, be available (IsAvailable=1), not full (`countSlotBookings < MaxBookings`).
- **Duplicate block**: same parent+student can't double-book the same slot (unless prior booking cancelled).
- **Composite uniqueness**: (SlotID + StudentID).

**Status Transitions** — booked → completed (teacher records minutes + action items) / cancelled (cannot cancel a completed booking) / no_show.

**Generated IDs** — `nextRowId`.

---

## 28. Substitutes

**Permission Rules** — read/write gated to admin/supervisor (+ teacher own assignments read).

**Business Rules / Validation** (`validateSubAllocations`)
- Per-day allocations validated against the **timetable**: each allocated period must actually belong to the absent teacher that weekday, with matching class+subject.
- Substitute ≠ absent teacher.
- **Substitute conflict**: substitute must not already have a slot that period.
- Allocation status ∈ enum (pending/confirmed/missed); reason ∈ {sick,personal,training,emergency,other}; record status pending/in_progress/completed.
- **Uniqueness**: (AbsentTeacherID + Date).

**Generated IDs** — `nextRowId`. Soft delete. Allocations stored as JSON.

---

## 29. Assets & Maintenance

**Permission Rules** — read/write gated (admin/clerk write; maintainer role for maintenance).

**Validation Rules**
- AssetTag unique; Category/Condition/Status enums; PurchasePrice, DepreciationRate 0–100, CurrentValue numeric.
- Maintenance: Type/Status enums, Cost, NextDueDate; UnderWarranty flag.

**Calculations** — depreciation/current value tracked via DepreciationRate (written-down value field).

**Generated IDs** — `nextRowId`. Soft delete (assets); maintenance history per asset.

---

## 30. Stock / Inventory

**Permission Rules** — read/write gated; **issue (transactions): admin/clerk** (`canIssueStock`).

**Validation Rules** — ItemCode unique; Unit/Category; ReorderLevel, ReorderQuantity, MinimumStock, UnitCost numerics; transaction Type ∈ {in,out,adjustment}; quantity ≥0.

**Calculations / Business Rules (transactions)**
- `in` → CurrentStock + qty; `out` → CurrentStock − qty (**blocked if qty > current** "Insufficient stock"); `adjustment` → absolute set.
- CurrentStock written back atomically with each transaction.
- `getReorderAlerts`: flags items at/below reorder/minimum levels.

**Approval Rules** — optional ApprovedBy (second signature) on high-value issues.

**Generated IDs** — `nextRowId` (items soft-deleted; transactions hard).

---

## 31. School Calendar

**Permission Rules** — read: all; write gated (`canWriteCalendar`).

**Validation Rules** (`validateCalendarPayload`)
- EventName + valid EventDate required; EndDate ≥ EventDate; EventType enum (holiday/event/exam/meeting/sports/function/ptm/working_day/other); AcademicYear `YYYY-YYYY`; ApplicableTo ∈ {all,staff,students,class_specific}; class_specific requires TargetClassID; Color must be `#RRGGBB`.

**Automatic Actions**
- **IsHoliday auto-set** to 1 when EventType = holiday (holidays block attendance); IsRecurring flag for annual repeats.

**Generated IDs** — `nextRowId`. Soft delete.

---

## 32. Hall Tickets

**Permission Rules** — scoped via `getHallTicketData(examId, studentId)`; students/parents own only.

**Business Rules** — assembles exam + student + schedule data; exam must be published/eligible. Client builds PDF (`buildHallTicketPdfDoc` / `downloadHallTicket`).

---

## 33. School Settings

**Permission Rules** — read: public (app boot, no auth); **update: admin** (`updateSchoolSettings`).

**Business Rules** — single-row config (ID always 1); WorkingDays CSV drives attendance %/timetable; AcademicYear, Currency, TimeZone, academic-year start/end dates. `defaultSchoolSettings` seeds defaults.

---

## 34. Reports

**Permission Rules** — `canViewReports` (admin/clerk/supervisor); teachers limited to `attendance_summary` + `exam_result` for own classes only.

**Report Dependencies** (source modules per report):
| Report | Reads from |
|--------|-----------|
| Fee Collection | Fee_Payments (+ Students, Classes, Fee_Structure) |
| Outstanding Dues | Fee_Dues / Fee_Payments |
| Daily Cash Book | Fee_Payments + Account_Transactions |
| Income & Expense | Account_Transactions (+ fee income) |
| Class Marksheet / Exam Results | Exams, Marks, Students, Subjects |
| Attendance Summary | Attendance, Students, Classes |
| Student Roster | Students, Classes |
| Staff List | Users |
| Admissions Pipeline | Admissions |
| System Activity Log | Logs |

- All reports honour date-range (`_reportRange`/`_inRange`) and role scope.

---

## 35. Dashboards & Sidebar Badges

**Permission Rules** — per-role endpoints (`getAdmin/Student/Parent/Supervisor/Clerk/TeacherDashboardData`); each scoped to the caller's role/own data.

**Calculations / Automatic Actions**
- Admin dashboard aggregates counts, alerts, **birthdays today**, class capacity, recent activity (last 10 logs), charts.
- **Sidebar badges** (`getSidebarBadges`) cached 5 minutes (CacheService); counts pending items in Helpdesk, Complaints, Discipline, PTM, Notices, Substitutes.
- Student/parent/teacher dashboards compute attendance %, result %, today's schedule, fee summaries.

---

## Appendix — Rule-Type Coverage Matrix

| Module | Validation | Status Transitions | Auto Actions | Permissions | Calculations | Generated IDs | Locking | Approval | Notification |
|--------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| Auth | ✓ | — | ✓ | ✓ | — | — | — | — | — |
| Users | ✓ | ✓ | ✓ | ✓ | — | ✓ | — | — | — |
| Classes | ✓ | — | ✓ | ✓ | ✓ | ✓ | — | — | — |
| Subjects | ✓ | — | — | ✓ | — | ✓ | — | — | — |
| Assignments | ✓ | — | — | ✓ | — | ✓ | — | — | — |
| Students | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — |
| Parents/Links | ✓ | — | ✓ | ✓ | — | ✓ | — | — | — |
| Admissions | ✓ | ✓ | ✓ | ✓ | — | ✓ | — | ✓ | — |
| Exams | ✓ | ✓ | ✓ | ✓ | — | ✓ | ✓ | ✓ | — |
| Marks | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — |
| Attendance | ✓ | — | ✓ | ✓ | ✓ | ✓ | ✓ | — | — |
| Fee Structure | ✓ | — | — | ✓ | — | ✓ | — | — | — |
| Fee Payments | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | ✓ | ✓ |
| Monthly Dues | — | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — |
| Daily Accounts | ✓ | — | ✓ | ✓ | — | ✓ | — | — | — |
| Discipline | ✓ | ✓ | ✓ | ✓ | — | ✓ | — | — | ✓ |
| Conduct | ✓ | — | ✓ | ✓ | — | ✓ | — | — | — |
| Activities | ✓ | — | ✓ | ✓ | — | ✓ | — | — | — |
| Complaints | ✓ | ✓ | ✓ | ✓ | — | ✓ | — | ✓ | — |
| Helpdesk | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | ✓ | — |
| Notices | ✓ | — | ✓ | ✓ | — | ✓ | — | — | ✓ |
| Lesson Plans | ✓ | ✓ | ✓ | ✓ | — | ✓ | — | ✓ | — |
| Logbook | ✓ | — | ✓ | ✓ | — | ✓ | — | — | — |
| Documents | ✓ | — | ✓ | ✓ | — | ✓ | — | ✓ | — |
| Periods | ✓ | — | — | ✓ | — | ✓ | — | — | — |
| Timetable | ✓ | — | ✓ | ✓ | — | ✓ | — | — | — |
| PTM | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — |
| Substitutes | ✓ | ✓ | ✓ | ✓ | — | ✓ | — | — | — |
| Assets | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — | — | — |
| Stock | ✓ | — | ✓ | ✓ | ✓ | ✓ | — | ✓ | — |
| Calendar | ✓ | — | ✓ | ✓ | — | ✓ | — | — | — |
| Settings | ✓ | — | ✓ | ✓ | — | — | — | — | — |
| Reports | — | — | — | ✓ | ✓ | — | — | — | — |

*(✓ = rules of that type exist in the code; — = none found.)*
