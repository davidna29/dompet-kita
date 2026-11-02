---
name: Dompet Kita
description: Papan keuangan keluarga mobile-first — catat, lihat, dan koreksi uang rumah dari HP.
colors:
  limewash: "#F2EDDE"
  paper: "#FBF8EF"
  raised-paper: "#FFFDF6"
  ink: "#232A1F"
  muted-ink: "#5F6554"
  hairline: "#D9D0BB"
  hairline-strong: "#C4B89C"
  olive: "#3D4A2E"
  olive-deep: "#2C3621"
  olive-soft: "#E4E7D2"
  tomato: "#A83B24"
  tomato-soft: "#F2DED6"
  ochre: "#87570F"
  ochre-soft: "#F1E3C5"
  sage: "#4F6B4A"
  sage-soft: "#DFE8D5"
  sky: "#496976"
  sky-soft: "#DFE8EA"
  butter: "#E8C36A"
  butter-soft: "#F5EAD0"
  shell-ink: "#FBF8EF"
typography:
  display:
    fontFamily: "Bricolage Grotesque Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "clamp(2rem, 5vw, 2.6rem)"
    fontWeight: 600
    lineHeight: 1
    letterSpacing: "-0.035em"
  headline:
    fontFamily: "Bricolage Grotesque Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: "-0.02em"
  title:
    fontFamily: "Bricolage Grotesque Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 600
    lineHeight: 1.25
  body:
    fontFamily: "Karla Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.6
  amount:
    fontFamily: "Karla Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 700
    lineHeight: 1.1
    letterSpacing: "-0.025em"
    fontFeature: "tnum"
  label:
    fontFamily: "Azeret Mono Variable, ui-monospace, monospace"
    fontSize: "0.625rem"
    fontWeight: 500
    lineHeight: 1.2
    letterSpacing: "0.14em"
  micro:
    fontFamily: "Azeret Mono Variable, ui-monospace, monospace"
    fontSize: "0.5625rem"
    fontWeight: 500
    lineHeight: 1.2
    letterSpacing: "0.1em"
  caption:
    fontFamily: "Karla Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.6875rem"
    fontWeight: 400
    lineHeight: 1.45
  brand:
    fontFamily: "Bricolage Grotesque Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.1875rem"
    fontWeight: 600
    lineHeight: 1
    letterSpacing: "-0.03em"
rounded:
  xs: "4px"
  sm: "8px"
  md: "10px"
  lg: "12px"
  card: "14px"
  xl: "16px"
  pill: "999px"
spacing:
  inset: "16px"
  xs: "4px"
  sm: "8px"
  md: "12px"
  lg: "16px"
  xl: "20px"
  section: "24px"
  page: "40px"
components:
  button-primary:
    backgroundColor: "{colors.olive}"
    textColor: "{colors.shell-ink}"
    rounded: "{rounded.lg}"
    padding: "8px 16px"
    height: "44px"
  button-primary-hover:
    backgroundColor: "{colors.olive-deep}"
    textColor: "{colors.shell-ink}"
  button-add:
    backgroundColor: "{colors.tomato}"
    textColor: "{colors.shell-ink}"
    rounded: "{rounded.lg}"
    padding: "8px 16px"
    height: "44px"
  button-ghost:
    backgroundColor: "transparent"
    textColor: "{colors.ink}"
    rounded: "{rounded.lg}"
    padding: "8px 16px"
    height: "44px"
  chip:
    backgroundColor: "{colors.raised-paper}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
    padding: "4px 10px"
    height: "44px"
  input:
    backgroundColor: "{colors.raised-paper}"
    textColor: "{colors.ink}"
    rounded: "{rounded.lg}"
    padding: "10px 14px"
    height: "44px"
  paper:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.ink}"
    rounded: "{rounded.card}"
    padding: "16px 20px"
  paper-raised:
    backgroundColor: "{colors.raised-paper}"
    textColor: "{colors.ink}"
    rounded: "{rounded.card}"
    padding: "20px 24px"
