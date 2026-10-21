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
        return view('qr.scan');
    }
}