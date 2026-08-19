# Confessionnal — Build Spec

`blackpig-creatif/confessionnal` · `BlackpigCreatif\Confessionnal\`

## What this is

A conversational, Typeform-style form builder for Filament, supporting two
modes: one-question-per-screen for surveys, and multiple-fields-per-page for
onboarding/data-collection — built for Blackpig Créatif's own client stable
rather than as a general-purpose SaaS competitor. Primary driver: replacing
ad-hoc survey/market-research forms and client session-intake forms with
something reusable across projects.

**Not the goal:** feature-parity with Flex Forms (flexforms.bjanczak.com) or
FilaForms (filaforms.app). Both are mature commercial products (Studio
builders, Insights analytics with Redis rollups, async integration ledgers,
Stripe, white-label). Confessionnal targets the ~20% of that surface BPC
actually needs, built on infrastructure BPC already owns.

## Technical foundation

Scaffold from the official [filamentphp/plugin-skeleton](https://github.com/filamentphp/plugin-skeleton)
rather than a bespoke package layout — gives the standard Spatie-package-tools
structure, Plugin/ServiceProvider classes conforming to Filament's plugin
contract, config publishing, and asset pipeline out of the box. Layer BPC's
own conventions (namespace `BlackpigCreatif\Confessionnal\`, Packagist vendor
`blackpig-creatif/confessionnal`) on top of the skeleton rather than deviating
from it.

**Stack**: Laravel 12/13, Filament v5, Livewire v4, Tailwind v4, Alpine.js,
Pest v4, PHP 8.4 — matches the rest of the BPC suite, not the skeleton's
defaults if they differ.

**Dependencies**: Épître, ChambreNoir, Réplique, and Atelier are stable,
published BPC packages on Packagist — require them normally via Composer,
same as any other dependency.

**Testing**: Pest coverage expected for each phase as it lands (data model,
fill runtime validation, submission handling, target-model mapping), in
line with standard BPC practice — not a separate testing phase bolted on
at the end.

## Reuse, don't rebuild

| Need | Reuse |
|---|---|
| Notification / confirmation emails | Épître (editable templates) |
| Spam prevention | Réplique's honeypot pattern |
| Theming to match client site | Atelier's Tailwind v4 `@theme` tokens |
| Multi-tenant panel scoping | Existing BPC panel-scoping conventions |
| File uploads | ChambreNoir `RetouchMediaUpload` |

## Core v1 scope

1. **Builder** (admin panel) — field types: text, textarea, single/multi
   choice, scale, date, file upload. Per-field validation rules (required,
   min/max, email, regex, etc. — Laravel validation, enforced client- and
   server-side). Per-field options config for choice-type fields (select,
   radio, checkbox — label/value pairs). Basic conditional logic (show/hide
   field based on a prior answer). **Preview** action — renders the actual
   fill runtime from the admin panel before publishing, without recording a
   real submission.
2. **Form mode** — set per form: **conversational** (one field per page,
   Typeform-style, for surveys) or **standard** (multiple fields grouped per
   page, for onboarding/data-collection forms). Fill runtime rendering and
   default builder layout adapt to the chosen mode; both modes share the
   same data model (a page just holds one field vs several).
3. **Fill runtime** — dedicated Livewire component, not admin-panel chrome.
   Conversational mode: one question per screen, progress indicator,
   keyboard nav (Enter to advance), smooth transitions. Standard mode:
   grouped fields per page, still paginated, more traditional form layout.
   Locale-specific fill links per form (e.g. `/forms/{slug}/{locale}`),
   respondent's `locale` recorded against the submission. Public route, no
   auth required.
4. **Sections** — a form is organised into ordered Sections. Each Section
   has an interstitial intro (title, subtext, optional image) followed by
   its own ordered set of question pages. The interstitial's "Continue"
   button moves into the section's first question. Progress indicator
   becomes section-aware ("Section 2 of 4") rather than a flat question
   count.
5. **Reference image on a question page** — the builder can attach a
   context image (via ChambreNoir) to any question page. Displayed
   alongside the question purely as reference — respondent answers the
   field(s) normally, no interaction with the image itself (no
   annotation/hotspot in v1).
6. **Query string capture** — on fill-runtime page load, capture query
   params (e.g. `PROLIFIC_PID`, `STUDY_ID`, `SESSION_ID`, `utm_*`) and store
   them against the submission — needed for panel-recruitment services like
   Prolific. Configurable per form: capture-all or a whitelist of expected
   param names.
7. **Completion redirect** — per-form setting: redirect the respondent to
   an external URL on submit instead of (or after) showing a thank-you
   screen, with captured query params passed through. Needed for panel
   services like Prolific, which require redirecting back with a completion
   code rather than ending on an in-app thank-you page.
8. **Randomised section order** — Sections can be marked into a
   randomisation group (e.g. an A/B-style comparison — a "L'Alambic
   Errant" section and an "L'Ange Errant" section, each with its own intro
   and questions). Order of Sections within the group is shuffled per
   respondent to control for presentation-order bias — the section moves
   as a whole block, questions within it keep their fixed internal order.
   The order actually shown is recorded against the submission so analysis
   can check whether order affected results, not just eliminate the bias
   blind.
9. **Submissions** — see data model below. CSV/Excel export for survey
   analysis out of the box.
10. **Notifications** — on submit, fire an Épître-templated email (to
    respondent and/or internal recipient), config per form.
11. **Default contact form template** — ships in the package as an example/
    starting point, stylable and extendable per client site.
12. **Lightweight analytics** — views / starts / completes / drop-off per
    page. Simple daily aggregate table — no Redis, no beacon/rollup
    infrastructure (that's Flex Forms' Insights, overkill at BPC's traffic).
13. **Multi-tenant from day one** — forms scoped per panel/tenant, following
    existing BPC conventions, so any client site can adopt it later without a
    rebuild.

## Data model — the key architectural decision

Default storage is generic and requires zero setup. Optional structured
mapping lets a form write into a real Eloquent model instead of staying
trapped as survey data — this is the feature neither commercial competitor
offers, and it's what the session-intake use case needs.

- `Form` — name, slug, tenant scope, `mode` (`conversational` | `standard`),
  settings (notification config, theme overrides, target model mapping if
  any, query-param capture config, completion redirect URL + param
  passthrough toggle)
- `Section` — ordered, belongs to Form. Carries the interstitial intro
  content (title, subtext, optional image). Optional `randomisation_group`
  — Sections sharing a group value are shuffled as whole blocks per
  respondent instead of shown in fixed order; question pages within a
  Section always keep their internal order regardless.
- `FormPage` — ordered within its Section. `type`: `question` (the
  interstitial content now lives on Section itself, not a page type). Any
  page may carry an optional `context_image` (ChambreNoir media relation)
  shown for reference alongside the page content — display-only, not
  interactive. In `standard` mode a page can hold several `FormField`s; in
  `conversational` mode, one.
- `FormField` — type, `validation_rules` (Laravel rule set), `options`
  (label/value pairs, for choice-type fields), conditional-logic config,
  belongs to FormPage
- `Submission` — form_id, JSON `answers` payload, timestamps, `locale`
  (which language the respondent completed the form in), `section_order`
  (actual order Sections were shown in, for groups that were randomised),
  meta (IP/referrer, captured query params) — always written, this is what
  CSV/Excel export reads from; export/analysis can filter or group by
  `locale` or by randomised section order
- **Target model mapping** (optional, per form): a config block mapping
  field keys → columns on a specified Eloquent model. On submit, in addition
  to the generic `Submission`, the package creates or updates a record on
  that model. Config, not custom code per form — e.g. an intake form maps
  straight onto a `Client` or `Booking` model rather than only existing as
  survey data.

## Explicitly out of scope for v1

Stripe/payments, calculated fields, CRM/Slack integration layer with
delivery ledger + replay + circuit breaker, white-label licensing, CAPTCHA
beyond honeypot, Redis-backed analytics rollups. Bolt on later only if a
specific client need justifies it.

## Multi-language — confirmed

One `Form` record, Spatie-translatable content (question text, subtext,
option labels), locale-specific fill links (e.g. `/forms/your-survey/fr` and
`/forms/your-survey/en`). Every `Submission` records the `locale` it was
completed in, so export/analysis can filter or group per language without
needing separate Form records — editing a question updates all languages at
once rather than risking drift between a French copy and an English copy.

Assumes translation, not divergent versions, as the default case. A specific
study that needs genuinely different wording per language (not just
translated) can still use two independent Form records — flag if that's
needed for a particular survey rather than building it as the general case.

File uploads via ChambreNoir are a settled requirement, not just a
default — both the upload field type and reference images depend on it.

## Suggested build phases for CC

1. Data model + migrations + Filament resources for the builder (incl. form
   mode, Sections with ordered pages, field validation rules, field
   options, Spatie translatable content)
2. Fill runtime (Livewire, public route, both modes, section-aware
   pagination, no styling polish yet)
3. Submissions: generic storage + CSV/Excel export
4. Épître notification hook
5. Default contact form template + Atelier theming integration
6. Target-model mapping config + write-through on submit
7. Lightweight analytics (views/starts/completes/drop-off)
8. Conditional logic in builder + fill runtime
9. Section-aware progress indicator

**— POC checkpoint —** sections, submissions, notifications, target-model
mapping, and conditional logic are all in by this point: a usable, testable
form can be built and filled end-to-end. Good point to pause and review
before continuing into the phases below.

10. Reference image on question pages (ChambreNoir attach + display)
11. Query string capture + completion redirect, param passthrough
12. Randomised section order (session-level shuffle + recorded order on
    submission) — optional to defer past initial POC if it slows early
    testing
13. Builder preview action
14. Polish pass: transitions, keyboard nav, progress indicator

## Open questions for Stuart before handing off

- Confirm the multi-language default above
- Any specific client/project this ships on first (to generalise from), or
  greenfield?
- No hard budget ceiling — this is being built in-house within BPC, not as
  a fixed-cost CC engagement. The phase order above is still worth keeping
  as the build sequence (data model → runtime → submissions before the
  polish items), so the package is usable at each intermediate stage rather
  than only at the very end.
- Build model: Opus 4.6 — the model-mapping config and conditional-logic
  phases benefit from that over a lighter model.
