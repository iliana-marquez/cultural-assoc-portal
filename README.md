# KulturCMS
PHP MVC web portal for Cultural and Non-Profit Oganisations with built-in content management. 

<!-- add section
> First deployed for Kultur Klub Alsergrund, Vienna — the real-world use case that inspired this project.
-->

#### The Fact: 
> The mission of **Cultural & Non-Profit Organisations** is to **strengthen communities, preserve cultural heritage, and create opportunities for participation, education, and social engagement**. 

In today's digital environment, a **website** has become an **essential tool for supporting this mission**. It provides a structured and permanent space to communicate activities, present projects, showcase collaborators, preserve institutional memory, and maintain an accessible public presence alongside social media platforms.

#### The Problem: 
> **A website should not become another responsibility to manage** 

Maintaining **website content** over time often becomes a challenge in itself. Information accumulates, content structures evolve inconsistently, and websites gradually become harder to maintain. While content management systems (CMS) have made website editing more accessible, many remain either too technical, too complex, or too expensive for organisations that operate with limited budgets and volunteer-driven efforts.

#### The Solution:
> A website framework with integrated content management designed to preserve structure, consistency, and continuity over time.

The goal is not only to **make content easy to edit**, but to **ensure that website management remains sustainable, transferable, and independent of specialized technical knowledge**.

