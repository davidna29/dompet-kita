---
name: Dompet Kita
description: Papan keuangan keluarga yang hangat, jelas, dan mobile-first.
colors:
  canvas: "#F2EDDE"
  paper: "#FBF8EF"
  paper-raised: "#FFFDF6"
  ink: "#232A1F"
  muted: "#5F6554"
  line: "#D9D0BB"
  line-strong: "#C4B89C"
  olive: "#3D4A2E"
  olive-deep: "#2C3621"
  olive-soft: "#E4E7D2"
  primary-ink: "#FBF8EF"
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
  shell-muted: "rgba(251, 248, 239, 0.64)"
  shell-line: "rgba(251, 248, 239, 0.16)"
  shell-soft: "rgba(251, 248, 239, 0.1)"
  shell-active: "rgba(251, 248, 239, 0.16)"
  action: "#A83B24"
  action-ink: "#FBF8EF"
  action-soft: "#F2DED6"
  rose-canvas: "#F8EEF1"
  rose-paper: "#FFF9FA"
  rose-paper-raised: "#FFFCFD"
  rose-ink: "#32212A"
  rose-muted: "#6F5862"
  rose-line: "#E7D5DC"
  rose-line-strong: "#CDB4BE"
  rose-primary: "#8F3E5F"
  rose-deep: "#642B43"
  rose-soft: "#F1DDE5"
  rose-action: "#A23F67"
  rose-action-ink: "#FFF9FA"
  rose-action-soft: "#F4DFE8"
  rose-dark-canvas: "#191217"
  rose-dark-paper: "#241920"
  rose-dark-paper-raised: "#2D2028"
  rose-dark-ink: "#F6EAF0"
  rose-dark-muted: "#C4ACB7"
  rose-dark-line: "#47313D"
  rose-dark-line-strong: "#654753"
  rose-dark-primary: "#E3A3BC"
  rose-dark-deep: "#472232"
  rose-dark-soft: "#442737"
  rose-dark-action: "#F0A0BE"
  rose-dark-action-ink: "#2C1420"
  rose-dark-action-soft: "#4A2939"
typography:
  display:
    fontFamily: "Bricolage Grotesque Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "clamp(1.65rem, 4.2vw, 2.35rem)"
    fontWeight: 600
    lineHeight: 1.05
    letterSpacing: "-0.03em"
  headline:
    fontFamily: "Bricolage Grotesque Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 600
  title:
    fontFamily: "Bricolage Grotesque Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 600
  body:
    fontFamily: "Karla Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
  amount:
    fontFamily: "Karla Variable, ui-sans-serif, system-ui, sans-serif"
    fontWeight: 700
    letterSpacing: "-0.025em"
    fontFeature: "tnum"
  label:
    fontFamily: "Azeret Mono Variable, ui-monospace, monospace"
    fontSize: "0.625rem"
    fontWeight: 500
    letterSpacing: "0.14em"
  micro:
    fontFamily: "Azeret Mono Variable, ui-monospace, monospace"
    fontSize: "0.5625rem"
    fontWeight: 500
    letterSpacing: "0.1em"
  caption:
    fontFamily: "Karla Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.6875rem"
    fontWeight: 400
rounded:
  xs: "4px"
  sm: "8px"
  lg: "12px"
  card: "14px"
  xl: "16px"
  pill: "999px"
spacing:
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
    textColor: "{colors.paper}"
    rounded: "{rounded.lg}"
    padding: "8px 16px"
    height: "44px"
  button-primary-hover:
    backgroundColor: "{colors.olive-deep}"
  button-add:
    backgroundColor: "{colors.action}"
    textColor: "{colors.action-ink}"
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
    backgroundColor: "{colors.paper-raised}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
    padding: "4px 10px"
    height: "44px"
  input:
    backgroundColor: "{colors.paper-raised}"
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
    backgroundColor: "{colors.paper-raised}"
    textColor: "{colors.ink}"
    rounded: "{rounded.card}"
    padding: "20px 24px"
  palette-choice:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.ink}"
    rounded: "{rounded.lg}"
    padding: "0 12px"
    height: "44px"
---

# Design System: Dompet Kita

## Overview

**Creative North Star: "Papan yang Diteken"**

