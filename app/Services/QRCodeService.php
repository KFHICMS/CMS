<?php

namespace App\Services;

use App\Models\Child;
use App\Models\QRCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QRCodeService
{
    public function generateForChild(Child $child): QRCode
    {
        // Generate a secure random token.
        $token = Str::random(64);

        // Store only the SHA-256 hash in the database.
        $tokenHash = hash('sha256', $token);

        // URL encoded inside the QR code.
        $scanUrl = url('/qr/scan/' . $token);

        // Generate QR code as SVG.
        $renderer = new ImageRenderer(
            new RendererStyle(400, 2),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        $qrImage = $writer->writeString($scanUrl);

        // Store QR image.
        $imagePath = 'qrcodes/' . $child->child_code . '_' . time() . '.svg';

        Storage::disk('public')->put($imagePath, $qrImage);

        // Save QR information.
        return QRCode::create([
            'child_id' => $child->id,
            'token_hash' => $tokenHash,
            'qr_image_path' => $imagePath,
            'status' => 'active',
            'issued_at' => now(),
            'expires_at' => null,
        ]);
    }
}