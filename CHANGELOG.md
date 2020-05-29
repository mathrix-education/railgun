# Changelog

## 7.1.0
- feat(auth): add policy directive and users policy on `User` type. The following fields are now protected:
    - `firstname`
    - `lastname`
    - `email`
    - `scopes` (`users:read` permission)
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
- feat(datetime): override the `DateTime` Lighthouse scalar by our own implementation which always uses the ISO 8601
  "Zulu" date format (fixes #385)
- feat(freeze): `frozen_at` field on Course, Exercise, Flashcard and Exam type
  `frozen_at` allows freezing models to their current state and requires the `{models}:freeze` permission to be set
  or bypassed (fixes #384 and #389, PR #386 and #391)
- feat(logging): rewrite exception trace logger
- feat(graphql): CreateNotion `slug` field is now required
- feat(graphql): CreateFlashcard `slug` field is now required
- feat(graphql): CreateSubject `slug` field is now required
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
- fix(plans): use IS8601 date format for `proration_anchor` field (fixes #385)
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
