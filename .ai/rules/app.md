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

## Authorize stock opname management through Gate
All stock-opname administrative actions use the manage_stock_opname Gate ability. Ordinary participation remains authenticated without that ability and is limited to DataAset::forUser() or PIC assignment; manage_stock_opname implies unrestricted execution scope, matching the prior manager behavior.

## Preserve finalized stock opname records
Completed (`selesai`) stock-opname sessions are historical records and cannot be deleted, including by Superadmin. Gate bypass affects authorization only; lifecycle state rules remain mandatory. Active sessions retain legacy deletion behavior, including deletion of findings and related finding photos.
