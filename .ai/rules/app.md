---
paths:
  - 'app/**'
---

# App

## Centralize custom RBAC through Laravel Gates
Keep ASETRA's custom permission tables. Register canonical permission abilities in AppServiceProvider and delegate them to User::hasPermission(). Gate::before() grants the global bypass only when role_id_role === 1; do not use role names or organization-name heuristics for new authorization decisions.
