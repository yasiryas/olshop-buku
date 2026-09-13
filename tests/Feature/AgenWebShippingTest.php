<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\AgenWebShipping;
use App\Support\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AgenWebShippingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        Setting::updateOrCreate(['key' => 'agenweb_api_key'], ['value' => 'awk_live_test']);
        Setting::updateOrCreate(['key' => 'agenweb_origin_city_id'], ['value' => '210']);
        Setting::updateOrCreate(['key' => 'agenweb_origin_postal_code'], ['value' => '55651']);
        Setting::updateOrCreate(['key' => 'agenweb_city_list'], ['value' => [
            ['city_id' => '210', 'city_name' => 'Kulon Progo', 'province' => 'DI Yogyakarta', 'postal_code' => '55611'],
            ['city_id' => '22', 'city_name' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40135'],
            ['city_id' => '114', 'city_name' => 'Surabaya', 'province' => 'Jawa Timur', 'postal_code' => '60135'],
        ]]);

        StoreSettings::invalidateCache();

        Http::fake([
            'https://api.agenwebsite.com/v1/locations/search*' => function ($request) {
                $q = $request['q'];
                $row = $q === '55651'
                    ? ['province' => 'Daerah Istimewa Yogyakarta', 'city' => 'Kulon Progo', 'district' => 'Wates', 'postal_code' => '55651']
                    : ['province' => 'Jawa Barat', 'city' => 'Bandung', 'district' => 'Coblong', 'postal_code' => '40135'];
                return Http::response(['success' => true, 'data' => [$row]]);
            },
            'https://api.agenwebsite.com/v1/rates' => Http::response([
                'success' => true,
                'data' => ['rates' => [
                    [
                        'courier_name' => 'J&T Express',
                        'service_name' => 'Regular',
                        'service_code' => 'jnt_ez',
                        'cost' => 21000,
                        'etd_text' => '2-3 days',
                    ],
                    [
                        'courier_name' => 'TIKI',
                        'service_name' => 'ECO',
                        'service_code' => 'tiki_eco',
                        'cost' => 14000,
                        'discounted_cost' => 13000,
                        'etd_text' => '4 days',
                    ],
                ]],
            ]),
        ]);
    }

    public function test_empty_when_agent_not_configured(): void
    {
        Setting::updateOrCreate(['key' => 'agenweb_api_key'], ['value' => '']);
        StoreSettings::invalidateCache();

        $this->assertSame([], AgenWebShipping::rates(22, 1000, '40135'));

        Http::assertNothingSent();
    }

    public function test_rates_send_province_city_district_and_normalize_response(): void
    {
        $rates = AgenWebShipping::rates(22, 1000, '40135');

        $this->assertCount(2, $rates);
        $this->assertSame([
            'code' => 'agenweb-0',
            'courier' => 'J&T Express',
            'service' => 'Regular',
            'cost' => 21000,
            'eta' => '2-3 days',
            'rate_id' => 'agenweb-jnt_ez',
        ], $rates[0]);
        $this->assertSame(13000, $rates[1]['cost']);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.agenwebsite.com/v1/rates'
            && $request['weight'] === 1000
            && $request['shipper'] === ['province' => 'Daerah Istimewa Yogyakarta', 'city' => 'Kulon Progo', 'district' => 'Wates']
            && $request['destination'] === ['province' => 'Jawa Barat', 'city' => 'Bandung', 'district' => 'Coblong']);
    }

    public function test_retries_after_502_and_serves_cached_rates(): void
    {
        Http::swap(new Factory);

        Http::fake([
            'https://api.agenwebsite.com/v1/locations/search*' => function ($request) {
                $q = $request['q'];
                $row = $q === '55651'
                    ? ['province' => 'Daerah Istimewa Yogyakarta', 'city' => 'Kulon Progo', 'district' => 'Wates', 'postal_code' => '55651']
                    : ['province' => 'Jawa Barat', 'city' => 'Bandung', 'district' => 'Coblong', 'postal_code' => '40135'];
                return Http::response(['success' => true, 'data' => [$row]]);
            },
            'https://api.agenwebsite.com/v1/rates' => Http::sequence()
                ->push(['success' => false, 'error' => ['code' => 'courier_upstream_error']], 502)
                ->push(['success' => false, 'error' => ['code' => 'courier_upstream_error']], 502)
                ->push(['success' => true, 'data' => ['rates' => [
                    ['courier_name' => 'J&T Express', 'service_name' => 'Regular', 'service_code' => 'jnt_ez', 'cost' => 21000, 'etd_text' => '2-3 days'],
                ]]])
                ->push(['success' => false, 'error' => ['code' => 'courier_upstream_error']], 502),
        ]);

        $first = AgenWebShipping::rates(22, 1000, '40135');
        $this->assertCount(1, $first);
        $this->assertSame(21000, $first[0]['cost']);

        $second = AgenWebShipping::rates(22, 1000, '40135');
        $this->assertSame($first, $second);

        Http::assertSentCount(5);
    }

    public function test_location_search_result_is_cached_between_requests(): void
    {
        AgenWebShipping::rates(22, 1000, '40135');
        AgenWebShipping::rates(22, 1000, '40135');

        Http::assertSentCount(3);
    }
}