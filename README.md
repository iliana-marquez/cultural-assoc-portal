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
└── authorised_editors       # Editors, OTP fields, and access control
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
│       │   ├── newsletter_subcribe.php
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
│       ├── emails/
│       │   ├── otp.php
│       │   ├── newsletter.php
│       │   └── newsletter_confirmation.php
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
│       │   ├── archive.php
│       │   ├── contact.php
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
│   ├── Mailer.php                  ← PHPMailer wrapper with renderView()
│   ├── RateLimiter.php             ← session-based rate limiting utility
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

### **Zero-configuration codebase**

No deployment-specific values exist in the codebase. All settings live in `config/app.php` and `.env`.
A developer deploys the OWS by configuring two files only — never by searching and replacing values in code.

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

- DB connection and credentials
- BaseModel prepared statements work
- OrganisationModel fetches real data
- UrlModel fetches related URLs
- Controller passes data to view
- BaseController render chain works
- main.php layout wraps content

DEPLOYED:

```
GitHub → auto-deploys → Hostinger
.env → DB connected
Router → working
Models → tested
Layout → rendering
```

Staging checklist done:

- Git auto-deployment
- Document root configured
- .env path resolution across environments
- DB connection on live server
- Full render chain working

## Second phase...

### Global Data — Available in Every View

`BaseController::render()` automatically passes three data sets
to every view without requiring controllers to pass them manually:

```php
// $data['isLoggedIn'] = $this->isLoggedIn();   // edit mode visibility
$data['config']     = $this->config;          // app settings
$data['org']        = $orgModel->get();       // organisation identity
```

**`$org`** — fetched from `organisation_info` on every render.
Provides organisation name, logo, contact and legal data to
nav, footer and any page that needs it. Single source of truth —
update once in the DB, reflects everywhere instantly.

**`$isLoggedIn`** — session check passed to every view.
Edit mode UI (edit bar, edit icons) rendered only when true —
never present in the DOM for visitors, not even as hidden elements.

**`$config`** — app settings available in views where needed
(e.g. admin path in login/verify forms).

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

- Dynamic Admin path routes / Customisable admin URL path once set per deployment (.env)

```php
$adminPath = $config['admin_path'];

$router->get('/' . $adminPath, 'AuthController', 'showLogin');
$router->post('/' . $adminPath, 'AuthController', 'sendOtp');
$router->post('/' . $adminPath . '/verify', 'AuthController', 'verifyOtp');
$router->get('/logout', 'AuthController', 'logout');
```

**`core/Mailer.php`**

- Composer autoload handles PHPMailer
- `fromName` from controller (org name from DB) - customiseble from user side
- `expiryMin` no default — always passed from controller via config/app.php
- `UTF-8` hardcoded — always correct for emails, not configurable
- Fallback on `fromName` → uses `MAIL_FROM` email address if no name passed
- `renderView` is completely generic: One Mailer class, infinite email types — each with its own clean template.

2. authorised_editors

- `AuthModel` — authorised_editors table, auth only
- `EditorModel` — authorised_editors table, CRUD only
- `AuthController` — can_manage_editors session variable

3. Edit-bar in Editor-Mode in main.php:

- only show it for the logged-in editor. It doesn't exist in the DOM , not hidden with CSS, not rendered at all. Zero HTML output for non-editors.
- Visitors see zero edit UI — not even in page source.
- Tested amd working.

# FEATURE: OTP Authentication & Edit-Mode

Passwordless authentication for website editors using One-Time Passwords (OTP).
Session-based edit mode activates on successful login, making all edit controls visible exclusively to authenticated editors.

## Architecture Decisions

### UserModel as base authentication layer

Rather than a standalone auth utility, authentication logic lives in `UserModel` —
the direct equivalent of Django's `auth.User` or Laravel's `Authenticatable`.
Entity-specific models extend it, inheriting auth methods automatically:

```php
class EditorModel extends UserModel {
    public function __construct() {
        parent::__construct('authorised_editors');
    }
    // inherits: findByEmail, findById, generateOtp, validateOtp, clearOtp
}
```

## Bugs Fixed

**Double increment** — `RateLimiter::increment()` called twice per submission
due to misplaced call before email format validation. Fixed by moving increment
after format check — invalid format does not count as an attempt.

**Session key literal** — `$_SESSION['keyAttempts']` instead of
`$_SESSION[$keyAttempts]`. Counter never reset correctly because the variable
name was used as a string literal instead of its value as the key.

**PRG refresh resubmission** — refreshing the verify page resubmitted the POST
form, incrementing the rate limit counter. Fixed by PRG pattern — refresh hits
GET route, never re-processes the form.

**Verify page after rate limit** — `pending_email` persisted in session after
block, making the verify page accessible via refresh even after rate limit
exhaustion. Fixed by clearing all pending session data on block.

**Session fixation** — `session_regenerate_id(true)` added immediately after
successful OTP verification before writing session variables.

**Verify page loop** — `showLogin()` redirected to verify if `pending_editor_id`
existed, making it impossible to return to the login form. Fixed by removing
the redirect — `/wkk` always renders the login form, pending data overwritten
on next submission.

---

## Testing

