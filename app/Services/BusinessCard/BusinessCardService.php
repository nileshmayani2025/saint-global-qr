<?php

declare(strict_types=1);

namespace App\Services\BusinessCard;

use App\Models\BusinessCard;
use App\Models\User;
use App\Repositories\Contracts\BusinessCardRepositoryInterface;
use App\Services\CrudService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BusinessCardService extends CrudService
{
    public function __construct(BusinessCardRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * The user's card, created on first use so the editor always has a model
     * (and a share link) to work with.
     */
    public function forUser(User $user): BusinessCard
    {
        return BusinessCard::firstOrCreate(
            ['user_id' => $user->id],
            ['slug' => BusinessCard::newSlug(), 'status' => 'active'],
        );
    }

    /**
     * Issue a fresh public link, invalidating every copy already shared.
     */
    public function regenerateLink(BusinessCard $card): BusinessCard
    {
        $card->forceFill(['slug' => BusinessCard::newSlug()])->save();

        return $card;
    }

    /**
     * Replace or clear the card photo, deleting whatever it superseded.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function withPhoto(array $data, BusinessCard $card, ?UploadedFile $photo, bool $remove): array
    {
        $old = $card->photo_path;

        if ($photo) {
            $data['photo_path'] = $photo->store('business-cards', 'public');
        } elseif ($remove) {
            $data['photo_path'] = null;
        } else {
            return $data;
        }

        if ($old) {
            Storage::disk('public')->delete($old);
        }

        return $data;
    }

    public function forgetPhoto(BusinessCard $card): void
    {
        if ($card->photo_path) {
            Storage::disk('public')->delete($card->photo_path);
        }
    }
}
