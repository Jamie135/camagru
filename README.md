# Camagru

A small photo-booth web application, built for the 42Paris school.

Users sign up, confirm their account by email, take a picture with their webcam
(or upload one), overlay a selection of images on it, and publish the
result to a public gallery where anyone can browse it and registered users can
like and comment on it.

## Stack

| Piece      | What it is                                                       |
| ---------- | ---------------------------------------------------------------- |
| `nginx`    | Serves `public/`, proxies `.php` to PHP-FPM, serves uploaded images from `/uploads/` |
| `php`      | PHP 8.3-FPM with `pdo_pgsql` and `gd` (JPEG support compiled in)  |
| `db`       | PostgreSQL 16; SQL files in `db/init/` run once on first boot     |
| `mailpit`  | Catches all outgoing mail so account confirmations and password resets can be read locally |

## Getting started

```sh
cp .env.example .env                 # then fill in DB_NAME / DB_USER / DB_PASSWORD
cp assets/photos/*.jpg data/uploads/ # photos the gallery seed rows point at
docker compose up -d --build
```

- App: <http://localhost:8080>
- Mail UI: <http://localhost:8025>

Both ports come from `.env` (`NGINX_PORT`, `MAILPIT_UI_PORT`); change them there
if something else already owns 8080.

## Useful commands

### Lifecycle

```sh
docker compose up -d              # start everything in the background
docker compose up -d --build      # rebuild the PHP image, then start
docker compose ps                 # what is running, and is it healthy
docker compose stop               # stop, keep containers and data
docker compose down               # remove containers, keep the db volume
docker compose down -v            # remove containers AND wipe the database
docker compose restart php        # pick up a php.ini / extension change
docker image prune -a             # remove image not used by running container
docker system prune -a --volumes  # bulk cleanup
```

Application code is bind-mounted, so edits to `app/` and `public/` take effect on
the next request — no rebuild, no restart.

### Logs

```sh
docker compose logs -f            # everything, followed
docker compose logs -f php        # PHP errors and warnings land here
docker compose logs -f nginx      # access + error log
docker compose logs --tail=100 db
```

### Shells

```sh
docker compose exec php bash
docker compose exec php php -v
docker compose exec php php -m                      # confirm gd / pdo_pgsql are loaded
docker compose exec php php -l app/some_file.php    # syntax check
```

### Database

```sh
# interactive psql (env vars come from the container)
docker compose exec db psql -U "$DB_USER" -d "$DB_NAME"

# one-off query
docker compose exec db psql -U "$DB_USER" -d "$DB_NAME" -c '\dt'

# apply a schema file by hand
docker compose exec -T db psql -U "$DB_USER" -d "$DB_NAME" < db/init/schema.sql

# dump / restore
docker compose exec db pg_dump -U "$DB_USER" "$DB_NAME" > backup.sql
docker compose exec -T db psql -U "$DB_USER" -d "$DB_NAME" < backup.sql
```

> Files in `db/init/` only run when the `db_data` volume is created. To re-apply
> them after changing the schema, `docker compose down -v && docker compose up -d`
> — this destroys all data.

### Uploads

```sh
ls -lh data/uploads/                                 # what has been generated
docker compose exec php ls -ld /var/www/data/uploads # check ownership if writes fail
find data/uploads -type f ! -name .gitkeep -delete   # clear generated images
cp assets/photos/*.jpg data/uploads/                 # put the seed photos back
```

`data/uploads/` is gitignored, so it is empty on a fresh clone and after the
clean-up above. The photos the gallery seed rows point at are tracked in
`assets/photos/` and copied across instead; skip that step and every row from
`db/init/03_seed_gallery.sql` renders as a broken image.

## Notes

- The webcam requires a secure context. `http://localhost` counts as one, so
  `getUserMedia` works locally; over a LAN IP it will not.
- `client_max_body_size` is 10M in nginx; raise it together with PHP's
  `upload_max_filesize` and `post_max_size` if you allow bigger uploads.
- Nothing is sent to a real mail server in development — every message is
  captured by Mailpit at <http://localhost:8025>.