```

✅ Login form renders at /{admin_path}
✅ Logged-in editor redirected to / from login page
✅ Invalid email format → error shown, no attempt counted
✅ Unregistered email → verify page rendered (no code sent, no info leaked)
✅ Registered email → OTP email received within seconds
✅ Valid OTP → session created → edit bar visible → edit mode active
✅ Invalid OTP → error message on verify form
✅ Expired OTP → error message on verify form
✅ 3 attempts → rate limit block → "Zu viele Versuche"
✅ Rate limit window expires → fresh attempts allowed
✅ After rate limit block → verify page inaccessible (pending data cleared)
✅ Refresh on verify page → GET, no form resubmission, no counter increment
✅ Direct /verify access without session → redirect to login
✅ Logout → session destroyed → edit bar disappears → visitor view restored
✅ session_regenerate_id on successful login
✅ Organisation data in nav and footer from DB
✅ 404 page renders inside main layout with nav and footer

```

## feat/section-components

### Overview

Dynamic content section system for all pages. Sections stored as JSON
in the `pages` table and rendered via a universal PHP component.
Bootstrap grid handles responsive layout automatically — no custom
media queries needed.

### Architecture

PagesModel::getForPage('home')
↓
render-sections.php loops sections
↓
section.php — universal component
↓
partials/\_content.php + \_image.php + \_controls.php

### Section options

| Option           | Values                                   |
| ---------------- | ---------------------------------------- |
| Theme            | light / dark                             |
| Image position   | left / right / none                      |
| Layout           | 50-50 / 75-25 / 25-75                    |
| Text align       | left / center / right (no image only)    |
| Background image | optional — colour overlay at 90% opacity |
| Image fit        | cover / contain                          |
| CTA              | optional button with label + url         |
| Image credit     | optional photographer credit             |

CTA alignment mirrors image position automatically — no user choice needed.

### Homepage structure

hero.php ← fixed, driven by organisation_info
render-sections.php ← free sections from pages table

### Key decisions

- One universal `section.php` — not one file per layout type
- Partials for reusable sub-components (\_image, \_content, \_controls)
- Edit controls rendered server-side only when logged in — never in DOM for visitors
- Bootstrap grid replaces all custom responsive CSS
- `drop-shadow` filter on logo — follows PNG transparency, no box shadow

### Testing

```
✅ Hero renders with org name, tagline, description and logo from DB
✅ Logo drop-shadow follows PNG shape correctly
✅ Light segment — white background, dark red text
✅ Dark segment — dark red background, white text
✅ Background image with 90% colour overlay on both themes
✅ Image left layout — CTA aligns right automatically
✅ Image right layout — CTA aligns left automatically
✅ No image — text and CTA follow align setting (left/center/right)
✅ 50-50 layout — equal columns
✅ 75-25 layout — text wider, image narrower
✅ 25-75 layout — image wider, text narrower
✅ Image credit renders below image
✅ Image placeholder renders when no image set
✅ object-fit cover — fills frame, crops at 600px max height
✅ object-fit contain — full image visible
✅ CTA button renders with correct URL
✅ Edit controls visible when logged in
✅ Edit controls not in DOM for visitors
✅ Bootstrap stacks to single column on mobile
✅ Multiple sections render in correct order from DB
✅ Empty sections array — no render, no errors
✅ Deployed on Hostinger — sections render from production DB
```

## feat/contact-page

### Overview

Contact page displaying organisation info and a static contact form.
All data driven from DB — zero hardcoded content except website credit.

### Structure

```
Free section (pages table) ← editor customisable intro
Contact info (organisation_info + urls table)
├── Anfragen & Feedback → email (mailto link)
├── Veranstalter → org name + address
├── Social links → from urls table (Instagram, Facebook, YouTube)
├── ZVR registration number
└── Website credit (hardcoded — product attribution)
Static contact form ← functionality in feat/contact-form

```

### Key decisions

```

- Social links fetched from `urls` table — not hardcoded
- `mailto:` link on email — opens visitor's email client directly
- Form extracted as component-ready structure for reuse across pages
- Website credit hardcoded — deployment-independent product attribution
```

### Testing

```
✅ Org name, email, address render from organisation_info
✅ Social icons render with correct URLs from urls table
✅ mailto link opens email client
✅ Registration number renders
✅ Website credit displays correctly
✅ Static form renders — name, email, message, send button
✅ Bootstrap two column layout — info left, form right
✅ Stacks to single column on mobile
✅ Free intro section renders from pages table
✅ Page renders correctly on Hostinger staging
```

## URL Uniqueness — Edge Case

**DB level:** `UNIQUE KEY unique_entity_url (entity_type, entity_id, url)`
prevents duplicate URLs at database level regardless of app logic.

**App level:** `UrlModel::add()` checks for existing URL before INSERT —
returns `false` if duplicate detected.

**UX note (feat/edit-mode):**
Always display existing entity URLs before showing add form.
`add()` returning false → show "URL already exists" message to editor.
One URL per url_type per entity may be enforced in future (e.g. only
one Instagram per organisation) — monitor per client deployment.

## Feature: Universal Pages

v1

```
section_types   → registry only (name + label)
                  templates are hardcoded PHP partials
                  section_types just says "this type exists"

page_sections   → content per page (for now 'pages')
                  page_key + type_key + order + JSON content

partials/       → actual templates (hardcoded PHP)
├── _content.php
├── _image.php
└── _controls.php
```

v2 vision:

```

pages           ← NEW table — page registry
├── id
├── page_key    → 'home', 'ueber-uns', custom pages
├── title
├── slug
└── created_at

page_sections   ← renamed from current 'pages' table
├── page_id     → FK to pages table (replaces page_key string)
├── type_key    → FK to section_types
├── order_index
└── content     → JSON

section_types   ← template registry + dynamic field definitions
├── type_key
├── label
└── fields_schema → drives dynamic template rendering

```

