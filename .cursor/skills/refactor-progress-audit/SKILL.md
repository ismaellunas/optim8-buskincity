---
name: refactor-progress-audit
description: >-
  Audit the BuskinCity refactor task list against the ACTUAL codebase and produce a
  status report classifying every task as TODO, IN PROGRESS, DONE, or BLOCKED. Use when
  the user asks to check refactor/implementation progress, verify whether the todo items
  in the implementation guide are really implemented, generate a status/progress report,
  or asks "what's done / what's left / what's blocked" for the user-approval, roles,
  pitches, approval-workflow, or navigation refactor.
---

# Refactor Progress Audit

Verifies whether the tasks in the refactor plan are **actually implemented in code** (not just checked off) and emits a formatted status report.

## Source of truth

1. **Task list:** `migration-preparation/00-START-HERE-implementation-guide.md` — §7 (the phased task list T0.1…T7.4 + cross-cutting), §6 (blockers OQ1–OQ14), §4 (guardrails).
2. **Verification criteria & evidence pointers:** each task's **"Files"** and **"Verify"** lines, plus the linked specs:
   - `migration-preparation/new-requirements-frs-and-refactor-plan.md` (FR-* acceptance criteria, role matrix, ERM)
   - `migration-preparation/new-requirements-security-scalability-and-phasing.md` (V1–V9 mitigations, per-phase tests)
   - `migration-preparation/user-approval-roles-refactor-plan.md` (Phase 0–1 PR detail)

Always re-read the guide first; the task list is the live source. Do **not** assume the checkboxes are accurate — verify against code.

## Workflow

Copy this checklist and track it:

```
- [ ] 1. Read the implementation guide §7 task list, §6 blockers, §4 guardrails
- [ ] 2. Build the task set (IDs, deps, key files, Verify criteria, blocking OQs)
- [ ] 3. For each task, inspect the cited files in the codebase for real evidence
- [ ] 4. Classify each task: TODO / IN PROGRESS / DONE / BLOCKED (rules below)
- [ ] 5. Emit the report using the template below
```

**Step 3 — gather evidence (do not trust checkboxes):**
- Read/Grep the task's **Files** for the specific change the task requires (e.g., for T0.1, does `config/permission.php` `role_names` include `city_administrator` and does a `UserRole` enum exist; is the `Gate::after` literal replaced?).
- Check the task's **Verify** criteria as a concrete evidence list (e.g., for T3.2, is there a `Rule::in(scopedCities)` on `city_id` in `ProductEventRequest`?).
- Cite concrete evidence: `path:line`, symbol/string presence or absence. Prefer Grep/Read; never guess.
- For test-bearing tasks, check whether the named test files exist and assert the behavior.

## Status classification rules

Apply in order; pick the first that matches.

| Status | Rule |
|---|---|
| **🔴 BLOCKED** | A gating Open Question (the task's "Blocked by OQx" in §6) is **unanswered**, OR a dependency task is not DONE. Decision-bearing work cannot proceed. Record the blocker. (A task can be BLOCKED even if some scaffolding exists.) |
| **🟢 DONE** | Every part of the task's **Verify** criteria is satisfied by code evidence, the **Files** show the required change, and (if applicable) the tests exist and cover it. Cite evidence. |
| **🟡 IN PROGRESS** | Partial evidence: some Files changed or some Verify criteria met, but not all (e.g., migration added but no validation; UI done but server-side scope missing; code present but tests absent). |
| **⚪ TODO** | No implementation evidence found; the change is absent. (Not started.) |

Tie-breakers:
- Prefer **BLOCKED** over TODO/IN PROGRESS when an OQ gates the decision-bearing parts, but note any unblocked scaffolding that's already done.
- A task is **not DONE** on checkbox alone — require code evidence.
- If a guardrail (§4) was violated by the implementation (e.g., dropped `city_user`, renamed a role), flag it under the task as ⚠️ and treat the task as IN PROGRESS at best.

## Report template

Output exactly this structure.

```markdown
# Refactor Progress Report — <YYYY-MM-DD>

Source: `migration-preparation/00-START-HERE-implementation-guide.md` (verified against codebase)

## Summary
| Status | Count |
|---|---|
| 🟢 DONE | N |
| 🟡 IN PROGRESS | N |
| ⚪ TODO | N |
| 🔴 BLOCKED | N |
| **Total** | N |

Overall: Phase X of 0–7 in progress. Unblocked next task: **T<id>**.
Unanswered blockers gating work: OQ<list>.

## By phase
| Phase | Tasks | 🟢 | 🟡 | ⚪ | 🔴 |
|---|---|---|---|---|---|
| 0 — Role foundation | T0.1–T0.3 | … | … | … | … |
| 1 — Scope + City Admin | T1.1–T1.3 | … | … | … | … |
| 2 — Special Events Admin | T2.1–T2.2 | … | … | … | … |
| 3 — Pitch FKs + scope | T3.1–T3.3 | … | … | … | … |
| 4 — Pitch save | T4.1 | … | … | … | … |
| 5 — Approval workflow | T5.1–T5.2 | … | … | … | … |
| 6 — Pitch UX + 14-day | T6.1–T6.2 | … | … | … | … |
| 7 — Nav/type/pins/search | T7.1–T7.4 | … | … | … | … |
| Cross-cutting | T-PERF-CANCEL, T-TESTS | … | … | … | … |

## Task detail
### T<id> — <title> — <STATUS emoji+label>
- **Evidence:** <file:line / symbol present-or-absent — what proves the status>
- **Met:** <Verify criteria satisfied>  ·  **Missing:** <criteria not yet satisfied>
- **Blocked by:** <OQx / dependency, or "—">
- **Notes:** <guardrail flags, partial work, risks — optional>

(repeat for every task, grouped by phase)

## Recommended next actions
1. <unblocked task to pick up next, with the one-line reason>
2. <blocker OQ to get answered to unblock Phase 3, etc.>
```

## Rules of the report

- One entry per task in the guide; never invent tasks not in §7.
- Keep evidence concrete (paths + symbols). If you could not inspect something, say so rather than guessing.
- BLOCKED entries must name the specific OQ (from §6) or unfinished dependency.
- If the guide's checkbox disagrees with the code evidence, trust the **code** and note the mismatch.
- Do not modify any code or the guide during an audit — this skill is read-only reporting.
- Keep the summary honest: partial ≠ done.
