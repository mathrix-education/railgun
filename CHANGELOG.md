# Changelog

## 7.1.1
- feat(braintree): implement Braintree subscription webhooks
- fix(graphql): flush container on HTTP 500
- fix(migration): force migration run even in production environment (fixes #411)

## 7.1.0
- feat(auth): add policy directive and users policy on `User` type. The following fields are now protected:
    - `firstname`
    - `lastname`
    - `email`
    - `permissions` (`users:read` permission)
    - `premium`
    - `premium_bypass`
    - `premium_bypass_expires_at`
    - `stripe_id`
    - `braintree_id`
    - `notifications`
    - `packs`
    - `trainings`
    - `sessions`
    - `statistics`
    - `subscriptions`
- feat(datetime): override the `DateTime` Lighthouse scalar by our own implementation which always uses the RFC3339 date
  format (fixes #385)
- feat(freeze): `frozen_at` field on Course, Exercise, Flashcard and Exam type
  `frozen_at` allows freezing models to their current state and requires the `{models}:freeze` permission to be set
  or bypassed (fixes #384 and #389, PR #386 and #391)
- feat(logging): rewrite exception trace logger
- feat(graphql): CreateNotion `slug` field is now required
- feat(graphql): CreateFlashcard `slug` field is now required
- feat(graphql): CreateSubject `slug` field is now required
- feat(graphql): `scopes` on type `User`, `CreateUser` and `UpdateUser` are now deprecated and will be removed in version
  8. Use `permissions` instead.
- feat(permissions): added permissions (fixes #390):
    - `chapters:write:visible`
    - `chapters:write:slug`
    - `courses:write:premium`
    - `courses:write:slug`
    - `courses:write:visible`
    - `courses:write:seo_index`
    - `courses:write:seo_title`
    - `courses:write:seo_keywords`
    - `courses:write:seo_description`
    - `exams:write:premium`
    - `exams:write:visible`
    - `exams:write:slug`
    - `exercises:write:visible`
    - `flashcards:write:premium`
    - `flashcards:write:slug`
    - `flashcards:write:visible`
    - `notions:write:slug`
    - `notions:write:visible`
    - `subjects:write:premium`
    - `subjects:write:visible`
- feat(permissions): implement premium and product directives
- fix(cache): empty request cache after each request
- fix(plans): use RFC3339 date format for `proration_anchor` field (fixes #385)
- docs(graphql): finish schema documentation**
- chore(mailjet): export laravel-mailjet to [mathieu-bour/laravel-mailjet](https://github.com/mathieu-bour/laravel-mailjet)
- chore(prometheus): drop unused UserCollector

## 7.0.1
- security(graphql): disable tracing and playground in production (#379)
- chore(deps): upgrade dependencies, bump to version 7.0.1
- chore(housekeeping): delete Buster migrations
- chore(housekeeping): remove Braintree

## 7.0.0
- Initial version of Railgun
