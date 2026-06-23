@php
    $latStatePath = $latStatePath ?? 'data.latitude';
    $lngStatePath = $lngStatePath ?? 'data.longitude';
    $addressStatePath = $addressStatePath ?? 'data.address';
    $mapId = 'osm-map-' . \Illuminate\Support\Str::random(8);
@endphp

<div
    x-data="osmPicker({
        mapId: '{{ $mapId }}',
        lat: @entangle($latStatePath).live,
        lng: @entangle($lngStatePath).live,
        address: @entangle($addressStatePath).live,
        latStatePath: @js($latStatePath),
        lngStatePath: @js($lngStatePath),
        addressStatePath: @js($addressStatePath),
        apiKey: @js((string) config('api.mobile_key')),
        messages: {
            searching: @js(__('admin.map.searching')),
            choose_result: @js(__('admin.map.choose_result')),
            no_results: @js(__('admin.map.no_results')),
            search_failed: @js(__('admin.map.search_failed')),
            reverse_failed: @js(__('admin.map.reverse_failed')),
            selected: @js(__('admin.map.selected')),
        },
    })"
    x-init="init()"
    class="w-full space-y-2"
>
    <div class="relative z-[1000]" style="display: flex; align-items: flex-end; gap: 0.5rem; padding-bottom: 0.5rem;">
        <div style="flex: 1 1 auto;">
            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    :placeholder="__('admin.map.search_placeholder')"
                    x-model="searchQuery"
                    x-bind:disabled="isSearching"
                    x-on:keydown.enter.prevent="searchAddress()"
                />
            </x-filament::input.wrapper>
        </div>
        <x-filament::button
            type="button"
            x-bind:disabled="isSearching"
            x-on:click="searchAddress()"
            x-text="isSearching ? messages.searching : @js(__('admin.map.search_button'))"
        >
            {{ __('admin.map.search_button') }}
        </x-filament::button>
    </div>
    <div
        x-show="searchResults.length > 0"
        x-cloak
        class="rounded-lg border border-gray-200 bg-white p-2 shadow-sm"
        style="max-height: 200px; overflow-y: auto;"
    >
        <template x-for="(result, index) in searchResults" :key="index">
            <button
                type="button"
                class="w-full rounded px-2 py-1.5 text-left text-sm hover:bg-gray-50"
                x-on:click="selectSearchResult(result)"
                x-text="result.display_name"
            ></button>
        </template>
    </div>
    <p
        x-show="statusMessage"
        x-cloak
        x-text="statusMessage"
        class="text-xs"
        x-bind:class="{
            'text-gray-500': statusType === 'info',
            'text-red-600': statusType === 'error',
            'text-green-600': statusType === 'success',
        }"
    ></p>
    <div id="{{ $mapId }}" class="w-full rounded-lg border" style="height: 320px; margin-top: 0.25rem;"></div>
    <p class="text-xs text-gray-500">{{ __('admin.map.hint') }}</p>
</div>