- Editor creates new pages from admin panel — no developer needed ✅
- Pages managed in DB — not hardcoded in routes
- Dynamic routes based on pages.slug
- section_types.fields_schema drives template UI

## feat/pages-universal

### Overview

Universal page system replacing HomeController with a single PageController
that handles all free-section pages. Hero section promoted to a proper
section type — renderable on any page via the pages table. All pages
driven by DB content with no hardcoded page logic.

### Architecture

Request → PageController::show()
↓
derives page_key from URI
↓
PagesModel::getForPage()
↓
render-sections.php
↓
type: 'hero' → hero.php ($org data)
type: 'section' → section.php (JSON content)

### Pages handled by PageController

/ → home
/ueber-uns → about
/alsergrund → district portrait
/partner → partners
/sponsoren → sponsors
/mitglied-werden → membership
/archiv → archive

### Section CRUD routes (edit mode)

POST /page/section/add → add section to page
POST /page/section/{id}/save → update section content
POST /page/section/{id}/delete → hard delete section
POST /page/section/reorder → update order_index

### Key decisions

- HomeController removed — PageController handles all free pages
- Hero promoted to section type — DB-driven, renderable on any page
- page_key derived from URI — no hardcoding per page
- Hard delete for free sections — content blocks are recreatable
- buildSeo() moved to BaseController — available to all controllers
- $org loaded once in BaseController constructor — no duplicate DB calls
- section type mapped in PagesModel — type_key → $section->type

### v2 notes

- pages table → page_sections (rename)
- NEW pages table for page registry — dynamic routing from slug
- section_types.fields_schema → drives dynamic template system
- i18n — UI strings via gettext .po/.mo files
- SEO keywords column on organisation_info and page_sections

### Bugs fixed

- type_key not mapped in PagesModel::getForPage() → hero not rendering
- hero hardcoded in home.php → moved to section type in pages table
- OrganisationModel duplicate fetch → $this->org in BaseController
- SchemaBuilder not loaded globally → added to index.php
- Missing requires in index.php → AuthController, core files

### Testing

- ✅ Homepage renders — hero from DB + intro section from DB
- ✅ Hero pulls org name, tagline, description, logo from organisation_info
- ✅ /ueber-uns — placeholder renders, logged-in editor sees add button
- ✅ /alsergrund — placeholder renders
- ✅ /partner — placeholder renders
- ✅ /sponsoren — placeholder renders
- ✅ /mitglied-werden — placeholder renders
- ✅ /archiv — placeholder renders
- ✅ /kontakt — unaffected, ContactController still handles it
- ✅ Edit bar visible on all pages when logged in
- ✅ Section controls visible on sections when logged in
- ✅ SEO title correct per page
- ✅ 404 still works
- ✅ Deployed and tested on Hostinger staging

## feat/team

### Overview

Team listing and detail pages. Member cards link to individual
profile pages with full biography, motto, profession and social links.
Slug generated at app level — no DB column needed.

### Architecture

GET /team → TeamController::index()
PagesModel::getForPage('team') — free intro sections
TeamModel::getAll() — member cards
GET /team/{slug} → TeamController::show()
TeamModel::getBySlug() — finds member by app-generated slug
UrlModel::getForEntity('team', $id) — member URLs
POST /team/add → TeamController::add()
POST /team/{id}/save → TeamController::save()
POST /team/{id}/delete → TeamController::delete()

### Key decisions

- Slug generated from first + last name at app level — no DB column
- `iconv UTF-8 → ASCII//TRANSLIT` handles umlauts in slugs
- Hard delete — team members not historical record for this system
- URLs via polymorphic urls table — same pattern as organisation
- `TeamModel::displayName()` — static helper, title + first + last name
- `TeamModel::generateSlug()` — static helper, single source of truth
- Detail page SEO uses biography excerpt or motto as description

### Schema changes

```sql
ALTER TABLE team ADD COLUMN profession varchar(150) null AFTER role;
```

### Testing

- ✅ Team listing renders member cards
- ✅ Cards link to /team/{slug}
- ✅ Detail page renders all member fields
- ✅ Placeholder renders when no image
- ✅ Image credit renders when set
- ✅ Member URLs render as icons
- ✅ Back button returns to /team
- ✅ 404 for unknown slug
- ✅ SEO title correct on listing and detail
- ✅ Edit bar visible when logged in
- ✅ Free intro sections render on listing page

## feat/programme

```

Models:
├── VenueModel
├── ParticipantModel
├── EventModel
└── MediaModel

Controllers:
├── ParticipantController  (listing + detail)
└── EventController        (listing + detail + CRUD)

Views:
├── pages/events.php           ← listing (upcoming + past)
├── pages/event-detail.php     ← single event
├── pages/participants.php     ← listing
└── pages/participant-detail.php ← single participant
```

- Entities: venues, participants, events, media — all polymorphic where needed
- Controllers: EventController, ParticipantController, VenueController, MediaController
- Public pages: /veranstaltungen, /veranstaltungen/{slug}, /kuenstlerinnen, /kuenstlerinnen/{slug}
- No public page: venues, media — managed through edit mode only
- Archive: EventModel::getArchive() — pre-2025 filter, same table
- Media: polymorphic — promo/gallery per entity, usable in sections
- Participants: persons + ensembles, name auto-generated from fields
- Status: date-derived at app level — no DB column

