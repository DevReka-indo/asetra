---
paths:
  - 'app/Http/Controllers/*PengajuanPerbaikanController.php'
---

# Controllers

## Authorize repair management with manage_perbaikan_aset
Approval, rejection, completion, and full repair-management visibility use the manage_perbaikan_aset Gate ability. Superadmin is handled only by Gate::before(); role names, organization names, kode_bagian, isBagianUmum(), and isGeneralAffairs() must not authorize these actions.
