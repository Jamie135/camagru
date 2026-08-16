-- Development seed data for user accounts. Runs once, after 01_schema.sql, on a fresh volume.
-- Every account below uses the password:  Camagru42!

INSERT INTO users (username, email, password_hash, confirmed_at, notify_on_comment) VALUES
    ('jamie', 'jamie@example.com',
     '$2y$10$tfA8PedqL4WsQoFYJuaVEO8YSayOuwQo.XqibJqysBM871Sq9j1hm',
     now(), TRUE),

    ('bob', 'bob@example.com',
     '$2y$10$0toCVx1xemGQtHKZ3WTnK.LPjJTGEfaiSLr9hnDqWZm6LmPhYmJFi',
     now(), FALSE),

    ('carol', 'carol@example.com',
     '$2y$10$tfA8PedqL4WsQoFYJuaVEO8YSayOuwQo.XqibJqysBM871Sq9j1hm',
     now(), TRUE),

    ('dave', 'dave@example.com',
     '$2y$10$tfA8PedqL4WsQoFYJuaVEO8YSayOuwQo.XqibJqysBM871Sq9j1hm',
     now(), TRUE),

    ('erin', 'erin@example.com',
     '$2y$10$tfA8PedqL4WsQoFYJuaVEO8YSayOuwQo.XqibJqysBM871Sq9j1hm',
     now(), FALSE);

-- Deliberately left unconfirmed: login must refuse this one and say so.
INSERT INTO users (username, email, password_hash, confirmed_at, confirmation_token) VALUES
    ('newbie', 'newbie@example.com',
     '$2y$10$XkV/.y/5H69LIUZoE9OMr.x8d4uzdGKoRupB9GDF1EmCQ43YrWgyi',
     NULL, 'seed-confirmation-token-for-local-testing-only-0000000000000000');