On Controllers and Entities:

- Hard delete on Venue, Media and Participants, cascade effect - if a picture belongs to may events, or a venue... ? Revise relations
- if the name is automatic handled by the app, should we have 4 entries? (fist, last, title + name?) when editing the app level validation for data entry should be enogh, or?
-

## feat/programme

### Overview

Full cultural programme system — events, participants, venues and media.
Archive driven by date filter — no separate table needed.
URLs and media use pivot architecture — one record shared across many entities.

---

### Entities and Models

#### VenueModel

Physical locations reused across events.

```
venues
├── name, street, postcode, city, country
└── FK: events.venue_id → ON DELETE SET NULL
Delete venue → events unlinked (venue_id = null), events preserved. ✅
```

#### ParticipantModel

External contributors — individuals and groups.

```
participants
├── type: 'individual' | 'group'
├── individual → title + first_name + last_name
├── group → first_name only (full group name)
└── FK: category_id → participants_categories ON DELETE SET NULL
`displayName()` builds display string at app level — not stored in DB.
`generateSlug()` builds URL slug from displayName — not stored in DB.
Categories optional — participant stays if category deleted. ✅
```

#### EventModel

Programme events — current and historical.

```
events
├── date NOT NULL — unknown historical dates use placeholder
├── status derived at app level from date (upcoming/past)
├── slug generated at app level from title
├── FK: venue_id → ON DELETE SET NULL
├── FK: category_id → ON DELETE SET NULL
└── FK: project_id → ON DELETE SET NULL
Archive threshold: `ARCHIVE_YEAR = 2025`
/veranstaltungen → date >= 2025-01-01
/archiv → date < 2025-01-01
```

#### MediaModel — pivot architecture

```
media (stored once)
├── media_url UNIQUE
├── stage: 'promo' | 'gallery'
└── order_index
entity_media (pivot)
├── media_id → FK media ON DELETE CASCADE
├── entity_type → 'event' | 'participant' | 'venue' | 'organisation' | 'team'
└── entity_id
One media item linked to many entities.
Fix caption once → reflects everywhere. ✅
Delete entity → unlink pivot row → media preserved if linked elsewhere.
Delete media → CASCADE removes all pivot links. ✅
```

#### UrlModel — pivot architecture

```
urls (stored once)
├── url UNIQUE
├── url_type_id → FK url_types
└── label
entity_urls (pivot)
├── url_id → FK urls ON DELETE CASCADE
├── entity_type → 'event' | 'participant' | 'team' | 'organisation' | 'venue'
└── entity_id
One URL linked to many entities.
Barockfestival URL stored once → linked to events 7, 8, 9. ✅
Fix URL once → reflects on all linked entities. ✅
Manager never deals with duplicate URL maintenance. ✅
```

#### EventModel — pivot relationships

```
event_participants (many-to-many pivot)
├── event_id → FK events ON DELETE CASCADE
└── participant_id → FK participants ON DELETE CASCADE
Delete event → pivot rows cascade. ✅
Delete participant → removed from all events automatically. ✅
```

---

### Why pivot over polymorphic direct linking

**Before (polymorphic direct):**

```
urls
├── entity_type → 'event'
├── entity_id → 7
└── url → 'barockfestival.at'

urls
├── entity_type → 'event'
├── entity_id → 8
└── url → 'barockfestival.at' ← stored twice
```

URL changes → must update 3 rows. Manager may only find one. ❌

**After (pivot):**

```
urls
└── id: 10, url: 'barockfestival.at' ← stored once
entity_urls
├── url_id: 10, entity_type: 'event', entity_id: 7
├── url_id: 10, entity_type: 'event', entity_id: 8
└── url_id: 10, entity_type: 'event', entity_id: 9
```

URL changes → update once → all linked entities reflect change. ✅

---

### Participant type system

```
type: individual
├── title (optional) → Dr., Mag., Prof.
├── first_name → required
├── last_name → required
└── displayName() → "Dr. Anton Guberov"
type: group
├── first_name → full group name e.g. "Ensemble Freymut"
├── last_name → null
└── displayName() → "Ensemble Freymut"
Categories handle what kind of group (Ensemble, Orchestra, Theatre).
Type handles HOW the name is displayed. ✅
```

---

### Slug generation

No slug column in DB — generated at app level:

```php
// Events
EventModel::generateSlug($event->title)
→ 'wortwitz-saitenspiel'

// Participants
ParticipantModel::generateSlug($participant)
→ calls displayName() first
→ 'peter-nolan' | 'ensemble-musik'

// Team
TeamModel::generateSlug($member->first_name, $member->last_name)
→ 'monica-schuhmacher'
```

Name changes → slug changes automatically. No migration needed. ✅

---

### Controllers

```
EventController
├── GET /veranstaltungen → index() — upcoming + past
├── GET /veranstaltungen/{slug} → show() — detail
├── GET /archiv → archive() — pre-2025
├── POST /events/add → add()
├── POST /events/{id}/save → save()
├── POST /events/{id}/delete → delete()
├── POST /events/{id}/participant/add → addParticipant()
└── POST /events/{id}/participant/remove → removeParticipant()
ParticipantController
├── GET /kuenstlerinnen → index()
├── GET /kuenstlerinnen/{slug} → show()
├── POST /participants/add → add()
├── POST /participants/{id}/save → save()
└── POST /participants/{id}/delete → delete()
```