---

# Design System: Dompet Kita

## Overview

**Creative North Star: "Papan yang Diteken"**

Dompet Kita is a family ledger that behaves like a kitchen noticeboard: calm enough to live on a phone at the end of the day, precise enough that the bank statement arithmetic is never in doubt. The world is warm limewash plaster, cream paper slips, deep olive shell, and a small set of magnet colours doing real work. It is a working product first; the board is the material, not the metaphor overlaying the work.

The interface is built around one sheet — the month card — and the slips that hang under it. Pins and magnets mark only current, active, or newly pinned states, never decoration. The arithmetic lives in the type: amounts are set in a tabular, tightly tracked body face, while tiny monospaced caps act like printed labels on a household file. Mutasi is ochre and reads as movement; spending is tomato and reads as an alert; income is sage; pockets are sky.

The system is deliberately flat: warm hairlines and tonal paper do most of the hierarchy work, with one raised sheet reserved for the month board and the login sample. It is mobile-first at 360–390 px, expands to a sidebar at desktop, and never trades a familiar control for a costume.

**Key Characteristics:**
- Warm limewash ground, cream paper surfaces, deep olive shell.
- Tomato for spending and action, ochre for Mutasi, sage for income, sky for pockets.
- Pins and magnets are functional markers, not ornament.
- Tabular Karla amounts; Bricolage Grotesque headings; Azeret Mono labels.
- 44 px minimum targets, no horizontal scroll, visible states for loading, empty, error, success, and delete.
- Standard web controls in a committed visual world.

## Colors

The palette is a household stationery set: olive and limewash ground the room, paper keeps data legible, and three magnet colours carry meaning.

### Primary
- **Olive shell** — the sidebar, top bar, primary actions, and focus. It is the room, not an accent.
- **Deep olive** — the quiet counterweight for hover and shell depth.
- **Olive tint** — active navigation backgrounds, selected chips, and low-priority fills.
- **Tomato** — spending, destructive actions, alerts, and the add magnet. It is never used for income or Mutasi.

### Secondary
- **Ochre** — Mutasi, budget warnings, and "pindah kantong" markers. If a value moves between pockets, it is ochre.
- **Sage** — income, "aman" states, and successful confirmation.
- **Sky** — pocket identity, informational tags, and the BSI pocket.
- **Butter** — the demo disclosure, budget tape, and small highlights.

### Neutral
- **Limewash** — page canvas; flat, unpatterned, warm.
- **Paper** — ordinary data surfaces.
- **Raised paper** — the month board and the login sample only.
- **Ink** — body and headings.
- **Muted ink** — secondary copy; still at least 4.5:1 on canvas and paper.
- **Hairline / hairline strong** — borders, dividers, and table rules.

### Named Rules
**The Mutasi Colour Rule.** Ochre is reserved for movement between pockets, budget warnings, and Mutasi labels. Spending is tomato; income is sage. Never let Mutasi wear the spending colour alone.

**The One Pin Rule.** A pin or magnet marks one current, active, or newly pinned thing. The current month gets the pin; the active nav item gets the magnet. A second pin is a bug.

**The Honest Total Rule.** Every total states its scope. Belanja excludes Mutasi; saldo kantong includes it. Colour alone is not allowed to carry that distinction.

## Typography

**Display Font:** Bricolage Grotesque Variable (with ui-sans-serif fallback)
**Body Font:** Karla Variable (with ui-sans-serif fallback)
**Label/Mono Font:** Azeret Mono Variable (with ui-monospace fallback)

**Character:** Bricolage gives the board a human, slightly wonky confidence; Karla keeps the household copy plain and generous; Azeret Mono is the label-maker voice for metadata and printed numbers. Amounts are not mono — they are set in tabular Karla so the eye can compare rupiah values without the mechanical spacing of a code face.

