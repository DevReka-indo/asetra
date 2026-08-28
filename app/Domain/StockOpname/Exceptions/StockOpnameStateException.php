<?php

namespace App\Domain\StockOpname\Exceptions;

use App\Models\StockOpname;

final class StockOpnameStateException extends StockOpnameException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus = 409,
        array $context = [],
    ) {
        parent::__construct($message, $context);
    }

    public static function cannotAcceptFindings(StockOpname $session): self
    {
        return new self(
            'Sesi Stock Opname yang sudah selesai tidak dapat menerima temuan baru.',
            context: ['stock_opname_id' => $session->getKey(), 'status' => $session->status],
        );
    }

    public static function cannotReopen(StockOpname $session): self
    {
        return new self(
            'Sesi Stock Opname yang sudah selesai tidak dapat diaktifkan kembali.',
            context: ['stock_opname_id' => $session->getKey(), 'status' => $session->status],
        );
    }

    public static function mustBeCompleted(StockOpname $session): self
    {
        return new self(
            'Sesi Stock Opname harus diselesaikan sebelum dapat disinkronkan.',
            context: ['stock_opname_id' => $session->getKey(), 'status' => $session->status],
        );
    }

    public static function alreadySynchronized(StockOpname $session): self
    {
        return new self(
            'Sesi Stock Opname ini sudah pernah disinkronkan ke master aset.',
            context: ['stock_opname_id' => $session->getKey(), 'synced_at' => $session->synced_at],
        );
    }

    public static function findingAlreadyRecorded(StockOpname $session, int $assetId): self
    {
        return new self(
            'Aset ini sudah di-scan pada sesi Stock Opname ini.',
            httpStatus: 422,
            context: ['stock_opname_id' => $session->getKey(), 'aset_id' => $assetId],
        );
    }
}