---

### Testing

- ✅ /veranstaltungen — upcoming and past events render
- ✅ Event cards — title, date, venue, category, promo image
- ✅ /veranstaltungen/{slug} — full event detail
- ✅ Participants render as chips with links on event detail
- ✅ Review renders on past events
- ✅ /archiv — pre-2025 events render
- ✅ /kuenstlerinnen — all participants render as cards
- ✅ individual: title + first + last name displayed
- ✅ group: first_name only displayed
- ✅ /kuenstlerinnen/{slug} — participant detail
- ✅ Events list on participant detail
- ✅ Cross-reference: event → participant → event ✅
- ✅ Social URLs render via entity_urls pivot
- ✅ Promo images render via entity_media pivot
- ✅ 404 for unknown slugs
- ✅ Edit bar visible when logged in
- ✅ Deployed and tested on Hostinger staging

## feat/cloudinary

### Overview

Server-side Cloudinary integration for image and video uploads.
No SDK — pure PHP curl against Cloudinary REST API.
Files organised per client deployment via CLOUDINARY_FOLDER env var.
Media linked to any entity via polymorphic pivot table.

---

### Architecture

```
Editor uploads file (edit mode)
↓
MediaController::upload()
↓
CloudinaryService::upload()
├── detects resource_type (image | video)
├── generates public_id from entity context
├── signs request with API secret
└── POSTs to Cloudinary REST API
↓
Returns secure_url + public_id + resource_type
↓
MediaModel::addForEntity()
├── inserts into media table (url, resource_type, stage, caption)
└── links to entity via entity_media pivot
↓
Views read resource_type → render <img> or <video>
```

### Cloudinary folder structure

```
CLOUDINARY_FOLDER/ ← from .env e.g. 'ows/wkk'
├── team/ ← team member photos
├── events/ ← event promo + gallery
├── participants/ ← artist photos
└── pages/ ← section background images

Per client deployment — set `CLOUDINARY_FOLDER=ows/client-name` in `.env`. ✅
```

### Resource types

- image → jpg, png, webp, gif — renders as <img> with zoomable class
- video → mp4, mov, webm — renders as <video controls>
- Detected automatically from file mime type — no editor choice needed. ✅

### Signature generation

Cloudinary requires signed requests for server-side uploads:

```php
ksort($params);
$paramString = key=value&key=value (plain concatenation, no URL encoding)
$signature   = sha1($paramString . $apiSecret)
```

No URL encoding — common mistake that causes Invalid Signature errors. ✅

### Environment variables

CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key ← Admin role for full access
CLOUDINARY_API_SECRET=your_api_secret
CLOUDINARY_FOLDER=ows/client-name ← per deployment

### Media delete flow

```
Editor deletes media item
↓
MediaModel::unlinkFromEntity() → removes pivot row
↓
Check remaining links → if none → delete media record
↓
CloudinaryService::delete(publicId, resourceType)
→ removes from Cloudinary storage ✅
```

### Additional features

**Zoomable images** — event detail media renders with `.zoomable` class:

```css
.zoomable {
  cursor: zoom-in;
}
```

Click → opens full Cloudinary URL in new tab. No library needed. ✅

### Files

core/CloudinaryService.php ← upload, delete, generatePublicId
app/Controllers/MediaController.php ← upload, delete, reorder routes
app/Models/MediaModel.php ← resource_type added
app/Models/EventModel.php ← admission + ticket_url added
app/Views/pages/event-detail.php ← zoomable media, admission display
public/assets/css/main.css ← zoomable CSS
public/assets/js/app.js ← click-to-zoom JS

### Testing

- ✅ CloudinaryService uploads image — returns secure_url
- ✅ CloudinaryService uploads video — returns secure_url
- ✅ Signature generation correct — no Invalid Signature error
- ✅ File appears in Cloudinary Media Library under correct folder
- ✅ resource_type stored correctly in media table
- ✅ entity_media pivot linked correctly
- ✅ event-detail renders image with zoomable class
- ✅ event-detail renders video with controls
- ✅ Click zoomable image → opens full size in new tab
- ✅ Admission free → Eintritt frei renders
- ✅ Admission donation → Spenden willkommen renders
- ✅ Admission ticket → Tickets kaufen link renders

## feat/event-detail-enhancements (on main)

**Event admission** — new column on events table:

Admission system:

```
3 types:
├── free → 🤝 Eintritt frei · Spenden willkommen
├── donation → 🤝 Eintritt frei · Spenden willkommen ab {amount}
├── reserve → 🎫 Anmeldung erforderlich + Jetzt anmelden button
└── ticket → 🎫 Tickets: {amount} + Tickets kaufen button

3 columns on events table:
├── admission varchar(50) — type
├── admission_amount varchar(100) — flexible amount string
└── admission_url varchar(255) — action link (reserve + ticket only)
```

Event detail layout restructure:

- Row 1: [Promo image / carousel] | [Title, meta, description, participants, admission]
- Row 2: [Review] | [Video] (only if content exists)
- Row 3: [Gallery grid — zoomable]
- Back link

Media improvements:

- Multiple promo images → Bootstrap carousel
- Single promo image → static + zoomable
- Gallery images → grid, zoomable
- Videos → detected by URL extension (no DB column)
- Zoomable → scale on hover + click opens full size

CSS/UX fixes:

