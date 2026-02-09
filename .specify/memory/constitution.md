<!--
Sync Impact Report
===================
Version change: 0.0.0 → 1.0.0 (initial ratification)
Modified principles: N/A (initial creation)
Added sections:
  - Core Principles (3): Test-First, Symfony Conventions, Simplicity
  - Development Workflow
  - Quality Standards
  - Governance
Removed sections: N/A
Templates requiring updates:
  - .specify/templates/plan-template.md ✅ no changes needed
    (Constitution Check section is generic; gates derived at runtime)
  - .specify/templates/spec-template.md ✅ no changes needed
    (User stories and requirements align with principles)
  - .specify/templates/tasks-template.md ✅ no changes needed
    (Task phases and test-first workflow align with Principle I)
  - .specify/templates/agent-file-template.md ✅ no changes needed
  - .specify/templates/checklist-template.md ✅ no changes needed
Follow-up TODOs: none
-->

# PHP Symfony Sample Constitution

## Core Principles

### I. Test-First (NON-NEGOTIABLE)

All feature work MUST follow the red-green-refactor cycle:

1. Write failing tests that describe the expected behavior.
2. Implement the minimum code to make tests pass.
3. Refactor while keeping tests green.

- Unit tests MUST accompany every service and utility class.
- Integration tests MUST cover controller actions and
  database interactions.
- PHPUnit is the required testing framework.
- No pull request may be merged with failing tests.

**Rationale**: Tests are the primary documentation of behavior
and the safety net for refactoring. Writing them first forces
clear thinking about contracts before implementation.

### II. Symfony Conventions & API-First Design

All code MUST follow official Symfony best practices:

- Use Symfony Flex directory structure (`src/`, `config/`,
  `templates/`, `public/`).
- Prefer dependency injection via autowiring; avoid service
  locator patterns.
- Use Symfony attributes for routing, validation, and ORM
  mapping.
- API endpoints MUST be designed contract-first: define the
  request/response schema before writing controller logic.
- Use Symfony Serializer for all API input/output; never
  manually encode JSON in controllers.

**Rationale**: Following framework conventions reduces
cognitive load, enables tooling support, and ensures the
project remains a useful learning reference. API-first
design prevents implementation-driven contract drift.

### III. Simplicity (YAGNI)

- Every addition MUST solve a current, concrete requirement.
- Do not introduce abstractions, patterns, or libraries for
  hypothetical future needs.
- Prefer fewer files with clear responsibility over deep
  directory hierarchies.
- Configuration MUST use environment variables via Symfony's
  `%env()%` syntax; avoid custom config layers.
- Three similar lines of code are preferable to a premature
  abstraction.

**Rationale**: As a sample/learning project, clarity and
directness are paramount. Over-engineering obscures the
patterns being demonstrated.

## Quality Standards

- Code MUST pass PHP-CS-Fixer (PSR-12 profile) with zero
  violations before merge.
- Static analysis via PHPStan (level 6 minimum) MUST report
  zero errors.
- All Symfony deprecation warnings MUST be resolved before
  merge.
- Composer dependencies MUST be kept to the minimum required
  set; justify every non-Symfony package.

## Development Workflow

- Feature branches MUST follow the naming convention
  `<issue-number>-short-description`.
- Commits MUST be atomic: one logical change per commit.
- Pull requests MUST include a description linking to the
  relevant spec or issue.
- Code review is required before merging to `main`.
- The `main` branch MUST always be in a deployable state.

## Governance

This constitution is the authoritative source for project
standards. All pull requests, code reviews, and design
decisions MUST comply with these principles.

- **Amendments** require: a written proposal, review by at
  least one contributor, and an updated version number
  following semantic versioning (MAJOR for principle
  removals/redefinitions, MINOR for new principles or
  material expansions, PATCH for clarifications and typo
  fixes).
- **Compliance** is verified during code review; reviewers
  MUST check adherence to all three core principles.
- **Conflicts**: When a principle conflicts with a framework
  convention, Principle II (Symfony Conventions) takes
  precedence for framework-specific matters; Principle I
  (Test-First) takes precedence for quality matters.

**Version**: 1.0.0 | **Ratified**: 2026-02-08 | **Last Amended**: 2026-02-08