Dompet Kita is a household ledger with the calm clarity of a kitchen noticeboard and the exactness of a bank statement. Its default Olive palette uses warm limewash, cream paper, and an olive shell; the optional Pink Linen palette offers a blush-and-berry atmosphere without changing the product's familiar controls or arithmetic-first hierarchy.

The Beranda puts one raised month sheet first, followed by pockets, budget signals, and date-grouped transaction slips. Bricolage Grotesque gives headings a human voice; Karla keeps everyday copy and rupiah amounts open and legible; Azeret Mono labels dates and metadata. Pins, tabs, and magnets communicate current or active state only.

Both palettes have deliberately composed light and dark modes. On small screens the product uses a compact themed header and fixed five-slot bottom navigation; from the desktop breakpoint it becomes a fixed themed sidebar beside a centered content area. Palette and brightness preferences are independent and belong to the browser, not transaction data.

**Key Characteristics:**
- Olive is the default palette; Pink Linen is an optional blush-and-berry theme with its own dark mode.
- Tomato signals spending and destructive states; ochre marks Mutasi and budget warnings; sage marks income; sky identifies pockets. The add action follows the selected palette.
- Tabular Karla figures, Bricolage Grotesque headings, and Azeret Mono metadata.
- Month sheet and transaction slips are functional signature patterns, not decoration.
- Controls and navigation retain at least 44 px touch height; the mobile layout is designed down to 360 px.

## Colors

Two coordinated palettes share one semantic ledger: Olive remains the default; Pink Linen gives the user a warm blush canvas, cream paper, plum ink, and a confident rose action colour.

### Primary
- **Pantry olive** — the default primary action and active navigation colour; deep olive grounds its shell while olive tint marks selection.
- **Pink Linen** — an optional pink palette with a soft blush canvas, berry shell, rose primary action, and a darker plum foundation at night.
- **Action colour** — follows the active palette for Add, the logo mark, focus, and current-state magnets.
- **Tomato** — reserved for spending, destructive emphasis, and error states in both palettes.

### Secondary
- **Movement ochre** — Mutasi, near-limit budgets, and their labels; it must never be mistaken for spending.
- **Income sage** — income totals and successful or safe states.
- **Pocket sky** — pocket identity and informational category accents.
- **Budget butter** — the demo-data disclosure and small budget highlights.

### Neutral
- **Limewash canvas** — the warm, unpatterned page ground; paper and raised paper separate ordinary data from the month sheet.
- **Olive ink and quiet ink** — primary and secondary text, with muted copy remaining comfortably readable.
- **Warm hairlines** — borders, separators, and table rules; the stronger hairline is reserved for clearer boundaries.
- **Shell ink and translucent shell tints** — sidebar/header text, separators, hover fills, and the active navigation surface.

Each palette has explicit dark surfaces and foregrounds rather than a mechanical inversion. The semantic colors remain recognizable across both palettes; only the brand, shell, selection, and action roles change with the user's choice.

### Named Rules
**The Mutasi Colour Rule.** Ochre is the movement colour. Spending is tomato; income is sage. Never use spending colour alone to represent Mutasi.

**The Stable Ledger Colour Rule.** Palette choice changes brand atmosphere and action colour, never the established meanings of spending, Mutasi, income, or pockets.

**The One Pin Rule.** A pin or magnet marks one current or active thing. The current month may carry the month pin; active navigation gets its marker. Do not scatter pins as ornament.

**The Honest Total Rule.** State each total's scope in text: spending excludes Mutasi, while pocket balances include it. Colour alone cannot carry that distinction.

## Typography

**Display Font:** Bricolage Grotesque Variable (with ui-sans-serif fallback)
**Body Font:** Karla Variable (with ui-sans-serif fallback)
**Label/Mono Font:** Azeret Mono Variable (with ui-monospace fallback)

**Character:** Bricolage brings a confident, human edge to headings; Karla is plainspoken and generous for everyday reading and figures; Azeret Mono provides a compact label-maker voice for metadata. All three variable fonts are bundled locally.