- btn-section → inline-flex, icon + text aligned
- nav-icon-ux → new class for back links with icon

## feat/edit-mode

### Overview

Inline editing system for OWS — free sections and entity data.
No page reloads. All changes via AJAX. Two patterns:

- **editable-block** → free sections (layout, content, images)
- **entity-edit-row** → structured entity data (org info, events, team)

---

### Architecture

#### Free Sections — editable-block

PHP renders section with .editable-block wrapper

Editor clicks pencil → activateBlock()

```
├── block.classList.add('editing') → indigo border + controls
├── Toggle data-values saved before clone
├── .block-edit-controls cloned → stale listeners cleared
├── Toggle data-values restored on fresh clone
├── contentEditable = true on [data-field] elements
├── Field labels shown (Titel / Untertitel / Text)
└── body.classList.add('is-editing') → pulse indicator
On Speichern:
├── All [data-field] innerText collected
├── All [data-toggle] data-values collected
├── AJAX POST → /page/section/{id}/save
├── PageController merges into existing JSON
└── deactivateBlock() → resets state
```

- Layout toggles → updateColumns() updates .section-text-col + .section-image-col
- Flip → flipImage() uses insertBefore for correct DOM order
- Image upload → Cloudinary → img-placeholder innerHTML swap
- BG upload → .bg-btn-wrap innerHTML swap

#### Entity Edit Rows — entity-edit-row

```
[label ─────────────── ✏️ / ✓ ✗]
[span.entity-field data-field="x" ]
```

-Inactive: pencil visible, grey header
-Active: save/cancel visible, indigo header, span contenteditable
-Save: AJAX POST → field=value → OrganisationController::save()

→ OrganisationModel::updateField()

#### Edit bar

[● Bearbeitungsmodus] [Vereinsinfo | Abmelden]
body.is-editing → green pulsing indicator

Vereinsinfo → /wkk/org (all org data)

Abmelden → /logout

---

### CSS Variables

```css
--edit-inactive: rgba(120, 120, 120, 0.4) /* grey */ --edit-active: indigo /* active state */;
```

Change once in :root → entire edit UI recolours. ✅

---

### Key decisions

- **No full page reloads** — all changes via AJAX, immediate DOM update
- **Clone pattern** — .block-edit-controls cloned on each activation to clear stale listeners. Save/Cancel outside clone zone — attached once at init.
- **Named col classes** — .section-image-col + .section-text-col for reliable JS targeting
- **img-placeholder unified** — one class for image and placeholder, ratio modifiers per context
- **Org data centralised** — /wkk/org only, contact.php display only
- **BG button swap** — .bg-btn-wrap innerHTML swapped after upload/remove

---

### Files

```
public/assets/js/edit-mode.js ← edit mode JS — all interactions
public/assets/css/edit-mode.css ← edit mode CSS — variables driven
app/Views/components/sections/
├── section.php ← editable-block wrapper, named col classes
├── partials/
│ ├── \_controls.php ← pencil/controls/save/cancel, bg-btn-wrap
│ ├── \_content.php ← data-field attributes, field labels
│ └── \_image.php ← img-placeholder block-img
app/Controllers/OrganisationController.php ← edit + save
app/Models/OrganisationModel.php ← updateField()
app/Views/pages/org-edit.php ← /wkk/org edit page
app/Views/components/edit-bar.php ← sticky edit bar
```

---

### Testing

- ✅ Free section text editing — saves and persists
- ✅ Theme toggle — renders immediately
- ✅ Layout toggle — both cols resize immediately
- ✅ Flip — image moves left/right immediately
- ✅ Fit toggle — object-fit changes immediately
- ✅ Align toggle — text alignment changes immediately
- ✅ Bildspalte — col appears/disappears correctly
- ✅ 100-100 stacked layout
- ✅ Image upload — renders in correct col immediately
- ✅ Image remove → placeholder restores
- ✅ BG upload → renders immediately, button swaps
- ✅ BG remove → clears immediately, button swaps
- ✅ Save/Cancel work on first and subsequent edits
- ✅ Toggle state preserved across multiple activations
- ✅ Blinking indicator when editing
- ✅ Unsaved changes warning on page leave
- ✅ /wkk/org — all org fields editable
- ✅ Single field AJAX save with feedback
- ✅ Edit bar — Vereinsinfo link + Abmelden

## Bug Fixes — feat/edit-mode

---

### 1. Save/Cancel listeners duplicated on re-activation

**Bug:** Save/Cancel stopped working after first edit session.  
**Cause:** Listeners attached inside `activateBlock()` — called on every activation — stacking multiple listeners on same buttons.  
**Fix:** Save/Cancel moved outside `.block-edit-controls` clone zone. Attached once at init via `document.querySelectorAll('.editable-block').forEach`.

---

### 2. Toggle data-values reset on re-activation

**Bug:** Layout/flip/theme reverted to DB values on second edit without refresh.  
**Cause:** `.block-edit-controls` clone on activation reset all `data-value` attributes to PHP-rendered values.  
**Fix:** Save all `data-toggle` values before clone, restore on fresh clone in `activateBlock()`.

---

### 3. `updateColumns()` stripped named col classes

**Bug:** Layout toggle worked once then broke — image col lost `section-image-col` class.  
**Cause:** `imageCol.className = c.image` replaced entire className, removing `section-image-col`.  
**Fix:** `imageCol.className = c.image + ' section-image-col' + hidden` — always preserves named class.

---

### 4. Image col not found by JS