#### The Details:
>- [Overview](#overview)
>- [Database](#database-schema)

## Overview
This is a vanilla PHP MVC portal built from scratch that provides a public-facing website with an integrated content management layer that allows a non-technical web manager to independently edit and manage `content`, `events`, `projects`, `team members`, `participants`, and `collaborators` directly on the live website, without a separate backend dashboard or service UI.

## Target Audience
Small to mid-size cultural associations and non-profit organisations operating with:
- Limited budgets
- Volunteer or part-time web managers
- No dedicated technical staff
- Need for full data ownership and portability

## Features

## Database Schema

```text
SEED
├── section_types           # Dynamic content block type definitions
└── url_types               # Link label and icon lookup

CONTENT
└── pages                   # Page content stored as JSON (per section)

ORGANISATION
└── organisation_info       # Identity, contact, legal — single row

EXTERNAL ORGANISATIONS
├── contributors              # partners + sponsors + collaborators
└── contributors_assignments  # polymorphic: association | project | event

EXTERNAL URLs:
└── urls # polymorphic: organisation(s) | contributors | participants | projects | events

TEAM
└── team                    # Team member profiles

PARTICIPANTS
├── participants              # Participant profiles
└── participants_categories   # Configurable (deployment-specific)

PROJECTS
├── projects               # Funded initiatives, series, productions
└── project_categories     # Configurable (deployment-specific)

EVENTS
├── events                 # Event records
├── event_categories       # Configurable (deployment-specific)
└── event_participants     # Many-to-many: events ↔ participants

MEDIA
└── media                  # Media files
                           # polymorphic: event | project 

ARCHIVE
└── archive                 # Historical events (legacy import / museum archive)

AUTH
└── authorised_users       # Editors, OTP fields, and access control
```

---

### Entity Relationship Diagram

![Entity Relationship Diagram](/docs/images/ERD-db-relational-schema-V3.png)

### Design Principles

**Single source of truth** — every piece of data has exactly one home. 
Primary assets (logo, profile photo, hero image) live as columns on their 
entity table. Secondary and variable data live in dedicated tables. No 
field is duplicated across tables.

**Polymorphic relationships** — three tables replace multiple entity-specific tables 
with a single flexible structure.

- `urls` — external links for organisation, contributors, team, 
  participants, projects and events
- `media` — promotional and documentary media for events and projects, 
  distinguished by `stage` (promo | gallery) set at application level and nullable for any entity type where this doesn't aplly
- `contributors_assignments` — maps contributors to the association 
  globally, or to specific projects and events

`entity_type` is stored as `varchar`, keeping the pattern open to new entity types 
without schema changes. Referential integrity for `entity_id` is 
validated at model level.

**Content-driven display** — page sections store structured content as 
JSON, typed by `section_types`. Array fields (media, buttons) drive 
display automatically: one item renders as a single element, multiple 
items as a carousel or button group — no additional configuration needed.

**Date-driven logic** — events and projects have no manual status field. 
Display context (upcoming, past, active, completed) is derived entirely 
from date comparison at render time, eliminating human error and manual 
status management.

**Configurable per deployment** — category tables (`participants_categories`, 
`event_categories`, `project_categories`) and `url_types` are defined per 
deployment. Each organisation configures the classifications that reflect 
their own programme and disciplines without touching the codebase.

**Seed data** — only two tables are pre-populated on every deployment: 
`section_types` (content block definitions) and `url_types` (link labels 
and icons). Everything else is a white canvas the organisation fills from 
day one.


## Project Folder Structure

<details>
   <summary>Click to display 👇</summary>

```
cultural-assoc-portal/
│
├── public/                         ← document root (maps to html/ on server)
│   ├── index.php                   ← single entry point
│   ├── .htaccess                   ← mod_rewrite rules
│   └── assets/
│       ├── css/
│       │   ├── main.css            ← compiled styles
│       │   └── theme.css           ← CSS variables (colours, fonts)
│       ├── js/
│       │   ├── app.js              ← main JS
│       │   ├── editor.js           ← edit mode interactions
│       │   └── sortable.js         ← drag and drop ordering
│       └── images/
│           └── placeholder/        ← default images for empty states
│
├── app/
│   ├── Controllers/
│   │   ├── BaseController.php      ← render(), redirect(), isLoggedIn()
│   │   ├── HomeController.php
│   │   ├── AboutController.php
│   │   ├── TeamController.php
│   │   ├── ParticipantsController.php
│   │   ├── EventsController.php
│   │   ├── ProjectsController.php
│   │   ├── ContributorsController.php
│   │   ├── ArchivController.php
│   │   ├── KontaktController.php
│   │   ├── MitgliedController.php
│   │   ├── AlsergrundController.php
│   │   ├── AuthController.php      ← OTP login/logout
│   │   └── Admin/
│   │       ├── AdminBaseController.php  ← session guard
│   │       ├── PagesAdminController.php
│   │       ├── TeamAdminController.php
│   │       ├── ParticipantsAdminController.php
│   │       ├── EventsAdminController.php
│   │       ├── ProjectsAdminController.php
│   │       ├── ContributorsAdminController.php
│   │       ├── MediaAdminController.php
│   │       ├── OrganisationAdminController.php
│   │       └── UsersAdminController.php
│   │
│   ├── Models/
│   │   ├── BaseModel.php           ← PDO wrapper, prepared statements
│   │   ├── PageModel.php
│   │   ├── TeamModel.php
│   │   ├── ParticipantModel.php
│   │   ├── EventModel.php
│   │   ├── ProjectModel.php
│   │   ├── ContributorModel.php
│   │   ├── MediaModel.php
│   │   ├── UrlModel.php
│   │   ├── ArchivModel.php
│   │   ├── OrganisationModel.php
│   │   └── AuthModel.php
│   │
│   └── Views/
│       ├── layouts/
│       │   ├── main.php            ← public layout (header, nav, footer)
│       │   └── auth.php            ← login page layout
│       ├── components/
│       │   ├── nav.php
│       │   ├── footer.php
│       │   ├── event-card.php
│       │   ├── team-card.php
│       │   ├── participant-card.php
│       │   ├── contributor-card.php
│       │   ├── media-carousel.php
│       │   ├── media-gallery.php
│       │   ├── edit-bar.php        ← floating editing mode bar
│       │   └── sections/           ← section type components
│       │       ├── hero.php
│       │       ├── text-block.php
│       │       └── cta-block.php
│       ├── pages/
│       │   ├── home.php
│       │   ├── about.php
│       │   ├── team.php
│       │   ├── team-detail.php
│       │   ├── participants.php
│       │   ├── events.php
│       │   ├── event-detail.php
│       │   ├── projects.php
│       │   ├── project-detail.php
│       │   ├── contributors.php
│       │   ├── archiv.php
│       │   ├── kontakt.php
│       │   ├── mitglied.php
│       │   └── alsergrund.php
│       └── admin/
│           ├── login.php
│           ├── pages-edit.php
│           ├── team-list.php
│           ├── team-form.php
│           ├── participants-list.php
│           ├── participants-form.php
│           ├── events-list.php
│           ├── events-form.php
│           ├── projects-list.php
│           ├── projects-form.php
│           ├── contributors-list.php
│           ├── contributors-form.php
│           ├── media-upload.php
│           ├── organisation-form.php
│           └── users-list.php
│
├── core/
│   ├── Router.php                  ← URL to controller mapping
│   ├── Request.php                 ← wraps $_GET, $_POST, $_SERVER
│   ├── Response.php                ← redirects, status codes
│   └── Database.php                ← PDO singleton connection
│
├── config/
│   ├── routes.php                  ← all route definitions
│   ├── organisation.php            ← deployment-specific config
│   └── app.php                     ← environment, debug, session config
│
├── database/
│   ├── schema.sql                  ← full DB structure + seed data
│   └── .gitkeep
│
├── storage/
│   └── logs/                       ← error logs, OTP logs
│
├── .env.example                    ← template — committed
├── .env                            ← credentials — never committed
├── .gitignore
└── README.md
```
</details>

### Key decisions:

* **`public/`as document root:** so everything above is inaccessible from the browser. Security foundation for clasic headless PHP, being `index.php` the only one getting directly hit by the browser.

* **`core/` separate from `app/`:** clean separation of project vs deplployment-specific code.

* **`Request.php` & `Response.php`:** wrapping superglobals ($_GET, $_POST) keeps controllers testable and clean. No raw superglobals on controllers.
 
* **`config/routes.php`:** all routes in one file, easy to see the full URL map at a glace.

* **`Admin/` subfolder in Controllers:** keeps admin controllers grouped and namespaced separatelly from public controlers.

* **`components/sections/`:** section type components isolated. Adding a new section type = one new file here.

* **`storage/logs/`:** OTP attempts, errors logged here. Never in `public/`.

