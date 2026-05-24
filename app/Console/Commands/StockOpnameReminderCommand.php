<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\DataAset;
use App\Models\User;
use App\Notifications\SystemNotification;
use Carbon\Carbon;

class StockOpnameReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock-opname:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automatic notifications to asset PICs/PJs and GA/Superadmin 3 days and 1 day before stock opname deadline';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $activeOpnames = StockOpname::where('status', 'aktif')->get();
        if ($activeOpnames->isEmpty()) {
            $this->info('No active Stock Opname periods found.');
            return;
        }

        foreach ($activeOpnames as $opname) {
            $deadline = Carbon::parse($opname->tanggal_berakhir)->startOfDay();
            $today = Carbon::today();
            $daysLeft = (int)$today->diffInDays($deadline, false);

            $this->info("Stock Opname: {$opname->periode} | Deadline: {$opname->tanggal_berakhir} | Days left: {$daysLeft}");

            // Notify if exactly 3 days or 1 day left
            if ($daysLeft === 3 || $daysLeft === 1) {
                // Get all assets
                $allAsets = DataAset::all();
                $totalAset = $allAsets->count();

                // Get already checked asset ids for this opname period
                $checkedAsetIds = StockOpnameDetail::where('stock_opname_id', $opname->id)
                    ->pluck('aset_id')
                    ->toArray();

                // Get unchecked assets
                $uncheckedAsets = $allAsets->whereNotIn('id', $checkedAsetIds);
                $uncheckedCount = $uncheckedAsets->count();

                if ($uncheckedCount > 0) {
                    $title = 'Pemberitahuan Stock Opname';
                    $formattedDeadline = $deadline->format('d M Y');

                    // Map users to their unchecked assets to prevent notification spamming
                    $userAssets = [];
                    foreach ($uncheckedAsets as $aset) {
                        $recipientIds = array_filter(array_unique([$aset->pic_id, $aset->penanggung_jawab_id]));
                        foreach ($recipientIds as $userId) {
                            if (!isset($userAssets[$userId])) {
                                $userAssets[$userId] = [];
                            }
                            $userAssets[$userId][] = $aset;
                        }
                    }

                    // Notify each User/PIC/PJ
                    foreach ($userAssets as $userId => $assets) {
                        $user = User::find($userId);
                        if ($user) {
                            $count = count($assets);
                            $message = "Anda memiliki {$count} aset yang belum dilakukan stock opname. Batas akhir {$daysLeft} hari lagi ({$formattedDeadline}).";
                            $url = route('stock-opname.user-show', $opname->id);
                            
                            $user->notify(new SystemNotification($title, $message, $url, 'stock_opname'));
                            $this->info("Notified User ID: {$userId} for {$count} unchecked assets.");
                        }
                    }

                    // Notify all GA & Superadmins
                    $adminGas = User::where('role_id_role', 1)->get()->merge(
                        User::all()->filter(fn($u) => $u->isGeneralAffairs())
                    )->unique('id');

                    $summaryTitle = 'Progress Laporan Stock Opname';
                    $summaryMsg = "Terdapat {$uncheckedCount} dari {$totalAset} aset yang belum dilakukan stock opname. Batas akhir {$daysLeft} hari lagi.";
                    $summaryUrl = route('stock-opname.show', $opname->id);

                    foreach ($adminGas as $adminGa) {
                        $adminGa->notify(new SystemNotification($summaryTitle, $summaryMsg, $summaryUrl, 'stock_opname'));
                    }
                    $this->info("Notified General Affairs & Superadmin users.");
                } else {
                    $this->info("All assets checked for stock opname {$opname->periode}.");
                }
            } else {
                $this->info('No notifications needed for today based on days left.');
            }
        }
    }
}
