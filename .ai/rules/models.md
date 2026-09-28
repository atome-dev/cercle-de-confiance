---
paths:
  - 'app/Models/Thread.php,app/Actions/CreateThreadWithMessage.php,app/Actions/ShareThread.php,app/Models/ThreadKeyGrant.php'
---

# Models

## Thread access is grant-based, not role-based
Access to a Thread is decided solely by the existence of a ThreadKeyGrant row for that (thread, user) pair — never by hasRole(). Roles (parent/professeur/administrateur) only govern: (1) who is auto-granted on a "group" thread at creation (all current `parent` users, falling back to all `administrateur` users only if zero parents exist), and (2) page-level access to /dossiers. Any grantee (any role) can share a thread with any other user via ShareThread — no role restriction on who shares or who receives. Removing someone's role does NOT revoke grants they already hold; revocation is explicit (delete the ThreadKeyGrant row). administrateur has no automatic grant on new threads — must be shared explicitly, exactly like professeur. Each grant seals its own copy of the thread key via ThreadEncryptionService::sealForApp() — this is per-recipient revocable-by-row, not a single shared envelope.
