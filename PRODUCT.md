# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

Laravel 13 monolith (UI + API), PHP 8.5 target (minimum 8.3; development machine runs 8.4), MySQL utf8mb4, Blade + Livewire + Tailwind, Chart.js built locally, session auth for the UI and a separate Bearer token for the STB agent API. Deployed to Hostinger shared hosting via SSH; no Node, Redis, queue worker, or persistent process on the server.

## Users

- **David NA** and **Nurul Azizah** — a married couple recording their household finances from their phones. They log expenses through Telegram chat with the Hermes agent, and want to look up, add, correct, and delete records visually without opening a terminal. They are not accountants; the interface must stay legible under everyday mobile conditions.
- **Hermes (STB agent)** — an automated agent running on a set-top box that parses Telegram messages, pushes transactions to the API, pulls updates before scheduled reports, and sends reminders. It is a machine user with write access through a separate admin token.
- David NA and Nurul Azizah are the initial UI account managers. They can add household members; new accounts are regular members and map `pencatat` automatically from the logged-in account.

## Product Purpose

Replace a chat-and-CSV-only household ledger with one consistent source of truth on Hostinger (MySQL + API + UI), while Hermes stays the capture channel and sync agent. Success means: balances and reports always agree between UI and Hermes, no duplicate or lost entries when the same record travels both paths, and the family can add, inspect, correct, and delete transactions from a phone in seconds.

## Positioning

A two-channel family ledger: conversational capture through Hermes with an idempotent transaction API, and a mobile-first web UI over the same rows. The mechanism — `client_key` idempotency, soft deletes visible to the sync client, server-owned `updated_at`, and one shared rule set for Mutasi (internal transfers) — is what a generic expense app cannot copy without rebuilding its data contract.

## Operating Context

- Ledger previously lived in `/root/keuangan-keluarga/transaksi.csv` (19 rows as of 2026-11-02); Hostinger becomes primary, the CSV becomes cache/backup on the STB.
- Scheduled Hermes reports: daily 21:30, weekly Sunday 19:00, monthly on the 1st at 09:00 WIB. UI changes must appear in those reports, so pulls are mandatory before reporting.
- Telegram pushes with `sumber=telegram` and `client_key=TELEGRAM-<chatid>-<msgid>`; UI writes with `sumber=ui`; deletion is always soft.
- All dates and timestamps in Asia/Jakarta. Amounts are plain integer rupiah digits in storage; UI formats them as Rp35.000.
- Initial pockets (kantong): Tunai, BSI, Shopee. A transfer is `jenis=keluar`, `kategori=Mutasi`, with `metode` = origin and `kantong_tujuan` = destination.
- Reports and UI totals for spending exclude Mutasi; pocket balances include Mutasi. Both must be labelled clearly.
- The app is used on phones (design for ~360 px first) and must remain usable on a normal mobile connection.

## Capabilities and Constraints

**Must (built now):** login with rate limiting (5 attempts/minute/IP), admin-managed household accounts, transaction list with filters, create/edit/soft-delete, period summary, Bearer-token API for the STB, CSV import path, last-write-wins conflict handling, server-owned timestamps.

**Should (built now):** CSV export, dashboard with charts, category budgets, recurring transactions and bill reminders confirmed by the user, category icons/colors/order and type-aware income suggestions, change history, managed/archived pockets with safe rename and pocket types (harian/tabungan/investasi), pocket balances, debts/receivables (FR-023), PWA install + Olive/Pink Linen light and dark themes, on-demand pull support.

**Hard data rules:** `jenis` only `masuk`/`keluar`; Mutasi is `keluar` + `kategori=Mutasi`; spending and income totals exclude Mutasi, Hutang, and Piutang while pocket balances include them; `jumlah` is a positive integer; category is free text (max 50 chars, normalized trim + first letter capital); soft delete only; `client_key` unique and idempotent; `updated_at` set by the server; pocket rename updates historical references transactionally; archive only zero-balance pockets with no active schedules; new members are not admins; no new `jenis` values, no formatted amount strings, no permanent deletion from the UI, no offline-first UI.

**API contract:** `GET /api/health`, `GET /api/transaksi?since=&limit=&offset=` including soft-deleted rows, `POST /api/transaksi` idempotent on `client_key`, `PUT/DELETE /api/transaksi/{id}`. JSON responses, errors as HTTP status + `{error: "pesan singkat Bahasa Indonesia"}`. `since`/`server_time` are `YYYY-MM-DDTHH:MM:SS` in Asia/Jakarta without offset. List envelope `{data, next_offset, server_time}`. POST/PUT/DELETE return the full row; repeated POST returns the existing row with 200. Stage 2 adds `GET /api/kantong`, `GET /api/kategori`, `GET/POST /api/anggaran`, `DELETE /api/anggaran/{id}`, `POST /api/anggaran/salin`, `GET/POST /api/pengingat`, `PUT/DELETE /api/pengingat/{id}`, `POST /api/pengingat/{id}/konfirmasi`. Anggaran write is an idempotent upsert per period + category; `Mutasi` is rejected. Recurring schedules use a nullable unique `client_key` for idempotent creation, keep it immutable on update, and use soft delete.

**Deployment constraints:** Hostinger shared hosting with SSH, no persistent processes or websockets, `queue=sync` and database/file cache, assets built off-server.

**Undecided / deferred:** receipt photo upload (won't), savings goals, calendar, free-text search, tags, PDF/Excel reports. Open question: monthly transaction volume, which would affect pagination depth.

## Brand Commitments

Bahasa Indonesia throughout the interface. Family-scale, calm, trustworthy tone; no corporate-banking coldness and no playful gamification of money. Amounts always read as Indonesian rupiah. The product is named **Dompet Kita** ("our wallet"); the interface uses that name and a ledger-board mark. The visual system is recorded in DESIGN.md, not here.

## Evidence on Hand

- `docs/prd-ok.md` — approved PRD v0.3 with data schema, sync contract, value rules, and Stage 1/2 scope.
- The real 19-row CSV is **not** in the repository. Development uses clearly labelled realistic demo data plus an importer command for the real file; never present demo figures as real family history.

## Product Principles

1. **One row, two doors.** Any transaction must survive the Telegram→API→UI→CSV round trip without duplication or loss.
2. **The rule set outranks convenience.** Mutasi never pollutes spending totals; soft deletes stay visible to sync; amounts stay plain integers.
3. **Thumb-first, glance-second.** The most frequent jobs (add, check, correct) must work one-handed on a phone in a few taps.
4. **Trust is legible.** Every total states what it includes; every change names who made it and when.
5. **Boring infrastructure, careful data.** Shared hosting with no daemons still gets idempotency, backups, and server-owned timestamps.

## Accessibility & Inclusion

Mobile-first at ~360 px, no horizontal scrolling, touch targets at least ~44 px, readable text without zoom, sufficient contrast in light and dark modes, explicit loading/empty/error/success states, and Indonesian labels that avoid unexplained financial jargon.
