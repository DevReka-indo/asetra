---
paths:
  - 'app/**'
  - 'app/**/*StockOpname*.php'
---

# App

## Centralize custom RBAC through Laravel Gates
Keep ASETRA's custom permission tables. Register canonical permission abilities in AppServiceProvider and delegate them to User::hasPermission(). Gate::before() grants the global bypass only when role_id_role === 1; do not use role names or organization-name heuristics for new authorization decisions.

## Stock opname completion is terminal
Stock opname lifecycle is `aktif -> selesai`; completed sessions cannot accept findings or reopen. Synchronization is allowed only once for a completed session, records `synced_at`, and must go through `StockOpnameLifecycle` so row locks and transactions protect web/API consistently.
