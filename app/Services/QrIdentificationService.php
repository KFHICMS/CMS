<?php

namespace App\Services;

use App\Models\Child;
use App\Models\QRCode;

class QrIdentificationService
{
    /**
     * Resolve a scanned QR URL/token or a typed child code to an active child.
     *
     * @return array{child: ?Child, method: ?string, reason: ?string, message: ?string, status: int}
     */
    public function resolve(?string $token, ?string $childCode): array
    {
        $qrCode = null;

        if (filled($token)) {
            $path = parse_url($token, PHP_URL_PATH);
            $prefix = '/qr/scan/';

            if (! is_string($path) || ! str_starts_with($path, $prefix)) {
                return $this->failure('unrecognised QR format', 'Use a valid KFHI child QR code.', 422);
            }

            $plain = substr($path, strlen($prefix));
            if (! preg_match('/^[A-Za-z0-9]{64}$/D', $plain)) {
                return $this->failure('malformed QR token', 'The QR code token is invalid.', 422);
            }

            $qrCode = QRCode::query()
                ->with('child')
                ->where('token_hash', hash('sha256', $plain))
                ->where('status', 'active')
                ->where(fn ($query) => $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now()))
                ->first();

            if (! $qrCode || ! $qrCode->child) {
                return $this->failure(
                    'QR code invalid, expired, or revoked',
                    'This QR code is invalid, expired, or revoked.',
                    404
                );
            }

            $child = $qrCode->child;
            $method = 'qr';
        } else {
            $child = Child::query()
                ->where('child_code', $childCode)
                ->where('status', 'active')
                ->first();
            $method = 'manual';
        }

        if (! $child || $child->status !== 'active') {
            return $this->failure(
                $qrCode ? 'child inactive' : 'child code not found or inactive',
                'No active child was found for that identifier.',
                404
            );
        }

        if ($qrCode) {
            $qrCode->update(['last_scanned_at' => now()]);
        }

        return ['child' => $child, 'method' => $method, 'reason' => null, 'message' => null, 'status' => 200];
    }

    private function failure(string $reason, string $message, int $status): array
    {
        return ['child' => null, 'method' => null, 'reason' => $reason, 'message' => $message, 'status' => $status];
    }
}