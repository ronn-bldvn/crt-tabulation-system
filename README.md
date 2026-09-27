# Pageant Tabulation System

A Laravel app for running a pageant (or similar judged competition): configure
segments and weighted judging criteria, assign judges who score contestants from
their own device, and print official results, per-judge scoresheets, and
certificates.

## Features

- **Events** — draft/active/closed status, choose between two ranking methods:
  - *Weighted points* — overall score = segment-weighted average of normalized scores
  - *Average of ranks* — classic pageant method, overall = weighted average of each contestant's rank per segment
- **Segments & criteria** — unlimited segments per event (e.g. Swimwear, Evening Gown, Q&A), each with its own weight (% of overall) and its own set of criteria (each with a weight % and a max raw score). The math auto-normalizes even if weights don't sum to exactly 100.
- **Contestants** — number, name, "representing", photo, active/inactive (for DQs/withdrawals).
- **Judges** — admin creates judge login accounts and assigns them to specific events. Each judge scores from their own device/browser (phone, tablet, laptop) — no shared terminal needed.
- **Live scoring** — judges pick a segment, expand a contestant, enter a score per criterion (validated against each criterion's max), save. Admin can lock scoring event-wide or per-segment at any time (e.g. once a segment is done) to prevent further edits.
- **Live results** — real-time computed rankings, segment-by-segment breakdown, per-judge score detail, and a completeness warning if not everyone has scored yet.
- **Printing (PDF, via dompdf)**:
  - Official results / winners sheet, with signature lines
  - Full segment-by-segment breakdown (all judges, all criteria)
  - Individual judge scoresheet (blank or filled-in, per judge)
  - Certificates (per contestant, auto-fills placement e.g. "1st Place / Winner")

## Tech stack

Laravel 12, Blade + Tailwind CSS v4 (via Vite), MySQL, `barryvdh/laravel-dompdf` for PDFs. No JavaScript framework required — the scoring UI uses native `<details>` accordions, so it works reliably on basic tablet browsers.


1. **Judges** page → create a login for each judge (a temporary password is
   shown once — send it to them).
2. **New Event** → set the ranking method.
3. On the event page: add **segments** (with overall weight %), add
   **criteria** to each segment (with weight % and max score), add
   **contestants**, and **assign judges** to the event.
4. Hand judges their login — they open the app on their own phone/tablet,
   go to *My Events*, and score each contestant per segment.
5. Watch **Results** update live. Use **Lock Scoring** once judging is done,
   then print the official results, breakdown, scoresheets, and certificates.

## Notes on the math

For each judge, a segment score is the weighted sum of `(raw score ÷
criterion max) × criterion weight`, normalized to a 0-100 scale even if
criteria weights don't sum to exactly 100. That segment score is averaged
across all judges. The overall score is then the weighted average of segment
scores (points method) — or, if you chose "average of ranks", each
contestant's *rank* within each segment is averaged instead, weighted by
segment weight (lower is better). All of this lives in
`app/Services/TabulationService.php` if you want to adjust the formula (e.g.
add "drop highest/lowest judge score" rules).