### Hierarchy
- **Display** (600, `clamp(1.65rem, 4.2vw, 2.35rem)`, 1.05): page titles, with the larger login statement treated as a one-off responsive display size.
- **Headline** (600, 1.125rem): month-sheet and section headings.
- **Title** (600, 1rem): compact card and row headings.
- **Body** (400, 0.875rem): descriptions, notes, and standard form copy; relaxed leading is used for longer text.
- **Amount** (700, contextual size, tabular figures, -0.025em): rupiah values, usually right-aligned in transaction slips and summaries.
- **Label** (500, 0.625rem, 0.14em, uppercase): field labels, dates, and compact metadata. Mono is not a prose face.
- **Micro** (500, 0.5625rem, 0.1em, uppercase): compact status tags and tiny serial labels.
- **Caption** (400, 0.6875rem): helper text and secondary metadata.

### Named Rules
**The Figures Are Content Rule.** Set amounts in tabular Karla, not mono. Reserve mono for labels, keys, and printed dates.

## Layout

The app is mobile-first with a 360 px floor. Below 1024 px, a sticky themed 64 px header carries the wordmark, brightness control, and account menu; demo mode adds a narrow disclosure above it. A fixed five-slot bottom bar contains Beranda, Transaksi, the raised palette-coloured Tambah magnet, Ringkasan, and Kantong. Page content uses 16 px side insets on phones, 24 px from the small breakpoint, and a 1180 px maximum content width.

At 1024 px and wider, the mobile header and bottom bar give way to a fixed 256 px themed sidebar. The content area uses up to 40 px page insets. The Beranda pairs the raised month sheet with a narrower pocket/budget rail; data-heavy pages use one- or two-column layouts according to the task. Spacing is drawn from a compact 4 / 8 / 12 / 16 / 20 / 24 / 40 px rhythm. Avoid fixed-width data columns that can force horizontal scrolling.

The month sheet presents the spending total, its Mutasi scope note, a three-value ledger strip, and six months of trend. Pocket and budget summaries sit beside or immediately below it. Transaction rows are grouped by date, with a narrow date rail, category marker, descriptive copy, and a right-aligned amount.

## Elevation & Depth

Depth is mostly tonal. Ordinary paper cards use a warm hairline without a shadow; the month sheet and the login sample receive a soft ambient lift. Pins and the add magnet use a small shadow to read as controls. Toasts and confirmation dialogs sit above the page with stronger separation. Dark mode deepens surfaces rather than adding glow.

### Shadow Vocabulary
- **Raised sheet** (`0 1px 0 rgba(64, 54, 35, 0.05), 0 14px 28px -22px rgba(48, 42, 27, 0.5)`): month sheet and the login sample.
- **Pin** (`0 1px 2px rgba(42, 35, 20, 0.2), 0 5px 10px -6px rgba(42, 35, 20, 0.28)`): the month pin.
- **Add magnet:** a palette- and mode-aware soft shadow from `--shadow-action` keeps the floating control grounded without a red cast in Pink Linen.
- **Confirmation dialog**: the delete dialog uses the standard deep modal shadow over a dimmed scrim.

### Named Rules
**The Flat-By-Default Rule.** Ordinary data surfaces remain flat. Elevation belongs to the month sheet, functional magnets, toasts, and overlays—not every card.

## Shapes

Use softly cut-paper geometry: 14 px cards, 12 px buttons and fields, 8 px chips, and full circles only for compact markers or the circular add control. Month tabs have a squared lower edge and rounded top corners. Transaction slips use dotted separators; larger sections use solid warm hairlines. Keep surfaces flat and legible—no torn edges, fake tape, or cork texture.

## Components

### Buttons
- **Shape:** rounded 12 px corners, bold Karla, and a 44 px minimum height.
- **Primary:** selected palette's primary fill with contrasting text; dark mode uses a bright brand fill and palette-specific dark ink.
- **Add:** selected palette's action colour with a verified foreground in both modes. The persistent mobile add action is a 56 px circle raised above the bottom bar.
- **Ghost / soft / danger:** transparent hairline, olive tint, or tomato tint respectively. Focus-visible uses a 2 px action-colour outline with a 3 px offset. Press feedback is a restrained 0.985 scale; no hover lift.

### Chips
- **Style:** raised-paper fill, warm hairline, 8 px corners, compact semibold Karla, and 44 px minimum height.
- **State:** selected chips use an olive tint and olive border; Mutasi chips use ochre. Keep labels explicit and readable without relying on colour alone.

