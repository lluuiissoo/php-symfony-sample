# Tasks: Social Profile & Follow System

**Input**: Design documents from `/specs/001-social-profile/`
**Prerequisites**: plan.md, spec.md, data-model.md, contracts/routes.md, research.md, quickstart.md

**Tests**: Constitution Principle I (Test-First) is NON-NEGOTIABLE. All user story phases include test tasks written before implementation. Red-green-refactor cycle enforced.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3, US4)
- Include exact file paths in descriptions

## Phase 1: Setup

**Purpose**: Symfony project initialization and tooling configuration

- [x] T001 Initialize Symfony 7.2 project with `symfony new` and install core dependencies (symfony/security-bundle, doctrine/orm, doctrine/doctrine-bundle, symfony/form, symfony/validator, symfony/twig-bundle, symfony/asset) via Composer
- [x] T002 Install dev dependencies: symfony/test-pack, dama/doctrine-test-bundle, phpstan/phpstan, friendsofphp/php-cs-fixer
- [x] T003 [P] Configure SQLite database connection in .env (`DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"`)
- [x] T004 [P] Configure PHP-CS-Fixer with PSR-12 profile in .php-cs-fixer.dist.php
- [x] T005 [P] Configure PHPStan at level 6 in phpstan.neon
- [x] T006 [P] Configure DAMA DoctrineTestBundle in phpunit.xml.dist for test transaction isolation
- [x] T007 Create base Twig layout in templates/base.html.twig with navigation (Register, Login, Users, Profile, Followers, Following, Pending Requests, Logout)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Entities, security configuration, and database schema that ALL user stories depend on

**CRITICAL**: No user story work can begin until this phase is complete

- [x] T008 Create User entity implementing UserInterface and PasswordAuthenticatedUserInterface in src/Entity/User.php with fields: id, email (unique, 180 chars), password, roles (json), registeredAt (datetime)
- [x] T009 Create Profile entity in src/Entity/Profile.php with fields: id, displayName (string 50), bio (text nullable), photoFilename (string 255 nullable), wizardCompleted (boolean default false), user (OneToOne with User, cascade remove)
- [x] T010 Create FollowRequest entity in src/Entity/FollowRequest.php with fields: id, requester (ManyToOne User), target (ManyToOne User), status (string 10: pending/approved/rejected), requestedAt (datetime), resolvedAt (datetime nullable)
- [x] T011 [P] Create UserRepository in src/Repository/UserRepository.php extending ServiceEntityRepository
- [x] T012 [P] Create ProfileRepository in src/Repository/ProfileRepository.php extending ServiceEntityRepository
- [x] T013 [P] Create FollowRequestRepository in src/Repository/FollowRequestRepository.php extending ServiceEntityRepository
- [x] T014 Generate and run initial Doctrine migration for all three entities via `php bin/console make:migration` and `php bin/console doctrine:migrations:migrate`
- [x] T015 Configure security.yaml: password hasher (auto), entity user provider (email property), form_login firewall, logout path, access_control rules (anon for /register and /login, ROLE_USER for everything else)
- [x] T016 [P] Install and configure vich/uploader-bundle in config/packages/vich_uploader.yaml with profile_photos mapping pointing to public/uploads/photos
- [x] T017 [P] Install and configure knplabs/knp-paginator-bundle in config/packages/knp_paginator.yaml

**Checkpoint**: Foundation ready — entities, security, and infrastructure configured. User story implementation can now begin.

---

## Phase 3: User Story 1 - User Signup (Priority: P1) MVP

**Goal**: Visitors can create accounts with email/password and log in/out

**Independent Test**: Submit signup form, verify account is created and user can log in with credentials

### Tests for User Story 1

> **Write these tests FIRST. Ensure they FAIL before implementation.**

- [x] T018 [P] [US1] Write entity unit test in tests/Entity/UserTest.php: test email/password getters/setters, roles default to ROLE_USER, registeredAt is set
- [x] T019 [P] [US1] Write controller test in tests/Controller/RegistrationControllerTest.php: test GET /register renders form, POST with valid data creates user and redirects, POST with duplicate email shows error, POST with short password shows validation error
- [x] T020 [P] [US1] Write controller test in tests/Controller/SecurityControllerTest.php: test GET /login renders form, successful login redirects, invalid credentials show error, logout works

### Implementation for User Story 1

- [x] T021 [US1] Create RegistrationType form class in src/Form/RegistrationType.php with email (EmailType) and plainPassword (PasswordType, min 8 chars) fields
- [x] T022 [US1] Create RegistrationController in src/Controller/RegistrationController.php with index action: render form on GET, validate+hash password+persist user+create empty Profile on POST, redirect to /profile/wizard
- [x] T023 [US1] Create registration template in templates/registration/register.html.twig with form rendering and error display
- [x] T024 [US1] Create SecurityController in src/Controller/SecurityController.php with login action (render form + getLastAuthenticationError) and logout action (empty, handled by firewall)
- [x] T025 [US1] Create login template in templates/security/login.html.twig with email/password form and error display
- [x] T026 [US1] Verify all US1 tests pass (run `php bin/phpunit tests/Controller/RegistrationControllerTest.php tests/Controller/SecurityControllerTest.php tests/Entity/UserTest.php`)