### Hierarchy
- **Display** (600, clamp 2rem–2.6rem, 1.0): page titles and the login statement; tracking never tighter than -0.04em.
- **Headline** (600, 1.125rem, 1.2): month card, section heads, and sheet titles.
- **Title** (600, 1rem, 1.25): card labels and row emphasis.
- **Body** (400, 0.875rem, 1.6): descriptions, notes, and form labels; measure kept under ~72ch.
- **Amount** (700, tabular, -0.025em): rupiah values at every size, from 11px row values to the 64px month total.
- **Label** (500, 0.625rem, 0.14em, uppercase): field labels, metadata, serial keys, and tags. Mono never sets prose.
- **Micro** (500, 0.5625rem, 0.1em, uppercase): tiny status tags and serial metadata.
- **Caption** (400, 0.6875rem, 1.45): helper text and secondary metadata.
- **Brand** (600, 1.1875rem, -0.03em): the wordmark in the shell.

### Named Rules
**The Figures Are Content Rule.** Amounts use tabular Karla, not mono. Mono is for labels, keys, and printed dates; it is a measuring voice, not a costume.

## Layout

The first build is a phone screen at 390 px, with a 360 px floor. A sticky olive app bar carries the wordmark, theme toggle, and account menu; a persistent 22 px demo strip sits above it when synthetic data is active. The bottom navigation is five slots — Beranda, Transaksi, a raised tomato Tambah magnet, Ringkasan, Kantong — with at least 48 px of vertical reach and the center action at 56–64 px. The page content is full-width with 16 px inset; no horizontal scroll, no dense tables.

From 1024 px, a 256 px olive sidebar replaces the top bar and bottom navigation, while the content expands inside a 1180 px maximum with 40 px page padding. The sidebar keeps the same five primary jobs plus Kelola: Anggaran, Berulang, Kategori, Pengaturan. The month board and its supporting rail sit in a 1.45 / 0.55 split; other pages use one or two columns depending on the job. Spacing follows 4 / 8 / 12 / 16 / 24 / 32 / 40; headings get more space above than below.

The month card is the first viewport's thesis: a large tabular pengeluaran figure, an honest "Mutasi tidak dihitung" note, a three-part ledger strip, and six months of bars, pinned with one magnet and tabbed with the month. Recent slips follow. Nothing is trapped inside a generic dashboard grid.

## Elevation & Depth

Depth is tonal and paper-like. Ordinary surfaces are flat paper with a warm hairline; the month board and login sample are the only raised sheets, using a soft, offset ambient shadow. Modals float on a dim ground with a deeper soft shadow. There are no hard offset shadows, no glowing edges, and no shadow on every card.

### Shadow Vocabulary
- **Pinned sheet** (`0 1px 0 rgba(64,54,35,.05), 0 14px 28px -22px rgba(48,42,27,.5)`): the month board and the login sample.
- **Magnet** (`0 1px 2px rgba(42,35,20,.2), 0 5px 10px -6px rgba(42,35,20,.28)`): the pin dot and the add magnet.
- **Modal** (`shadow-2xl` in Tailwind): the delete and confirmation dialogs.

### Named Rules
**The Flat-By-Default Rule.** A sheet is flat unless it is the month board or a modal. If everything is elevated, nothing is.

## Shapes

The board is made of cut paper: 14 px corners for cards, 12 px for buttons and inputs, 8 px for chips and small tags, and full pills only for tiny status dots and the add magnet. The month tab is the one squared-top silhouette: a small tear-off tab, not a ticket stub. Dividers between slips are dotted; larger section rules are solid hairlines. Pins are perfect small circles. There is no torn-paper clip path, no fake tape, no corkboard texture, and no nested cards.

## Components

