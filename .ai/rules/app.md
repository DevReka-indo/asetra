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

## Audit active Stock Opname corrections
StockOpnameDetail correction requires the manage_stock_opname Gate and is allowed only while the parent session is active, even for superadmin. Scope detail lookup through the parent session, limit changes to kondisi_temuan, lokasi_temuan, keterangan, and foto_temuan, and persist each real change with its revision in one DB transaction.

## Authorize active Stock Opname corrections by permission or ownership
StockOpnameDetail correction is allowed when the user has manage_stock_opname or the detail's dicek_oleh equals the authenticated user ID; Superadmin remains covered by Gate::before(). Scope details through the parent session and enforce active-session lifecycle separately, so no authorization path can edit a completed session. Only kondisi_temuan, lokasi_temuan, keterangan, and foto_temuan are correctable, with each real change and revision committed atomically.

## Audit active Stock Opname corrections
StockOpnameDetail correction is allowed when the user has manage_stock_opname or owns the finding through dicek_oleh matching their user ID; Superadmin is covered by Gate::before(). Scope detail lookup through the parent session, enforce active-session lifecycle separately for everyone, limit changes to kondisi_temuan, lokasi_temuan, keterangan, and foto_temuan, and persist each real change with its revision in one DB transaction.

## Correction ownership supersedes manager-only rule
Client clarification supersedes the earlier manager-only StockOpnameDetail correction rule: an ordinary participant may correct a detail only when dicek_oleh matches their authenticated user ID. manage_stock_opname and Superadmin may correct any detail, while completed sessions remain immutable for everyone.
