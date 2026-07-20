{{--
    Cascading Country → State → City selects plus a free-text address.

    Expects: $locationOwner (any model with country_id/state_id/city_id/address),
             $countries, $statesByCountry, $citiesByState (see App\Support\Geo\LocationOptions).

    Pass $hideAddress = true when the three selects are used to target a group
    rather than to record someone's address.
--}}
@php
    $selectedCountry = (int) old('country_id', $locationOwner->country_id ?? 0);
    $selectedState = (int) old('state_id', $locationOwner->state_id ?? 0);
    $selectedCity = (int) old('city_id', $locationOwner->city_id ?? 0);
@endphp

{{-- Two columns until there is real room for three: at narrower widths the
     Select2 labels were being truncated to "— Select cou…". --}}
<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4" data-location-fields>
    <div>
        <label class="block text-sm font-medium mb-1.5">Country</label>
        <select name="country_id" data-location="country" class="w-full lux-field px-3.5 py-2.5">
            <option value="">Select country</option>
            @foreach ($countries as $c)
                <option value="{{ $c->id }}" @selected($selectedCountry === $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1.5">State</label>
        <select name="state_id" data-location="state" class="w-full lux-field px-3.5 py-2.5">
            <option value="">Select state</option>
            @foreach ($statesByCountry[$selectedCountry] ?? [] as $s)
                <option value="{{ $s['id'] }}" @selected($selectedState === $s['id'])>{{ $s['name'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="sm:col-span-2 xl:col-span-1">
        <label class="block text-sm font-medium mb-1.5">City</label>
        <select name="city_id" data-location="city" class="w-full lux-field px-3.5 py-2.5">
            <option value="">Select city</option>
            @foreach ($citiesByState[$selectedState] ?? [] as $c)
                <option value="{{ $c['id'] }}" @selected($selectedCity === $c['id'])>{{ $c['name'] }}</option>
            @endforeach
        </select>
    </div>
</div>

@unless ($hideAddress ?? false)
    <div>
        <label class="block text-sm font-medium mb-1.5">Address</label>
        <textarea name="address" rows="3" placeholder="Street, area, landmark, PIN code…"
                  class="w-full lux-field px-3.5 py-2.5">{{ old('address', $locationOwner->address ?? '') }}</textarea>
    </div>
@endunless

@once
    @push('scripts')
        <script>
            (function () {
                var data = @json(['states' => (object) $statesByCountry, 'cities' => (object) $citiesByState]);

                document.querySelectorAll('[data-location-fields]').forEach(function (root) {
                    var country = root.querySelector('[data-location="country"]');
                    var state = root.querySelector('[data-location="state"]');
                    var city = root.querySelector('[data-location="city"]');
                    if (!country || !state || !city) return;

                    // Select2 wraps these selects, so after swapping the <option>
                    // set we must tell it to redraw — a native change event alone
                    // only reaches our own listeners.
                    function notify(select) {
                        if (window.jQuery && window.jQuery(select).data('select2')) {
                            window.jQuery(select).trigger('change.select2');
                        }
                    }

                    function repopulate(select, items, placeholder) {
                        select.innerHTML = '';
                        var blank = new Option(placeholder, '');
                        select.appendChild(blank);

                        (items || []).forEach(function (item) {
                            select.appendChild(new Option(item.name, item.id));
                        });

                        select.value = '';
                        notify(select);
                    }

                    country.addEventListener('change', function () {
                        repopulate(state, data.states[country.value], 'Select state');
                        repopulate(city, [], 'Select city');
                    });

                    state.addEventListener('change', function () {
                        repopulate(city, data.cities[state.value], 'Select city');
                    });
                });
            })();
        </script>
    @endpush
@endonce
