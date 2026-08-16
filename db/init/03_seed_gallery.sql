-- Development seed data for the gallery. Runs once, after 02_seed_users.sql, on a fresh volume.

INSERT INTO photos (user_id, filename, caption, created_at)
SELECT u.id, v.filename, v.caption, now() - v.age
  FROM (VALUES
      ('seed-01.jpg', 'jamie', 'Waiting for the lilies to open.',                      interval '12 minutes'),
      ('seed-02.jpg', 'bob',   NULL,                                                   interval '55 minutes'),
      ('seed-03.jpg', 'carol', 'The last of the light on the ridge.',                   interval '3 hours'),
      ('seed-04.jpg', 'jamie', 'Tried six overlays before this one. The rest are in a folder I will never open again.', interval '8 hours'),
      ('seed-05.jpg', 'dave',  NULL,                                                   interval '1 day'),
      ('seed-06.jpg', 'jamie', 'Somewhere between the water and the sky.',              interval '3 days'),
      ('seed-07.jpg', 'bob',   'My hand slipped and it came out better.',               interval '3 days'),
      ('seed-08.jpg', 'erin',  'Everything was very still.',                            interval '5 days'),
      ('seed-09.jpg', 'jamie', NULL,                                                    interval '9 days'),
      ('seed-10.jpg', 'carol', 'Third attempt. Worth it.',                              interval '12 days'),
      ('seed-11.jpg', 'jamie', 'I have been coming back to this spot every week since spring, and it has never once looked the same twice, which is either the light or me.', interval '20 days'),
      ('seed-12.jpg', 'bob',   NULL,                                                    interval '26 days'),
      ('seed-13.jpg', 'dave',  'The oldest thing in here, and still my favourite.',      interval '40 days')
  ) AS v (filename, username, caption, age)
  JOIN users u ON u.username = v.username;

INSERT INTO likes (photo_id, user_id)
SELECT p.id, u.id
  FROM (VALUES
      ('seed-01.jpg', 'bob'),
      ('seed-01.jpg', 'carol'),
      ('seed-01.jpg', 'dave'),
      ('seed-01.jpg', 'erin'),

      ('seed-02.jpg', 'jamie'),
      ('seed-02.jpg', 'carol'),

      ('seed-03.jpg', 'bob'),
      ('seed-03.jpg', 'erin'),

      ('seed-05.jpg', 'jamie'),
      ('seed-05.jpg', 'dave'),
      ('seed-05.jpg', 'erin'),

      ('seed-06.jpg', 'bob'),
      ('seed-06.jpg', 'carol'),

      ('seed-07.jpg', 'jamie'),

      ('seed-09.jpg', 'bob'),
      ('seed-09.jpg', 'carol'),
      ('seed-09.jpg', 'dave'),
      ('seed-09.jpg', 'erin'),

      ('seed-10.jpg', 'jamie'),

      ('seed-11.jpg', 'bob'),

      ('seed-12.jpg', 'jamie'),
      ('seed-12.jpg', 'carol'),
      ('seed-12.jpg', 'dave'),

      ('seed-13.jpg', 'bob'),
      ('seed-13.jpg', 'carol'),
      ('seed-13.jpg', 'dave'),
      ('seed-13.jpg', 'erin')
  ) AS v (filename, username)
  JOIN photos p ON p.filename = v.filename
  JOIN users  u ON u.username = v.username;

INSERT INTO comments (photo_id, user_id, body, created_at)
SELECT p.id, u.id, v.body, now() - v.age
  FROM (VALUES
      ('seed-01.jpg', 'carol', 'That framing is perfect 😍', interval '5 minutes'),

      ('seed-03.jpg', 'bob',   'How did you line the overlay up so well?', interval '2 hours'),
      ('seed-03.jpg', 'dave',  'Stealing this idea, sorry not sorry.', interval '90 minutes'),

      ('seed-04.jpg', 'erin',  '<script>alert("xss")</script>', interval '7 hours'),

      ('seed-05.jpg', 'jamie', 'The lighting on this one is unfair.', interval '20 hours'),

      ('seed-06.jpg', 'bob',   'Ha! The cat ears make it.', interval '2 days'),
      ('seed-06.jpg', 'carol', 'Agreed, best one this week.', interval '1 day'),
      ('seed-06.jpg', 'dave',  'Third.', interval '6 hours'),

      ('seed-09.jpg', 'erin',  E'Two things.\n\nFirst, the colours. Second, whatever you did to the background — tell me.', interval '8 days'),

      ('seed-11.jpg', 'jamie', 'Still my favourite of the batch.', interval '19 days'),

      ('seed-12.jpg', 'carol', 'It''s the little details that sell it.', interval '25 days'),

      ('seed-13.jpg', 'bob',   'Where was this taken?', interval '30 days'),
      ('seed-13.jpg', 'erin',  'Late to this but it is great.', interval '12 days')
  ) AS v (filename, username, body, age)
  JOIN photos p ON p.filename = v.filename
  JOIN users  u ON u.username = v.username;
