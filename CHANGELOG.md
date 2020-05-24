# Changelog

## 7.1.0
- feat(freeze): `frozen_until` field on Course, Exercise, Flashcard and Exam type
  `frozen_until` allows freezing models to their current state and requires the `{models}:freeze` permission to be set
  or bypassed (fixes #384, PR #386)
- feat(datetime): override the `DateTime` Lighthouse scalar by our own implementation which always uses the ISO 8601
  "Zulu" date format (fixes #385)
- fix(plans): use IS8601 date format for `proration_anchor` field (fixes #385)

## 7.0.1
- security(graphql): disable tracing and playground in production (#379)
- chore(deps): upgrade dependencies, bump to version 7.0.1
- chore(housekeeping): delete Buster migrations
- chore(housekeeping): remove Braintree

## 7.0.0
- Initial version of Railgun