**Checkpoint**: Users can register, log in, and log out. MVP functional.

---

## Phase 4: User Story 2 - Profile Wizard (Priority: P2)

**Goal**: New users complete a multi-step profile wizard; all users can edit their profile later

**Independent Test**: Sign up, get redirected to wizard, fill in display name and bio, verify profile is saved and visible on profile page

### Tests for User Story 2

> **Write these tests FIRST. Ensure they FAIL before implementation.**

- [x] T027 [P] [US2] Write entity unit test in tests/Entity/ProfileTest.php: test displayName/bio/photoFilename getters/setters, wizardCompleted default false, user relationship
- [x] T028 [P] [US2] Write controller test in tests/Controller/ProfileControllerTest.php: test wizard redirects for new user, wizard saves profile data, wizard handles photo skip (default avatar), edit form loads with existing data, edit form saves changes, show action displays public profile

### Implementation for User Story 2

- [x] T029 [US2] Create ProfileWizardType form class in src/Form/ProfileWizardType.php with displayName (TextType, 2-50 chars), bio (TextareaType, max 500, not required), photoFile (VichFileType, not required)
- [x] T030 [US2] Create ProfileEditType form class in src/Form/ProfileEditType.php (same fields as wizard, reusable for edit)
- [x] T031 [US2] Create EventSubscriber in src/EventSubscriber/ProfileWizardSubscriber.php that listens to kernel.request: if authenticated user has Profile with wizardCompleted=false, redirect to /profile/wizard (except if already on /profile/wizard or /logout)
- [x] T032 [US2] Create ProfileController in src/Controller/ProfileController.php with: wizard action (GET renders form, POST saves profile + sets wizardCompleted=true + redirects to /users), edit action (GET/POST for updating profile), show action (GET /profile/{id} displays public profile with display name, bio, photo)
- [x] T033 [US2] Create wizard template in templates/profile/wizard.html.twig with form fields and skip option for photo
- [x] T034 [P] [US2] Create edit template in templates/profile/edit.html.twig with form fields for updating profile
- [x] T035 [P] [US2] Create show template in templates/profile/show.html.twig displaying display name, bio, photo (or default avatar if null)
- [x] T036 [US2] Verify all US2 tests pass (run `php bin/phpunit tests/Entity/ProfileTest.php tests/Controller/ProfileControllerTest.php`)

**Checkpoint**: Users can complete the profile wizard and edit their profile. Profile pages display correctly with default avatars.

---

## Phase 5: User Story 3 - User Discovery (Priority: P3)

**Goal**: Logged-in users can browse and search a paginated directory of other users

**Independent Test**: Create multiple users, log in, verify user directory shows other users with pagination and search

### Tests for User Story 3

> **Write these tests FIRST. Ensure they FAIL before implementation.**

- [x] T037 [P] [US3] Write repository test in tests/Repository/UserRepositoryTest.php: test query that returns paginated users excluding current user, test search filtering by display name (case-insensitive substring match)
- [x] T038 [P] [US3] Write controller test in tests/Controller/UserControllerTest.php: test GET /users returns paginated list, test search query parameter filters results, test pagination with page parameter, test unauthenticated access redirects to login

### Implementation for User Story 3

- [x] T039 [US3] Add method to ProfileRepository: findPaginatableQueryExcludingUser(User $excludeUser, ?string $searchQuery) returning a QueryBuilder for KnpPaginator in src/Repository/ProfileRepository.php
- [x] T040 [US3] Create UserController in src/Controller/UserController.php with directory action: inject PaginatorInterface, query ProfileRepository excluding current user, apply search filter from ?q parameter, paginate at 20 per page
- [x] T041 [US3] Create directory template in templates/user/directory.html.twig with search input, user cards (photo, display name, bio excerpt), and KnpPaginator pagination controls
- [x] T042 [US3] Verify all US3 tests pass (run `php bin/phpunit tests/Repository/UserRepositoryTest.php tests/Controller/UserControllerTest.php`)

**Checkpoint**: User directory is fully functional with search and pagination.

---

## Phase 6: User Story 4 - Follow Requests & Approval (Priority: P4)

**Goal**: Users can send follow requests, approve/reject them, and manage follower/following lists

**Independent Test**: Create two users, send follow request from one to the other, approve it, verify both follower/following lists update

### Tests for User Story 4

> **Write these tests FIRST. Ensure they FAIL before implementation.**

