<?php

declare(strict_types=1);

namespace App\Http\Requests\Notification;

use App\Models\PushNotification;
use App\Support\Access\AccessControl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PushNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $audience = (string) $this->input('audience');

        return [
            // Kept short because browsers truncate both aggressively in the
            // native notification popup.
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'action_url' => ['nullable', 'string', 'max:500'],
            'audience' => ['required', Rule::in(PushNotification::audiences())],

            'roles' => [Rule::requiredIf($audience === PushNotification::AUDIENCE_ROLE), 'array'],
            'roles.*' => ['string', Rule::in(AccessControl::roles())],

            'user_ids' => [Rule::requiredIf($audience === PushNotification::AUDIENCE_USERS), 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],

            // At least one of the three is required for a location send,
            // enforced below rather than per-field so the message is clearer.
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('audience') !== PushNotification::AUDIENCE_LOCATION) {
                return;
            }

            if (! $this->filled('country_id') && ! $this->filled('state_id') && ! $this->filled('city_id')) {
                $validator->errors()->add('country_id', __('Pick at least a country, state or city for a location-based send.'));
            }
        });
    }

    /**
     * The audience selection, normalised into the shape stored on the model.
     *
     * @return array<string, mixed>
     */
    public function audienceFilters(): array
    {
        return match ($this->input('audience')) {
            PushNotification::AUDIENCE_ROLE => ['roles' => array_values($this->input('roles', []))],
            PushNotification::AUDIENCE_USERS => ['user_ids' => array_map('intval', $this->input('user_ids', []))],
            PushNotification::AUDIENCE_LOCATION => array_filter([
                'country_id' => $this->input('country_id'),
                'state_id' => $this->input('state_id'),
                'city_id' => $this->input('city_id'),
            ]),
            default => [],
        };
    }
}
