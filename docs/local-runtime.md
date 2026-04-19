# Local Runtime Verification

## Goal
- Run Milestone 0/1 locally on this machine without requiring host-installed PHP, Composer, or Docker.

## Strategy
- Provision PHP and Composer into a workspace-local `.runtime` directory.
- Use SQLite for local verification.
- Keep `docker-compose.yml` and Sail settings as the target stack for team development and deployment-oriented workflows.

## Local Verification Checklist
1. Download and extract PHP into `.runtime/php`.
2. Create `.runtime/php/php.ini` from the production template and enable:
   - `openssl`
   - `pdo_sqlite`
   - `sqlite3`
   - `mbstring`
   - `fileinfo`
   - `tokenizer`
   - `curl`
3. Download `composer.phar` into `.runtime`.
4. Run Composer using the local PHP binary.
5. Create `database/database.sqlite`.
6. Use a local `.env` configured for:
   - `DB_CONNECTION=sqlite`
   - `DB_DATABASE=<absolute path>/database/database.sqlite`
   - `SESSION_DRIVER=array` or `database` after sessions migration exists
   - `CACHE_STORE=array`
   - `QUEUE_CONNECTION=sync`
7. Run migrations, seeders, and the test suite.

## Notes
- This path is for Milestone 0/1 verification only.
- The committed `.env.example` remains aligned with the Sail/MySQL/Redis target stack.