### Buttons
- **Shape:** 12 px radius, 44 px minimum height, bold 14 px Karla, 16 px horizontal padding.
- **Primary:** olive on shell ink. Hover deepens to olive-deep; focus is a 2 px tomato outline with 3 px offset.
- **Add:** tomato with shell ink in light mode; dark ink on tomato in dark mode. The add magnet is a 56 px circle with the same rules.
- **Ghost / soft / danger:** hairline outline, olive tint, or tomato tint respectively. No button is a pill except the circular add.

### Chips
- **Style:** raised paper, hairline border, 8 px radius, 44 px minimum height, 12 px semibold Karla. Selected chips use olive tint and olive-deep text, with an olive border.
- **State:** selected, unselected, filter, action, and Mutasi (ochre tint) variants. Chips never carry a gradient.

### Cards / Containers
- **Corner Style:** 14 px radius.
- **Background:** paper for data; raised paper only for the month board and login sample.
- **Shadow Strategy:** flat except the pinned sheet and modal.
- **Border:** 1 px warm hairline; no colored left/right border above 1 px.
- **Internal Padding:** 16 px on mobile, 20–24 px on desktop.

### Inputs / Fields
- **Style:** 12 px radius, raised paper, warm hairline, 44 px minimum height. Labels are mono caps above the field.
- **Focus:** olive border plus a soft olive ring; focus-visible stays at 2 px tomato outline.
- **Error:** tomato message below the field; disabled controls remain legible at 55% opacity.

### Navigation
- **Sidebar:** deep olive, 44 px rows, 12 px radius, shell-ink text at 70% by default, full shell ink with a shell-tint active fill. One tomato magnet on the active row in mobile navigation.
- **Bottom bar:** paper ground, hairline top, five slots, 10 px labels, red active dot, tomato add magnet raised above the bar. Active labels use olive; inactive muted ink.
- **Top bar:** deep olive, wordmark, theme, account magnet; the demo strip above it uses butter tint and ochre text.

### Signature: Month Board and Slips
The month board is a raised sheet with one magnet at its top edge and a month tab at its right. It carries the month's pengeluaran in tabular Karla, the honest scope note, the three-part ledger strip, and six months of solid bars. Transaction slips are rows with a date pin column, a category icon tinted from `kategori_meta`, a short note, and a right-aligned tabular amount. Mutasi slips show `asal → tujuan` in ochre and use an ochre amount; the tag says "Pindah kantong". New or corrected rows can arrive with a short slip-in motion; the month total presses down once. No motion runs under `prefers-reduced-motion`.

## Do's and Don'ts

### Do:
- **Do** keep the month card, slips, and pockets as the primary composition; the board is the product.
- **Do** use tomato for spending/action, ochre for Mutasi/warning, sage for income, sky for pockets.
- **Do** state scope next to every total; "Mutasi tidak dihitung" belongs on the month card and summary.
- **Do** keep 44 px targets, 16 px page inset, and the 360 px floor.
- **Do** use one pin or magnet for the current/active thing.
- **Do** use tabular Karla for amounts and Azeret Mono only for labels, keys, and printed dates.
- **Do** label synthetic data with the persistent "Data contoh" strip.
- **Do** show the actual changed fields in history; "Diubah" is not enough.
- **Do** use the designed delete/confirm modal, not the browser confirm.

### Don't:
- **Don't** turn the board into scrapbook: no handwriting, cork, torn paper, fake tape, sticker packs, or decorative pins.
- **Don't** use a grey screen with one green accent; the magnet colours are part of the system.
- **Don't** put a pin on every card or a shadow under every surface.
- **Don't** use mono for prose or as a "technical" costume.
- **Don't** put cards inside cards or use colored border-left blocks.
- **Don't** use gradient text, glass, glowing edges, or hard offset shadows.
- **Don't** use page-number pagination; keep previous/next with a plain Indonesian count.
- **Don't** present synthetic rupiah as real family history.
- **Don't** add offline data storage; the PWA caches static assets only.
- **Don't** let a heading depend on an eyebrow label; the heading carries its own weight.
