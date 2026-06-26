# 08 – Communication Domain

**Product:** SchoolERP · **Domain Model** · **Version:** 1.0 · **Last updated:** 2026-06-26

> Conceptual domain model. No tables, columns, SQL, or APIs. See
> [00-domain-overview.md](00-domain-overview.md) for conventions.

---

## 1. Purpose

The Communication domain owns all **outbound engagement and support interaction**: notices and
messages (SMS / Email / Push), reusable templates, communication logs, the public **photo and video
galleries** and their **website/app synchronization**, and the case-based **support** channels
(complaints and helpdesk). It is the school's voice to staff, students, and parents.

---

## 2. Responsibilities

- Compose and publish **notices** to selected destinations (Internal ERP, Website, App, Push, SMS, Email).
- Dispatch **messages** through the central notification service across channels, including bulk and scheduled sends.
- Maintain reusable **SMS/Email templates** and personalize per recipient.
- Record **communication logs** for every send (channel, recipient, status).
- Manage **photo/video galleries** and the **one-way outward sync** of public notices and galleries to the website and the app.
- Operate the **complaint** and **helpdesk** case channels (intake, assignment, status, SLA, resolution).

---

## 3. Owned Business Entities

| Entity (concept) | Responsibility / natural identity | Aggregate root |
|------------------|-----------------------------------|:--------------:|
| **Notice** | A published announcement with audience, priority, expiry, and selected destinations. | ✓ |
| **Message** | An outbound communication instance (SMS/Email/Push) to a recipient. | ✓ |
| **Template** | A reusable SMS/Email template with placeholders. | ✓ |
| **Communication Log** | A record of one send (channel, recipient, content reference, status, timestamps). | ✓ |
| **Gallery Album** | A collection of public photos. | ✓ |
| **Gallery Photo** | A photo within an album. | — (within Album) |
| **Video** | A public video entry (link/upload). | ✓ |
| **Website Sync Item** | The outward, public-facing representation of a notice/gallery item for website/app sync. | ✓ |
| **Complaint** | A grievance case (polymorphic submitter, category, priority, status); identified by a complaint code. | ✓ |
| **Helpdesk Ticket** | A support case related to a student (category, priority, SLA due-by, status); identified by a ticket code. | ✓ |

---

## 4. Referenced Entities

| Referenced (by identity) | Owning domain | Why |
|--------------------------|---------------|-----|
| **Student, Parent** | Student | Audiences, complaint/helpdesk subjects, message recipients. |
| **Staff / User** | Staff / Foundation | Posters, assignees, message recipients, submitters. |
| **Class** | Academic | Class-specific notice/audience targeting. |
| **Number Sequence (complaint/ticket codes)** | Foundation | Codes are issued centrally. |
| **Media Asset** | Foundation | Notice/gallery attachments and images are stored in the media library. |
| **Audit, Channel/Gateway settings** | Foundation | Audit; SMS/SMTP/Push configuration. |
| **Domain events (fee receipt, result published, attendance, PTM, discipline)** | Finance/Examination/Attendance/Academic/Student | Triggers for automatic communications. |

---

## 5. Relationships

- A **Notice** targets an **audience** (all/staff/teachers/students/parents/class-specific) and fans out to selected **destinations**; class-specific notices reference a **Class**.
- A **Message** is sent on one **channel** to one **recipient** (a Student/Parent/Staff) and produces one **Communication Log**; a **Template** may shape many messages.
- A **Gallery Album** has many **Photos**; **Videos** stand alone; public **Notices/Gallery** items produce **Website Sync Items** for the public site/app (one-way).
- A **Complaint** is raised by a polymorphic submitter and may reference a **Student**; a **Helpdesk Ticket** references a **Student** and an SLA; both may be **assigned to** a **Staff/User** and move through a status lifecycle.
- Communications are **triggered** by events from other domains (e.g., receipt issued → email), but the message/log are owned here.

---

## 6. Business Boundaries

**Inside:** notices, messages, templates, communication logs, galleries, website-sync items, complaints, helpdesk tickets.

**Outside (not owned here):**
- The **central notification service mechanics** (channel drivers) are an Architecture/platform concern; this domain *uses* them and owns the *notice/message/log* business records.
- **Audience source data** (students, parents, staff, classes) — owned by Student/Staff/Academic (referenced for targeting).
- The **public website itself** — the existing external site; Communication only **feeds** it via sync items (no CMS).
- **Audit entries** — owned by Foundation (distinct from communication logs owned here).
- **Codes** (complaint/ticket) — issued by Foundation's number generator.

**Consistency boundaries:**
- A Notice with its destinations and resulting sync items is one boundary.
- A Message with its Communication Log is one boundary; bulk/scheduled sends fan out into many such boundaries.
- A Complaint or Helpdesk Ticket with its status/assignment/resolution is one boundary.

---

## 7. Dependency Rules

- Communication **depends on Foundation** (numbering, media, audit, channel settings) and **references Student, Staff, Academic** (audiences/subjects) and **events from Finance/Examination/Attendance** (triggers).
- Communication must **not be depended upon** by core record domains for their own correctness; those domains **emit events** that Communication consumes.
- The **outward website/app sync is one-way** (ERP → website/app); Communication never ingests content from the public website.
- Complaints/Helpdesk are owned here as **engagement/support cases**; they reference but do not own Student/Staff data.