**Bug:** `block.querySelector('.section-image-col')` returned null.  
**Cause:** Hidden image col (text block) rendered before content col in DOM — `section-image-col` class missing from second col.  
**Fix:** Named classes `section-image-col` and `section-text-col` added to PHP output. Hidden col always renders after content col.

---

### 5. Flip moved cols vertically instead of horizontally

**Bug:** Flip button pushed image col above or below text.  
**Cause:** `row.appendChild(imageCol)` moved col to end but Bootstrap row had wrong col widths — both cols adding up to more than 12.  
**Fix:** `updateColumns()` always sets complementary sizes (text + image = 12). `flipImage()` uses `insertBefore` for both directions.

---

### 6. Placeholder rendered outside col layout

**Bug:** Image placeholder appeared outside the Bootstrap column.  
**Cause:** `$isImageBlock` evaluated as false when `image: null` — image col not rendered at all, placeholder floated outside row.  
**Fix:** `$showImageCol = $isImageBlock && ($hasImage || $isLoggedIn)` — col renders based on `image_pos`, not image presence.

---

### 7. `MediaController::jsonSuccess()` not found

**Bug:** Image upload silently failed — no JSON response.  
**Cause:** `jsonSuccess()` was `private` in `PageController` — not inherited by `MediaController`.  
**Fix:** Moved `jsonSuccess()` and `jsonError()` to `BaseController` as `protected`.

---

### 8. BG button not swapping after upload/remove

**Bug:** After BG upload, button stayed as `+ BG`. After remove, stayed as `BG entfernen`.  
**Cause:** BG button was a direct PHP-rendered element — no JS DOM update after state change.  
**Fix:** Wrapped in `.bg-btn-wrap`. JS swaps `innerHTML` of wrapper after upload/remove and re-runs `initImageControls()`.

---

### 9. Save button stuck disabled after first save

**Bug:** Save button disabled after first successful save — second edit couldn't save.  
**Cause:** `saveBlock()` set `saveBtn.disabled = true` but `deactivateBlock()` never reset it.  
**Fix:** `deactivateBlock()` resets `saveBtn.disabled = false` and restores check icon.

---

### 10. Field labels visible when block inactive

**Bug:** Titel / Untertitel / Text labels appeared on page load.  
**Cause:** CSS `.edit-field-label { display: none }` overridden by cascade — later rule winning.  
**Fix:** Labels get `style="display:none"` inline from PHP. JS explicitly sets `display: block/none` on activate/deactivate — bypasses CSS cascade entirely.

### 11. OrganisationController save — DB not updating on production

**Bug:** Org fields saved with `Gespeichert ✓` feedback but DB not updating on production.
**Cause:** `updateField()` hardcoded `WHERE id = 1` but production DB had organisation record at `id = 2`.
**Solution:** Updated `id` on production DB:

```sql
UPDATE organisation_info SET id = 1 WHERE id = 2;
```

# feat/programme-edit

## Overview

- Inline editing of event content directly on the public event detail page (`/veranstaltungen/{slug}`), visible only to logged-in editors
- Covers all event text fields, venue/admission/category selection, participant management, and a full promo + gallery media system
- No separate admin panel — editing happens in place on the live page, gated entirely by `$isLoggedIn`

## Architecture

- Three shared row patterns, all sharing one visual language (neutral border inactive, indigo border + header when `.editing`):
  - `entity-edit-row` — plain text/textarea fields (title, subtitle, description, date, time, review, admission amount/url)
  - `entity-select-row` — dropdown fields (venue, admission type, category); current value shown as display text when inactive, `<select>` gated until editing
  - `media-edit-row` — wraps the promo image block and the gallery block
- `participants-edit-row` — list-based variant: add via dropdown, remove via per-item trash button, empty-state placeholder mirrors the gallery's
- Media model: all media stored once in a shared `media` table, linked to entities via an `entity_media` pivot; `stage` (`promo`/`gallery`) distinguishes role; `caption` (alt text) and `credit` (photographer attribution) are separate columns
- No page reloads for any edit action, with one deliberate exception: deleting an image from a multi-image carousel falls back to a full reload (see Known Issues)

## Development

- Built field by field: text fields → venue/admission selectors → participants → promo image → gallery → category selector → new-event creation flow → bug fixes
- Gallery batch upload, photo selection, and caption/credit modal built as one connected system, reusing the single-image delete endpoint for bulk delete rather than building a second endpoint
- New-event creation flow surfaced two real bugs (see Bugs / Fixes) that required fixing before the feature could be considered stable

## Files

- **`event-detail.php`** — full inline-edit view; local `$editRow()` closure for text fields; explicit markup for venue/admission/category select-rows; participants block with add/remove + empty-state; promo block (single image / Bootstrap carousel); gallery block (dropzone, checkboxes, select-all, batch caption/credit/delete); shared meta modal; delete-event button
- **`edit-mode.js`** — all client-side behavior, additive only; generic `entity-edit-row` handler (now syncs URL on title save); `entity-select-row` handler; `participants-edit-row` handler (DOM-only, shared `bindRemoveParticipant()`, empty-state restore); `media-edit-row` handler for both promo and gallery (sequential batch upload, slug-based filenames, `has-selection`/`has-items` state classes, bulk delete); new-event button handler
- **`edit-mode.css`** — additive only, no existing rules modified; new rules for the three row types, gallery checkboxes, dropzone, modal, and visibility fixes for buttons that were invisible on light backgrounds
- **`EventController.php`** — `add()` creates a minimal event, returns its slug; `save()` returns the new slug specifically when `title` changes
- **`EventModel.php`** — `add()` returns the new row's `int` id; `updateField()` whitelist includes `category_id`
- **`MediaController.php`** — `upload()` accepts optional `public_id` and `credit`, returns the new media id; added `updateMeta()` and `batchMeta()`
- **`MediaModel.php`** — `addForEntity()` returns `int` id; `update()` rewritten as a genuine partial update
- **Database** — `media.credit` column added; production `media.id` primary key/auto-increment restored

