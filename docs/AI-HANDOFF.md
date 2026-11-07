# AI Handoff — Dompet Kita

Last updated: 2026-11-06

## Repository and current state

- Repo: `https://github.com/davidna29/dompet-kita`
- Base branch: `main` at `9dfc31b docs: sync AI handoff with latest API commits`.
- Work in progress branch: `feat/kantong-tipe-hutang` — pocket types and debts/receivables (FR-023). Not yet committed or merged when this note was written; run `git status` / `git log` to confirm.
- `main` includes budget read/write, `GET /api/kategori`, and recurring CRUD with `transaksi_berulang.client_key` (`9a43f62`).
- Untracked `docs/fitur-hutang-kantong.md` is the user-supplied implementation prompt for this branch; keep it until the work is merged.
- `docs/rangkuman-perbaikan-dompet-kita.md` was supplied by the user and read as the Hermes audit source. Its text was preserved while the agreed findings were applied to the app.
- `docs/rangkuman-perbaikan-dompet-kita.md` was supplied by the user and read as the Hermes audit source. Its text was preserved while the agreed findings were applied to the app.
- Preserve the Pink Linen, account, wallet, API, animation, and documentation work summarized below.

## Implemented

### Pocket types and debts/receivables (branch `feat/kantong-tipe-hutang`)

- `kantong.tipe` is a plain string restricted by validation to `harian`, `tabungan`, `investasi` (not a MySQL ENUM, so SQLite tests stay compatible). The migration defaults existing rows to `harian`. `GET /api/kantong` now includes `tipe`; `SaldoService::totalByTipe()` groups balances.
- The Kantong UI shows a type badge, a type filter, and three totals (harian/tabungan/investasi). Create/edit accepts `tipe`; rename/archive behaviour is unchanged.
- FR-023 `hutang_piutang` table and `HutangService`: creating a record never creates a transaction. `bayar()` locks the row, creates the transaction through `TransaksiService` (bayar hutang = `keluar` + `Hutang`; terima piutang = `masuk` + `Piutang`), reduces `sisa`, and sets `lunas` at zero. Idempotent through `HUTANG-<id>-<YYYYMMDD>-<jumlah>`.
- API: `GET /api/hutang` (filter `jenis`/`status`), `POST /api/hutang`, `POST /api/hutang/{id}/bayar`, `DELETE /api/hutang/{id}` (soft, idempotent). Web: Hutang list, create, detail/payment, soft delete.
- `Transaksi::KATEGORI_TRANSFER` now holds `Mutasi`, `Hutang`, `Piutang`. All spending/income totals, per-category charts, daily cumulative, and budget usage exclude them; pocket balances still include every transaction. Budget writes reject all three transfer categories.
- PRD raised to v0.4 (FR-023 moves to Should/Tahap 2b), and `README`, `PRODUCT.md`, `AGENTS.md`, `docs/api.md` were updated.

### Themes and motion

- Olive remains the default palette; optional **Pink Linen** is available from **Pengaturan → Tampilan**. Light/dark choice is independent from palette choice; only theme preferences are stored in browser local storage.
- Tomato/ochre/sage/sky keep their ledger meanings across both palettes.
- Motion uses shared 180/200/240 ms timing and the Papan Dapur magnet-and-slip signature. Interactive controls, disclosures, dialogs, toasts, charts, progress fills, and Livewire loading states have state-based feedback. Static content has no page-load entrance or looping animation.
- `prefers-reduced-motion` collapses authored movement; contrast, labels, and state remain visible.
- `DESIGN.md`, `.impeccable/design.json`, and `.impeccable/surfaces/beranda.md` document both palettes and motion direction.

### Wallets, income categories, users

- Kantong can be created, renamed, soft-archived, and restored. Rename updates `metode` / `kantong_tujuan` on active and soft-deleted transactions through `TransaksiService` (so `updated_at` and transaction history are maintained), and updates related recurring schedules. Rename is rejected if it would make a Mutasi source and destination identical.
- Archiving requires a current balance of zero and no active recurring schedule. Archived wallets remain visible and restorable; their names stay reserved to avoid confusing text-based historical references. Archived wallets are omitted from the active balance API, and still count as registered historical method names in the UI warning logic.
- Income quick suggestions are Gaji, Honor, Upah, Bonus, Hadiah, and Proyek. Categories remain free text; Mutasi stays outgoing-only and is hidden from income suggestions. New UI method choices must be active wallets, while unchanged legacy/archived values remain editable.
- David and Nurul are admin users. The `is_admin` migration promotes users already present when it runs; demo seeding also marks both as admins. Pengaturan lets admins create member accounts with unique email, a confirmed 12+ character password, and `pencatat` initialized from the member name. New members are not admins and cannot access user creation.

### API and Livewire fixes from the Hermes audit

