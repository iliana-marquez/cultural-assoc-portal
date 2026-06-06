-- =============================================================
-- Migration 001
-- OWS — Organisation Website System
-- Apply in phpMyAdmin SQL tab
--
--
--
--
--
--
-- =============================================================


-- ── organisation_info — add new columns ──────────────────────

alter table organisation_info
    add column description          varchar(300)    null            after tagline,
    add column organisation_type    varchar(100)    null            after description,
    add column street               varchar(255)    null            after logo_url,
    add column postcode             varchar(20)     null            after street,
    add column city                 varchar(100)    null            after postcode,
    add column country              varchar(100)    null            after city,
    add column schema_type          varchar(100)    not null        default 'Organization' after registration_number,
    drop column address;


-- ── venues — new table ───────────────────────────────────────

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


-- ── events — remove location fields, add venue_id ────────────

alter table events
    drop column location_name,
    drop column location_url,
    drop column image_url,
    add column venue_id int null after category_id,
    add foreign key (venue_id) references venues(id) on delete set null;


-- ── projects — add venue_id, remove image_url ────────────────

alter table projects
    drop column image_url,
    add column venue_id int null after category_id,
    add foreign key (venue_id) references venues(id) on delete set null;


-- ── team — rename image_url to image ─────────────────────────

alter table team
    change column image_url image varchar(255) null;


-- ── participants — rename image_url to image ─────────────────

alter table participants
    change column image_url image varchar(255) null;


-- ── contributors — rename logo_url to image ──────────────────

alter table contributors
    change column logo_url image varchar(255) null;


-- ── archive — rename image_url to image ──────────────────────

alter table archive
    change column image_url image varchar(255) null;


-- ── url_types — add Maps, remove Apple Maps/Google Maps ──────

insert into url_types (label, icon) values
    ('Maps',    'ti-map-pin'),
    ('Press',   'ti-news'),
    ('Radio',   'ti-radio'),
    ('TV',      'ti-device-tv');