<script>
    if (!window.__osmPickerLoaded) {
        window.__osmPickerLoaded = true;

        window.__loadLeaflet = (callback) => {
            if (window.L) {
                callback();
                return;
            }

            if (!document.getElementById('leaflet-css')) {
                const link = document.createElement('link');
                link.id = 'leaflet-css';
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(link);
            }

            const scriptId = 'leaflet-js';
            if (document.getElementById(scriptId)) {
                document.getElementById(scriptId).addEventListener('load', callback);
                return;
            }

            const script = document.createElement('script');
            script.id = scriptId;
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.onload = callback;
            document.body.appendChild(script);
        };

        window.osmPicker = function ({
            mapId,
            lat,
            lng,
            address,
            latStatePath,
            lngStatePath,
            addressStatePath,
            apiKey,
            messages,
        }) {
            return {
                map: null,
                marker: null,
                lat,
                lng,
                address,
                latStatePath: latStatePath || '',
                lngStatePath: lngStatePath || '',
                addressStatePath: addressStatePath || '',
                apiKey: apiKey || '',
                messages: messages || {},
                searchQuery: '',
                searchResults: [],
                isSearching: false,
                statusMessage: '',
                statusType: 'info',
                init() {
                    window.__loadLeaflet(() => this.initMap());

                    this.$watch('lat', () => this.syncMarker());
                    this.$watch('lng', () => this.syncMarker());
                },
                parseNumber(value) {
                    if (value === null || value === undefined || value === '') {
                        return null;
                    }
                    const normalized = typeof value === 'string' ? value.replace(',', '.') : value;
                    const numberValue = Number(normalized);
                    return Number.isFinite(numberValue) ? numberValue : null;
                },
                initMap() {
                    const fallbackLat = -6.2;
                    const fallbackLng = 106.8166667;
                    const latValue = this.parseNumber(this.lat) ?? fallbackLat;
                    const lngValue = this.parseNumber(this.lng) ?? fallbackLng;

                    this.map = L.map(mapId).setView([latValue, lngValue], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                    }).addTo(this.map);

                    this.marker = L.marker([latValue, lngValue], { draggable: true }).addTo(this.map);

                    this.marker.on('dragend', async (event) => {
                        const position = event.target.getLatLng();
                        this.updateCoordinates(position.lat, position.lng);
                        await this.reverseGeocode(position.lat, position.lng);
                    });

                    this.map.on('click', async (event) => {
                        const position = event.latlng;
                        this.updateCoordinates(position.lat, position.lng, true);
                        await this.reverseGeocode(position.lat, position.lng);
                    });
                },
                updateCoordinates(latValue, lngValue, shouldCenterMap = false) {
                    const normalizedLat = Number(latValue);
                    const normalizedLng = Number(lngValue);

                    if (!Number.isFinite(normalizedLat) || !Number.isFinite(normalizedLng)) {
                        return;
                    }

                    const formattedLat = normalizedLat.toFixed(7);
                    const formattedLng = normalizedLng.toFixed(7);

                    this.lat = formattedLat;
                    this.lng = formattedLng;

                    if (this.$wire && this.latStatePath) {
                        this.$wire.set(this.latStatePath, formattedLat);
                    }

                    if (this.$wire && this.lngStatePath) {
                        this.$wire.set(this.lngStatePath, formattedLng);
                    }

                    if (this.marker && this.map) {
                        this.marker.setLatLng([normalizedLat, normalizedLng]);

                        if (shouldCenterMap) {
                            this.map.setView([normalizedLat, normalizedLng], 15);
                        }
                    }
                },
                updateAddress(value) {
                    if (!value) {
                        return;
                    }

                    this.address = value;

                    if (this.$wire && this.addressStatePath) {
                        this.$wire.set(this.addressStatePath, value);
                    }
                },
                async fetchApi(path, query = {}) {
                    const params = new URLSearchParams();

                    Object.entries(query).forEach(([key, value]) => {
                        if (value !== null && value !== undefined && value !== '') {
                            params.append(key, String(value));
                        }
                    });

                    if (this.apiKey) {
                        params.append('api_key', this.apiKey);
                    }

                    const url = `${path}?${params.toString()}`;

                    return fetch(url, {
                        headers: this.apiKey ? { 'X-API-KEY': this.apiKey } : {},
                    });
                },
                async reverseGeocode(lat, lng) {
                    const resolvedByNominatim = await this.reverseGeocodeWithNominatim(lat, lng);
                    if (resolvedByNominatim) {
                        this.updateAddress(resolvedByNominatim);
                        return;
                    }

                    try {
                        const response = await this.fetchApi('/api/geocode/reverse', {
                            latitude: lat,
                            longitude: lng,
                        });

                        if (!response.ok) {
                            this.setStatus(this.messages.reverse_failed || '', 'error');
                            return;
                        }
                        const data = await response.json();
                        if (data && data.data && data.data.display_name) {
                            this.updateAddress(data.data.display_name);
                            this.clearStatus();
                            return;
                        }

                        this.setStatus(this.messages.reverse_failed || '', 'error');
                    } catch (_) {
                        this.setStatus(this.messages.reverse_failed || '', 'error');
                    }
                },
                async reverseGeocodeWithNominatim(lat, lng) {
                    try {
                        const response = await fetch(
                            `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`
                        );

                        if (!response.ok) {
                            return false;
                        }

                        const payload = await response.json();
                        if (!payload || !payload.display_name) {
                            return null;
                        }

                        this.clearStatus();
                        return payload.display_name;
                    } catch (_) {
                        return null;
                    }
                },
                async searchAddress() {
                    const query = this.searchQuery.trim();
                    if (!query) {
                        this.searchResults = [];
                        this.clearStatus();
                        return;
                    }

                    this.isSearching = true;
                    this.searchResults = [];
                    this.setStatus(this.messages.searching || '', 'info');

                    try {
                        let results = await this.searchAddressWithNominatim(query);
                        if (!results || !results.length) {
                            results = await this.searchAddressWithApi(query);
                        }

                        if (!results.length) {
                            this.setStatus(this.messages.no_results || '', 'error');
                            return;
                        }

                        this.searchResults = results;

                        if (results.length === 1) {
                            this.selectSearchResult(results[0]);
                            return;
                        }

                        this.setStatus(this.messages.choose_result || '', 'info');
                    } catch (_) {
                        this.setStatus(this.messages.search_failed || '', 'error');
                    } finally {
                        this.isSearching = false;
                    }
                },
                selectSearchResult(result) {
                    if (!result) {
                        return;
                    }

                    const latValue = Number(result.latitude);
                    const lngValue = Number(result.longitude);

                    if (!Number.isFinite(latValue) || !Number.isFinite(lngValue)) {
                        this.setStatus(this.messages.search_failed || '', 'error');
                        return;
                    }

                    this.updateCoordinates(latValue, lngValue, true);
                    this.updateAddress(result.display_name || '');
                    this.searchQuery = result.display_name || this.searchQuery;
                    this.searchResults = [];

                    this.setStatus(this.messages.selected || '', 'success');
                },
                async searchAddressWithApi(query) {
                    const response = await this.fetchApi('/api/geocode/search', { q: query });

                    if (!response.ok) {
                        return [];
                    }

                    const payload = await response.json();
                    const results = payload && payload.data ? payload.data : [];
                    if (!Array.isArray(results)) {
                        return [];
                    }

                    return results
                        .map((item) => ({
                            display_name: item.display_name || '',
                            latitude: item.latitude,
                            longitude: item.longitude,
                        }))
                        .filter((item) => Number.isFinite(Number(item.latitude)) && Number.isFinite(Number(item.longitude)));
                },
                async searchAddressWithNominatim(query) {
                    try {
                        const response = await fetch(
                            `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=5&q=${encodeURIComponent(query)}`
                        );

                        if (!response.ok) {
                            return [];
                        }

                        const results = await response.json();
                        if (!Array.isArray(results)) {
                            return [];
                        }

                        return results
                            .map((item) => ({
                                display_name: item.display_name || '',
                                latitude: item.lat,
                                longitude: item.lon,
                            }))
                            .filter((item) => Number.isFinite(Number(item.latitude)) && Number.isFinite(Number(item.longitude)));
                    } catch (_) {
                        return [];
                    }
                },
                setStatus(message, type = 'info') {
                    this.statusMessage = message || '';
                    this.statusType = type;
                },
                clearStatus() {
                    this.statusMessage = '';
                    this.statusType = 'info';
                },
                syncMarker() {
                    if (!this.marker || !this.map) {
                        return;
                    }

                    const latValue = this.parseNumber(this.lat);
                    const lngValue = this.parseNumber(this.lng);
                    if (latValue === null || lngValue === null) {
                        return;
                    }

                    const current = this.marker.getLatLng();
                    if (current.lat === latValue && current.lng === lngValue) {
                        return;
                    }

                    this.marker.setLatLng([latValue, lngValue]);
                    this.map.panTo([latValue, lngValue]);
                },
            };
        };
    }
</script>
