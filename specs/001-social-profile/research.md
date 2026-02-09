# Research: Social Profile & Follow System

**Date**: 2026-02-08
**Feature Branch**: `001-social-profile`

## 1. Symfony Version & PHP Requirements

**Decision**: Symfony 7.2 LTS with PHP 8.2+
**Rationale**: Current LTS version with long-term bug fix and
security support. PHP 8.2 enables modern language features
(readonly classes, enums, fibers).
**Alternatives**: Symfony 6.4 LTS (previous LTS, shorter remaining
support), Symfony 7.0 (standard release, shorter support window).

## 2. Essential Packages

**Decision**: Minimal Symfony bundle stack:
- `symfony/security-bundle` — Authentication and access control
- `symfony/form` — Form handling
- `symfony/validator` — Data validation
- `doctrine/doctrine-bundle` + `doctrine/orm` — Database persistence
- `vich/uploader-bundle` — Profile photo uploads
- `knplabs/knp-paginator-bundle` — User directory pagination
- `symfony/twig-bundle` — Templating
- `symfony/asset` — Asset management

**Rationale**: Each package solves a specific spec requirement.
No extras beyond what the 14 functional requirements demand.
**Alternatives**: Manual file handling (more boilerplate), Doctrine
Paginator directly (less feature-rich), API Platform (overkill
for this scope).

## 3. Authentication Approach

**Decision**: Symfony Security component with `form_login`
authenticator, session-based, email as user identifier.
**Rationale**: Built-in, zero additional dependencies, follows
Symfony conventions (Constitution Principle II). Session-based
matches the spec assumption.
**Alternatives**: Custom authenticator (unnecessary complexity),
JWT (stateless, not needed for server-rendered app), Guard
authenticators (deprecated).

## 4. File Upload (Profile Photos)

**Decision**: VichUploaderBundle with Doctrine lifecycle integration.
**Rationale**: Handles naming, saving, deletion automatically.
Supports PHP 8 attributes. Mature and well-documented.
**Alternatives**: Manual `UploadedFile` handling (more code, same
result), Flysystem (cloud storage abstraction — not needed).

## 5. Pagination

**Decision**: KnpPaginatorBundle.
**Rationale**: Autowirable, template-ready, zero-config for Doctrine
queries. Handles sorting and filtering out of the box.
**Alternatives**: Doctrine Paginator (lighter but more manual work),
Pagerfanta (similar features, less Symfony integration).

## 6. Database

**Decision**: SQLite for development and testing.
**Rationale**: Zero infrastructure setup for a sample/learning
project. No Docker or database server required. Simplicity
principle (Constitution III). Sufficient for 100 concurrent
users (SC-005).
**Alternatives**: PostgreSQL (production-grade but requires server
setup), MySQL (same concern).

## 7. Testing

**Decision**: PHPUnit via `symfony/test-pack` with
`dama/doctrine-test-bundle` for database test isolation.
**Rationale**: Constitution Principle I (Test-First) mandates
PHPUnit. DAMA bundle wraps tests in transactions for fast,
isolated database tests.
**Alternatives**: Pest (less Symfony tooling), Codeception
(heavier than needed).
