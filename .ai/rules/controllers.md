---
paths:
  - 'app/Http/Controllers/*PengajuanPerbaikanController.php'
  - 'app/Http/Controllers/**/*Perbaikan*Controller.php'
---

# Controllers

## Authorize repair management with manage_perbaikan_aset
Approval, rejection, completion, and full repair-management visibility use the manage_perbaikan_aset Gate ability. Superadmin is handled only by Gate::before(); role names, organization names, kode_bagian, isBagianUmum(), and isGeneralAffairs() must not authorize these actions.

## Repair submissions use asset object scope
Creating a repair request is authorized by the `submit_repair_for_asset` Gate. Non-superadmins may submit only for assets in `DataAset::forUser()` or assets assigned to them through `pic_id`; `manage_perbaikan_aset` does not expand submission scope. Web and API must enforce this same Gate before uploads or database mutations.