- API `pencatat` remains required; attribution is important, and the internal service fallback is not a reason to relax the STB contract.
- Repeated API DELETE on a soft-deleted transaction now returns the same row with 200. PUT on a soft-deleted row remains unavailable; there is no restore API endpoint.
- A no-op PUT no longer calls `touch()`, so it does not advance `updated_at` or reappear in `since` pulls.
- API wallet methods remain text-compatible while Hermes does not sync `GET /api/kantong`; unknown names are surfaced in the UI. Strict active-wallet validation is limited to new UI choices.
- Offset pagination and `next_offset` are unchanged. A keyset cursor needs an explicit Hermes/STB compatibility update before changing this contract.
- PRD normalization now describes title case per word, matching the service and `AGENTS.md`.
- The Livewire `livewire:navigating` / `livewire:navigated` lifecycle reapplies stored theme preferences, reinitializes page behaviors, and keeps toast dismissal working after navigation.
- Budget API is now read/write: `GET /api/anggaran`, `POST /api/anggaran` (idempotent upsert per `periode` + `kategori`, `null`/`__total__` = total bulanan), `DELETE /api/anggaran/{id}` (soft delete, idempotent), and `POST /api/anggaran/salin`. Categories are title-cased; `Mutasi` is rejected. Borrowed rows keep `id: null` and cannot be deleted from the borrowing period; POST materializes them first. Usage still excludes Mutasi and income.
- `GET /api/kategori` is a read-only list merging transaction categories and active `kategori_meta`, with icon/colour/order fallback, expense/income usage counts, totals, and a Mutasi flag. Category meta writes remain UI-only.
- Recurring schedules now have read/write API: `GET /api/pengingat` (active by default, `semua=1` includes inactive), `POST /api/pengingat`, `PUT /api/pengingat/{id}` (partial, `client_key` immutable), `DELETE /api/pengingat/{id}` (soft, idempotent), plus the existing confirm endpoint. `transaksi_berulang.client_key` is nullable unique for idempotent POST; a key matching a soft-deleted schedule returns that row with `deleted_at` rather than restoring it. Web `BerulangController` now uses the shared `PengingatService` save/update logic.
- Hostinger production `/api/health` returned 401 despite a 64-character `ADMIN_TOKEN`. The exposed token must be rotated. Compare SHA-256 of the `.env` value and cached config value before assuming the header is at fault; a stale `config:cache` can hold a different token of the same length.

## Local Herd environment

- Herd site: `http://dompet-kita.test` (linked under the `dompet-kita` site name).
- PHP CLI/FPM: Herd PHP 8.4; local DB connection: SQLite at `database/database.sqlite`.
- Local `.env` has an APP_KEY and `DEMO_MODE=true`; it is gitignored. `vendor/`, `node_modules/`, and SQLite files are also ignored.
- The local database was migrated and seeded with synthetic demo data. Do **not** run `migrate:fresh --seed` unless intentionally resetting this local demo DB.
- `composer install` and `npm ci` completed. Composer reported that `composer.lock` is not fully synchronized with `composer.json`; install succeeded. No `composer update` was run.
- `npm run build` succeeded; `public/build` outputs are tracked and currently reflect the latest source.

## Verification already completed

- `herd php artisan test`: **97 passed, 502 assertions** (includes `ApiAnggaranTest`, `ApiKategoriTest`, `ApiPengingatTest`, `ApiHutangTest`, and `KantongTipeTest`).
- `npm run build`: passed.
- Pint `--test` on changed PHP files: passed.
- `git diff --check`: passed.
- Local `/login` returned HTTP 200.
- Impeccable detector ran once. It flagged a progress-width animation; this was changed to `scaleX`/transform, and a source check confirmed no remaining width/height/margin/padding transitions. Do not rerun the detector in the same task unless targets change again.

## Pending / before release

0. **Branch `feat/kantong-tipe-hutang` still needs review, commit, and merge.** Visual QA for the new Hutang pages and the Kantong type filter/badges is pending on desktop and 360 px, light/dark and both palettes.
1. **Visual QA is still pending for this motion pass:** inspect desktop and 360 px, both light/dark and Olive/Pink Linen, toast close/auto-dismiss, dialog/disclosure, loading, hover/focus, and Reduce Motion; check console and horizontal overflow. The existing Chrome window was on a `Tambah jadwal` form, so it was left untouched rather than navigated away. Check with the user or use a separate safe browser context.
2. **Hostinger API auth is still unresolved:** `/api/health` returns 401. Rotate the exposed `ADMIN_TOKEN`, refresh `config:cache`, confirm the Authorization header reaches PHP, and confirm `index.php` serves the same app instance as Tinker. The budget, category, and recurring APIs cannot be used by Hermes until this passes.
3. Run `php artisan migrate --force` on Hostinger for the new `transaksi_berulang.client_key` column before using the recurring API.
4. Perform production deployment checks: `APP_ENV=production`, `APP_DEBUG=false`, HTTPS `APP_URL`, `SESSION_SECURE_COOKIE=true`, strong `ADMIN_TOKEN` kept only in environment, and `DEMO_MODE=false`.
5. For a fresh production DB with no users present during the role migration, provision the first two accounts as admins. README contains a Tinker example; replace its sample emails with the real account emails.
6. The real 19-row CSV is absent from the repo; import comparison remains pending until the source CSV is available.
7. Coordinate any future keyset-pagination or API method-validation change with Hermes/STB first.

## Useful commands

```bash
herd php artisan test
npm run build
herd php artisan migrate                 # applies new migrations without resetting local data
herd php artisan migrate:fresh --seed    # local reset only; replaces the SQLite demo data
```
