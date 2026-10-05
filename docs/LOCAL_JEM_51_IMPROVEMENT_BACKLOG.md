# LOCAL JEM 5.1+ improvement backlog

> Local Koelman roadmap / testing guidance.  
> Do not automatically post these items upstream. Review and split them into focused upstream proposals when useful.

## Product direction

Primary target from October 2026 onward:

- **Joomla 6**
- **JEM 5.1.0+**
- Joomla 5 compatibility is **best effort only** when it comes naturally from Joomla APIs and does not add a separate implementation path, extra UX complexity, or architectural compromises.
- New local acceptance and product guidance should optimize for Joomla 6 first.
- Avoid adding settings merely because they are possible. Prefer clear defaults, progressive disclosure, and explainable behaviour.

## Top 10 improvement priorities

### 1. Settings simplification / progressive disclosure
**Priority: very high**

JEM is becoming powerful enough that configuration overload is becoming a product risk.

Direction:
- Basic / Advanced / Expert presentation rather than one growing wall of settings.
- Hide dependent options until a feature is enabled.
- Group settings by administrator task: Events, Registrations, Media, Maps, Notifications, Privacy, SEO, Integrations.
- Search settings.
- Show effective value and source where inheritance exists.
- Provide section reset/default actions.
- Prefer safe, privacy-conscious and sensible defaults.
- Avoid duplicate global/menu/item controls unless precedence is explicit.

Goal: retain power without making JEM intimidating.

### 2. Joomla Privacy / personal-data lifecycle integration
**Priority: very high — APPROVED**

JEM increasingly stores or processes registrations, notification history, audit history, DOB-related eligibility and future ticketing data.

Review / proposal:
- Joomla privacy export integration.
- Controlled anonymisation/erasure.
- Configurable retention where appropriate.
- Clear separation between operational/audit records and personally identifying data.
- Preserve historical integrity where possible without retaining unnecessary identity data.
- Document what JEM stores and why.

Related existing work: privacy-friendly IP storage already exists, but this is broader than IP handling.

### 3. Support & Health view
**Priority: high — APPROVED**

Expand upstream #2196 into a real JEM Support & Health surface.

Possible checks:
- Joomla / JEM / PHP / database versions.
- Schema/migration health.
- Required and optional JEM plugins.
- Scheduler/task status where JEM depends on it.
- Writable media/attachment/temp paths.
- Required PHP extensions.
- Mail capability/status without credentials.
- Active JEM template overrides.
- Relevant feature status.
- Privacy-safe “Copy support report”.
- Never expose secrets, tokens, passwords or unnecessary personal data.

### 4. Effective Event diagnostics / “Why?”
**Priority: high — APPROVED**

Provide a read-only administrator diagnostic explaining effective behaviour for an Event.

Examples:
- Published state.
- Joomla access level / ACL result.
- Effective Event + Venue age rule.
- Registration eligibility.
- Relevant menu/filter context.
- Timezone used.
- Capacity/waiting-list state.
- Feature/settings source where inherited.

Goal: answer “Why can/can’t this user see/register for this event?” without reading database rows or source code.

Privacy requirement: do not expose arbitrary personal data; use a safe explicit test context.

### 5. Joomla 6 native architecture modernization
**Priority: high**

Continue reducing legacy Joomla MVC / non-namespaced architecture in controlled slices.

Related upstream issue: #2037.

Principles:
- Joomla 6 first.
- No rewrite-at-once.
- Preserve stable behaviour with characterization tests.
- Prefer service/provider/DI and documented Joomla APIs.
- Do not keep Joomla 5-specific branches unless compatibility is effectively free.

### 6. Stable extension points / plugin contracts
**Priority: high**

Reduce the need for template/core patches by providing documented extension contracts.

Direction:
- Stable JEM events/hooks at meaningful boundaries.
- Clear mutable vs read-only payload contracts.
- Versioned/deprecation-aware hooks.
- Hooks for event rendering, registration, notifications, media and diagnostics where appropriate.
- Tests proving hooks fire exactly once and do not alter behaviour without consumers.

Local native Event-view hook work is evidence that this can be useful.

### 7. Upgrade / migration self-checks
**Priority: medium-high**

Make upgrades easier to trust and troubleshoot.

Possible checks:
- Database schema version parity.
- Missing migration detection.
- Required columns/tables/plugins.
- Stale legacy files where relevant.
- Post-update health result.
- Idempotent update expectations.
- Report actionable repair guidance, not destructive automatic repair by default.

Could integrate with Support & Health.

### 8. Performance budgets and query-regression testing
**Priority: medium-high**

Treat performance as a contract instead of only a bug report.

Related upstream issue: #2194 category N+1 loading.

Direction:
- Representative event-list datasets.
- Query-count / response-time characterization.
- Multi-category and recurring-event coverage.
- No regression of ACL/category correctness for performance gains.
- Optimize only after measuring.

### 9. Accessibility baseline
**Priority: medium — APPROVED AS PRAGMATIC PLUS**

Not intended as a separate accessibility rewrite.

Use Joomla/browser standards where practical:
- Correct labels and semantic controls.
- Useful focus behaviour for dialogs/forms.
- Errors and statuses understandable without relying only on colour.
- Screen-reader-friendly names for important controls.
- Keyboard operability for essential actions where standard HTML/Joomla controls already support it.
- Respect reduced motion where custom animation is introduced.

Goal: avoid custom UI that breaks normal browser accessibility; do not turn this into a huge standalone project unless required.

### 10. Data portability / operational export
**Priority: medium**

Give administrators a predictable way to move or archive their JEM data independently from presentation.

Potential scope:
- Document authoritative JEM data entities.
- Export/import strategy for Events, Venues, Categories and configuration where safe.
- Explicit treatment of registrations/personal data.
- Preserve identifiers/references only where appropriate.
- Validation/reporting before import.
- Avoid pretending a raw database dump is a user-facing portability solution.

## Configuration philosophy

As JEM grows, every new feature should answer:

1. Does this require a setting at all?
2. Can a sensible default cover most sites?
3. Is the setting only shown when its parent feature is enabled?
4. Is the scope clear: global, menu, event, venue, category or user?
5. If multiple levels can define it, can the administrator see the **effective value and its source**?
6. Can the feature be understood without reading documentation first?
7. Can advanced controls be moved behind an Advanced/Expert section?

Prefer:

```text
Simple by default
Powerful when needed
Explainable when inherited
Safe when misconfigured
```

## Local testing implication

New Playwright work should target **Joomla6Demo / JEM 5.1.0+** unless a test explicitly exists to verify backwards compatibility.

Joomla6T2 remains protected and must not be used as a writable test target.
