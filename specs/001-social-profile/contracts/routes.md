# Route Contracts: Social Profile & Follow System

**Date**: 2026-02-08
**Feature Branch**: `001-social-profile`

All routes use Symfony's standard form-based request/response
pattern (server-rendered HTML). No JSON API endpoints.

## Authentication Routes

| Route               | Method | Controller Action    | Auth   | Description                    |
|---------------------|--------|----------------------|--------|--------------------------------|
| `/register`         | GET    | Registration::index  | Anon   | Show signup form               |
| `/register`         | POST   | Registration::index  | Anon   | Process signup form            |
| `/login`            | GET    | Security::login      | Anon   | Show login form                |
| `/login`            | POST   | Security::login      | Anon   | Process login (form_login)     |
| `/logout`           | GET    | Security::logout     | User   | End session (handled by fw)    |

## Profile Routes

| Route               | Method | Controller Action     | Auth   | Description                    |
|---------------------|--------|-----------------------|--------|--------------------------------|
| `/profile/wizard`   | GET    | Profile::wizard       | User   | Show profile wizard form       |
| `/profile/wizard`   | POST   | Profile::wizard       | User   | Save wizard data               |
| `/profile/edit`     | GET    | Profile::edit         | User   | Show profile edit form         |
| `/profile/edit`     | POST   | Profile::edit         | User   | Save profile changes           |
| `/profile/{id}`     | GET    | Profile::show         | User   | View a user's public profile   |

## User Directory Routes

| Route               | Method | Controller Action     | Auth   | Description                    |
|---------------------|--------|-----------------------|--------|--------------------------------|
| `/users`            | GET    | User::directory       | User   | Paginated user list with search |

**Query parameters**:
- `page` (int, default: 1) — Page number
- `q` (string, optional) — Search by display name

## Follow Routes

| Route                       | Method | Controller Action      | Auth   | Description                   |
|-----------------------------|--------|------------------------|--------|-------------------------------|
| `/follow/{userId}`          | POST   | Follow::request        | User   | Send follow request           |
| `/unfollow/{userId}`        | POST   | Follow::unfollow       | User   | Remove follow relationship    |
| `/follow-requests`          | GET    | Follow::pendingList    | User   | List pending requests         |
| `/follow-requests/{id}/approve` | POST | Follow::approve     | User   | Approve a follow request      |
| `/follow-requests/{id}/reject`  | POST | Follow::reject      | User   | Reject a follow request       |
| `/followers`                | GET    | Follow::followers      | User   | List current user's followers  |
| `/following`                | GET    | Follow::following      | User   | List users current user follows |

## Response Behaviors

- **Successful form submissions**: Redirect with flash message.
- **Validation errors**: Re-render form with error messages.
- **Unauthorized access**: Redirect to `/login`.
- **Follow self attempt (FR-012)**: Redirect back with error
  flash message.
- **Duplicate pending request**: Redirect back with info flash
  message (request already pending).

## Template Structure

```
templates/
├── base.html.twig
├── registration/
│   └── register.html.twig
├── security/
│   └── login.html.twig
├── profile/
│   ├── wizard.html.twig
│   ├── edit.html.twig
│   └── show.html.twig
├── user/
│   └── directory.html.twig
└── follow/
    ├── pending.html.twig
    ├── followers.html.twig
    └── following.html.twig
```
