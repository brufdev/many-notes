# End-to-end (e2e) tests for web

Browser tests written in plain English for [Kane CLI](https://www.testmuai.com/kane-cli/).

| Test | What it covers |
|---|---|
| `vault_test.md` | Registering and creating a vault |
| `note_test.md` | Registering, creating a vault and a note, and checking the note is saved after a reload |

## Run

Start a fresh instance on port 4880:

```bash
docker run -d --name many-notes-e2e -p 4880:8080 -e APP_URL=http://localhost:4880 brufdev/many-notes:latest
npm install -g @testmuai/kane-cli
kane-cli login
kane-cli testmd run e2e/kane/vault_test.md
kane-cli testmd run e2e/kane/note_test.md
```

Both tests register a new user, so recreate the container before each run.
