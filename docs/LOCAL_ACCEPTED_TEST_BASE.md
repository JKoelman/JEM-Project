# JEM 5.1 local accepted test base

This document defines the Koelman Labs local JEM 5.1 integration baseline used for acceptance testing before an upstream fix is available.

## Purpose

All new JEM acceptance work should run against:

1. the current upstream `jemproject/JEM-Project` JEM 5.1 integration state; plus
2. every local product patch that already passed its own acceptance cycle and has not yet been incorporated upstream.

This prevents later tests from accidentally dropping fixes that were already proven locally.

The branch is a **test/integration baseline only**. It is not an upstream release branch and does not replace issue-specific candidate branches.

## Canonical branch

Repository:

```text
JKoelman/JEM-Project
```

Branch:

```text
integration/jem-510-local-accepted
```

Current upstream base:

```text
jemproject/JEM-Project
JEM-5.1.0-dev-integration
fc65b276657a66ebdda1c5e008bb26e89ac1768c
```

## Accepted local deltas

The branch currently carries these local changes on top of the upstream base.

### #2353 — Resource-aware ACL

The previously accepted local #2353 backend and frontend patches are no longer carried as local deltas.

Upstream incorporated the stored-resource ACL correction in:

```text
865191fd52f2e5f7657d8a6df97e1275f9031153
```

The upstream implementation now provides the authoritative #2353 product behaviour on this baseline. The Playwright #2353 regression remains part of the preservation gate.

### #2339 — Eventslist Contact selector

Accepted frontend contract:

- the editable Contact filter is a compact single-choice selector;
- the backend request name remains compatible with the existing array-based resolution;
- the obsolete multi-select hint is no longer rendered.

Integration commit:

```text
f1621c0654bf208ddbf5e8e762fa860192d76328
```

### JEM Presentation native event-view hook

The accepted local extension point is preserved in:

```text
site/views/event/view.html.php
```

Contract:

```php
$dispatcher->triggerEvent('onJemPrepareEventView', array(&$this));
```

Placement:

- after JEM has prepared the event document/structured data;
- immediately before the final `parent::display($tpl)`;
- no listener means normal JEM rendering remains unchanged.

Integration commit:

```text
c87bd841a8d2414238b965dc292978f57089ee49
```

## Upstream-integrated fixes

A previously accepted local fix must **not** be carried twice after upstream incorporates an equivalent fix.

At the current base:

- upstream contains the #2353 stored-resource ACL fix; the former local backend/frontend #2353 patches are not re-applied;
- upstream already contains its own #2352 fix for custom-field labels and shared frontend editor model loading; the old local #2352 patch is not re-applied;
- upstream contains the #2351 Events List `Include Subcategories` recursion fix; focused JEM 5.1 acceptance confirmed HTTP 200, child-category Event rendering and no browser errors;
- upstream contains #2356 global/per-event Event timezone visibility controls; focused JEM 5.1 acceptance confirmed global/event precedence, classic/responsive details/compact parity, untimed-event suppression, unchanged JSON-LD `startDate`, and unchanged iCalendar `DTSTART`/`DTEND` semantics when the human-readable timezone is hidden;
- upstream contains the #1949 JEM 5.1 module CSS override rework; local acceptance confirmed current Teaser/Wide stylesheet names at runtime, CSS Manager legacy override discovery, explicit safe migration, conflict preservation, and no overwrite of an existing canonical target;
- #2132 age access is locally accepted through the core assignment/intersection slice and DOB event-date boundary slice; local product fix commit 7e3f38a5 normalizes Joomla User - Profile DOB values stored as `Y-m-d H:i:s` before JEM age calculation;
- #2339's compact single-choice Contact selector is still missing upstream and remains a local accepted delta;
- the native `onJemPrepareEventView` extension hook is still missing upstream and remains a local accepted delta.

The related Playwright regressions remain part of the preservation gate.

## Runtime policy

The disposable runtime is:

```text
C:\wamp2\www\Joomla6Demo
```

The protected environment is:

```text
C:\wamp2\www\Joomla6T2
```

Never install, update or mutate JEM on Joomla6T2 as part of this track.

For a new test cycle:

1. refresh `integration/jem-510-local-accepted` from the latest accepted upstream JEM 5.1 integration point;
2. re-apply only local accepted deltas still missing upstream;
3. build one exact package from the local-accepted branch;
4. install that package as a normal update on Joomla6Demo;
5. run the preservation gate;
6. only after the gate is green, start the next issue-specific acceptance slice.

Do not uninstall/reinstall merely to refresh the baseline unless a test explicitly targets installer lifecycle behaviour.

## Issue-specific fixes

When a new product RED is found:

1. keep `integration/jem-510-local-accepted` unchanged;
2. create an isolated issue-specific candidate branch;
3. prove FIRST RED against the accepted baseline;
4. prove targeted candidate GREEN;
5. run the relevant impact/closure gate;
6. only then copy the accepted delta into `integration/jem-510-local-accepted`.

This keeps FIRST RED evidence separate from the cumulative integration baseline.

## Preservation gate

Canonical Playwright repository:

```text
koelmanlabs/playwright
```

Preservation branch:

```text
test/jem-510-local-accepted-preservation-v1
```

The gate includes the accepted regression suites for:

- #2353 resource-aware ACL;
- #2352 category-aware custom fields;
- #2339 Eventslist filters;
- JEM Presentation native-hook integration.

Runtime rules:

- Chromium;
- `--workers=1`;
- browser/UI assertions;
- tests create and clean up their own fixtures;
- no fixed database IDs;
- no direct database assertions;
- local Playwright runtime result is the authoritative Runtime PASS.

## Refresh rule

Whenever upstream `JEM-5.1.0-dev-integration` advances:

1. inventory the upstream diff;
2. classify every local accepted delta as:
   - still missing upstream;
   - upstream-equivalent;
   - conflicting/reworked upstream;
3. rebuild `integration/jem-510-local-accepted` from the new upstream point;
4. do not blindly replay obsolete patches;
5. run the preservation gate before using the refreshed baseline for new feature testing.

This document should be updated whenever the accepted local delta set changes.
