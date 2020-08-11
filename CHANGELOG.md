# Changelog

## 11.0.1
### Bugfixes
- **graphql**: remove results duplicates (#487)

## 11.0.0
### Breaking changes
- **exercises**: collapse `questions`, `choices`, `solutions` and `explanations` to `content` in `Exercise` type
- **exercises**: delete type-dependent exercise types
  - `createMCQExercise` and `createProofExercise` mutations are replaced by `createExercise`
  - `updateMCQExercise` and `updateProofExercise` mutations are replaced by `updateExercise`
  - `MCQExercise` and `ProofExercise` types are replaced by `Exercise`
  - `MCQExercise | ProofExercise` is replaced by `Exercise` in `Commentable`, `Notable` annd `Reportable` unions 
- **plans**: deleted `prorated`, `proration_price` and `proration_anchor` from `Plan` type

### Bugfixes
- **lighthouse**: forget `GraphQLRequest` on request teardown

## 10.0.1
### Bug fixes
- **ppm**: set request max size to 100MB

## 10.0.0
### Features
- **docker**: add docker-compose.yaml file to run a local instance

### Breaking changes
- **flashcards**: combine flashcards `fronts` and `backs` into `content` on:
  - `Flashcard` type
  - `CreateFlashcardInput` input
  - `UpdateFlashcardInput` input
- **flashcards**: drop flashcards `slug` on:
  - `Flashcard` type
  - `CreateFlashcardInput` input
  - `UpdateFlashcardInput` input

### Continuous integration
- **actions**: bump `setup-php` to version 2.4.1

## 9.1.0
- **chapters**: add `redirect` column

## 9.0.1
### Bug fixes
- **container**: do not flush containers on HTTP 500 codes

## 9.0.0
### Features
- **railgun**: add version action to `/`

### Breaking changes
- **notions**: delete `slug` field from `Notion` type and `CreateNotion` and `UpdateNotion` attributes (closes #441)
- **playground**: move playground from `/` to `/playground`
- **permissions**: delete the `notions:write:slug` permission
- **themes**: delete `slug` field from `Theme` type and `CreateTheme` and `UpdateTheme` attributes (closes #441)

### Bug fixes
- **comments**: guard createComment and updateComment mutations (fixes #462)

### Refactors
- **graphql**: rename $rootValue to $_ in Lighthouse GraphQL resolvers (closes #455)

### Tests
- **interactions**: stabilize InteractionTest::testGetChapter (closes #457)
- **interactions**: stabilize Interactable::interactions() converage (closes #451)
- **subjects**: stabilize Subject::exercises() converage (closes #453)

### Continuous integration
- **actions**: rename master branch to main (closes #435)
- **repo**: rename repo from `education-api` to `railgun` (closes #434)

## Docs
- **license**: update license to [Mathrix Tech Internal License](https://github.com/mathrix-education/mathrix-tech-license) (closes #452)
- **readme**: add badges

## Chores
- **deps**: bump phpredis extension from version 5.2.1 to version 5.3.1

## 8.1.0
### Features
- **google**: implement login with auth code

### Bug fixes
- **google**: write google id in user metadata

### Continuous integration
- **actions**: add images latest and version tags

## 8.0.1
### Continuous integration
- **actions**: update workflow
- **actions**: fix container name
- **actions**: do not deploy on pull_request

### Chores
- delete mapping

## 8.0.0
### Features
- **themes**: implements themes, which now wraps chapters into the grade/discipline tuple
    - `Theme` type
    - `CreateTheme` and `UpdateTheme` inputs
    - `listThemes` query (replaces `listChapters` query)
    - `readTheme` query:
    - `createTheme` mutation
    - `updateTheme` mutation
    - Chapters default theme is "Défaut (Grade/Discipline)"

### Breaking changes
- **chapters**: update `Chapter` type
    - added `theme` field
    - removed `grade` and `discipline` fields (they are now accessible through `theme.grade` and `theme.discipline`)
- **chapters**: `CreateChapter` and `UpdateChapter` inputs:
    - added `theme_id` (mandatory on `CreateChapter`)
    - removed `grade_id` and `discipline_id`
- **chapters**: removed `listChapters` query (replaced by `listThemes`)

## 7.2.1
### Features
- **comments**: allow users to edit their comments

### Bug fixes
- **search**: hide invisible objects from results

## 7.2.0
### Features
- **exercise**: add exercise `proof` type

### Bug fixes
- **prometheus**: GraphQL collectors

## 7.1.2
### Bug fixes
- **braintree**: ensure that the message is always defined
- **http**: flush container properly

## 7.1.1
### New features
- **braintree**: implement Braintree subscription webhooks

### Bug fixes
- **graphql**: flush container on HTTP 500
- **migration**: force migration run even in production environment (fixes #411)

## 7.1.0
### New features
- **auth**: add policy directive and users policy on `User` type. The following fields are now protected:
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
- **datetime**: override the `DateTime` Lighthouse scalar by our own implementation which always uses the RFC3339 date
  format (fixes #385)
- **freeze**: `frozen_at` field on Course, Exercise, Flashcard and Exam type
  `frozen_at` allows freezing models to their current state and requires the `{models}:freeze` permission to be set
  or bypassed (fixes #384 and #389, PR #386 and #391)
- **logging**: rewrite exception trace logger
- **graphql**: CreateNotion `slug` field is now required
- **graphql**: CreateFlashcard `slug` field is now required
- **graphql**: CreateSubject `slug` field is now required
- **graphql**: `scopes` on type `User`, `CreateUser` and `UpdateUser` are now deprecated and will be removed in version
  8. Use `permissions` instead.
- **permissions**: added permissions (closes #390):
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
- **permissions**: implement premium and product directives

### Bug fixes
- **cache**: empty request cache after each request
- **plans**: use RFC3339 date format for `proration_anchor` field (fixes #385)

### Docs
- **graphql**: finish schema documentation

## Chores
- **mailjet**: export laravel-mailjet to [mathieu-bour/laravel-mailjet](https://github.com/mathieu-bour/laravel-mailjet)
- **prometheus**: drop unused UserCollector

## 7.0.1
### Security fixes
- **graphql**: disable tracing and playground in production (#379)

### Chores
- **deps**: upgrade dependencies, bump to version 7.0.1
- **housekeeping**: delete Buster migrations
- **housekeeping**: remove Braintree

## 7.0.0
### New features
- Initial version of Railgun
