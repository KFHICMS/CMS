<?php

namespace App\Http\Controllers\ChildOfficer;

use App\Http\Controllers\Controller;
use App\Models\QRCode;
use Illuminate\Http\Request;

class QRCodeController extends Controller
{
    public function scan()
    {
        return view('child_officer.qr.scan');
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $scannedValue = trim($request->token);

        /*
         * The existing KFHI QR code contains:
         *
         * /qr/scan/{token}
         *
         * Extract only the token from the scanned URL.
         */
        $path = parse_url($scannedValue, PHP_URL_PATH);

        if (!$path) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code format.',
            ], 400);
        }

        $prefix = '/qr/scan/';

        if (!str_starts_with($path, $prefix)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid KFHI QR code.',
            ], 400);
        }

        $token = substr($path, strlen($prefix));

        if ($token === '') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code token.',
            ], 400);
        }

        // Hash the original token exactly like QRCodeService.
        $tokenHash = hash('sha256', $token);

        // Find the active QR code.
        $qrCode = QRCode::with('child')
            ->where('token_hash', $tokenHash)
            ->where('status', 'active')
<<<<<<< HEAD
            ->first();

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive QR code.',
=======
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now()))
            ->first();

        if (!$qrCode || !$qrCode->child || $qrCode->child->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid, expired, or inactive QR code.',
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
            ], 404);
        }

        // Update last scanned time.
        $qrCode->update([
            'last_scanned_at' => now(),
        ]);

        $child = $qrCode->child;

        if (!$child) {
            return response()->json([
                'success' => false,
                'message' => 'Child record not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Child identified successfully.',

            'child' => [
                'id' => $child->id,

                'child_code' => $child->child_code,

                'full_name' => trim(
                    $child->first_name . ' ' .
                    ($child->middle_name ?? '') . ' ' .
                    $child->last_name
                ),

                'gender' => $child->gender,

                'date_of_birth' => $child->date_of_birth
                    ? $child->date_of_birth->format('Y-m-d')
                    : null,

                'school_name' => $child->school_name,

                'grade' => $child->grade,

                'district' => $child->district,

                'gn_division' => $child->gn_division,

                'status' => $child->status,
            ],
        ]);
    }
}