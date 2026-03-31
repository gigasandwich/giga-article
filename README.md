Si docker ne marche pas avec
```bash
docker compose up --build
```

Il faut rendre `docker-entrypoint.sh` LF et non CRLF.
