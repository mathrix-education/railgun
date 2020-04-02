---
name: GraphQL Bug Report
about: Report an inconsistency on our GraphQL API
title: Query|Mutation [name] ...
labels: 'Environment: v7, Service: GraphQL, Type: Bug'
assignees: mathieu-bour

---

**Description**
Example: got 500 model_not_found (see screenshot)

**GraphQL Payload**
```graphql
query {
  me {
    id
  }
}
```

**Screenshots**
If applicable, add screenshots to help explain your problem.

**Additional context**
Add any other context about the problem here.
