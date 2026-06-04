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

## Overview
This is a vanilla PHP MVC portal built from scratch that provides a public-facing website with an integrated content management layer that allows a non-technical web manager to independently edit and manage `content`, `events`, `team members`, `participants`, `partners` and `sponsors` directly on the live website, without a separate backend dashboard or service UI.


## Database Schema

```text
SEED
├── section_types           # Dynamic content block type definitions
└── url_types              # Link label and icon lookup

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
                           # stage: promo (pre) | gallery (after)

ARCHIVE
└── archiv                 # Historical events (legacy import / museum archive)

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

