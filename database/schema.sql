-- =============================================================
-- Organisation Website System (OWS)
-- Database Schema
-- MySQL 8.0+
-- =============================================================


-- =============================================================
-- SEED
-- Pre-populated on every deployment
-- =============================================================

create table if not exists section_types (
    type_key        varchar(50)     not null,
    label           varchar(100)    not null,
    fields_schema   json            not null,
    primary key (type_key)
);

create table if not exists url_types (
    id              int             not null auto_increment,
    label           varchar(100)    not null,
    icon            varchar(100)    not null,
    primary key (id)
);


-- =============================================================
-- CONTENT
-- Page sections — JSON-driven, dynamic per deployment
-- =============================================================

create table if not exists pages (
    id              int             not null auto_increment,
    page_key        varchar(50)     not null,
    section_key     varchar(50)     not null,
    type_key        varchar(50)     not null,
    order_index     int             not null default 0,
    content         json            not null,
    created_at      timestamp       not null default current_timestamp,
    updated_at      timestamp       not null default current_timestamp on update current_timestamp,
    primary key (id),
    foreign key (type_key) references section_types(type_key)
);


-- =============================================================
-- ORGANISATION
-- Identity, contact, legal — single row per deployment
-- =============================================================

create table if not exists organisation_info (
    id                  int             not null auto_increment,
    name                varchar(255)    not null,
    tagline             varchar(255)    null,
    description         varchar(300)    null,
    organisation_type   varchar(100)    null,
    logo_url            varchar(255)    null,
    street              varchar(255)    null,
    postcode            varchar(20)     null,
    city                varchar(100)    null,
    country             varchar(100)    null,
    registration_number varchar(100)    null,
    email               varchar(255)    null,
    phone               varchar(50)     null,
    statutes_url        varchar(255)    null,
    schema_type         varchar(100)    not null default 'Organization',
    updated_at          timestamp       not null default current_timestamp on update current_timestamp,
    primary key (id)
);


-- =============================================================
-- CONTRIBUTORS
-- Partners, sponsors, collaborators — polymorphic assignments
-- =============================================================

create table if not exists contributors (
    id              int             not null auto_increment,
    name            varchar(255)    not null,
    type            varchar(50)     not null,
    description     text            null,
    image           varchar(255)    null,
    created_at      timestamp       not null default current_timestamp,
    updated_at      timestamp       not null default current_timestamp on update current_timestamp,
    primary key (id)
);

create table if not exists contributors_assignments (
    id              int             not null auto_increment,
    contributor_id  int             not null,
    entity_type     varchar(50)     not null,
    entity_id       int             null,
    primary key (id),
    foreign key (contributor_id) references contributors(id) on delete cascade
);


-- =============================================================
-- URLS
-- External links — polymorphic across all entities
-- =============================================================

create table if not exists urls (
    id              int             not null auto_increment,
    entity_type     varchar(50)     not null,
    entity_id       int             not null,
    url_type_id     int             not null,
    url             varchar(255)    not null,
    primary key (id),
    foreign key (url_type_id) references url_types(id)
);


-- =============================================================
-- TEAM
-- Internal member profiles
-- =============================================================

create table if not exists team (
    id              int             not null auto_increment,
    first_name      varchar(100)    not null,
    last_name       varchar(100)    not null,
    title           varchar(100)    null,
    role            varchar(150)    not null,
    motto           text            null,
    biography       text            null,
    image           varchar(255)    null,
    image_credits   varchar(255)    null,
    created_at      timestamp       not null default current_timestamp,
    updated_at      timestamp       not null default current_timestamp on update current_timestamp,
    primary key (id)
);


-- =============================================================
-- PARTICIPANTS
-- External contributors — artists, speakers, performers
-- =============================================================

create table if not exists participants_categories (
    id              int             not null auto_increment,
    label           varchar(100)    not null,
    created_at      timestamp       not null default current_timestamp,
    primary key (id)
);

create table if not exists participants (
    id              int             not null auto_increment,
    first_name      varchar(100)    not null,
    last_name       varchar(100)    not null,
    category_id     int             not null,
    field           varchar(150)    null,
    image           varchar(255)    null,
    created_at      timestamp       not null default current_timestamp,
    updated_at      timestamp       not null default current_timestamp on update current_timestamp,
    primary key (id),
    foreign key (category_id) references participants_categories(id) on delete restrict
);


-- =============================================================
-- VENUES
-- Physical locations for events and projects
-- =============================================================

