# Organisation Website System (OWS)

A deployable PHP MVC website system with integrated content management for Non-Profit Organisations, designed to help them present and preserve their institutional activities in a sustainable and independent way, and without technical barriers.

<!-- add section
> First deployed for Kulturklub Alsergrund, Vienna - the real-world use case that inspired this project.
-->

#### The Fact:

> The mission of **Cultural and Non-Profit Organisations** is to **strengthen communities, preserve cultural heritage, and create opportunities for participation, education, and social engagement**.

In today's digital environment, a **website** has become an **essential tool for supporting this mission**. It provides a structured and permanent space to communicate activities, present projects, showcase collaborators, preserve institutional memory, and maintain an accessible public presence alongside social media platforms.

#### The Problem:

> **A website should not become another responsibility to manage**

Maintaining **website content** over time often becomes a challenge in itself. Information accumulates, content structures evolve inconsistently, and websites gradually become harder to maintain. While content management systems (CMS) have made website editing more accessible, many remain either too technical, too complex, or too expensive for organisations that operate with limited budgets and volunteer-driven efforts.

#### The Solution:

> A website system with integrated content management designed to preserve structure, consistency, and continuity over time.

The goal is not only to **make content easy to edit**, but to **ensure that website management remains sustainable, transferable, and independent of specialized technical knowledge**.

#### The Details:

