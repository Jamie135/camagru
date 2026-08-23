-- Creation of users, photos, comments, and likes tables, with constraints and indexes.

CREATE TABLE users (
    id                SERIAL PRIMARY KEY,
    username          VARCHAR(32)  NOT NULL,
    email             VARCHAR(255) NOT NULL,
    password_hash     VARCHAR(255) NOT NULL,

    -- Email confirmation
    confirmed_at      TIMESTAMPTZ,
    confirmation_token VARCHAR(64),

    -- Password reset
    reset_token       VARCHAR(64),
    reset_expires_at  TIMESTAMPTZ,

    -- Email change. The address stays here until the link sent to it is
    -- clicked, so the old one keeps working until the new one is proven.
    pending_email           VARCHAR(255),
    email_change_token      VARCHAR(64),
    email_change_expires_at TIMESTAMPTZ,

    -- Password change. Same idea: the new hash waits here until the link is
    -- clicked, so the old password keeps working and the new one does not.
    pending_password_hash      VARCHAR(255),
    password_change_token      VARCHAR(64),
    password_change_expires_at TIMESTAMPTZ,

    notify_on_comment BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at        TIMESTAMPTZ  NOT NULL DEFAULT now(),

    -- Constraints to enforce username length and email format.
    CONSTRAINT users_username_length CHECK (char_length(username) BETWEEN 3 AND 32),
    CONSTRAINT users_email_shape     CHECK (position('@' IN email) > 1)
);

-- Unique indexes to enforce case-insensitive uniqueness for email and username, and to index tokens for efficient lookups.
CREATE UNIQUE INDEX users_email_lower_idx    ON users (lower(email));
CREATE UNIQUE INDEX users_username_lower_idx ON users (lower(username));

CREATE UNIQUE INDEX users_confirmation_token_idx ON users (confirmation_token)
    WHERE confirmation_token IS NOT NULL;
CREATE UNIQUE INDEX users_reset_token_idx ON users (reset_token)
    WHERE reset_token IS NOT NULL;
CREATE UNIQUE INDEX users_email_change_token_idx ON users (email_change_token)
    WHERE email_change_token IS NOT NULL;
CREATE UNIQUE INDEX users_password_change_token_idx ON users (password_change_token)
    WHERE password_change_token IS NOT NULL;

-- ---------------------------------------------------------------------------
-- photos
-- ---------------------------------------------------------------------------

CREATE TABLE photos (
    id         SERIAL PRIMARY KEY,
    user_id    INTEGER      NOT NULL REFERENCES users (id) ON DELETE CASCADE,

    filename   VARCHAR(255) NOT NULL UNIQUE,

    caption    TEXT,

    created_at TIMESTAMPTZ  NOT NULL DEFAULT now(),

    CONSTRAINT photos_caption_length CHECK (caption IS NULL OR char_length(caption) BETWEEN 1 AND 200)
);


CREATE INDEX photos_created_at_idx ON photos (created_at DESC);
CREATE INDEX photos_user_id_idx    ON photos (user_id, created_at DESC);

-- ---------------------------------------------------------------------------
-- comments
-- ---------------------------------------------------------------------------

CREATE TABLE comments (
    id         SERIAL PRIMARY KEY,
    photo_id   INTEGER     NOT NULL REFERENCES photos (id) ON DELETE CASCADE,
    user_id    INTEGER     NOT NULL REFERENCES users (id)  ON DELETE CASCADE,
    body       TEXT        NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),

    CONSTRAINT comments_body_length CHECK (char_length(body) BETWEEN 1 AND 1000)
);

CREATE INDEX comments_photo_id_idx ON comments (photo_id, created_at);

-- ---------------------------------------------------------------------------
-- likes
-- ---------------------------------------------------------------------------

CREATE TABLE likes (
    photo_id   INTEGER     NOT NULL REFERENCES photos (id) ON DELETE CASCADE,
    user_id    INTEGER     NOT NULL REFERENCES users (id)  ON DELETE CASCADE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),

    PRIMARY KEY (photo_id, user_id)
);