create table if not exists venues (
    id          int             not null auto_increment,
    name        varchar(255)    not null,
    street      varchar(255)    null,
    postcode    varchar(20)     null,
    city        varchar(100)    null,
    country     varchar(100)    null,
    created_at  timestamp       not null default current_timestamp,
    updated_at  timestamp       not null default current_timestamp on update current_timestamp,
    primary key (id)
);



-- =============================================================
-- PROJECTS
-- Funded initiatives, series, productions
-- =============================================================

create table if not exists project_categories (
    id              int             not null auto_increment,
    label           varchar(100)    not null,
    created_at      timestamp       not null default current_timestamp,
    primary key (id)
);

create table if not exists projects (
    id              int             not null auto_increment,
    category_id     int             null,
    title           varchar(255)    not null,
    subtitle        varchar(255)    null,
    description     text            null,
    venue_id        int             null,
    start_date      date            null,
    end_date        date            null,
    created_at      timestamp       not null default current_timestamp,
    updated_at      timestamp       not null default current_timestamp on update current_timestamp,
    primary key (id),
    foreign key (category_id) references project_categories(id) on delete set null,
    foreign key (venue_id) references venues(id) on delete set null
);




-- =============================================================
-- EVENTS
-- Programme records
-- =============================================================

create table if not exists event_categories (
    id              int             not null auto_increment,
    label           varchar(100)    not null,
    created_at      timestamp       not null default current_timestamp,
    primary key (id)
);

create table if not exists events (
    id              int             not null auto_increment,
    project_id      int             null,
    category_id     int             null,
    title           varchar(255)    not null,
    subtitle        varchar(255)    null,
    description     text            null,
    date            date            not null,
    time            time            null,
    venue_id        int             null,
    review          text            null,
    created_at      timestamp       not null default current_timestamp,
    updated_at      timestamp       not null default current_timestamp on update current_timestamp,
    primary key (id),
    foreign key (project_id) references projects(id) on delete set null,
    foreign key (category_id) references event_categories(id) on delete set null,
    foreign key (venue_id) references venues(id) on delete set null
);

create table if not exists event_participants (
    event_id        int             not null,
    participant_id  int             not null,
    primary key (event_id, participant_id),
    foreign key (event_id) references events(id) on delete cascade,
    foreign key (participant_id) references participants(id) on delete cascade
);


-- =============================================================
-- MEDIA
-- Promotional and documentary media — polymorphic
-- =============================================================

create table if not exists media (
    id              int             not null auto_increment,
    entity_type     varchar(50)     not null,
    entity_id       int             not null,
    media_url       varchar(255)    not null,
    caption         varchar(255)    null,
    stage           varchar(50)     null,
    order_index     int             not null default 0,
    created_at      timestamp       not null default current_timestamp,
    primary key (id)
);


-- =============================================================
-- ARCHIVE
-- Historical events — read only after import
-- =============================================================

create table if not exists archive (
    id              int             not null auto_increment,
    title           varchar(255)    not null,
    date            date            null,
    description     text            null,
    image           varchar(255)    null,
    artists         json            null,
    created_at      timestamp       not null default current_timestamp,
    primary key (id)
);


-- =============================================================
-- AUTH
-- Authorised editors — OTP based, no passwords
-- =============================================================

create table if not exists authorised_users (
    id                  int             not null auto_increment,
    name                varchar(100)    not null,
    email               varchar(255)    not null unique,
    can_manage_users    boolean         not null default false,
    otp_code            varchar(255)    null,
    otp_expires_at      timestamp       null,
    created_at          timestamp       not null default current_timestamp,
    primary key (id)
);


-- =============================================================
-- SEED DATA
-- Inserted on every deployment
-- =============================================================

insert into section_types (type_key, label, fields_schema) values
('hero',        'Hero Banner',    '["title","subtitle","text","media_urls","buttons"]'),
('text_block',  'Text Block',     '["title","subtitle","text","media_urls","buttons"]'),
('cta_block',   'Call to Action', '["title","text","buttons"]');

insert into url_types (label, icon) values
('Website',         'ti-world'),
('Facebook',        'ti-brand-facebook'),
('Instagram',       'ti-brand-instagram'),
('LinkedIn',        'ti-brand-linkedin'),
('YouTube',         'ti-brand-youtube'),
('Spotify',         'ti-brand-spotify'),
('SoundCloud',      'ti-brand-soundcloud'),
('Vimeo',           'ti-brand-vimeo'),
('Bandcamp',        'ti-brand-bandcamp'),
('Email',           'ti-mail'),
('Press',           'ti-news'),
('Radio',           'ti-radio'),
('TV',              'ti-device-tv'),
('Maps',             'ti-map-pin'),
('Other',            'ti-link');