> - [Overview](#overview)
> - [Database](#database-schema)

## Overview

This is a vanilla PHP MVC application built from scratch that provides a public-facing website with an integrated content management layer that allows a non-technical web manager to independently edit and manage `content`, `events`, `projects`, `team members`, `participants`, and `collaborators` directly on the live website, without a separate backend dashboard or service UI.

## Target Audience

Small to mid-size cultural associations and non-profit organisations operating with:

- Limited budgets
- Volunteer or part-time web managers
- No dedicated technical staff
- Need for full data ownership and portability

## Features

## Database Schema

<details>
   <summary>Click to display schema overview 👇</summary>

```text
SEED
├── section_types           # Dynamic content block type definitions
└── url_types               # Link label and icon lookup

CONTENT
└── pages                   # Page content stored as JSON (per section)

ORGANISATION
└── organisation_info       # Identity, contact, legal, SEO — single row

EXTERNAL ORGANISATIONS
├── contributors              # partners + sponsors + collaborators
└── contributors_assignments  # polymorphic: association | project | event

EXTERNAL URLs:
└── urls # polymorphic: organisation(s) | team | contributors | participants | projects | events

TEAM
└── team                    # Team member profiles

PARTICIPANTS
├── participants              # Participant profiles
└── participants_categories   # Configurable (deployment-specific)

VENUES
└── venues                  # Physical locations for events and projects

PROJECTS
├── projects               # Funded initiatives, series, productions
└── project_categories     # Configurable (deployment-specific)

EVENTS
├── events                 # Event records
├── event_categories       # Configurable (deployment-specific)
└── event_participants     # Many-to-many: events ↔ participants

MEDIA
└── media                  # Audiovisual content — polymorphic: event | project

ARCHIVE
└── archive                 # Historical events (legacy import / museum archive)

AUTH
└── authorised_users       # Editors, OTP fields, and access control
```

</details>

---

### Entity Relationship Diagram

![Entity Relationship Diagram](/docs/readme-images/ERD-db-relational-schema-V4.png)

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
`event_categories`, `project_categories`) and `url_types` customisable. Each organisation configures the classifications that reflect
their own programme and disciplines without touching the codebase.

**Seed data** — only two tables are pre-populated on every deployment:
`section_types` (content block definitions) and `url_types` (link labels
and icons). Everything else is a white canvas the organisation fills from
day one.

**Venues as independent entities** — physical locations are stored in a
dedicated `venues` table referenced by both `events` and `projects` via
a standard FK. Change a venue address once — all associated events and
projects reflect it automatically.

**Semantic image fields** — identity assets (team profile photos,
participant photos, contributor logos) are stored as `image` columns
directly on their entity tables. Audiovisual content for events and
projects flows exclusively through the polymorphic `media` table,
eliminating duplication and maintaining a single source of truth.

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
│   ├── SchemaBuilder.php           ← builds JSON-LD structured data for search engines and AI crawlers
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

- **`public/`as document root:** so everything above is inaccessible from the browser. Security foundation for clasic headless PHP, being `index.php` the only one getting directly hit by the browser.

- **`core/` separate from `app/`:** clean separation of project vs deplployment-specific code.

- **`Request.php` & `Response.php`:** wrapping superglobals ($\_GET, $\_POST) keeps controllers testable and clean. No raw superglobals on controllers.

- **`config/routes.php`:** all routes in one file, easy to see the full URL map at a glace.

- **`Admin/` subfolder in Controllers:** keeps admin controllers grouped and namespaced separatelly from public controlers.

- **`components/sections/`:** section type components isolated. Adding a new section type = one new file here.

- **`storage/logs/`:** OTP attempts, errors logged here. Never in `public/`.

## Development

### Request Lifecycle

All requests are routed through a single entry point (`public/index.php`) via Apache mod_rewrite. The Router maps URLs to controllers and methods, supporting both static and dynamic routes with named parameters.

Request -> public/index.php -> Router -> Controller -> View -> Response

Routes are defined in `config/routes.php`:

```php
$router->get('/', 'HomeController', 'index');
```

Dynamic parameters (`{slug}`, `{id}`) are extracted automatically and passed to the controller method.

### MVC Skeletton

`ob_start()` / `b_get_clean()` — to capture the view output into $content instead of sending it directly to the browser. Then `$content`is available inside`main.php` layout to be placed where needed.

`extract($data)` — to convert array keys to variables:

```php
// $data = ['team' => [...], 'title' => 'Team']
// becomes:
$team = [...];
$title = 'Team';
// both available in the view
requireLogin() — one line in any admin controller method protects it completely.
```

Browser → .htaccess → index.php → Router → HomeController
→ BaseController::render() → ob_start() → home.php captured
→ main.php layout → nav.php + $content + footer.php → Browser

### Implementing SEO from the start

Pulling SEO data from the DB makes it truly headless: `main.php` contains the relevant tags prepared to be filled according to the db reccords, so they can be found by human and ai search.

1. **SEO Data set dynamically in the controllers**:

   ```php
   // HomeController
   $this->render('pages/home', [
       'seo' => [
           'title'       => $org->name . ' | ' . $org->tagline,
           'description' => $org->description,
           'image'       => $org->logo_url,
           'url'         => 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
           'type'        => 'website',
           'schema'      => null,
       ]
   ]);
   ```

2. **`main.php` recives `$seo` and renders it**:

   ```php
      <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">

       <title><?= htmlspecialchars($seo['title'] ?? '$org->name') ?></title>
       <meta name="description" content="<?= htmlspecialchars($seo['description'] ?? '') ?>">

       <!-- Open Graph -->
       <meta property="og:title"       content="<?= htmlspecialchars($seo['title'] ?? '') ?>">
       <meta property="og:description" content="<?= htmlspecialchars($seo['description'] ?? '') ?>">
       <meta property="og:image"       content="<?= htmlspecialchars($seo['image'] ?? '') ?>">
       <meta property="og:url"         content="<?= htmlspecialchars($seo['url'] ?? '') ?>">
       <meta property="og:type"        content="<?= htmlspecialchars($seo['type'] ?? 'website') ?>">

       <!-- Twitter Card -->
       <meta name="twitter:card"        content="summary_large_image">
       <meta name="twitter:title"       content="<?= htmlspecialchars($seo['title'] ?? '') ?>">
       <meta name="twitter:description" content="<?= htmlspecialchars($seo['description'] ?? '') ?>">
       <meta name="twitter:image"       content="<?= htmlspecialchars($seo['image'] ?? '') ?>">

       <!-- JSON-LD Schema -->
       <?= $seo['schema'] ?? '' ?>

       <!-- canonical URL -->
       <link rel="canonical" href="<?= htmlspecialchars($seo['url']) ?>">
   </head>

   ```

3. JSON-LD SchemaBuilder utility class
   - Builds JSON-LD structured data strings for search engines and AI crawlers.
   - All values come from the database — no hardcoded content data.
   - Schema.org vocabulary (@context, @type) is fixed as per the standard.
   - Usage:
     - SchemaBuilder::build('organisation', $org);
     - SchemaBuilder::build('occurrence', $event); // events and projects
     - SchemaBuilder::build('person', $person); // team and participants

4. **Default SEO from organisation_info:**

   Every controller that needs defauls calls:

   ```php
   // BaseController helper
   protected function defaultSeo(object $org): array
   {
       return [
           'title'       => $org->name . ' | ' . $org->tagline,
           'description' => $org->tagline,
           'image'       => $org->logo_url,
           'url'         => 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
           'type'        => 'website',
           'schema'      => null,
       ];
   }
   ```

5. **The full headless SEO flow:**

   ```
   organisation_info (DB)
         ↓
   BaseController::defaultSeo()     ← default for every page
         ↓
   Controller overrides per page    ← event title, image etc.
         ↓
   passed to render() as $seo array
         ↓
   main.php renders all meta tags   ← no logic, just output
   ```

6. **Benefits**

   > Built-in meta tags: One file, permanent benefit across every platform

   **Humans visiting the site** → find and see the designed page

   **Social media bots** (Facebook, LinkedIn, Twitter) → read Open Graph + Twitter Card tags → display rich preview cards when links are shared
   Search engine bots (Google, Bing, DuckDuckGo) → read:
   - `<title>` → page title in search results
   - `<meta name="description">` → snippet under the title
   - `<link rel="canonical">` → which URL to index
   - JSON-LD schema → rich results (event dates, locations shown directly in Google)

   **AI crawlers** (ChatGPT, Perplexity, Claude) → read structured data and meta tags to understand and cite your content accurately

   **For a cultural assosiation this is particularly valuable:**
   When someone searches "Konzert Wien 9. Bezirk" — Google can show the event directly in results with date, time and location from the JSON-LD schema. No click needed to see the details, so is higher in visibility than a plain blue link.

### BaseController

- `fetchAll()` — returns array of objects, multiple rows
- `fetchOne()` — returns single object or null, never false
- `execute()` — INSERT/UPDATE/DELETE, returns bool
- `lastInsertId()` — call immediately after INSERT
- `count()` — for pagination and existence checks
- `beginTransaction/commit/rollback()` — for multi-step operations
- All queries go through `prepare()` → `execute()` — SQL injection impossible by design.

BaseController Load Test succesfull:

![test-BaseController](/docs/screenshots/test_BaseModel_load_works.png)

### OrganisationModel → Manages the single organisation_info row.

- `get()` — fetches the single org row
- `getWithUrls()` — org + all social/external links joined
- `update()` — edit mode saves contact/identity data

### UrlModel → Manages all external URLs across the system

- all URL CRUD for any entity via `entity_type` + `entity_id`

### HomeController → render homepage with real data

### Then SEO → built from real model data, tested in browser

Backend wiring is working (db connection/BaseModel to extend to other Models and )

First test:

db connection -> wired
BaseModel -> prepared with CRUD
BaseController -> prepared to render other controllers
Views/layout/main.php prepared to render specific $content from whatever mask
Model ready to render Organisation + Url(rel. entity)

First Test
-> OrganisationModel (basic test, one entry queried)
-> UrlModel (basic test, one entry queried)

LOCAL:

```
MySQL (test data)
      ↓
OrganisationModel::get()      ← fetches org row
UrlModel::getForEntity()      ← fetches urls
      ↓
HomeController::index()       ← calls models, passes data
      ↓
BaseController::render()      ← captures view into $content
      ↓
main.php                      ← renders $content in layout
      ↓
Browser displays var_dump     ← real data from DB
```

What it proves:
✅ DB connection and credentials
✅ BaseModel prepared statements work
✅ OrganisationModel fetches real data
✅ UrlModel fetches related URLs
✅ Controller passes data to view
✅ BaseController render chain works
✅ main.php layout wraps content

DEPLOYED:

```
GitHub → auto-deploys → Hostinger
.env → DB connected
Router → working
Models → tested
Layout → rendering
```

Staging checklist done:
✅ Git auto-deployment
✅ Document root configured
✅ .env path resolution across environments
✅ DB connection on live server
✅ Full render chain working

## Second phase...

1. AuthController + OTP flow

`AuthController` has:

```
GET  /$adminPath           -> show login form
POST /$adminPath           -> receive email -> validate -> send OTP
POST /$adminPath/verify    -> receive OTP code -> validate -> create session
GET  /logout               -> destroy session -> redirect to /
```

**`config/app.php`**
gets the admin path

```php
    //Admin path
    'admin_path' => 'path',

```

**`config/routes.php`**

- Dynamic Admin path routes / Customisable admin URL path once set per deployment

```php
$adminPath = $config['admin_path'];

$router->get('/' . $adminPath, 'AuthController', 'showLogin');
$router->post('/' . $adminPath, 'AuthController', 'sendOtp');
$router->post('/' . $adminPath . '/verify', 'AuthController', 'verifyOtp');
$router->get('/logout', 'AuthController', 'logout');
```

2. authorised_users — insert first row manually in phpMyAdmin
3. Edit mode bar in main.php
4. OrganisationController — edit form
5. Cloudinary connection
6. Nav + Footer wired to org data
7. Contact page
8. Then everything else replicates

<!--

## ROADMAP/KNOWN ISSUES

- logo_url column in organisation info (semantic image name)
- update SEO SECTION (readme)

V2

- could have:
- implement relationship btw. projects and participants
└── .ics calendar export per event
    └── used for .ics calendar export
    └── set during onboarding
    └── replaces hardcoded 'Europe/Vienna' in app.php
-->
