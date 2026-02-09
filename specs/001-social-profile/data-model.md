# Data Model: Social Profile & Follow System

**Date**: 2026-02-08
**Feature Branch**: `001-social-profile`

## Entities

### User

The authentication identity. Implements Symfony's `UserInterface`
and `PasswordAuthenticatedUserInterface`.

| Field            | Type         | Constraints                    |
|------------------|--------------|--------------------------------|
| id               | integer (PK) | Auto-generated                |
| email            | string(180)  | Unique, not blank, valid email |
| password         | string(255)  | Hashed, min 8 chars raw input  |
| roles            | json         | Default: `["ROLE_USER"]`       |
| registeredAt     | datetime     | Set on creation                |

**Relationships**:
- One-to-one with Profile (owning side on Profile)

---

### Profile

Public-facing user information. Created during the profile wizard.

| Field              | Type         | Constraints                      |
|--------------------|--------------|----------------------------------|
| id                 | integer (PK) | Auto-generated                  |
| displayName        | string(50)   | Not blank, 2-50 characters       |
| bio                | text         | Nullable, max 500 characters     |
| photoFilename      | string(255)  | Nullable (default avatar if null) |
| wizardCompleted    | boolean      | Default: false                   |
| user               | User (FK)    | Unique, not null, cascade remove |

**Relationships**:
- Many-to-one with User (unique constraint = one-to-one)

---

### FollowRequest

A pending, approved, or rejected follow request between two users.

| Field        | Type         | Constraints                         |
|--------------|--------------|-------------------------------------|
| id           | integer (PK) | Auto-generated                     |
| requester    | User (FK)    | Not null                            |
| target       | User (FK)    | Not null                            |
| status       | string(10)   | Enum: pending, approved, rejected   |
| requestedAt  | datetime     | Set on creation                     |
| resolvedAt   | datetime     | Nullable, set on approve/reject     |

**Constraints**:
- Unique together: (requester, target) when status = "pending"
- requester != target (application-level check, FR-012)

**Relationships**:
- Many-to-one with User (as requester)
- Many-to-one with User (as target)

---

## State Transitions

### FollowRequest Status

```
[none] --send--> pending --approve--> approved
                         --reject---> rejected

rejected --re-request--> pending (new record)

approved --unfollow--> [record deleted]
```

- When a request is approved, the status changes to "approved"
  and the relationship is active.
- When a user unfollows, the approved FollowRequest record is
  deleted entirely.
- When a request is rejected, the requester may create a new
  pending request later (the rejected record is deleted to
  allow re-requesting).

## Design Decisions

- **No separate Follow entity**: The spec lists Follow as a
  distinct entity, but using FollowRequest with status=approved
  is simpler and avoids data duplication. The "Follow"
  relationship is simply a FollowRequest where status=approved.
  This aligns with Constitution Principle III (Simplicity).

- **Profile as separate entity**: Although simpler to embed
  profile fields on User, separating them keeps the User entity
  focused on authentication (Symfony convention) and Profile
  focused on public display data.

- **Default avatar**: Handled at the template/view layer. When
  `photoFilename` is null, the template renders a default
  avatar image. No database default needed.