## Bugs / Fixes

- **Production `media` table missing `PRIMARY KEY`/`AUTO_INCREMENT`** — traced to an earlier DB wipe/reimport session being interrupted before reaching the schema-altering statements near the end of the dump. Caused every promo upload to fail with a `SQLSTATE[HY000]` error. Fixed via direct `ALTER TABLE`.
- **Duplicate events created via reload-based participant actions** — `EventModel::getBySlug()` computes the slug from the current title on every call rather than storing it; a stale browser URL (from an earlier title edit) combined with `window.location.reload()` on participant-add caused mismatched/duplicate event creation. Fixed by removing the reload entirely (DOM update instead) and syncing the URL via `history.replaceState()` whenever the title changes.
- **Fatal error on new-event creation** — `EventModel::add()` returned `bool`, while the controller tried to call the model's `protected` `lastInsertId()` directly. Fixed by having `add()` return the new row's `int` id.
- **`MediaModel::update()` positional SQL/parameter mismatch** — the `SET` clause listed 4 columns while 5 parameters were passed, silently writing `credit`'s value into the `stage` column. Fixed by rewriting `update()` as a genuine partial update keyed by column name, not position.
- **Cloudinary filenames used numeric entity id instead of slug** — gallery batch uploads correctly sent a slug-based `public_id`, but `MediaController::upload()` never read it from the request, always falling back to the generic `entityType-entityId-timestamp` pattern. Fixed by honoring a posted `public_id` when present.

## Testing

- All text field saves persist after reload
- Venue/admission/category selectors display correctly when inactive, save correctly when changed
- Participants can be added/removed without a page reload; empty-state placeholder appears/disappears correctly
- Promo image upload/delete works for empty-placeholder and existing-image cases; placeholder rebuilds in DOM after delete (single-image case; multi-image carousel intentionally reloads)
- Gallery drag-and-drop batch upload works for multiple simultaneous files with sequential progress feedback
- Gallery checkbox selection, select-all, and caption/credit modal apply correctly to one or many photos
- Gallery bulk-delete removes selected photos and restores empty-state when gallery becomes empty
- New-event creation redirects into a real, unique database row
- Title edits update the browser URL without a reload
- No automated tests written for this feature
<!--

## ROADMAP/KNOWN ISSUES

MUST HAVE — after core features complete:
└── newsletter_subscribers
├── signup form on website
├── double opt-in (GDPR required — EU law)
├── confirmation email with token
├── unsubscribe link in every email
└── confirmed subscribers list for super admin

Automated newsletter generation
on events "new event" "cancelled event"
record_status: active, cancelled, deleted (no hard delete)

```sql
id          int PK
email       varchar(255) unique
name        varchar(100) null
confirmed   boolean default false
token       varchar(255) null      ← temporary, cleared after confirmation
created_at  timestamp
```

```
Enter email → "Check your inbox for confirmation"
      ↓
One click in email → confirmed
      ↓
"You're subscribed!" — no further steps
```

<!-- - logo_url column in organisation info (semantic image name)
- update SEO SECTION (readme)

V2 -->

<!-- - could have:
- implement relationship btw. projects and participants
- .ics calendar export per event
  └── used for .ics calendar export
  └── set during onboarding
  └── replaces hardcoded 'Europe/Vienna' in app.ph
- Move to organisation_info
  - ADMIN_PATH="value" (so it is customisable form the client side)
- Timezone
  No APP_TIMEZONE or APP_LOCALE in .env
  No hardcoded values in config/app.php
  Derived automatically from organisation_info.country

```php
// future v2 logic in app.php or a helper
$country = $org->country ?? 'Austria';

$timezones = [
    'Austria'     => 'Europe/Vienna',
    'Germany'     => 'Europe/Berlin',
    'France'      => 'Europe/Paris',
    'Spain'       => 'Europe/Madrid',
];

$locales = [
    'Austria'     => 'de_AT',
    'Germany'     => 'de_DE',
    'France'      => 'fr_FR',
    'Spain'       => 'es_ES',
];

$timezone = $timezones[$country] ?? 'UTC';
$locale   = $locales[$country]   ?? 'en_US';
```

## v2 — automated event newsletter:

OWS generates newsletters automatically from your data (event, project, news), styled with your organisation's branding — no external tools, no manual formatting, no copy-pasting.
├── native branding from organisation_info
├── event content auto-populated
├── consistent web + email visual identity
├── zero external newsletter dependency
└── triggered on event status → 'published'

## v2 — data-source agnostic auth:

└── consider Laravel-style Authenticatable interface
└── any entity (editor, member) can authenticate via OTP
└── AuthModel becomes a pure OTP utility

--> -->

Mentions:

[Tabler-Icons](https://tabler.io/icons)

🛠️ _Developed by [Iliana Márquez](https://ilianamarquez.com)_
