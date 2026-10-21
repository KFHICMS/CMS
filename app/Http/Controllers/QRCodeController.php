<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\QRCode;
use App\Services\QRCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QRCodeController extends Controller
{
    public function generate(
        Child $child,
        QRCodeService $qrCodeService
    ): RedirectResponse {
        $child->qrCode()
            ->where('status', 'active')
            ->update([
                'status' => 'inactive',
            ]);

        $qrCode = $qrCodeService->generateForChild($child);

        return redirect()
            ->route('children.show', $child)
            ->with(
                'success',
                'QR code generated successfully.'
            );
    }

    public function show(Child $child): View
    {
        $qrCode = $child->qrCode;

        return view(
            'admin.qr_codes.show',
            compact('child', 'qrCode')
        );
    }

    public function scan(string $token): View
    {
<<<<<<< HEAD
        $tokenHash = hash('sha256', $token);

        $qrCode = QRCode::with('child')
            ->where('token_hash', $tokenHash)
            ->where('status', 'active')
            ->first();

        if (!$qrCode) {
            abort(404, 'Invalid or inactive QR code.');
        }

        $qrCode->update([
            'last_scanned_at' => now(),
        ]);

        $child = $qrCode->child;

        return view(
            'qr.scan',
            compact('child', 'qrCode')
        );
=======
        return view('qr.scan');
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
    }
}