- [x] T043 [P] [US4] Write entity unit test in tests/Entity/FollowRequestTest.php: test requester/target/status getters/setters, requestedAt set on creation, resolvedAt nullable, status transitions (pending→approved, pending→rejected)
- [x] T044 [P] [US4] Write repository test in tests/Repository/FollowRequestRepositoryTest.php: test findPendingRequestsForUser, test findFollowersForUser (status=approved), test findFollowingForUser (status=approved), test findPendingBetween(requester, target), test self-follow prevention
- [x] T045 [P] [US4] Write controller test in tests/Controller/FollowControllerTest.php: test POST /follow/{id} creates pending request, test POST /follow/{id} for self returns error, test duplicate pending request shows info message, test GET /follow-requests lists pending requests, test POST approve changes status to approved, test POST reject deletes request, test POST /unfollow deletes approved request, test GET /followers lists approved followers, test GET /following lists approved following

### Implementation for User Story 4

- [x] T046 [US4] Add repository methods to FollowRequestRepository in src/Repository/FollowRequestRepository.php: findPendingForTarget(User), findFollowers(User), findFollowing(User), findPendingBetween(User requester, User target), countPendingForTarget(User)
- [x] T047 [US4] Create FollowController in src/Controller/FollowController.php with actions: request (POST /follow/{userId} — validate not self, not duplicate pending, create FollowRequest with status=pending), approve (POST /follow-requests/{id}/approve — set status=approved, set resolvedAt), reject (POST /follow-requests/{id}/reject — delete the request record), unfollow (POST /unfollow/{userId} — delete approved FollowRequest), pendingList (GET /follow-requests), followers (GET /followers), following (GET /following)
- [x] T048 [US4] Create pending requests template in templates/follow/pending.html.twig with requester photo, display name, and Approve/Reject buttons
- [x] T049 [P] [US4] Create followers template in templates/follow/followers.html.twig listing followers with photo, display name, link to profile
- [x] T050 [P] [US4] Create following template in templates/follow/following.html.twig listing followed users with photo, display name, Unfollow button
- [x] T051 [US4] Update templates/profile/show.html.twig to show Follow/Pending/Unfollow button based on current relationship status (hide on own profile per FR-012)
- [x] T052 [US4] Update templates/user/directory.html.twig to show follow status indicator on each user card
- [x] T053 [US4] Add pending follow request count badge to navigation in templates/base.html.twig
- [x] T054 [US4] Verify all US4 tests pass (run `php bin/phpunit tests/Entity/FollowRequestTest.php tests/Repository/FollowRequestRepositoryTest.php tests/Controller/FollowControllerTest.php`)

**Checkpoint**: Complete follow system functional — send, approve, reject, unfollow, follower/following lists.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Quality gates, cleanup, and final validation

- [x] T055 [P] Run PHP-CS-Fixer on entire src/ and tests/ directories and fix all violations
- [x] T056 [P] Run PHPStan level 6 on src/ and fix all reported errors
- [x] T057 Run full test suite (`php bin/phpunit`) and verify all tests pass
- [x] T058 [P] Verify quickstart.md instructions work end-to-end on a clean checkout
- [x] T059 Generate final Doctrine migration if any entity changes occurred during implementation

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion — BLOCKS all user stories
- **User Story 1 (Phase 3)**: Depends on Foundational (Phase 2)
- **User Story 2 (Phase 4)**: Depends on Foundational (Phase 2), integrates with US1 (registration creates Profile)
- **User Story 3 (Phase 5)**: Depends on Foundational (Phase 2), uses Profile entity from US2
- **User Story 4 (Phase 6)**: Depends on Foundational (Phase 2), uses Profile from US2, directory from US3
- **Polish (Phase 7)**: Depends on all user stories being complete

### Within Each User Story

1. Tests MUST be written and FAIL before implementation
2. Entities/repositories before controllers
3. Form types before controllers
4. Controllers before templates
5. Core implementation before integration with other stories
6. Verify all story tests pass before moving to next story

### Parallel Opportunities

- Phase 1: T003, T004, T005, T006 can run in parallel
- Phase 2: T011, T012, T013 in parallel; T016, T017 in parallel
- Phase 3: T018, T019, T020 (tests) in parallel
- Phase 4: T027, T028 (tests) in parallel; T034, T035 (templates) in parallel
- Phase 5: T037, T038 (tests) in parallel
- Phase 6: T043, T044, T045 (tests) in parallel; T049, T050 (templates) in parallel
- Phase 7: T055, T056, T058 in parallel

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL — blocks all stories)
3. Complete Phase 3: User Story 1 (signup + login)
4. **STOP and VALIDATE**: Test User Story 1 independently
5. Deploy/demo if ready

### Incremental Delivery

1. Setup + Foundational → Foundation ready
2. Add User Story 1 → Test → Deploy/Demo (MVP!)
3. Add User Story 2 → Test → Deploy/Demo (profiles)
4. Add User Story 3 → Test → Deploy/Demo (discovery)
5. Add User Story 4 → Test → Deploy/Demo (follow system)
6. Each story adds value without breaking previous stories

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story is independently completable and testable
- Tests MUST fail before implementing (Constitution Principle I)
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
