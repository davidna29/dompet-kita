---
version: 1
slug: "beranda"
primary_target: "beranda"
related_targets: []
---

# Surface brief — Dompet Kita

## Scope and mode

Whole product shell and the Beranda as the primary surface: Beranda, Transaksi, form tambah/edit, Ringkasan, Kantong, Anggaran, Berulang, Kategori, Pengaturan. Web, mobile-first (390 px first, 360 px floor), Operate. Online-only; the PWA caches static assets, never data.

## Audience and job

David and Nurul, a married couple recording a household ledger from their phones. They arrive in the evening, often one-handed, to check what the month has cost, pin a new expense, correct a mistake, and see whether a bill or budget needs attention. Hermes, the Telegram agent, is a machine user of the API and never sees this screen.

Success: the month's spending is understood in one glance, Mutasi is never confused with spending, and a transaction is added in seconds. A polished result that feels like a scrapbook, a corkboard, handwritten notes, or a gamified finance toy is wrong. Nothing may obscure amounts, dates, states, or standard controls.

## Direction contract

THESIS: A household money app that keeps a kitchen noticeboard's calm, physical clarity while doing bank-statement arithmetic. It refuses the generic fintech dashboard of interchangeable white cards, one green accent, and a chart mosaic with no point of view.
OWN-WORLD: Warm limewash-plaster canvas (#F3EEDF), flat and unpatterned; paper cards (#FCF9F0) with warm hairlines; deep olive shell (#3D4A2E); tomato (#C4492D) for spending and alerts; ochre (#B7791F) for Mutasi; sage (#4F6B4A) for income; sky (#5F7F8E) for pockets; butter (#E8C36A) for budget bands. Bricolage Grotesque for display, Karla for body, Azeret Mono for tabular figures and label-maker caps. Pins, magnets and clipped tabs are functional markers of current, active, or newly pinned—never scattered decoration.
STORY: The family understands the month before it notices the world, trusts what the total excludes, and pins a new expense without hunting for a form. Mutasi reads as movement between pockets, not as spending.
FIRST VIEWPORT: A 390 px screen. Olive app bar: wordmark, "NOVEMBER" tear-off tab, account magnet. Greeting "Selamat malam, David" with today's date. The pinned month card: small "PENGELUARAN BULAN INI" label; a huge tabular Rp number; "Mutasi tidak dihitung" beside it; masuk/selisih columns under a rule; a six-month bar strip; a magnet pin at the top edge and a month tab at the right. Below: three pocket chips (Tunai, BSI, Shopee), one budget alert with an ochre "hampir habis" flag, then recent transaction slips grouped under a date pin. Bottom nav: Beranda, Transaksi, tomato add magnet in the centre, Ringkasan, Kantong.
FORM: Papan Dapur — the user-pinned grounded direction chosen from the hand (seed key 3593886f). Position: top-ranked grounded direction among seven considered; it beat Buku Rekening on audience identification and held product clarity by keeping standard controls and a strict arithmetic layer.
SIGNATURE MOVE: The magnet-and-slip. A month is a clipped tab; a transaction is a paper slip. On save, the slip lands under its date pin while the month total presses down once, like a fresh sheet pinned to the board. The active nav item holds a magnet dot; the current month carries the only pin on a card. All motion is 180–240 ms, stepped, and disabled under prefers-reduced-motion.
RAISES: From Japanese high-density web—hairline module discipline and compact label caps, so the board stays dense without whitespace inflation. From boarding pass and gate board—a changed or pending row keeps its identity in place with a persistent marker; no disappearing rows. From Labanotation—time is read as a continuous rail, used for date groups and the six-month strip. From Saville catalog—small serial numbers and colour-block category coding on slips. Declined as worlds: Ikeda datamatics (strobing conflicts with money), RW acetate manual (office-manual costume), Saville catalog as a whole (too austere for a family), Labanotation as layout (bottom-up chronology is unusable), Japanese high-density as identity (borrowed discipline only), boarding pass as identity (travel costume).
FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance.

## Constraints

- Bahasa Indonesia; amounts plain integers in storage and Rp-formatted in the UI.
- Touch targets ≥44 px; no horizontal scroll at 360 px; readable without zoom; light and dark modes.
- Every total states its scope; Mutasi separated; pocket balances include Mutasi.
- Loading, empty, network error, save success, delete confirmation states are designed, not left to chance.
- Standard web controls only: buttons, inputs, selects, tabs, dialogs, navigation. The world supplies type, palette, density, and the magnet-and-slip move only.
- Demo data is labelled "Data contoh"; never presented as real family history.

## Unresolved

- Monthly volume (affects pagination depth) remains TBD in the PRD; use simple pagination.
- Real 19-row CSV is absent; importer command and demo seed cover development.
- The production admin token and real account emails are deployment-time inputs, never committed.
