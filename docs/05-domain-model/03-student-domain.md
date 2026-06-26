# 03 – Student Domain

**Product:** SchoolERP · **Domain Model** · **Version:** 1.0 · **Last updated:** 2026-06-26

> Conceptual domain model. No tables, columns, SQL, or APIs. See
> [00-domain-overview.md](00-domain-overview.md) for conventions.

---

## 1. Purpose

The Student domain owns the **people at the center of the school** — students and their parents/
guardians — and the **student lifecycle** from admission through promotion to an exit state. It also
owns **behavioural and co-curricular records** (discipline, conduct, activities) that are about a
student.

---

## 2. Responsibilities

- Maintain the **student master record** and the student **status lifecycle** (admission → enrollment → active → promotion → graduation / transfer / withdrawal / dropout).
- Maintain **parent/guardian** records and the **parent–student** relationships (including primary contact).
- Run the **admission pipeline** (register → confirm → enroll) and provision enrolled students.
- Manage **promotion** (single/bulk, preview, roll-number regeneration, section change, optional-subject and fee-structure update) and keep **promotion history** with rollback before confirmation.
- Record **discipline incidents**, periodic **conduct evaluations**, and **co-curricular activities** for a student.

---

## 3. Owned Business Entities

| Entity (concept) | Responsibility / natural identity | Aggregate root |
|------------------|-----------------------------------|:--------------:|
| **Student** | The enrolled learner; identified by a unique admission number (numeric ≤6 digits). | ✓ |
| **Parent / Guardian** | A guardian person; identified by parent id / unique mobile (/ email). | ✓ |
| **Parent–Student Link** | The relationship between a parent and a student, with primary-contact flag; unique per parent+student. | — (within Parent/Student) |
| **Admission** | An applicant progressing register → confirm → enroll (+ reject/cancel); identified by a unique registration number. | ✓ |
| **Promotion Record** | A historical promotion event for a student (source/target class, roll, year), supporting rollback before confirmation. | ✓ |
| **Discipline Incident** | A disciplinary event about a student (type, severity, status, parent-notified). | ✓ |
| **Conduct Evaluation** | A periodic conduct grade for a student; unique per student/period/label/year. | ✓ |
| **Activity Record** | A co-curricular achievement of a student. | ✓ |

---

## 4. Referenced Entities

| Referenced (by identity) | Owning domain | Why |
|--------------------------|---------------|-----|
| **Class / Section** | Academic | A student is assigned to a class/section; admission allots a class. |
| **Subject** | Academic | Optional-subject choices during promotion. |
| **Teacher / Staff** | Staff | Discipline/conduct/activity records may reference the reporting/evaluating/coaching staff. |
| **Fee Structure** | Finance | Promotion applies the destination class's fee structure; enrollment may raise an admission fee. |
| **Fee Payment** | Finance | Admission enrollment links the admission fee receipt. |
| **User / Account** | Foundation | Each student/parent has an auto-provisioned login. |
| **Number Sequence, Audit, Document, Media** | Foundation | Admission/registration numbers, audit, certificates, photos. |

---

## 5. Relationships

- A **Student** is assigned to **one Class/Section** (referenced from Academic) for the current year.
- A **Parent** links to **one or many Students**; a **Student** links to **one or many Parents** via **Parent–Student Link** (one may be primary contact).
- An **Admission**, once enrolled, **produces one Student** and links the **admission Fee Payment**; an admission references the allotted **Class**.
- A **Promotion Record** moves a **Student** from a source **Class** to a target **Class** for the next year; many records form the student's **promotion history**.
- A **Student** has many **Discipline Incidents**, **Conduct Evaluations**, and **Activity Records**.
- A **Student** and a **Parent** each correspond to **one User/Account** (Foundation) for login.

**Student lifecycle (state concept):**
```
Admission → Enrollment → Active → Promotion → (next year Active)
                                   └→ Graduation / Transfer Certificate / Withdrawal / Dropout
```

---

## 6. Business Boundaries

**Inside:** student and parent master data, family links, admissions pipeline, promotion lifecycle/history, discipline, conduct, activities.

**Outside (not owned here):**
- The **Class/Section/Subject** definitions — owned by Academic (Student references them).
- **Attendance, Marks, Fees** for a student — owned by Attendance, Examination, Finance (which reference the Student).
- The **login account** — owned by Foundation (Student domain triggers its provisioning on record creation).
- **Documents/certificates** files — stored via Foundation's media/document services.

**Consistency boundaries:**
- An Admission and its state transitions form one boundary; enrollment's creation of a Student is a coordinated cross-domain workflow (Student + Finance + Foundation) executed transactionally by the owning service.
- A Promotion batch (single/bulk) is one boundary, reversible before confirmation.
- A Parent with its Parent–Student Links is one boundary.

---

## 7. Dependency Rules

- Student **depends on Foundation** (accounts, numbering, audit, documents) and **Academic** (class/section/subject), and **references Staff** and **Finance**.
- Student must **not depend on** Attendance, Examination, or Communication.
- Attendance, Examination, Finance, and Communication domains **reference** the Student; they do not own student data.
- Cross-domain effects of enrollment and promotion (login provisioning, fee structure, receipts) occur via the owning domains' services and domain events — never by the Student domain mutating Foundation/Finance data directly.
