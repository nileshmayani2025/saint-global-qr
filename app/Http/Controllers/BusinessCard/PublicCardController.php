<?php

declare(strict_types=1);

namespace App\Http\Controllers\BusinessCard;

use App\Http\Controllers\Controller;
use App\Models\BusinessCard;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The public face of a business card. No authentication — the whole point is
 * that the owner can hand the link to a customer — but the slug is a 20-char
 * random string, so cards cannot be enumerated, and an inactive card 404s.
 */
class PublicCardController extends Controller
{
    public function show(string $slug): View
    {
        $card = $this->resolve($slug);

        return view('cards.show', ['card' => $card, 'owner' => $card->user]);
    }

    /**
     * "Save to contacts" — a vCard the phone's address book understands.
     */
    public function vcard(string $slug): StreamedResponse
    {
        $card = $this->resolve($slug);
        $filename = (Str::slug((string) $card->user?->name) ?: 'contact').'.vcf';

        return response()->streamDownload(
            fn () => print $card->toVCard(),
            $filename,
            ['Content-Type' => 'text/vcard; charset=utf-8'],
        );
    }

    /**
     * QR pointing back at this card, for printing or showing on screen.
     */
    public function qr(string $slug): Response
    {
        $card = $this->resolve($slug);

        $png = (new PngWriter())->write(new QrCode(
            data: $card->publicUrl(),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 420,
            margin: 16,
        ));

        return response($png->getString(), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * A card is only reachable while it is active and its owner is active —
     * a suspended account should not keep publishing contact details.
     */
    private function resolve(string $slug): BusinessCard
    {
        $card = BusinessCard::query()
            ->active()
            ->with(['user' => fn ($q) => $q->with('city:id,name', 'state:id,name', 'country:id,name', 'company:id,name')])
            ->where('slug', $slug)
            ->first();

        abort_if($card === null || $card->user === null || ! $card->user->isActive(), 404);

        return $card;
    }
}
