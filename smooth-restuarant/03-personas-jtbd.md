---
title: 03 Personas & JTBD
tags:
  - smooth-restaurant/personas
  - rnd
aliases:
  - Personas
---

# 03 — Personas & JTBD

> [!abstract] Goal
> Name the buyer, the daily user, and the diner. If a feature serves none of them, it dies in [[04-feature-map]].

## ICP (whiteboard, 2026-09-05) ✅ imported

> Ideal Customer = anyone involved in the food business:
> i) Physical restaurant / café / food-truck owner, ii) Cloud kitchen, iii) Agency / Developer (channel + buyer).

## Persona map

```mermaid
flowchart TD
    OWNER["🧑‍🍳 Owner (buyer)<br/>wants: full tables,<br/>no chaos, no fees"]
    STAFF["🧾 Staff (daily user)<br/>wants: fast order<br/>flow, KDS clarity"]
    DINER["📱 Diner (end user)<br/>wants: order in<br/><60s, pay easily"]
    OWNER -->|pays, configures| PLUGIN["Smooth Restaurant"]
    STAFF -->|operates daily| PLUGIN
    DINER -->|orders / books| PLUGIN
    PLUGIN --> OWNER
```

## JTBD (jobs-to-be-done) — starter set, edit freely

| Who | Job | Today (painful workaround) | Success = |
|-----|-----|----------------------------|-----------|
| Owner | "Get Friday night fully booked without phone chaos" | Phone + paper + no-show | _TODO: metric_ |
| Owner | "Launch online ordering this week, no dev" | 5 plugins + Woo maze | Menu live in <1 day |
| Staff | "Fire orders to kitchen without re-typing" | Print + shout | _TODO_ |
| Diner | "Order from table by QR, pay, leave" | Wait for waiter | <60s to paid |
| Diner | "Book a table in 3 taps" | Call during rush | Instant confirm + reminder |

## Diner journey (happy path)

```mermaid
sequenceDiagram
    participant D as Diner
    participant S as Smooth (QR/menu)
    participant K as Kitchen
    participant P as Payment
    D->>S: Scan QR / open menu
    S->>D: Fast menu (<2s)
    D->>S: Cart + checkout
    S->>P: Pay (card/wallet/COD)
    P->>S: Confirmed
    S->>K: Fire ticket (KDS/print)
    K->>D: Food + status updates
```

## Anti-personas (who we ignore in V1)

- [ ] **TODO:** e.g. delivery-fleet operators
- [ ] **TODO:** e.g. enterprise POS integrators

> [!tip] Founder check
> If torn between two features, ask: "Which persona's Friday night does this save?" Build that one.
