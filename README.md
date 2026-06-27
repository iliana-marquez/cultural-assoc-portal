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
