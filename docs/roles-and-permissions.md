# Roles and permissions

Superadmins manage accounts under **Users** and module access under **Roles & Permissions**.

- **Superadmin:** all modules, user accounts, and role permissions. This access cannot be restricted.
- **Admin:** initially all hotel-management modules. A superadmin can remove or restore individual module permissions.
- **Users:** initially dashboard access only. A superadmin can grant specific module permissions.

Permissions are per module and include its viewing and management actions, including create, update, and delete where applicable. They are enforced on every dashboard request and reflected in navigation and dashboard data. User administration and permission administration remain exclusive to Superadmin.

The three role identifiers are `superadmin`, `admin`, and `user`, preserving existing account roles. Accounts with unknown roles cannot access the dashboard. There is no public registration.

When creating a user, set a password of at least 12 characters and confirm it. Passwords are hashed and are never displayed again. On edit, leave the password blank to keep it. A password reset invalidates existing dashboard sessions and remember-me credentials. Inactive accounts cannot sign in or continue using the dashboard. A superadmin cannot deactivate or demote their own account.

Apply `php artisan migrate` when deploying to create and initialize the `roles` table. Existing user records are preserved. Permission edits apply on the next request without requiring another login.
