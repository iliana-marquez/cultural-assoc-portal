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

# feat/programme-edit #20

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
- Promo media rendering (single image / carousel / empty placeholder) lives once in a reusable partial (`components/event/promo-media.php`), included by both the full page and a small fragment endpoint — no rendering logic duplicated between initial load and post-edit DOM updates
- Caption/credit editing uses a single, generic, site-wide `input-modal` component (`components/input-modal.php`), rendered once in the main layout and controlled entirely from `edit-mode.js` via `openInputModal({...})` — both promo (single-image, per-button) and gallery (batch, per-selection) call the same modal with their own config, rather than each maintaining a separate modal
- No page reloads for any edit action, including carousel image delete/upload, which now goes through the fragment endpoint rather than a full reload

## Development

- Built field by field: text fields → venue/admission selectors → participants → promo image → gallery → category selector → new-event creation flow → bug fixes → carousel fragment fix → promo caption/credit → modal consolidation
- Gallery batch upload, photo selection, and caption/credit modal built as one connected system, reusing the single-image delete endpoint for bulk delete rather than building a second endpoint
- Carousel delete/upload originally fell back to a full page reload; later replaced by extracting promo rendering into a reusable partial shared with a new fragment endpoint, fixing both the delete-side and a previously-undiscovered upload-side bug (uploading a second image never upgraded the single-image view into a carousel)
- Promo images gained the same caption/credit editing gallery already had, surfacing a fragile text-parsing bug (regex-based extraction of existing values from rendered HTML) that was fixed by storing raw values in `data-caption`/`data-credit` attributes instead
- Gallery's and promo's previously-independent modal-handling code were consolidated into one shared, headless `input-modal` component and controller, removing the risk of two competing click listeners on the same shared DOM element

## Files

- **`event-detail.php`** — full inline-edit view; local `$editRow()` closure for text fields; explicit markup for venue/admission/category select-rows; participants block with add/remove + empty-state; promo block now delegates to `promo-media.php` via include; gallery block (dropzone, checkboxes, select-all, batch caption/credit/delete, `data-caption`/`data-credit` on each checkbox); delete-event button. No longer contains modal markup.
- **`components/event/promo-media.php`** _(new)_ — reusable partial rendering promo media in all three states (single image, carousel, empty placeholder); includes caption/credit/delete overlay buttons with `data-caption`/`data-credit` attributes; carousel auto-advance disabled for logged-in editors (`data-bs-ride="false"`), preserved for public visitors
- **`components/input-modal.php`** _(new)_ — generic, headless text-input modal; no hardcoded purpose-specific content; rendered once site-wide via `main.php`
- **`main.php`** — now includes `input-modal.php` alongside `edit-bar.php`, gated by the same `$isLoggedIn` check
- **`edit-mode.js`** — all client-side behavior, additive only; generic `entity-edit-row` handler (syncs URL on title save); `entity-select-row` handler; `participants-edit-row` handler (DOM-only, shared `bindRemoveParticipant()`); `media-edit-row` handler for both promo and gallery; new-event button handler; shared `inputModal` controller (`openInputModal`/`closeInputModal`/`bindInputModalOnce`) used by both promo's `initPromoMetaButtons()` and gallery's `openBatchMetaModal()`; promo fragment fetch-and-swap (`/events/{id}/promo-fragment`) replacing the old reload fallback for both upload and delete; live carousel position counter (`updatePromoCount()`) tracking Bootstrap's `slid.bs.carousel` event
- **`edit-mode.css`** — additive only, no existing rules modified; new rules for the three row types, gallery checkboxes, dropzone, light-background button fixes, and the renamed `input-modal-*` selectors
- **`EventController.php`** — `add()` creates a minimal event, returns its slug; `save()` returns the new slug specifically when `title` changes; new `promoFragment()` method renders the promo partial as a standalone HTML fragment for AJAX consumption
- **`EventModel.php`** — `add()` returns the new row's `int` id; `updateField()` whitelist includes `category_id`
- **`MediaController.php`** — `upload()` accepts optional `public_id` and `credit`, returns the new media id; added `updateMeta()` and `batchMeta()`
- **`MediaModel.php`** — `addForEntity()` returns `int` id; `update()` rewritten as a genuine partial update
- **Database** — `media.credit` column added; production `media.id` primary key/auto-increment restored

## Bugs / Fixes

- **Production `media` table missing `PRIMARY KEY`/`AUTO_INCREMENT`** — traced to an earlier DB wipe/reimport session being interrupted before reaching the schema-altering statements near the end of the dump. Caused every promo upload to fail with a `SQLSTATE[HY000]` error. Fixed via direct `ALTER TABLE`.
- **Duplicate events created via reload-based participant actions** — `EventModel::getBySlug()` computes the slug from the current title on every call rather than storing it; a stale browser URL combined with `window.location.reload()` on participant-add caused mismatched/duplicate event creation. Fixed by removing the reload entirely and syncing the URL via `history.replaceState()` on title save.
- **Fatal error on new-event creation** — `EventModel::add()` returned `bool`, while the controller tried to call the model's `protected` `lastInsertId()` directly. Fixed by having `add()` return the new row's `int` id.
- **`MediaModel::update()` positional SQL/parameter mismatch** — the `SET` clause listed 4 columns while 5 parameters were passed, silently writing `credit`'s value into the `stage` column. Fixed by rewriting `update()` as a genuine partial update keyed by column name.
- **Cloudinary filenames used numeric entity id instead of slug** — `MediaController::upload()` never read a posted `public_id`, always falling back to the generic pattern. Fixed by honoring a posted `public_id` when present; applied to both promo and gallery uploads.
- **Carousel arrows/auto-advance silently broken in edit mode** — root cause was not CSS or Bootstrap initialization, but the upload success handler hand-building single-image HTML regardless of how many promo images actually existed, so a second upload never produced a real carousel. Fixed by routing both upload and delete through the same server-rendered fragment.
- **In-progress feedback messages auto-clearing before the real result arrived** — `showEntityFeedback()` always scheduled a 3-second auto-clear regardless of message type. Fixed with an optional `persistent` flag and cancellation of any previously scheduled clear timer.
- **Caption/credit pre-fill silently wrong or missing** — existing values were recovered by regex-parsing rendered display text (`📷 Alex Ronacher` → only "Ronacher" survived a greedy single-word strip), and credit specifically could be entirely unrecoverable when an image had a caption but no credit yet, since the display span for credit was conditionally absent from the DOM. Fixed by storing raw values in `data-caption`/`data-credit` attributes, read directly with no parsing.
- **Two independent modal handlers attached to one shared DOM element** — gallery's and promo's caption/credit modals each attached their own listeners to the same `#mediaMetaModal`, relying on each handler's own empty-selection guard to avoid firing incorrectly. Consolidated into one shared `input-modal` component with a single owner.

## Testing