### Cards / Containers
- **Corner Style:** 14 px.
- **Background:** paper for ordinary data and the login sample; raised paper for the month sheet.
- **Shadow Strategy:** flat by default; use the raised-sheet treatment only for defined hierarchy.
- **Border:** one warm hairline on ordinary cards; avoid coloured side rails.
- **Internal Padding:** commonly 16 px on mobile and 20–24 px where a panel needs more breathing room.

### Inputs / Fields
- **Style:** raised-paper background, warm hairline, 12 px corners, 44 px minimum height, and Azeret Mono uppercase labels above.
- **Focus:** olive border and a soft olive ring; keyboard focus remains visibly outlined in tomato.
- **Error / Disabled:** place tomato validation copy directly below its field; disabled buttons remain legible at reduced opacity.

### Navigation
- **Mobile:** sticky themed header; fixed paper bottom bar with five slots, 48 px minimum links, a palette-coloured active dot, and a raised 56 px add magnet. Preserve safe-area padding.
- **Desktop:** fixed 256 px themed sidebar with 44 px rows, muted shell text, a translucent active fill, and a palette-coloured magnet on the active row. Primary destinations and management links are grouped separately.
- **Account and theme:** use familiar buttons and menus in the shell; keep the theme toggle reachable at both mobile and desktop sizes.

### Palette Selection
Settings offers Olive and Pink Linen as native radio choices with visible swatches and a selected state. Changing the palette updates the shell, selection, action, and browser theme color immediately; it does not alter the light/dark preference or any ledger data.

### Signature: Month Sheet and Transaction Slips
The month sheet is the single raised paper surface: a functional pin at its top edge, a month tab, a large tabular spending amount, explicit scope note, ledger strip, and six-month trend. Transaction slips stay in date groups; category icons use their configured tint and amounts align to the trailing edge. Mutasi shows its source-to-destination movement in ochre. A newly saved slip lands once while the month total presses once; both settle within the committed motion range and collapse under `prefers-reduced-motion`.

### Motion
Motion follows the task, not the page. Immediate control feedback uses the fast token (180 ms); selected, expanded, and loading states use the state token (200 ms); the transaction slip, month total, chart update, and toast arrival use the settle token (240 ms). Entering states use confident ease-out; exits are shorter. Interactive controls receive color/border feedback and a slight press response, while ordinary cards and static page content remain still. No looping effects or scroll-reveal sequences.

Under `prefers-reduced-motion`, authored movement and keyframes collapse to near-instant changes. Color and opacity still confirm selection, loading, success, and dismissal; state never depends on motion.

## Do's and Don'ts

### Do:
- **Do** keep the month sheet, transaction slips, and pocket summaries central to the visual hierarchy.
- **Do** keep spending tomato, Mutasi ochre, income sage, and pockets sky in every palette.
- **Do** let the selected palette own brand shell, primary controls, logo mark, focus, and active magnets.
- **Do** keep the palette preference local to the browser and independent from light/dark mode.
- **Do** label the scope beside every total: spending excludes Mutasi; pocket balances include it.
- **Do** preserve at least 44 px control heights, phone insets, and the 360 px layout floor.
- **Do** use one pin or magnet for the current or active state and keep it functional.
- **Do** use tabular Karla for rupiah figures and Azeret Mono for labels and metadata.
- **Do** disclose synthetic data as “Data contoh” wherever demo mode is active.
- **Do** provide visible loading, empty, error, success, and confirmation states in the same visual language.

### Don't:
- **Don't** turn the board into scrapbook: no handwriting, cork, torn paper, fake tape, sticker packs, or decorative pins.
- **Don't** flatten the palette into a grey screen with one green accent; the semantic magnet colours matter.
- **Don't** put a pin or shadow on every card.
- **Don't** use mono for prose, gradients, glass effects, glowing edges, or hard offset shadows.
- **Don't** put cards inside cards or use coloured border-left blocks as the main hierarchy.
- **Don't** make a heading depend on an eyebrow label to communicate its meaning.
- **Don't** let responsive tables or controls create horizontal scrolling at the phone floor.
