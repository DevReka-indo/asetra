<?php

namespace App\Domain\StockOpname;

use App\Domain\StockOpname\Exceptions\StockOpnameStateException;
use App\Models\AsetFoto;
use App\Models\DataAset;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockOpnameDetailRevision;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class StockOpnameLifecycle
{
    /**
     * @var list<string>
     */
    private const CORRECTABLE_FINDING_FIELDS = [
        'kondisi_temuan',
        'lokasi_temuan',
        'deskripsi_temuan',
        'foto_temuan',
    ];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(StockOpname $session, array $attributes): StockOpname
    {
        return DB::transaction(function () use ($session, $attributes): StockOpname {
            $lockedSession = $this->lockSession($session);
            $requestedStatus = $attributes['status'] ?? $lockedSession->status;

            if ($lockedSession->isCompleted() && $requestedStatus === 'aktif') {
                throw StockOpnameStateException::cannotReopen($lockedSession);
            }

            $lockedSession->update($attributes);

            return $lockedSession->fresh();
        }, 3);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function recordFinding(StockOpname $session, array $attributes): StockOpnameDetail
    {
        return DB::transaction(function () use ($session, $attributes): StockOpnameDetail {
            $lockedSession = $this->lockSession($session);

            if (! $lockedSession->canAcceptFindings()) {
                throw StockOpnameStateException::cannotAcceptFindings($lockedSession);
            }

            $assetId = (int) $attributes['aset_id'];
            $alreadyRecorded = StockOpnameDetail::query()
                ->where('stock_opname_id', $lockedSession->id)
                ->where('aset_id', $assetId)
                ->exists();

            if ($alreadyRecorded) {
                throw StockOpnameStateException::findingAlreadyRecorded($lockedSession, $assetId);
            }

            if (! array_key_exists('deskripsi_temuan', $attributes)) {
                $attributes['deskripsi_temuan'] = DataAset::query()
                    ->whereKey($assetId)
                    ->value('deskripsi');
            }

            return $lockedSession->detail()->create($attributes);
        }, 3);
    }

    public function synchronize(StockOpname $session): StockOpname
    {
        return DB::transaction(function () use ($session): StockOpname {
            $lockedSession = $this->lockSession($session);

            if (! $lockedSession->isCompleted()) {
                throw StockOpnameStateException::mustBeCompleted($lockedSession);
            }

            if ($lockedSession->isSynchronized()) {
                throw StockOpnameStateException::alreadySynchronized($lockedSession);
            }

            $details = $lockedSession->detail()->orderBy('id')->get();

            foreach ($details as $detail) {
                $asset = DataAset::query()->lockForUpdate()->find($detail->aset_id);

                if ($asset === null) {
                    continue;
                }

                $asset->status_kondisi = $detail->kondisi_temuan;

                if ($detail->kondisi_temuan === 'Hilang') {
                    $asset->status_aset = 'Hilang';
                }

                if (is_numeric($detail->lokasi_temuan)) {
                    $asset->lokasi_id = $detail->lokasi_temuan;
                }

                if (filled($detail->deskripsi_temuan) && $detail->deskripsi_temuan !== $asset->deskripsi) {
                    $asset->deskripsi = $detail->deskripsi_temuan;
                }

                $asset->save();

                $this->synchronizeFindingPhoto($lockedSession, $detail, $asset);
            }

            $lockedSession->update(['synced_at' => now()]);

            return $lockedSession->fresh();
        }, 3);
    }

    /**
     * @return Collection<int, string>
     */
    public function delete(StockOpname $session): Collection
    {
        return DB::transaction(function () use ($session): Collection {
            $lockedSession = $this->lockSession($session);

            if ($lockedSession->isCompleted()) {
                throw StockOpnameStateException::cannotDeleteCompleted($lockedSession);
            }

            $findingPhotoPaths = $lockedSession->detail()
                ->whereNotNull('foto_temuan')
                ->pluck('foto_temuan');

            $lockedSession->detail()->delete();
            $lockedSession->delete();

            return $findingPhotoPaths;
        }, 3);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function correctFinding(
        StockOpname $session,
        StockOpnameDetail $detail,
        User $changedBy,
        array $attributes,
    ): ?StockOpnameDetailRevision {
        return DB::transaction(function () use ($session, $detail, $changedBy, $attributes): ?StockOpnameDetailRevision {
            $lockedSession = $this->lockSession($session);

            if (! $lockedSession->isActive()) {
                throw StockOpnameStateException::cannotCorrectCompleted($lockedSession);
            }

            $lockedDetail = $lockedSession->detail()
                ->lockForUpdate()
                ->findOrFail($detail->getKey());

            $lockedDetail->fill(Arr::only($attributes, self::CORRECTABLE_FINDING_FIELDS));
            $afterValues = Arr::only($lockedDetail->getDirty(), self::CORRECTABLE_FINDING_FIELDS);

            if ($afterValues === []) {
                return null;
            }

            $beforeValues = [];
            foreach (array_keys($afterValues) as $field) {
                $beforeValues[$field] = $lockedDetail->getOriginal($field);
            }

            $lockedDetail->save();

            return $lockedDetail->revisions()->create([
                'changed_by' => $changedBy->getKey(),
                'before_values' => $beforeValues,
                'after_values' => $afterValues,
            ]);
        }, 3);
    }

    private function lockSession(StockOpname $session): StockOpname
    {
        return StockOpname::query()->lockForUpdate()->findOrFail($session->getKey());
    }

    private function synchronizeFindingPhoto(
        StockOpname $session,
        StockOpnameDetail $detail,
        DataAset $asset,
    ): void {
        if ($detail->foto_temuan === null) {
            return;
        }

        $photoExists = AsetFoto::query()
            ->where('aset_id', $asset->id)
            ->where('path_foto', $detail->foto_temuan)
            ->exists();

        if ($photoExists) {
            return;
        }

        AsetFoto::query()->create([
            'aset_id' => $asset->id,
            'path_foto' => $detail->foto_temuan,
            'urutan' => ($asset->foto()->max('urutan') ?? 0) + 1,
            'keterangan' => 'Sinkronisasi dari Stock Opname: '.($session->periode ?? 'Temuan'),
        ]);
    }
}