- All text field saves persist after reload
- Venue/admission/category selectors display correctly when inactive, save correctly when changed
- Participants can be added/removed without a page reload; empty-state placeholder appears/disappears correctly
- Promo image upload/delete works for empty-placeholder, single-image, and carousel cases, with no reload in any case
- Carousel correctly upgrades from single-image on second upload; correctly collapses back to single-image when deleted down to one remaining image
- Carousel auto-advance disabled in edit mode; manual arrow/indicator navigation still functional; live position counter updates on slide change
- Promo and gallery caption/credit modals correctly pre-fill existing values (single value for promo; shared value across a multi-select gallery batch when all selected items agree, blank with an explanatory placeholder when they don't)
- Gallery drag-and-drop batch upload works for multiple simultaneous files with sequential progress feedback that remains visible for the full duration of a slow upload
- Gallery checkbox selection, select-all, and caption/credit modal apply correctly to one or many photos
- Gallery bulk-delete removes selected photos and restores empty-state when gallery becomes empty
- New-event creation redirects into a real, unique database row
- Title edits update the browser URL without a reload
- No automated tests written for this feature

# feat/related-entity-lifecycle-urls

## Overview

- Lets an editor search for an existing external link or add a new one, with the right type validated automatically, directly from whatever entity hosts it — events, organisation info, and (later) participants
- Replaces what would otherwise be a separate "manage all links" admin screen — every link an editor would ever touch is already visible wherever it's actually used
- Built once as a single reusable partial and JS/backend system; proven on two entities (event, organisation) with zero duplicated markup or logic between them

## Architecture

- URLs are stored once in `urls`, linked to any entity via the generic `entity_urls` pivot (`entity_type` + `entity_id`) — the same shared-resource pattern already used by `media`/`entity_media`
- One URL can be linked to many entities at once; editing it once updates everywhere it's referenced; unlinking only deletes the underlying row when no other entity references it anymore (checked automatically before every removal)
- Validation has two authorities, deliberately kept in sync: the server (`UrlModel::validateForType()`) is the real, unbypassable check; the client mirrors the same rules for instant feedback, but the server re-validates every request regardless of what the client already approved
- Three generic, reusable modal components were built or significantly reworked for this feature: `input-modal` (single text value, e.g. caption/credit), `confirm-modal` (named-consequence confirmation, replacing native `confirm()`), `attach-entity-modal` (search-existing-or-add-new shell, with a third "edit existing" mode added later) — none of them know anything about URLs specifically, so any future entity type can reuse them unchanged
- The entire feature's markup — header, pencil/cancel toggle, the list, the add trigger — lives in one file, `entity-urls.php`, deliberately not split across separate header/list/item files; the AJAX refresh after any action re-renders this same file with a `$fragmentOnly` flag, swapping only the inner list container's contents, never the header — so editing and viewing the rendered markup are never two diverging code paths
- Every add/edit/remove action re-fetches and swaps in real, server-rendered HTML rather than constructing it in JavaScript — eliminates an entire class of drift bugs between what JS guesses the markup should look like and what the PHP partial actually renders

## Development

- Built in stages: model and controller first (verified via a disposable browser-based test page before any UI existed), then the generic modal shell, then the URL-specific configuration and validation on top of it, then the visual integration (pencil/cancel toggle, button placement) to match the rest of the editor's established conventions
- Validation rules were extended iteratively based on actual manual testing, not speculative edge cases — each addition (domain-shape rejection, Maps' multi-domain handling, extending the platform-mismatch warning beyond just "Website") came from a specific, reproduced problem, not a guess at what might go wrong
- A genuine three-file partial split (header/wrapper, list, per-item) was built, then deliberately collapsed back into one file after determining the split was never actually load-bearing — the real protection for the header's JS event listeners comes from precise DOM targeting in the refresh function, not from any PHP file boundary
- Repeated, deliberate file-state verification throughout — multiple rounds of fixes were built against a stale copy of `edit-mode.js`/`attach-entity-modal.js` before being caught and corrected, reinforcing the practice of checking real file content before editing rather than trusting prior context

## Files

- **`UrlModel.php`** — `getForEntity()`, `addForEntity()` (create-or-reuse via the unique URL string, returns the resolved id), `attachToEntity()`, `unlinkFromEntity()` (orphan-check-then-delete), `delete()` (explicit force-delete), `update()`, `getTypes()`, `getById()`, `countLinks()`, `normalize()` (https upgrade, trailing-slash strip, domain lowercasing, automatic `mailto:` for the Email type), `validateForType()` (email shape, generic domain.tld shape, per-platform domain suffix matching, Maps' multi-domain rule)
- **`UrlController.php`** — `search`, `types`, `add`, `attach`, `unlink` (two-step: returns `needsConfirmation` before actually deleting an orphaned link), `delete`, `save`, `fragment` (re-renders `entity-urls.php` with `$fragmentOnly = true`, returning just the inner list HTML)
- **`components/entity-urls.php`** — the single, consolidated partial: header (pencil/cancel, label, add-trigger) plus the list/empty-state/per-item markup, toggled entirely by `$isLoggedIn` and `$fragmentOnly`. Required variables: `$entityType`, `$entityId`, `$urls`, `$isLoggedIn`
- **`components/modals/input-modal.php`**, **`confirm-modal.php`**, **`attach-entity-modal.php`** — the three generic modal components, plus their matching JS controllers (`confirm-modal.js`, `attach-entity-modal.js`, and `input-modal`'s controller inline in `edit-mode.js`)
- **`edit-mode.js`** — the `.links-edit-row` handler: pencil/cancel toggle, the add-new flow (fetches `/urls/types` first, builds the type-aware `validateAndPreview()` function shared by both add and edit), the edit flow (`bindEditUrl`, opens the same modal in `mode: 'edit'`), the remove flow (`bindRemoveUrl`, handles the two-step confirm), and `refreshLinksList()` (the single function every action calls to re-sync the visible list with the database)
- **`edit-mode.css`** — the consolidated, shared modal CSS (`.ows-modal-*` classes, deliberately prefixed to avoid colliding with Bootstrap's own `.modal-*` classes), the two shared feedback-color variables (`--feedback-success`/`--feedback-error`), and the Links row's inactive/editing states
- **`event-detail.php`**, **`org-edit.php`** — both reduced to a three-line variable setup (`$entityType`, `$entityId`, relying on `$urls` already being in scope) plus one `include`

## Bugs / Fixes

- **Production `urls` table missing primary key/auto-increment** — identical root cause to an earlier `media` table incident from a prior feature; fixed via direct `ALTER TABLE`, and confirmed via a full-database audit that no other table was silently affected
- **Bootstrap `.modal-backdrop` name collision** — the shared modal CSS originally used `.modal-backdrop`/`.modal-card`/etc., which collided with Bootstrap's own class of the same name (already loaded for carousels elsewhere on the page), silently breaking the modal's full-screen positioning. Fixed by renaming every shared modal class to an `.ows-` prefixed namespace
- **Modal size constraint applied to the wrong element** — `max-width`/`max-height` were originally set on the full-screen backdrop element itself rather than the inner card, shrinking the entire overlay instead of just the visible card
- **Pasted non-URL text silently accepted as a valid hostname** — `new URL()`/`parse_url()` are both permissive enough to extract _some_ hostname from almost any string, including pasted garbage text, producing a syntactically-parseable but meaningless punycode-encoded result. Fixed with a strict domain.tld shape regex, applied identically client- and server-side
- **Add-new validation always failed with "all fields required"** — the type dropdown was never actually included as a field in the add-new form at all, meaning `url_type_id` was structurally impossible to provide; fixed by adding select-field support to the generic modal shell and fetching real types before opening it
- **List didn't update after the first add when starting from empty** — the empty-state-removal logic accidentally deleted the list's own parent container right before trying to append the new item into it; fixed, then later made moot entirely by moving to a full server-rendered refresh instead of incremental DOM patching
- **Icon/label `undefined` after editing a link** — `UrlController::save()` only ever returned `{"success": true}`, with no data for the JS to render the updated item from; fixed to return the same complete shape `add()` already did
- **Validation warning didn't disable the save button** — the warning state was originally non-blocking by design, then deliberately changed to block until explicitly acknowledged (via a new "Trotzdem speichern" override) once testing showed a silent warning was too easy to miss
- **Disabled button state was functionally correct but visually identical to enabled** — added explicit `:disabled` styling (reduced opacity, not-allowed cursor) since the existing button color rules had no exception for the disabled state
- **JS-rendered links visually inconsistent with PHP-rendered ones** (icon fallback logic, button/link ordering) — both were symptoms of the same root cause, a second, independent HTML-building implementation in JavaScript that had drifted from the PHP partial; resolved permanently by eliminating JS-side HTML construction entirely

## Testing

- Add, search-and-attach, edit, and remove all tested directly in the event detail page's edit mode
- Domain/type validation tested per category: Email (valid/invalid shape), generic types (any syntactically valid URL, pasted-garbage rejection), strict platform types (correct domain, wrong domain, Bandcamp subdomain acceptance, adversarial fake-domain rejection), Maps (Google/Apple/OpenStreetMap/goo.gl acceptance, bare `google.com` without `/maps` rejection)
- Warning-and-override flow tested end to end: warning appears and blocks save, "Typ wechseln" correctly switches the type and re-validates, "Trotzdem speichern" correctly unblocks and the save proceeds with the originally-typed value
- Orphan-check-before-delete tested in both directions: a link still attached elsewhere unlinks silently with no prompt; a link with no other attachments triggers the confirm modal, and is genuinely deleted from the database (not just hidden) once confirmed
- Full inactive/editing toggle tested for visual and behavioral parity with the existing participants/media rows, including the navbar's global edit indicator turning on and off correctly on pencil/cancel
- `entity-urls.php` integration re-tested after the org-edit.php wiring to confirm zero behavior change on the already-working event page — same partial, two different controllers' data shapes, no regressions

# feat/free-sections-lifecycle

## Overview

- Lets an editor build, arrange, and style the content of any free-section page (home, über uns, etc.) entirely from the browser, without touching the database or code
- Replaces what would otherwise be a static, developer-maintained page structure — every section an editor would ever touch is already visible where it lives, with controls appearing only when needed
- Rich-text formatting (bold, italic, link, bullet list) is applied live in the editor and stored as a safe marker syntax, converted back to semantic HTML at display time — editors never see markup syntax
- Built as a single, reusable section rendering and editing system; proven across all free-section pages with zero duplicated markup or logic between them

## Architecture

- Sections are stored as rows in the `pages` table, each with a `page_key`, `order_index`, and a JSON `content` blob holding all field values (title, subtitle, text, theme, layout, image settings)
- The `order_index` sequence is always kept gapless — adding a section shifts later sections up, deleting one shifts later sections down, reordering swaps exactly two rows
- CTAs (call-to-action buttons) follow the same shared-resource pattern as links and media: stored once in `urls`, linked to the section via `entity_urls` with a `cta_label` column on the pivot, so the same URL can carry a different button label on every section that uses it
- Rich text uses a deliberate marker syntax (`**bold**`, `*italic*`, `[text](url)`, `- item`) as the storage format — never raw HTML — so the stored content is safe to output regardless of what the editor typed or pasted. `RichTextFormatter::htmlToMarker()` runs on every save; `markerToHtml()` runs at display time
- The editor's own formatting is applied via CSS classes (`rt-bold`, `rt-italic`, `rt-ul`) on `<span>` elements — not `execCommand`, not semantic tags directly — so the browser never generates unexpected markup during editing
- The section editing controls are split into two deliberate groups: structural controls (pencil, move up/down, delete) visible when not editing; content controls (layout toggles, rich-text toolbar, save/cancel) visible only while editing. This matches the established pattern already used by participants, media, and links rows throughout the editor
- The rich-text toolbar lives in its own standalone partial (`_richtext-toolbar.php`) and JS module (`initRichTextToolbar`), scoped per-block and called fresh on every activation — making it reusable by any entity-edit-row (org-info, event description, team bio) with a single `include` and zero JS changes

## Development

- Built in five sequential steps: CTA/button lifecycle first (reusing `attach-entity-modal`), then add-section with trigger placement and order-shift logic, then delete-section with orphan cleanup and gap closing, then reorder with conditional chevrons, then rich-text formatting
- The rich-text toolbar went through multiple approaches before settling on the current design: `execCommand` was abandoned due to browser inconsistency (producing `<b>` vs `<strong>`, injecting unexpected `<span style="font-size">` wrappers); direct DOM manipulation via `Range.extractContents()` and custom span classes was chosen instead as the only reliably cross-browser approach
- Field containers were changed from semantic `<h2>`/`<p>` tags to `<div class="section-h2/h3/p">` after discovering that `execCommand('insertUnorderedList')` inside a `<p>` causes browsers to forcibly eject the `<ul>` outside the `<p>` (HTML spec violation), corrupting the field structure. `<div>` has no such constraint
- The toolbar's `initRichTextToolbar` is called fresh on every block activation (alongside `initToggles` and `initImageControls`) rather than registered once at page load — because `doActivateBlock()` clones `.block-edit-controls` on every activation to clear stale listeners, so any page-load-only listener would silently die on the first activate/deactivate cycle
- Several pre-existing bugs were found and fixed as a side effect of this feature creating enough new sections to expose them: `section.php`'s outer wrapper div was unconditionally opened but conditionally closed (causing every section to nest inside the previous one via browser error-recovery); `_controls.php`'s `image_pos` toggle defaulted to `'right'` instead of `'none'` (causing every untouched section to silently become an image block on its first save)

## Files

- **`RichTextFormatter.php`** — `htmlToMarker()` (editor HTML → safe marker syntax for storage, handling `<span class="rt-bold/italic/ul">`, `<a>`, `<strong>`, `<em>`, `<b>`, `<i>`, `<ul><li>` from any prior iteration), `markerToHtml()` (marker syntax → safe HTML for public display, converting `**text**`/`*text*`/`[text](url)`/`- item` to `<strong>`/`<em>`/`<a>`/`<ul><li>`)
- **`PagesModel.php`** — `getForPage()`, `addSection()`, `getById()`, `updateContent()`, `updateOrder()`, `deleteSection()`, `closeOrderGap()` (shifts all later sections down by one after a deletion)
- **`PageController.php`** — `show()`, `addSection()`, `saveSection()` (runs `htmlToMarker()` on rich-text fields before storage), `deleteSection()` (cleans up CTAs via orphan-aware unlink before deleting), `reorderSections()`, `removeSectionImage()`
- **`render-sections.php`** — two-mode rendering (`intro` for the capped single slot at `order_index=0` on listing pages, `rest` for all ordinary sections); runs `markerToHtml()` on title/subtitle/text at display time; computes `$canMoveUp`/`$canMoveDown` per section for conditional chevrons; suppresses the first add-trigger when a hero exists
- **`section.php`** — outer wrapper conditionally rendered only when logged in (fixing the unconditional-open/conditional-close bug); passes `$canMoveUp`/`$canMoveDown` through to `_controls.php`
- **`partials/_content.php`** — field containers use `<div class="section-h2/h3/p">` not `<h2>`/`<p>`; outputs raw HTML (no `htmlspecialchars`) since values have already been converted by `markerToHtml()`
- **`partials/_controls.php`** — two groups: `.block-structural-controls` (pencil, conditional chevrons, delete — visible when not editing) and `.block-controls` (toggle row with rich-text toolbar, save/cancel — visible only while editing); fixed `image_pos` toggle default from `'right'` to `'none'`
- **`partials/_add-section-trigger.php`** — the `+ Abschnitt hinzufügen` / `Einleitung hinzufügen` trigger partial, with `data-is-intro` flag to distinguish the reserved slot from an ordinary first-position trigger
- **`partials/_cta-buttons.php`** — supports a `$fragmentOnly` mode for in-place refresh after any CTA add/edit/remove, so the editor's unsaved work in other sections is never discarded by a full page reload
- **`components/_richtext-toolbar.php`** — standalone, entity-agnostic toolbar partial (Bold, Italic, Bullet list, Numbered list, Link); includable from any entity-edit-row with a single path-adjusted `include`
- **`UrlController.php`** — added `sectionCtaFragment()` (returns inner CTA row HTML for in-place refresh), `addInternalPage()` (adds an internal page link, auto-deriving the Website type), `namedPages()` (returns the site's own named pages for the page-picker tab)
- **`edit-mode.js`** — `initRichTextToolbar(block)` (scoped per-block, fresh every activation: `applySpanClass`, `applyLinkFormat`, delegated `mousedown`/`click` handlers); `triggerAddSection()` (reorder-shift then create, isolated for v2 layout picker extension); `moveSection()` (two-row order_index swap); delete-section confirm flow with `window.location.reload()`; `refreshCtaRow()` (fragment fetch replacing full-page reload)
- **`main.css`** — `.section-h2`, `.section-h3`, `.section-p` styles matching the replaced semantic tags; `.rt-bold`, `.rt-italic`, `.rt-ul`, `.rt-ol` for live editor formatting; `strong { font-weight: var(--font-bold) }` to override Bootstrap's `bolder` resolving to `400` against a `300` base weight; `[data-field] ul/ol` list marker restore

## Bugs / Fixes

- **`section.php` unconditional-open / conditional-close div** — the outer `.editable-block` wrapper was opened unconditionally but closed only when `$isLoggedIn`, leaving one unclosed `<div>` per section for logged-out visitors; the browser's HTML error-recovery silently nested every subsequent section inside the previous one. Never visible until pages accumulated multiple sections through this feature
- **`_controls.php` image_pos toggle wrong default** — the `data-value` fallback for the image position toggle was `'right'` instead of `'none'`; since `saveBlock()` sends every toggle's current rendered value on save regardless of whether the editor touched it, every first save of an untouched section silently wrote `image_pos: 'right'` into the database, turning it into a structural image block
- **`deleteSection()` never cleaned up CTAs** — the original implementation only deleted the `pages` row, leaving `entity_urls` rows permanently orphaned. Fixed to run the same orphan-aware unlink logic used everywhere else before deleting the section itself
- **`saveBlock()` sent `innerText` instead of `innerHTML`** — `innerText` strips all markup, meaning rich-text formatting was silently discarded before ever reaching the server. Fixed to send `innerHTML`
- **Bold not rendering publicly despite correct DB storage** — `strong { font-weight: bolder }` from Bootstrap resolves to `400` when the body's base weight is `300` (via `--font-regular: 300`), making bold visually indistinguishable from regular text. Fixed with an explicit `strong { font-weight: var(--font-bold) }` override
- **`execCommand` producing `<b>` instead of `<strong>` in Firefox** — `htmlToMarker()` originally only handled `<strong>`, so `<b>` tags from Firefox's `execCommand('bold')` were silently stripped by `strip_tags()`. Added `<b>` and `<i>` handling alongside their semantic equivalents for backwards compatibility
- **CTA add/edit/remove triggering full page reload** — discarded any unsaved edits the editor had open in another section. Fixed by adding a `$fragmentOnly` mode to `_cta-buttons.php` and a dedicated `/urls/section-cta-fragment` endpoint, replacing the reload with a targeted inner-HTML swap
- **Internal-page CTA edit always showing the raw URL form** — editing a CTA originally created via the "Seite hier" tab would reopen on the plain URL text field instead of the page-picker dropdown. Fixed by detecting whether the stored URL's origin matches `window.location.origin` and routing to the correct modal panel
- **`doActivateBlock()` cloning `.block-edit-controls`** — any listener attached directly to toolbar buttons at page load was silently destroyed on the first activate/deactivate cycle. Fixed by scoping `initRichTextToolbar` to run fresh on every activation, identical to `initToggles` and `initImageControls`

## Testing

- Add, edit (layout, theme, image, bg image, alignment), save, and cancel tested across all free-section pages
- Add-section trigger placement tested for all cases: pure free-section pages (triggers before first, between every pair, after last), listing pages (intro slot capped at one, rest sequence unlimited), hero suppression (no trigger between hero and first ordinary section)
- Order gap closing tested: delete a section at index 2 with sections at 3 and 4 — confirmed 3 becomes 2 and 4 becomes 3 in the database
- Reorder chevron conditionality tested: first section shows only down, last shows only up, middle shows both, hero and intro slot show neither
- CTA fragment refresh tested: add a CTA to one section while another section has unsaved text edits — confirmed the CTA saves and the unsaved text is untouched
- Internal vs external CTA edit routing tested: CTA created via "Seite hier" reopens on the page-picker tab with the correct page pre-selected; external CTA reopens on the URL form
- Rich-text round-trip tested: bold, italic, link, and bullet list each applied, saved, and confirmed in the database as marker syntax (`**text**`, `*text*`, `[text](url)`, `- text`); reloaded and confirmed as rendered HTML publicly
- `htmlToMarker()` tested against both editor span classes (`rt-bold`/`rt-italic`) and public display tags (`<strong>`/`<em>`/`<ul><li>`) to confirm the round-trip is stable across multiple edit cycles

# feat/participants-lifecycle

## Overview

- Lets an editor create, edit, and delete artist/ensemble/orchestra profiles entirely from the browser, without touching the database or code
- Follows the exact same inline editing pattern already proven on events and team — entity-edit-rows, pencil/cancel/save, confirm modal for destructive actions, no separate admin screen
- Profile images are stored via the shared `entity_media` pivot (same upload system as event promo and gallery), not a direct `image` column — the direct column was deliberately dropped from `participants` as part of this branch, with `entity_media` as the single source of truth
- The profile image partial (`profile-img.php`) is entity-agnostic and reusable — team members and any future entity with a single portrait-style image use the same file with zero changes

## Architecture

- Participants already existed as a database entity linked to events via `event_participants`. This branch adds the missing editor-facing lifecycle: create → redirect → edit in place → delete
- `bio TEXT NULL` was added to the `participants` table; the now-redundant `image` and `image_credit` columns were dropped — `entity_media` is the single source of truth for all participant media
- `participants.id` was missing `AUTO_INCREMENT` — fixed via `ALTER TABLE participants MODIFY id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY`
- Profile images use `stage='profile'` in `entity_media`, consistent with `stage='promo'` and `stage='gallery'` for events — stage is a storage/organisation label passed through by the model as a filter, never interpreted by the model itself
- `MediaModel::getFirstForEntity()` replaces the former `getPromo()` and `getProfile()` named methods — stage is now a caller concern, not a model concern. Any new stage value works automatically without touching the model
- Type-conditional name fields: selecting `Ensemble` or `Orchester` hides the `title` and `last_name` fields live in the editor, since those entity types use only `first_name` as their full name
- Slug generation is derived from `displayName()` at the app level — not stored in the DB — following the same convention as events
- Routes follow the static-before-dynamic rule throughout

## Media Re-render Architecture (key decision)

Previously the JS upload/delete handlers computed the fragment URL from `stage` and `entity_type` — meaning JS had to know the mapping `stage='promo' → /events/{id}/promo-fragment`, etc. This was fragile and broke every time a new entity or stage was added.

**New pattern: `data-fragment-url` on the `media-edit-row`**

Each `media-edit-row` that needs server-side re-rendering after upload or delete declares its own fragment URL:

```html
<div
  class="media-edit-row"
  data-entity-type="event"
  data-entity-id="<?= $event->id ?>"
  data-stage="promo"
  data-fragment-url="/events/<?= $event->id ?>/promo-fragment"
></div>
```

JS reads `row.dataset.fragmentUrl` and fetches it blindly — no stage branching, no entity type switching. Gallery rows have no `data-fragment-url` because gallery delete removes DOM nodes directly (no state-dependent restructuring needed).

**Adding a new entity with media in future:**

1. Add `data-fragment-url` to its `media-edit-row` template
2. Add a fragment method to its controller
3. Zero JS changes

This pattern already existed in the codebase as `data-save-url` on entity-edit-rows. `data-fragment-url` is the same idea for re-rendering.

## MediaModel Refactor (key decision)

- `getPromo()` and `getProfile()` removed — stage-specific named methods don't belong in the model
- `getFirstForEntity(entityType, entityId, ?stage)` replaces both — stage is an optional caller-provided filter
- `update()` rewritten as dynamic SET clause — only updates fields present in `$data`, prevents partial updates from nulling unrelated fields
- `addForEntity()` returns `int` (the actual media ID) instead of `bool` — this was the root cause of `data-media-id="true"` on dynamically appended gallery items, making deletion impossible
- No stage defaults anywhere in the model — callers pass stage explicitly or not at all

## Development Notes

- `ParticipantModel::add()` originally returned `bool`, making it impossible to fetch the new record and generate a slug for redirect. Changed to `int|false`, returning the actual inserted id
- `save()` was originally written to call `update($id, $_POST)` — full-row update from empty POST body. Rewrote to `updateField()` with an allowed list
- `MediaModel::update()` had a silent bug predating this branch: `credit` was in the params array but had no matching `?` placeholder — params were misaligned, credit silently dropped. Fixed with dynamic SET clause
- `profile-img.php` delete button carries `data-stage="profile"` — legacy from before the `data-fragment-url` refactor; harmless but no longer used for routing decisions
- Routes were ordered dynamic-before-static for the media group — `/media/{id}/delete` was matching `batch-meta` and `reorder`. Fixed by reordering static before dynamic
- Gallery upload via header button was missing checkbox markup — fixed to match dropzone output so batch caption/credit/delete works on all gallery items regardless of upload method

## Files Changed

- **`MediaModel.php`** — `getFirstForEntity()` replaces `getPromo()`/`getProfile()`; `addForEntity()` returns `int`; `update()` dynamic SET; no stage defaults
- **`ParticipantController.php`** — `add()` returns slug for redirect; `save()` uses `updateField()`; `show()` and `profileFragment()` use `getFirstForEntity()`; SEO uses `profileImg?->media_url` with org logo fallback
- **`ParticipantModel.php`** — `add()` returns `int|false`; `updateField()` added; `update()` includes `bio`; `image`/`image_credit` removed
- **`EventController.php`** — `index()`, `archive()` use `getFirstForEntity()` for promo
- **`TeamController.php`** — `index()` uses `getFirstForEntity()` for profile
- **`participants.php`** — rewritten (was team template); uses `profileImg->media_url`
- **`participant-detail.php`** — full inline edit UI with `data-fragment-url` on profile `media-edit-row`; `$entityType` set before `profile-img.php` include
- **`event-detail.php`** — `data-fragment-url` added to promo `media-edit-row`; gallery row has no fragment URL (DOM removal strategy)
- **`profile-img.php`** — entity-agnostic portrait partial; `data-stage="profile"` on delete button
- **`edit-bar.php`** — `+ Neue:r Künstler:in` button added
- **`edit-mode.js`** — upload/delete handlers read `row.dataset.fragmentUrl` — no stage branching; gallery append includes checkbox markup; `new-participant`, `delete-participant`, `delete-event` handlers added
- **`routes.php`** — static before dynamic throughout; `GET /participants/{id}/profile-fragment` added; dead participant image routes removed

## Deferred

- `feat/team-lifecycle` — full team CRUD with inline editing
- `feat/gallery-fragment` — gallery re-render via server fragment (pattern proven, not needed for MVP)
- Participant/team listing storytelling — filtering, search, visual design
- Rich text in bio and other entity fields
- Type toggle display logic — decided when listing design is done

## Testing

- Create via edit bar → redirect to `/kuenstlerinnen/{slug}` ✓
- All edit rows save correctly one field at a time ✓
- Profile image upload → appears in detail page and listing card ✓
- Profile image credit edit → saves and displays ✓
- Profile image delete → placeholder shown immediately, no Verbindungsfehler ✓
- Event promo upload → appears immediately ✓
- Event promo delete → placeholder shown immediately, no Verbindungsfehler ✓
- Gallery upload (header + dropzone) → correct integer `data-media-id`, checkbox present ✓
- Gallery delete (single + batch) → works correctly ✓
- Caption/credit on promo and gallery → saves correctly ✓
- Links add/edit/remove on participant ✓
- Events list on participant detail ✓
- Delete participant → confirm modal → redirect ✓
- Delete event → confirm modal → redirect ✓

# feat/team-lifecycle

## Overview

Full inline CRUD lifecycle for team members — create, edit, delete, profile image, external links — following the exact same patterns established in `feat/participants-lifecycle`. No new patterns introduced; everything reuses what already works.

## Architecture

- Profile images stored in `entity_media` with `stage='profile'` — `image` and `image_credits` columns dropped from `team` table
- `profile-img.php` partial reused without modification — entity-agnostic by design
- `data-fragment-url` on `media-edit-row` — same pattern as events promo and participant profile
- `TeamModel::updateField()` — single-field saves for entity-edit-rows
- `TeamController::profileFragment()` — re-renders `profile-img.php` in place after upload/delete
- Soft delete via `deleted_at` — preserves record for future event/project credit attribution
- `getAll()` filters `deleted_at IS NULL` — soft-deleted members invisible on public listing

## Soft Delete Rationale

Team members are internal organizational records with potential historical significance. Unlike participants (external contributors), team members may need to be credited for past events and projects. Hard delete would destroy that history. Soft delete keeps the record available for future attribution while removing the member from public display. The delete confirm modal communicates this explicitly.

## DB Changes

```sql
ALTER TABLE team DROP COLUMN image;
ALTER TABLE team DROP COLUMN image_credits;
```

Run on both local and production before deploying.

## Files Changed

- **`TeamModel.php`** — `add()` returns `int|false`; `updateField()` added; `update()` removes image columns; `delete()` soft deletes via `deleted_at`; `getAll()` filters soft-deleted
- **`TeamController.php`** — full implementation of `add()`, `save()`, `delete()`, `profileFragment()`; `index()` and `show()` use `getFirstForEntity()` for profile image; SEO uses `profileImg?->media_url`
- **`team.php`** — uses `$member->profileImg->media_url` via entity_media
- **`team-detail.php`** — full inline edit UI; `media-edit-row` with `data-fragment-url`; all fields as entity-edit-rows; `entity-urls.php`; soft delete button
- **`routes.php`** — `GET /team/{id}/profile-fragment` added; static before dynamic
- **`edit-mode.js`** — `new-team` and `delete-team` handlers added
- **`edit-bar.php`** — `+ Neues Teammitglied` button added

## Bugs & Fixes

- **`image` and `image_credits` columns** — dropped from `team` table; all references removed from model and views; `entity_media` is the single source of truth
- **`TeamModel::add()` returned `bool`** — same issue as `ParticipantModel` before fix; changed to `int|false` for slug generation and redirect

## Testing

- Create via edit bar → redirect to `/team/{slug}`
- All edit rows save correctly one field at a time
- Slug updates in browser URL when name fields change
- Profile image upload → appears in detail page and listing card
- Profile image credit edit → saves and displays
- Profile image delete → placeholder shown immediately
- Links add/edit/remove
- Soft delete → confirm modal → redirect to `/team`
- Soft-deleted member no longer appears in listing
- Soft-deleted member record still exists in DB with `deleted_at` set
- Team listing shows profile images from entity_media

## Deferred

- Event and project credit attribution for team members
- Team listing storytelling — filtering, sorting, visual design
- Rich text in biography and motto

# feat/venues-lifecycle

## Overview

Adds full venue management to the event detail page — search, create, edit, delete, and link map/website URLs — without a separate admin page. The editor manages venues entirely from context, inline with the event they're working on. The `entity-venues.php` partial is reusable for any future entity that needs a venue.

## Architecture

### Data model

- `map_url VARCHAR(255) NULL` and `website_url VARCHAR(255) NULL` added directly to `venues` table
- No `entity_urls` dependency for venues — two bounded URL types don't warrant the full pivot system
- Hard delete protected by `VenueModel::isLinked()` — blocks deletion if venue is attached to events

### `entity-venues.php` component

- Follows exact `entity-urls.php` edit-row pattern — CSS drives state via `.editing` class
- Edit button carries all venue data as `data-*` attributes — modal pre-fills without an extra fetch
- Trash button removes venue from event via `/events/{id}/save` with empty `venue_id`
- `Ort ändern` / `Ort hinzufügen` opens venue modal in search mode
- Public display: name links to `website_url`, address links to `map_url` when present

### Venue modal — two modes

- **Edit mode** — `openVenueModal('edit', venueData)` — pre-filled form, no tabs, saves field by field via `Promise.all` to `/venues/{id}/save`
- **Search mode** — `openVenueModal('search', onSelect)` — search tab loads all venues on open, filters as editor types; neue venue tab creates and assigns in one step
- Test links appear inline below map and website inputs as soon as a valid URL is typed

### CSS

- `.venue-edit-row` shares border/header rules with `.links-edit-row` — combined selector
- Venue-specific rules: `venue-pencil-btn`, `venue-cancel-btn`, `venue-item` edit/trash, `venue-change-wrap`
- Modal sizing via `.venue-modal .ows-modal-card { max-width: 80vw; max-height: 90vh }` — wider than other modals due to more fields

## DB Migration

```sql
ALTER TABLE venues ADD COLUMN map_url VARCHAR(255) NULL AFTER country;
ALTER TABLE venues ADD COLUMN website_url VARCHAR(255) NULL AFTER map_url;
```

Run on both local and Hostinger before deploying.

## Files Changed

- **`VenueModel.php`** — `add()` returns `int`; `updateField()` added; `search()` and `isLinked()` added; `map_url` and `website_url` in `add()` and `update()`
- **`VenueController.php`** — `search()`, `add()`, `save()` via `updateField()`, `delete()` with link guard; `map_url` and `website_url` in JSON responses
- **`venue-modal.php`** — two-mode modal: edit panel (pre-filled, no tabs) and search/new panels; test links on all URL inputs
- **`entity-venues.php`** — new reusable partial; edit-row pattern; edit/trash/change buttons; public URL linking
- **`main.php`** — venue-modal.php included site-wide
- **`event-detail.php`** — inline venue block replaced with `entity-venues.php` include; `$event->venue` object used for URL display
- **`EventController.php`** — `$event->venue` fetched via `getById()` in `show()`; aligned with existing property assignment style
- **`routes.php`** — `GET /venues/search`, `POST /venues/add`, `/venues/{id}/save`, `/venues/{id}/delete`
- **`edit-mode.js`** — `venue-edit-row` handler; `openVenueModal(mode, payload)`; `closeVenueModal()`; `bindVenueTestLink()` for four URL inputs; removed `:not(.venue-edit-row)` guard

## Bugs & Fixes

- **`$event->venue` null on public display** — `EventController::show()` was missing the `getById()` fetch for the venue object; `map_url` and `website_url` were never available to the template, so address and name never became links
- **Venue modal full-width** — backdrop was missing scoping class; fixed by reusing `attach-entity-modal` class which already has the correct `max-width` and `max-height` rules; editor later overrode with wider `venue-modal` rule for the larger form
- **`:not(.venue-edit-row)` on entity-select-row** — added defensively but unnecessary since `venue-edit-row` never uses `entity-select-row` class; removed

## Testing

Venue display — public side

- Event with venue — name and address show correctly as plain text
- Event with venue + website_url — venue name is a clickable link to website
- Event with venue + map_url — address is a clickable link to map
- Both URLs present — name links to website, address links to map
- Links open in new tab
- Event without venue — no venue section shown

Venue edit row — edit mode

- Pencil activates edit mode — border changes, cancel appears, pencil hides
- Cancel closes edit mode — display restored
- Edit and trash buttons on venue item hidden when not editing
- Edit and trash buttons visible when editing
- Ort ändern / Ort hinzufügen button hidden when not editing, visible when editing

Venue modal — search mode (Ort ändern)

- Opens on search tab with all venues listed
- Typing filters results
- Selecting a venue saves venue_id to event → page reloads with correct venue
- Switching to Neue Venue tab shows creation form
- Hinzufügen disabled until name is filled
- Creating new venue → saves to DB → assigned to event → page reloads
- Clicking backdrop closes modal
- Clicking × closes modal

Venue modal — edit mode (pencil on venue item)

- Opens pre-filled with current venue data — name, street, postcode, city, country, map_url, website_url
- No tabs shown — only edit form
- Editing name and saving → venue name updates on reload
- Adding map_url → address becomes link on reload
- Adding website_url → name becomes link on reload
- Clearing a field saves null correctly
- Speichern closes modal and reloads page

Venue remove

- Trash button shows confirm modal
- Confirming removes venue from event → page reloads with — kein Ort —
- Cancelling confirm modal does nothing

Venue creation — new venue

- Name required — submit blocked without it
- All fields optional except name
- Country defaults to Österreich
- New venue appears in search results on subsequent opens
- New venue appears in DB with correct fields

Venue Webiste and Map Urls

- On both edit, or add new venue forms, the given URLs can be tested for accuracy before submitting

Venue delete protection

- Venue linked to events → delete blocked, error message shown
- Venue with no event links → deletes successfully from DB

Regression

- Existing venue data displays correctly after deploy
- Event detail page loads without errors
- Admission, participants, links sections unaffected
- Public event listing unaffected

## Deferred

- Venue lifecycle for projects
- Venue listing / management page for bulk editing
- Venue search on public event listing / archive

# feat/team-participant-status-lifecycle

## Overview

Adds draft/published status lifecycle to participant and team member profiles — same pattern as `feat/event-status-lifecycle`. New profiles start as drafts, invisible to the public until explicitly published. Editor sees all profiles with greyscale + chip for drafts.

## Architecture

### Status model

- `status VARCHAR(20) NOT NULL` added to both `participants` and `team` tables
- Values: `'draft'` or `'published'` only — no enum, no DB default, app always passes explicitly
- No `cancelled_at` — not applicable to people entities
- Public display condition: `status = 'published'` — enforced in view via `continue`, not in model or controller

### Visibility pattern — view owns filtering

Model fetches all. View skips draft cards for public with `continue`. Controller guards `show()` only — 404 for direct URL access of draft profiles. Same principle established for linked events on participant detail.

### Status bar

Same UX pattern as events — top of detail page, conditional actions:

```
Diese:r Künstler:in ist ein Entwurf:      [Veröffentlichen]  [Löschen]
Diese:r Künstler:in ist veröffentlicht:   [Als Entwurf setzen]

Dieses Teammitglied ist ein Entwurf:      [Veröffentlichen]  [Löschen]
Dieses Teammitglied ist veröffentlicht:   [Als Entwurf setzen]
```

### Delete guard

`delete()` in both controllers checks `status === 'draft'` before allowing hard delete. Published profiles blocked — must unpublish first.

### Empty state fix

`empty($participants)` and `empty($members)` checked the raw unfiltered array — always non-empty even when all drafts. Fixed by computing `$visibleParticipants` / `$visibleMembers` filtered arrays first, then checking those for the empty state message.

### CSS naming refactor

Status bar classes renamed from `event-status-*` to `entity-status-*` — universal across events, participants and team. Update `edit-mode.css`, `event-detail.php`, `participant-detail.php`, `team-detail.php`.

## DB Migration

```sql
ALTER TABLE participants ADD COLUMN status VARCHAR(20) NOT NULL AFTER last_name;
ALTER TABLE team ADD COLUMN status VARCHAR(20) NOT NULL AFTER last_name;
UPDATE participants SET status = 'draft';
UPDATE team SET status = 'draft';
```

Run on both local and Hostinger before deploying.

## Files Changed

- **`ParticipantModel.php`** — `status` in `add()` INSERT; `'status'` in `updateField()` allowed list; `publish()` and `unpublish()` methods
- **`TeamModel.php`** — same as ParticipantModel
- **`ParticipantController.php`** — 404 guard in `show()` for drafts on public; `delete()` guarded to draft only; `publish()` and `unpublish()` endpoints
- **`TeamController.php`** — same as ParticipantController
- **`participant-detail.php`** — status bar with conditional actions; delete button moved from bottom to status bar
- **`team-detail.php`** — status bar with conditional actions; delete button moved from bottom to status bar
- **`participants.php`** — `continue` for drafts on public; greyscale + chip in edit mode; `$visibleParticipants` for correct empty state
- **`team.php`** — same as participants.php
- **`routes.php`** — `POST /participants/{id}/publish`, `/unpublish`; `POST /team/{id}/publish`, `/unpublish`
- **`edit-mode.js`** — `publish-participant`, `unpublish-participant`, `publish-team`, `unpublish-team` handlers with confirm modals

## Bugs & Fixes

- **Empty state not showing** — `empty($participants)` and `empty($members)` checked raw arrays which are never empty even when all entries are drafts. Fixed with `$visibleParticipants` / `$visibleMembers` computed before the empty check.
- **CSS class naming** — `event-status-bar` and related classes were event-specific in name but used across all entities. Renamed to `entity-status-*` for consistency. Pending CSS update.

## Testing

**DB**

- `participants.status` column exists, all rows `'draft'`
- `team.status` column exists, all rows `'draft'`

**Participant listing**

- Public: only published shown; all draft → empty state message shown
- Edit mode: all shown, drafts greyscale with Entwurf chip

**Participant detail**

- Draft: status bar shows Veröffentlichen + Löschen
- Published: status bar shows Als Entwurf setzen
- Publish confirm modal fires
- Unpublish confirm modal fires
- Draft URL returns 404 on public side
- Delete only available for drafts

**Team listing**

- Public: only published shown; all draft → empty state message shown
- Edit mode: all shown, drafts greyscale with Entwurf chip

**Team detail**

- Draft: status bar shows Veröffentlichen + Löschen
- Published: status bar shows Als Entwurf setzen
- Publish confirm modal fires
- Unpublish confirm modal fires
- Draft URL returns 404 on public side
- Delete only available for drafts

**New entity flow**

- New participant → draft detail page → publish when ready
- New team member → draft detail page → publish when ready

**Regression**

- Profile image upload/delete still works
- Links add/remove still works
- Event links on participant detail still correct

## Deferred

- `feat/participant-visibility-by-event-status` — participant with only cancelled/draft events optionally hidden from public listing
- CSS rename `event-status-*` → `entity-status-*` — pending edit-mode.css update

# feat/contact-form

## Overview

Wires the contact form on `/kontakt` — validates, sanitizes and sends visitor messages to the organisation's inbox via PHPMailer. Full security stack: CSRF protection, rate limiting, injection detection, DNS verification. Per-field inline validation with DSGVO consent checkbox.

## Architecture

### Security stack (server-side)

1. `$this->startSession()` — explicit session start in both `index()` and `send()`
2. CSRF token — generated in `index()`, stored in `$_SESSION['csrf_contact']`, validated in `send()` via `hash_equals()`, regenerated after successful send
3. Rate limiting — `RateLimiter::check('contact', 3, 600)` — 3 submissions per 10 minutes per session, window expires naturally (not reset on success)
4. `filter_input(INPUT_POST, ...)` — safe POST access
5. `strip_tags()` — HTML/script tag removal on all text inputs
6. Length limits — name 2-200, email ≤200, message 10-5000 chars
7. `filter_var(FILTER_VALIDATE_EMAIL)` — email format validation
8. `checkdnsrr()` — MX/A record lookup on email domain

### Client-side validation (app.js)

- Per-field validation functions: `validateName()`, `validateEmail()`, `validateMessage()`, `validateTerms()`
- `touched` object tracks which fields the user has interacted with
- Errors shown on `blur` — never on page load or first keystroke
- Injection detection: `/<|>|javascript:|on\w+\s*=/i`
- Send button gated on name/email/message validity — terms shown as error on click
- On submit: all errors shown regardless of touched state
- Fields and touched state reset after successful send

### Email

- Sent to `$org->email` from organisation_info
- Reply-to set to sender's email — editor replies directly from inbox
- Subject: `Nachricht von {name} über die Website`
- Body rendered via `Mailer::renderView('emails/contact-notification')` — template at `app/Views/emails/contact-notification.php`
- `Mailer::send()` extended with optional `replyTo` parameter

### DSGVO

- Checkbox required before send — linked to `/datenschutz`
- Error shown on submit attempt if unchecked
- Not included in button gate — avoids hidden blocker UX

## Critical Bug Fixed

`$_SESSION['csrf_contact']` was always `NONE` in `send()` because `BaseController::startSession()` is only called via `isLoggedIn()` — never triggered on public POST endpoints. CSRF check always failed, mailer never reached. Fixed by calling `$this->startSession()` explicitly in both `index()` and `send()`.

## Files Changed

- **`ContactController.php`** — `index()` generates CSRF token; `send()` full security stack + Mailer call
- **`Mailer.php`** — `replyTo` parameter added to `send()`
- **`contact.php`** — CSRF hidden input with `name` attribute, per-field error spans, DSGVO checkbox, `data-action="contact-form"` on form wrapper, button `type="button"`
- **`contact-notification.php`** — new email template at `app/Views/emails/`
- **`app.js`** — contact form handler with touched tracking, per-field blur validation, injection detection, fetch POST with CSRF token
- **`routes.php`** — `POST /kontakt` → `ContactController::send()`

## Testing

**Validation — client side**

- Page load — no errors shown on any field
- Name: leave empty → error shown; type 1 char → error; type 2+ → clears
- Email: invalid format → error shown; valid → clears
- Message: less than 10 chars → error; 10+ → clears
- Injection attempt (`<script>`) → error shown
- Send button disabled until name, email, message all valid
- Terms unchecked on submit → error shown under checkbox
- Terms checked → error clears

**Validation — server side**

- POST with empty fields → 400 + error message
- POST with invalid email → 400 + error message
- POST with non-existent email domain → 400 + DNS error
- POST with message > 5000 chars → 400 + error message
- POST without CSRF token → 400 + Ungültige Anfrage
- POST with wrong CSRF token → 400 + Ungültige Anfrage
- 4th submission within 10 minutes → rate limit error

**Successful send**

- All fields valid + terms checked → success message shown inline
- Fields cleared after success
- Email arrives in org inbox
- Reply-to is sender's email
- Subject: `Nachricht von {name} über die Website`
- Email body clean and readable
- No page reload at any point

**Regression**

- OTP login email still sends correctly
- GET /kontakt still renders correctly
- All fragment routes still present in routes.php

# feat/datenschutz-impressum-pages

## Overview

Adds legally required `/datenschutz` and `/impressum` pages. Org contact data is pulled dynamically from `organisation_info`. The president's name and role are fetched automatically from the `team` table — so the pages update whenever team leadership changes, with no manual intervention. Free sections below the org block allow editors to add full legal text via edit mode.

## Architecture

### President query — `TeamModel::getPresident()`

Queries the first published team member whose `role` contains `präsident` (case-insensitive via `LOWER()`). Returns `null` if none found — views fall back to `Information folgt in Kürze.`

```sql
SELECT * FROM team
WHERE deleted_at IS NULL
AND status = 'published'
AND LOWER(role) LIKE '%pr%sident%'
ORDER BY id ASC
LIMIT 1
```

The `%pr%sident%` pattern covers `Präsident`, `Präsidentin`, `Vizepräsident`, `Vizepräsidentin` while tolerating the `ä` → missing umlaut edge case.

### Page structure

```
[dark segment]   — org name, president role + name, address, email (+ phone, ZVR on impressum)
[light segments] — free sections managed by editor via edit mode
```

### PageController

Two dedicated methods `datenschutz()` and `impressum()` — both fetch president via `TeamModel::getPresident()` and pass sections + SEO to their respective views. `TeamModel` added as a dependency.

### Icons

Same Tabler icon classes as `contact.php`:

- `ti-map-pin` — address
- `ti-mail` — email
- `ti-phone` — phone (impressum only)
- `ti-file-certificate` — ZVR (impressum only)

## Files Changed

- **`TeamModel.php`** — `getPresident()` added
- **`PageController.php`** — `TeamModel` dependency; `datenschutz()` and `impressum()` methods
- **`datenschutz.php`** — new view at `app/Views/pages/`
- **`impressum.php`** — new view at `app/Views/pages/`
- **`routes.php`** — `GET /datenschutz` and `GET /impressum` → `PageController`

## Sub-issue — Role Standardisation

**`feat/team-role-standardisation`**

The president query relies on free text `role` field. A typo (`Presidentin`, `Prasident`) silently breaks the display — no error, just falls back to placeholder. Fix: replace free text `role` input on `team-detail.php` with a `<datalist>` or `<select>` offering predefined Austrian association roles:

```
Präsident / Präsidentin
Vizepräsident / Vizepräsidentin
Obmann / Obfrau
Kassier / Kassiererin
Schriftführer / Schriftführerin
Beirat / Beirätin
```

This ensures the `LIKE '%präsident%'` query always finds the correct record.

## Testing

**`/datenschutz`**

- Page loads without errors
- Org name, president role + name, address, email display correctly
- No president in DB → `Information folgt in Kürze.`
- Free sections render below (empty = no error)
- Edit mode — add section button visible

**`/impressum`**

- Page loads without errors
- Org name, president role + name, address, email, phone, ZVR display correctly
- No president → `Information folgt in Kürze.`
- Free sections render below

**President query**

- `Präsident` → found
- `Präsidentin` → found
- `Vizepräsident` → found
- No matching role → fallback shown

**Regression**

- `/kontakt` unaffected
- All fragment routes present
- Other PageController routes unaffected

# feat/newsletter-register-strip

## Overview

Fully owned newsletter subscription system — double opt-in, one-click unsubscribe, DSGVO compliant. No third-party services. Subscriber strip lives in the footer on every page. Editor manages subscribers via `/newsletter/subscribers` and exports CSV for manual sending.

## Architecture

### DB

```sql
CREATE TABLE newsletter_subscribers (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    email        VARCHAR(200) NOT NULL UNIQUE,
    confirmed    TINYINT(1) DEFAULT 0,
    token        VARCHAR(64) NULL,
    token_expiry DATETIME NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_at TIMESTAMP NULL
);
```

### Subscribe flow

1. Visitor submits email → CSRF, rate limit, format, DNS checks
2. Token generated via `bin2hex(random_bytes(32))` — 24h expiry
3. Stored as `confirmed = 0` with token
4. Confirmation email sent via `Mailer::renderView('emails/newsletter-confirm')`
5. Visitor clicks link → `confirm()` sets `confirmed = 1`, clears token
6. CSRF token regenerated after successful submission

### Unsubscribe flow

1. Unsubscribe link in every newsletter email contains token
2. Token set manually in DB before sending (MVP — manual send)
3. `deleteByToken()` checks token exists and hasn't expired
4. Hard delete — row removed entirely from DB

### Security stack

- CSRF token — generated in `BaseController::render()`, available site-wide
- `filter_input(INPUT_POST, ...)` — safe POST access
- `strip_tags()` + `filter_var(FILTER_VALIDATE_EMAIL)` — input sanitization
- `checkdnsrr()` — email domain DNS verification
- `RateLimiter::check('newsletter', 3, 600)` — 3 per 10 min per session
- `hash_equals()` — timing-safe CSRF comparison
- `bin2hex(random_bytes(32))` — cryptographically secure tokens

### newsletter-strip.php component

- Placed in `app/Views/components/`
- Required in `footer.php` between copyright and social links
- `id="newsletter"` anchor — CTA buttons can link to `#newsletter`
- DSGVO terms linked to `/datenschutz`

### Editor tools

- `/newsletter/subscribers` — confirmed subscriber list, login required
- `/newsletter/export` — CSV download, login required

## Files Changed

- **`NewsletterModel.php`** — full subscriber management
- **`NewsletterController.php`** — subscribe, confirm, unsubscribe, subscribers, export
- **`newsletter-confirm.php`** — confirmation email at `app/Views/emails/`
- **`newsletter-result.php`** — confirm/unsubscribe result page at `app/Views/pages/`
- **`newsletter-subscribers.php`** — editor subscriber list at `app/Views/pages/`
- **`newsletter-strip.php`** — signup component at `app/Views/components/`
- **`footer.php`** — newsletter strip included between copyright and socials
- **`BaseController.php`** — `csrf_newsletter` token generated in `render()`
- **`app.js`** — newsletter subscribe handler
- **`routes.php`** — all newsletter routes
- **`edit-bar.php`** — Abonnenten link added for direct editor access to subscriber list

## Bugs Fixed

- **False unsubscribe success** — `deleteByToken()` returned `true` on successful query even with 0 deleted rows. Fixed by fetching subscriber first, checking expiry, then deleting.
- **ERR_SSL_PROTOCOL_ERROR on localhost** — confirmation URL hardcoded `https://`. Fixed with `$_SERVER['HTTPS']` protocol detection.

## Demo Flow (for editor presentation)

1. Visit site footer → enter email → click Anmelden
2. Check inbox → click confirmation link → success page
3. Run SQL to set demo unsubscribe token:

```sql
UPDATE newsletter_subscribers
SET token = 'demo-unsubscribe-token',
    token_expiry = DATE_ADD(NOW(), INTERVAL 24 HOUR)
WHERE email = 'subscriber@email.com';
```

4. Add temp route + `testSend()` to `NewsletterController` (see demo instructions)
5. Visit `/newsletter/test-send` → newsletter email sent with unsubscribe link
6. Click unsubscribe → success page → row deleted from DB
7. Visit `/newsletter/subscribers` as editor → empty list confirmed
8. Clean up: remove `testSend()`, route, and `newsletter-test.php` template

## Testing

**Subscribe**

- Valid email → confirmation email received
- Invalid email format → error shown
- Non-existent domain → DNS error shown
- Empty submit → button disabled, no request fired
- 4th submission within 10 min → rate limit error
- Already confirmed email → silent success, no duplicate
- Unconfirmed email resubmitted → new confirmation sent

**Confirm**

- Valid token → confirmed = 1, token cleared in DB
- Expired token → invalid link message shown
- Already used token → invalid link message shown

**Unsubscribe**

- Valid token → row deleted from DB, success message
- Expired token → invalid/used message shown
- Already deleted → invalid/used message shown

**Editor**

- `/newsletter/subscribers` returns 404 for non-editors — does not reveal existence
- `/newsletter/export` returns 404 for non-editors
- Both accessible when logged in as editor

**Regression**

- Contact form CSRF unaffected
- OTP login unaffected
- Footer renders correctly on all pages
- All fragment routes present

## Deferred

- `feat/newsletter-in-free-section` — newsletter strip as free section CTA variant
- `feat/newsletter-on-event-publish` — notify subscribers on event publish
- `feat/newsletter-on-event-cancel` — notify subscribers on event cancel
- `feat/newsletter-on-free-section-type-news-publish` — notify subscribers on news publish
- `feat/newsletter-composer` — in-app composer for sending to all confirmed subscribers

# feat/org-logos

## Overview

Adds two independently managed logo fields to `organisation_info` — `logo_url` (Hero/Footer) and `inline_logo_url` (Navbar) — with full upload/delete lifecycle through the editor's org page. Both display locations degrade gracefully when no logo is set. Navbar and footer update instantly on upload/delete without a page reload.

## Architecture

### Two logo fields

- `logo_url` — large logo, used in `hero.php` and `footer.php`
- `inline_logo_url` — small logo, used in `nav.php`
- Both stored directly on `organisation_info` — no `entity_media` pivot needed, organisation is a singleton

### One logo per field — simple two-state UI

No gallery, no multiple images per field. Either "kein Logo" with an upload button, or one image with edit/delete controls — never both states visible at once.

### Upload/delete via OrganisationController

- `uploadLogo()` — deletes existing file from Cloudinary first (via `extractPublicId()`), then uploads new one, updates the field
- `deleteLogo()` — deletes from Cloudinary and clears the field
- Both gated by `requireLogin()`, both accept `field` POST param (`logo_url` | `inline_logo_url`)

### Edit-row pattern — entity-edit-row, not CSS-toggle

Pencil/cancel visibility and item controls are toggled via inline `style.display` set directly by JS — matching the existing `entity-edit-row` pattern used throughout the org-edit page, rather than the CSS-class-toggle pattern used by `media-edit-row` elsewhere. This avoids CSS specificity conflicts with the shared `.entity-edit-btn`/`.entity-cancel-btn` classes used across the whole edit page.

### Instant nav/footer sync

After successful upload or delete, the navbar and footer DOM are updated directly via JS — no `window.location.reload()`. Scoped to the editor's own browser tab during editing.

### Fallback behavior

- **Hero** — `$hasLogo` flag controls whether the logo column renders; content column is `col-md-7` with logo, `col-12` without
- **Footer** — logo container wrapped in `!empty($org->logo_url)` check, fully hidden when absent
- **Nav** — `inline_logo_url` shown as `<img>`, falls back to `<span class="nav-brand-text">` with org name when absent

## DB Migration

```sql
ALTER TABLE organisation_info
ADD COLUMN inline_logo_url VARCHAR(500) NULL AFTER logo_url;
```

Run on both local and Hostinger before deploying.

## Critical Bug Fixed

**`CloudinaryService::delete()` signature generation was broken for every media type in the app, not just logos.**

`delete()` used `http_build_query($params, '', '&', PHP_QUERY_RFC3986)` to build the signature string. RFC3986 encoding converts `/` to `%2F` — but `public_id` values always contain `/` (folder structure, e.g. `ows/wkk/organisation/logo-123`). Cloudinary's signature must be computed over the raw, unencoded string. The encoded slashes produced a mismatched signature, so Cloudinary rejected every delete request with `"Invalid Signature"` — silently, since the app only checked `$result['result'] === 'ok'` and treated failure as a soft `false`.

This meant **no Cloudinary file was ever actually deleted** anywhere in the app — event promo images, profile images, gallery images, section images, and now org logos — despite the DB rows being correctly removed and the UI showing success.

**Fix:** `delete()` now builds the signature string the same way `upload()` already did — manual `ksort()` + string concatenation, no URL encoding:

```php
$params = ['public_id' => $publicId, 'timestamp' => $timestamp];
ksort($params);
$paramString = '';
foreach ($params as $key => $value) {
    $paramString .= $key . '=' . $value . '&';
}
$paramString = rtrim($paramString, '&');
$signature = sha1($paramString . $apiSecret);
```

**Impact:** This fix retroactively unblocks Cloudinary deletion across the entire application, not just this feature. Worth a dedicated regression pass across all media-deletion flows.

## Files Changed

- **`OrganisationModel.php`** — `inline_logo_url` added to `updateField()` allowed list
- **`OrganisationController.php`** — `uploadLogo()`, `deleteLogo()` methods added
- **`CloudinaryService.php`** — `extractPublicId()` added; `delete()` signature bug fixed
- **`org-edit.php`** — Logos section with two `media-edit-row` blocks, conditional upload-wrap
- **`routes.php`** — `POST /{admin}/org/logo/upload`, `POST /{admin}/org/logo/delete`
- **`edit-mode.js`** — upload/delete handlers, instant nav/footer DOM sync, entity-edit-row-style display toggling
- **`hero.php`** — conditional logo column, full-width content fallback
- **`footer.php`** — conditional logo container
- **`nav.php`** — `inline_logo_url` with org-name-text fallback, hardcoded logo URL removed

## Testing

**Upload**

- Upload logo_url → item updates with edit/delete controls, upload-wrap removed, footer updates instantly
- Upload inline_logo_url → item updates, navbar updates instantly
- Re-upload replaces old file — verify old file gone from Cloudinary dashboard

**Delete**

- Delete logo_url → confirm modal → item reverts to "— kein Logo —", upload-wrap reappears, footer logo removed instantly
- Delete inline_logo_url → same, navbar reverts to org name text instantly
- Verify in Cloudinary dashboard — file actually gone (regression test for the signature fix)

**Edit-row toggle**

- No logo: only "Logo hochladen" visible, no edit/delete controls
- With logo: edit (pencil) + delete (trash) visible on the item, no separate upload button
- Pencil in header toggles editing — controls only appear in edit mode, hidden otherwise

**Hero**

- With logo_url → logo col-md-5, content col-md-7
- Without logo_url → no logo col, content col-12

**Footer**

- With logo_url → logo displays
- thout logo_url → no broken image, no empty space

**Nav**

- With inline_logo_url → image displays, links to /
- Without inline_logo_url → org name text displays, links to /

**Cloudinary deletion — full regression (critical, due to the signature bug fix)**

- Event promo image delete — verify gone from Cloudinary
- Participant profile image delete — verify gone from Cloudinary
- Team profile image delete — verify gone from Cloudinary
- Gallery image delete (single + batch) — verify gone from Cloudinary
- Section image/bg_image delete — verify gone from Cloudinary
- Section delete with images — verify gone from Cloudinary

**Regression**

- All other org-edit fields still save correctly
- Mobile sidebar nav unaffected
- All critical routes still present: profile-fragment, promo-fragment, kontakt, newsletter

# feat/image-deletion

## Overview

Unifies Cloudinary file deletion across the entire app — entity media (event promo, profile, gallery images) and section images (`image`/`bg_image`) — using a single consistent pattern: `CloudinaryService::extractPublicId()` recovers the public_id from the already-stored URL at delete time, no separate `public_id` column or client-supplied value needed. Also fixes profile images to behave as a true single-image slot, replacing rather than accumulating on re-upload.

## Architecture

### public_id recovery, not storage

Every deletion path — `MediaController::delete()`, `PageController::removeSectionImage()`, `PageController::deleteSection()`, `OrganisationController::deleteLogo()`/`uploadLogo()` — calls `CloudinaryService::extractPublicId($url)` on the relevant stored URL, then `CloudinaryService::delete($publicId)`. No DB migration, no trust placed in client-supplied values, works retroactively on any media uploaded before this fix.

### Profile image replace-on-upload

`MediaController::upload()` checks `stage === 'profile'` before uploading. If an existing profile-stage image is found via `getFirstForEntity()`, it's unlinked and — if no longer referenced anywhere — deleted from Cloudinary, before the new upload proceeds. Applies uniformly across participant, team, event, or any future entity using the profile stage.

### Section image cleanup on full section delete

`deleteSection()` now checks both `image` and `bg_image` fields in the section's JSON content before deleting the row, deleting each from Cloudinary if present — same reasoning as `removeSectionImage()`, since these URLs are never tracked via `entity_media`.

## Files Changed

- **`MediaController.php`** — `delete()` uses `extractPublicId($media->media_url)` instead of client-supplied `public_id`; `upload()` replaces existing profile-stage image before linking new one
- **`PageController.php`** — `removeSectionImage()` and `deleteSection()` re-gained their Cloudinary deletion calls (lost in an earlier rollback); `CloudinaryService` require added

## Dependency

This feature builds directly on the `CloudinaryService::delete()` signature bug discovered and fixed during `feat/org-logos` — `http_build_query(..., PHP_QUERY_RFC3986)` was URL-encoding slashes in `public_id`, breaking every Cloudinary delete signature in the app. That fix is what makes this feature's deletions actually work end to end, rather than silently failing while reporting success.

## Testing

**Entity media — single delete**

- Delete event promo image → file gone from Cloudinary dashboard
- Delete participant profile image → file gone from Cloudinary dashboard
- Delete team profile image → file gone from Cloudinary dashboard
- Delete single gallery image → file gone from Cloudinary dashboard, grid updates correctly

**Entity media — batch delete**

- Select multiple gallery images → batch delete → all files gone from Cloudinary dashboard

**Profile image replace**

- Upload profile photo → upload a second profile photo → first file gone from Cloudinary, only the new one shows, no orphaned DB row for the old one
- Repeat 3-4 times in a row → confirm only ever one profile image exists at any point

**Section images — individual removal**

- Remove section `image` field → file gone from Cloudinary dashboard, placeholder shown
- Remove section `bg_image` field → file gone from Cloudinary dashboard, placeholder shown

**Section delete — with images**

- Section with both `image` and `bg_image` set → delete entire section → both files gone from Cloudinary dashboard
- Section with no images → delete section → no errors, no unnecessary Cloudinary calls

**Edge cases**

- Malformed/non-Cloudinary URL in a field → `extractPublicId()` returns null, no crash, no Cloudinary call attempted
- Delete media that was already removed (double-click race) → no fatal error, graceful response

**Regression**

- Upload still works correctly for all entity types
- Caption/credit editing on existing media unaffected
- Org logo upload/delete still works (shares `CloudinaryService::delete()`)
- All critical routes still present (profile-fragment, promo-fragment, kontakt, newsletter)

## Deferred

- `feat/media-library-v2` — curated, reusable media catalog with "Aus Mediathek wählen" tab; depends on all image use cases being routed through the unified `entity_media`/`media` system. See user story for details.

# feat/team-roles-and-order

## Overview

Role standardisation and display order management for the team listing. Editors assign predefined roles to team members, designate a legal representative directly from the organisation settings page, and control the visual order of the staff grid via drag-and-drop — all without touching the database or code.

## Architecture

```
organisation_info (org-edit select)
      ↓
TeamModel::setLegalRepresentative()   ← sets order_index = 0
      ↓
team.php                              ← index 0 → locked row
                                      ← index 1+ → draggable grid
      ↓
edit-mode.js::team-staff-grid         ← drag reorder → POST /team/reorder
      ↓
TeamModel::reorderTeam()              ← bulk order_index update

TeamModel::getLegalRepresentative()   ← published + order_index = 0
      ↓
PageController::datenschutz/impressum ← display with fallback
```

## Bugs

**Duplicate `order_index = 0` when designating a draft member as legal rep**
`setLegalRepresentative()` originally called `getLegalRepresentative()` internally to find the previous holder. That method filters `status = 'published'`, so a draft member at index 0 was invisible to it — the demotion step was skipped and two members ended up with index 0.
Fixed by replacing the internal call with a raw status-agnostic query: `WHERE deleted_at IS NULL AND order_index = 0`.

## Files Changed

- **`TeamModel.php`** — `order_index` support; `reorderTeam()`, `setLegalRepresentative()` (status-agnostic internal lookup), `getLegalRepresentative()`, `getMaxOrderIndex()`, `publish()`, `unpublish()`
- **`TeamController.php`** — `reorder()`, `publish()`, `unpublish()` endpoints
- **`OrganisationController.php`** — `setLegalRepresentative()` endpoint; lives here because the UI is on org-edit, not team-detail
- **`team.php`** — legal rep locked row (index 0, not draggable); draggable staff grid (index 1+) with pencil/save/cancel header
- **`team-detail.php`** — role select with predefined Austrian association roles and Sonstiges free-text fallback; status bar with publish/unpublish/delete
- **`org-edit.php`** — legal representative select; sets `team.order_index = 0` via `OrganisationController`
- **`PageController.php`** — `datenschutz()` and `impressum()` use `getLegalRepresentative()` instead of `getPresident()`
- **`datenschutz.php`** / **`impressum.php`** — display legal rep name and role; fallback to "Information folgt in Kürze" when none is published at index 0
- **`edit-mode.js`** — team staff grid drag-and-drop handler; role-select live preview and Sonstiges custom input (capture-phase save override); publish/unpublish/delete handlers for team members
- **`edit-mode.css`** — `.team-staff-edit-row` border and editing states; `.team-staff-card.dragging` and `.drag-over` drag feedback
- **`routes.php`** — `POST /team/reorder`, `/team/{id}/publish`, `/team/{id}/unpublish`; `POST /{admin}/org/legal-representative`

---

## Testing

- Role select shows predefined options on team-detail
- Predefined role saves and displays correctly
- Sonstiges reveals free-text input
- Custom role saves correctly — not stored as `"__custom__"`
- Custom role shown as selected on page reload
- Legal rep select on org-edit shows all non-deleted member
- Assigning a published member sets `order_index = 0` correctly
- Assigning a draft member sets `order_index = 0` correctly
- Previous holder demoted to end of list in both cases
- Only one member holds `order_index = 0` at any time
- Legal rep appears in locked row on `/team`
- Legal rep card not draggable
- Staff grid cards draggable in edit mode (grab cursor)
- Drag reorder updates DOM immediately
- Save persists new order after page reload
- Cancel restores original DOM order without reload
- New team member gets `order_index = MAX + 1`, never 0
- `/impressum` shows legal rep name and role from index 0
- `/datenschutz` shows legal rep name and role from index 0
- No published member at index 0 → "Information folgt in Kürze"
- Draft members visible in edit mode (greyscale + Entwurf chip)
- Draft members hidden from public listing
- `Publish/unpublish/delete` work correctly from team-detail status bar

# feat/event-storytelling

## Overview

Restructures event display into two distinct experiences: `/veranstaltungen` shows only upcoming events (the live programme), and `/archiv` is a fully interactive time-travel archive of past events. The split is automatic — driven by `CURDATE()`, not a hardcoded year. The archive features a year timeline nav, category chips with event counts, AJAX filtering with shareable URLs, gallery-first card images, and a curation notice that transparently communicates how many events are still being prepared.

---

## Architecture

```
CURDATE() — single threshold for all date logic
      ↓
/veranstaltungen → EventModel::getUpcoming()   date >= CURDATE()
/archiv          → EventModel::getArchive()    date < CURDATE(), filtered by year + category

EventController::archive()
├── getArchiveYears()              → year timeline chips
├── getArchive($year, $category)   → event grid (all statuses)
├── countArchive($year, $category) → total count including drafts
├── getArchiveCategoriesByYear()   → published-only category chips with count
└── public filter strips drafts    → draft count = total - published sum → curation notice

EventController::archiveFilter()  ← AJAX endpoint
├── same logic as archive()
└── returns JSON { events: html, categories: html }
      ↓
JS swaps #archive-grid + #archive-categories
history.pushState → /archiv?year=2024&category=3
```

---

## Bugs & Fixes

**`ARCHIVE_YEAR` constant hardcoded to 2025**
Events from 2026 were not appearing in the archive because the threshold was fixed. Replaced with `CURDATE()` everywhere — the cut is always today's date, fully automatic.

**`COUNT(*) OVER()` window function not supported on MySQL 5.7**
Initial implementation used a window function to get total event count alongside the result set. MySQL 5.7 (MAMP) doesn't support window functions. Fixed by adding a separate `countArchive()` method with a plain `COUNT(*)` query.

**Subquery alias collision**
Second attempt used a correlated subquery with `e.date` alias inside a subquery that had no `e` alias — silently returning total count of all events (63) instead of the filtered year count. Fixed by extracting `countArchive()` as a completely separate, clean query with no alias dependency.

**Curation notice showing when no drafts exist**
The `$hasDrafts` comparison used `count($events)` after the public filter against `$totalEventsInPeriod` — but events without categories are invisible to the published chip sum, causing a false positive. Fixed by computing draft count as `$totalEventsInPeriod - array_sum(array_column($categories, 'event_count'))` where `$categories` already holds accurate published counts.

---

## Files Changed

- **`EventModel.php`** — remove `ARCHIVE_YEAR` constant; `getArchive()` accepts year + category params, uses `CURDATE()`; new `getArchiveYears()`; new `getArchiveCategoriesByYear()` (published + non-cancelled only, with count); new `countArchive()` (all statuses, for draft detection); `getPast()` and `getAll()` updated to remove hardcoded year
- **`EventController.php`** — `index()` upcoming only; `archive()` year/category filter with current-year default, gallery-first image, `totalEventsInPeriod` via `countArchive()`; new `archiveFilter()` AJAX endpoint returning two HTML fragments
- **`archive.php`** — complete rewrite: year timeline nav, `#archive-categories` and `#archive-grid` swappable containers
- **`events.php`** — upcoming only; newsletter strip always visible below grid with conditional copy; empty state leads directly into newsletter
- **`event-card.php`** — `$cardMedia = $event->cardImage ?? $event->promo ?? null` — gallery-first in archive context, promo-only on programme page
- **`routes.php`** — `GET /archiv/filter` added as static route before `/archiv`
- **`components/archive/events-grid.php`** _(new)_ — event grid fragment, reused by `archive.php` and `archiveFilter()`
- **`components/archive/categories.php`** _(new)_ — category chips with published counts; "Alle" only when multiple categories; single category starts active; curation notice inline when draft count > 0
- **`main.css`** — `.archive-timeline`, `.archive-year-chip`, `.archive-timeline-sep`, `.archive-categories`, `.archive-category-chip`, `.archive-category-count`, `.archive-curation-notice`
- **`app.js`** — archive filter IIFE: year chip clicks, category chip clicks (toggle behaviour), `history.pushState`, `popstate` handler for browser back/forward, `bindCategoryChips()` called after every AJAX swap

---

## Testing

- `/veranstaltungen` shows only upcoming events — no past section
- `/veranstaltungen` empty state shows newsletter strip directly
- `/veranstaltungen` with events shows newsletter strip below grid
- `/archiv` defaults` to current year (2026) on page load
- `/archiv` falls ba ck to most recent year with events when current year has none
- Year chips render for all years with past events, newest left
- Arrow separators between chips render correctly
- Active year chip is filled/highlighted
- Clicking a year chip filters grid via AJAX — no page reload
- URL updates to `/archiv?year=YYYY` on year click
- Shareable URL `/archiv?year=2025` loads correct year directly
- Browser back/forward restores filter state via popstate
- Category chips appear only when selected year has published events
- Single category: no "Alle", chip starts active
- Multiple categories: "Alle (total)" first, starts active
- Category count reflects published events only
- Clicking category filters grid to that category
- Clicking active category resets to "Alle"
- URL updates to `/archiv?year=2025&category=3` on category click
- Curation notice appears when draft events exist in selected year
- Curation notice count is accurate (total - published)
- Grammatical agreement: 1 → "wird", 2+ → "werden"
- No curation notice when all events are published
- No curation notice when year has no categories (no chips rendered)
- Archive card shows gallery image when available
- Archive card falls back to promo when no gallery
- Archive card falls back to music icon when no images
- Programme card uses promo image only
- Past events do not appear on `/veranstaltungen`
- Upcoming events do not appear on `/archiv`
- Draft events hidden from public archive grid
- Draft events visible to logged-in editors in archive
- Edit mode unaffected — create, publish, unpublish, delete still work

---

## Deferred

- `feat/video-embed` — YouTube/Cloudinary video embedding on event detail

# fix/free-section

## Overview

A set of issues and missing features discovered during real-world use of `feat/free-sections-lifecycle`, covering both the editor experience and the public display. Fixed and shipped as a standalone branch.

---

## Issues Fixed

### Richtext toolbar not working

Formatting could be applied but not removed — toggling bold, italic, or list off had no effect.

**Fix:** Toggle detection now runs before selection is restored, at the only point where DOM state is reliable. `resnapSelection()` re-snaps the saved range after every wrap/unwrap so subsequent toolbar clicks reflect current state correctly.

---

### Section image Ändern leaving orphaned Cloudinary files

Replacing a section image or bg image uploaded the new file and updated the DB, but silently left the old file in Cloudinary with no reference and no way to clean it up.

**Fix:** `uploadSection()` now checks for an existing URL before writing, extracts the old `public_id`, and deletes it from Cloudinary first.

---

### New section added without edit mode active

After adding a section the page reloaded with the new block inactive. The editor had to find it and manually click the pencil — easy to miss mid-page, and leaving an empty unedited section behind was a real risk.

**Fix:** The new section ID is stored in `sessionStorage` before reload. On page load the ID is read, cleared, and used to find and auto-activate the exact block via `doActivateBlock()`.

---

### Empty sections rendering as blank space on public side

Newly created or fully cleared sections rendered as an invisible gap in the public layout.

**Fix:** `render-sections.php` skips rendering when `image`, `bg_image`, `title`, `subtitle`, `text`, and `cta` are all empty. `theme` is excluded — it always has a default. Dark-theme sections with no content render as intentional colour separators. Empty sections remain visible to editors.

---

## New Features

### Image credit

Section images had no photographer attribution. A Credit button was added to the image overlay, saving via direct AJAX POST to the section save endpoint — independent of `saveBlock()`. Displays below the image on the public side.

---

### Background image abstracted into its own div

A bg image applied directly on `.segment` bled its own colours and textures into the section design. The bg image is now rendered in a dedicated `.segment-bg` child div, keeping it visually isolated from the content and overlay above it.

**Key decision:** Rather than applying a greyscale filter to the overlay (which would affect content), the image itself is contained in its own layer so greyscale filter applies only to it, adding texture and removing the color noise.

---

## Key Decisions

- **Credit saves independently of `saveBlock()`** — avoids the structural issue of `_image.php` being included twice in `section.php`
- **`uploadSection()` owns Cloudinary cleanup** — the layer that owns the upload owns the deletion
- **sessionStorage over URL params** — no URL pollution, survives same-tab reload, cleared immediately
- **theme excluded from empty check** — always has a default, never a reliable signal of editorial intent

---

## Files Changed

- `edit-mode.js`
- `app/Views/components/sections/partials/_image.php`
- `app/Views/components/sections/partials/_content.php`
- `app/Views/components/sections/section.php`
- `app/Controllers/Admin/MediaController.php`
- `app/Controllers/PageController.php`
- `app/Models/PagesModel.php`
- `app/Views/components/sections/render-sections.php`

## Testing

- Richtext bold/italic/list/link apply and toggle off correctly in same session ✓
- Image credit saves and persists after reload ✓
- Credit modal pre-fills existing value on re-open ✓
- BG image visually isolated — text and overlay unaffected by image colours ✓
- Section image Ändern — old file deleted from Cloudinary ✓
- BG image Ändern — old file deleted from Cloudinary ✓
- New section → correct block auto-activates on reload ✓
- New section inserted mid-page → correct block activated, not last block ✓
- Empty section → invisible on public side ✓
- Dark-theme empty section → renders as colour separator ✓
- Empty section → visible to logged-in editor ✓

# style/link-and-btn-interactions

## Overview

Establishes a consistent interaction language across links, buttons, and navigation. No new features — purely visual and UX polish on existing elements.

## Changes

### `inline-link` — animated underline for text links

`border-bottom: 2px solid transparent` at rest, `currentColor` on hover and active. Works on both light and dark segments. `inline-link--content` adds bold weight for richtext section links.

**Key decision:** class-based opt-in rather than global `a` rule — avoids conflicts with buttons, chips, cards, and UI controls that are also `<a>` tags.

### Button hover — segment-aware inversion

`.btn-section:hover` fills with `--text-on-light` or `--text-on-dark` depending on parent segment. CSS context selectors only — no JS.

### Form buttons — filled by default

Newsletter and contact submit buttons start filled, revert to transparent on hover. Signals primary action without an extra class.

### Social icon circles — segment-aware inversion

`.nav-socials a:hover` fills and inverts color based on segment context. Consistent with button behavior.

### Entity URL links — nav-icon-ux

External links on entity detail pages (team, participants) use `.nav-icon-ux` — icon + label, subtle hover fill.

### Nav active state

`navActive()` and `navActivePrefix()` PHP helpers in `nav.php` add `active` class to matching links. Active state shares the `inline-link` underline treatment. Applied to desktop nav, dropdown submenu, and mobile sidebar.

### Dropdown/sidebar hover

Individual submenu links invert to dark fill on hover/active. Transparent at rest.

### `/programm` routing

`/veranstaltungen` listing → `/programm`. Event detail back link conditional on event date.

### Participant display

Grouped by type, draft status visible to editors and visitors differently.

## Files Changed

- `public/assets/css/main.css`
- `app/Views/components/nav.php`
- `app/Views/layouts/footer.php`
- `app/Views/components/entity-urls.php`
- `app/Views/components/entity-venues.php`
- `app/Views/pages/event-detail.php`
- `app/Controllers/EventController.php` — `index()` page_key updated to `programm`
- `config/routes.php` — `/veranstaltungen` listing replaced by `/programm`
- `app/Views/pages/event-detail.php` — conditional back link: upcoming → `/programm`, past → `/archiv?year=` derived from event date
- `public/assets/js/edit-mode.js`

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
