## version 7.0.0
### BREAKING CHANGES
- removed activities
- removed modules
- removed POST /chain
- removed GET /products/slug/{slug}: use GET /products/{slug}?key=slug instead
- removed GET /chapter/{chapterId}/notions: use GET /notions?chapter_id={chapterId} instead
- removed GET /countries/{countryId}/grades: use GET /grades?grade_id={gradeId} instead
- removed GET /categories/{categoryId}/posts: use GET /posts?category_id={categoryId} instead
- removed GET /notions/{notionId}/activities: use GET /activities?notion_id={notionId} instead
- removed GET /posts/latest: use GET /posts?order=-created_at
- changed posts: they now have their content in the field `content` as a JSON field
- changed POST /auth/forgot: now returns a HTTP/204 on success
- changed POST /auth/reset
    - token have now to be passed in `token` field
    - now returns a HTTP/204 on success
