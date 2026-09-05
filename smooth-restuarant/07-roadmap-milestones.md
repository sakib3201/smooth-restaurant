---
title: 07 Roadmap & Milestones
tags:
  - smooth-restaurant/roadmap
  - rnd
aliases:
  - Roadmap
---

# 07 — Roadmap & Milestones

> [!abstract] Rule
> No date without an exit criterion. Each wave ends with something a founder can demo to a restaurant owner.

## Wave map

```mermaid
gantt
    title Smooth Restaurant — draft waves
    dateFormat  YYYY-MM-DD
    section R&D
    Lock scope (04+05+06)       :done,    rnd, 2026-09-10, 2w
    section MVP
    Menu + order + reserve      :active,  mvp, 2026-09-24, 6w
    Pilot with 3 restaurants    :         pilot, after mvp, 2w
    section V2
    QR tableside + reminders    :         v2a, after pilot, 4w
    Kitchen + delivery zones    :         v2b, after v2a, 4w
    section Launch
    wp.org + Pro pricing launch :         launch, after v2b, 2w
```

> [!todo] Fix the dates
> **TODO(Founder):** replace with real start date + team capacity. Gantt above is a placeholder shape.

## Wave detail (fill exit criteria)

| Wave | Ships | Exit criterion (demo-able) | Owner (TODO) |
|------|-------|----------------------------|--------------|
| 0 — R&D lock | This vault filled + D1–D7 decided | Founder signs [[04-feature-map]] MoSCoW + [[13-business-plan]] pricing; D1/D4/D5/D6 ✅ locked standalone | _TODO_ |
| 1 — MVP (standalone) | Native cart+block checkout, Stripe/PayPal/COD, ledger, slots, email queue, dashboard+print, reservations, QR view, importer | 1 pilot: real test payment + COD order + booking, **zero Woo installed** (per [[15-standalone-strategy#10. Decision log]]) | _TODO_ |
| 2 — Pilot | 3 live sites, onboarding <1 day | Testimonials + activation funnel data | _TODO_ |
| 3 — V2 ops | QR, reminders, coupons, delivery zones | No-show ↓, repeat order ↑ (TODO %) | _TODO_ |
| 4 — Launch | wp.org + Pro + docs + demo | _TODO_ installs + conversion in 30d | _TODO_ |

## Dependency chain

```mermaid
flowchart LR
    R["R&D lock<br/>scope + pricing + D1-D7"] --> M["MVP build"]
    M --> P["3 pilots"]
    P --> V["V2 ops"]
    V --> L["Launch"]
    P -.->|feedback| M
```

## What we deliberately defer (TODO)

- [ ] e.g. native POS hardware
- [ ] e.g. delivery-fleet tracking
- [ ] e.g. SaaS-hosted version
