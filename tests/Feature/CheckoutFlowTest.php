<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Support\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Cache::flush();
    }

    private function buyer(): User
    {
        return User::where('email', 'buyer@mail.com')->firstOrFail();
    }

    private function ensureCart(User $buyer): void
    {
        if ($buyer->carts()->count() === 0) {
            Cart::create([
                'user_id' => $buyer->id,
                'product_id' => \App\Models\Product::withStock()->firstOrFail()->id,
                'quantity' => 1,
            ]);
        }
    }

    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'recipient_name' => 'Budi',
            'phone_number' => '081234567890',
            'address' => 'Jl. Merdeka No. 12, RT 03/RW 05, dekat Masjid',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'post_code' => '40135',
            'notes' => '',
            'proof' => UploadedFile::fake()->image('bukti.png'),
            'shipping_method' => 'jne-reguler',
            'payment_method' => 'bca',
        ], $overrides);
    }

    private function enableAgenWeb(array $cityList): void
    {
        Setting::updateOrCreate(['key' => 'agenweb_api_key'], ['value' => 'test-key']);
        Setting::updateOrCreate(['key' => 'agenweb_origin_city_id'], ['value' => '210']);
        Setting::updateOrCreate(['key' => 'agenweb_origin_postal_code'], ['value' => '55651']);
        Setting::updateOrCreate(['key' => 'agenweb_city_list'], ['value' => $cityList]);
        StoreSettings::invalidateCache();
    }

    private function fakeAgenWebLocationsAndRates(): void
    {
        Http::fake([
            'https://api.agenwebsite.com/v1/locations/search*' => function ($request) {
                $row = $request['q'] === '55651'
                    ? ['province' => 'Daerah Istimewa Yogyakarta', 'city' => 'Kulon Progo', 'district' => 'Wates', 'postal_code' => '55651']
                    : ['province' => 'Jawa Barat', 'city' => 'Bandung', 'district' => 'Coblong', 'postal_code' => '40135'];
                return Http::response(['success' => true, 'data' => [$row]]);
            },
            'https://api.agenwebsite.com/v1/rates' => Http::response([
                'success' => true,
                'data' => ['rates' => [
                    ['courier_name' => 'J&T Express', 'service_name' => 'Regular', 'service_code' => 'jnt_ez', 'cost' => 21000, 'etd_text' => '2-3 days'],
                ]],
            ], 200),
        ]);
    }

    public function test_checkout_creates_order_and_saves_address_book(): void
    {
        $buyer = $this->buyer();
        $this->ensureCart($buyer);

        $this->actingAs($buyer)
            ->post(route('product_transactions.store'), $this->checkoutPayload([
                'save_address' => '1',
                'address_label' => 'Rumah',
            ]))
            ->assertRedirect(route('product_transactions.index'));

        $order = $buyer->productTransactions()->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame('Budi', $order->recipient_name);
        $this->assertSame('Coblong', $order->district);
        $this->assertSame('Jawa Barat', $order->province);
        $this->assertSame('40135', $order->post_code);

        $saved = $buyer->addresses()->first();
        $this->assertNotNull($saved);
        $this->assertSame('Rumah', $saved->label);
        $this->assertSame('Budi', $saved->recipient_name);
        $this->assertSame('Coblong', $saved->district);
        $this->assertSame('40135', $saved->postal_code);
        $this->assertTrue($saved->is_default);
        $this->assertFalse($buyer->carts()->exists());
    }

    public function test_checkout_reuses_existing_saved_address_without_duplicate(): void
    {
        $buyer = $this->buyer();
        $this->ensureCart($buyer);

        $saved = $buyer->addresses()->create([
            'label' => 'Kantor',
            'recipient_name' => 'Budi',
            'phone' => '081234567890',
            'address' => 'Jl. Jendral Sudirman No. 1',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'postal_code' => '40135',
        ]);

        $this->actingAs($buyer)
            ->post(route('product_transactions.store'), $this->checkoutPayload([
                'saved_address_id' => $saved->id,
                'save_address' => '1',
                'address' => 'Jl. Jendral Sudirman No. 1',
            ]))
            ->assertRedirect(route('product_transactions.index'));

        $this->assertSame(1, $buyer->addresses()->count());
        $this->assertSame('Jl. Jendral Sudirman No. 1', $buyer->addresses()->first()->address);
    }

    public function test_checkout_without_save_address_skips_address_book(): void
    {
        $buyer = $this->buyer();
        $this->ensureCart($buyer);

        $this->actingAs($buyer)
            ->post(route('product_transactions.store'), $this->checkoutPayload())
            ->assertRedirect(route('product_transactions.index'));

        $this->assertSame(0, $buyer->addresses()->count());
    }

    public function test_checkout_rejects_incomplete_address(): void
    {
        $buyer = $this->buyer();
        $this->ensureCart($buyer);

        $beforeCount = $buyer->productTransactions()->count();

        $response = $this->actingAs($buyer)
            ->post(route('product_transactions.store'), $this->checkoutPayload([
                'recipient_name' => '',
                'district' => '',
            ]));

        $response->assertSessionHasErrors(['recipient_name']);
        $this->assertSame($beforeCount, $buyer->productTransactions()->count());
    }

    public function test_agenweb_checkout_uses_selected_rate_and_saves_district(): void
    {
        $buyer = $this->buyer();
        $this->ensureCart($buyer);
        $this->enableAgenWeb([
            ['city_id' => '22', 'province' => 'Jawa Barat', 'city_name' => 'Bandung', 'postal_code' => '40135'],
        ]);
        $this->fakeAgenWebLocationsAndRates();

        $this->actingAs($buyer)
            ->post(route('product_transactions.store'), $this->checkoutPayload([
                'agenweb_city_id' => '22',
                'shipping_method' => 'agenweb-jnt_ez',
                'save_address' => '1',
            ]))
            ->assertRedirect(route('product_transactions.index'));

        $order = $buyer->productTransactions()->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame('J&T Express Regular', $order->shipping_method);
        $this->assertSame(21000, $order->shipping_cost);
        $this->assertSame('Coblong', $order->district);

        $saved = $buyer->addresses()->first();
        $this->assertSame('Coblong', $saved->district);
        $this->assertSame('22', $saved->city_id);
    }

    public function test_district_autocomplete_proxy_caches_search(): void
    {
        $this->actingAs($this->buyer());

        Http::fake([
            'https://api.agenwebsite.com/v1/locations/search*' => Http::response([
                'success' => true,
                'data' => [
                    ['province' => 'Daerah Istimewa Yogyakarta', 'city' => 'Kulon Progo', 'district' => 'Wates', 'postal_code' => '55651'],
                    ['province' => 'Daerah Istimewa Yogyakarta', 'city' => 'Bantul', 'district' => 'Banguntapan', 'postal_code' => '55198'],
                ],
            ], 200),
        ]);

        $first = $this->getJson(route('carts.locations', ['q' => 'wa']));
        $first->assertOk()->assertJsonCount(2);
        $this->assertSame('Wates', $first->json('0.district'));
        $this->assertSame('55651', $first->json('0.postal_code'));

        $this->getJson(route('carts.locations', ['q' => 'wa']))->assertOk();

        Http::assertSentCount(1);
    }

    public function test_checkout_without_proof_creates_pending_order(): void
    {
        $buyer = $this->buyer();
        $this->ensureCart($buyer);

        Notification::fake();

        $this->actingAs($buyer)
            ->post(route('product_transactions.store'), $this->checkoutPayload([
                'proof' => null,
            ]))
            ->assertRedirect(route('product_transactions.index'));

        $order = $buyer->productTransactions()->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame(\App\Models\ProductTransaction::STATUS_PENDING, $order->status);
        $this->assertNull($order->proof);

        Notification::assertSentTo(
            User::role(['owner', 'admin'])->get(),
            OrderCreatedNotification::class
        );
    }

    public function test_approve_blocked_until_proof_uploaded(): void
    {
        $buyer = $this->buyer();
        $owner = User::where('email', 'owner@mail.com')->firstOrFail();
        $this->ensureCart($buyer);

        $this->actingAs($buyer)
            ->post(route('product_transactions.store'), $this->checkoutPayload(['proof' => null]));

        $order = $buyer->productTransactions()->latest('id')->first();

        $this->actingAs($owner)
            ->post(route('admin.orders.approve', $order))
            ->assertSessionHas('error');
        $order->refresh();
        $this->assertSame(\App\Models\ProductTransaction::STATUS_PENDING, $order->status);

        $this->actingAs($buyer)
            ->post(route('product_transactions.proof', $order), [
                'proof' => UploadedFile::fake()->image('bukti.png'),
            ])
            ->assertRedirect();
        $order->refresh();
        $this->assertNotNull($order->proof);

        $this->actingAs($owner)
            ->post(route('admin.orders.approve', $order))
            ->assertRedirect();
        $order->refresh();
        $this->assertSame(\App\Models\ProductTransaction::STATUS_PROCESSING, $order->status);
    }

    public function test_status_change_creates_buyer_notification_and_email(): void
    {
        $buyer = $this->buyer();
        $owner = User::where('email', 'owner@mail.com')->firstOrFail();
        $this->ensureCart($buyer);

        $this->actingAs($buyer)
            ->post(route('product_transactions.store'), $this->checkoutPayload());

        $order = $buyer->productTransactions()->latest('id')->first();

        $this->actingAs($owner)
            ->post(route('admin.orders.approve', $order))
            ->assertRedirect();

        $this->assertSame(1, $buyer->notifications()->count());
        $this->assertSame(
            'Pesanan #' . $order->id . ' kini berstatus: ' . \App\Models\ProductTransaction::STATUS_LABELS[\App\Models\ProductTransaction::STATUS_PROCESSING],
            data_get($buyer->notifications()->first()->data, 'message')
        );
    }

    public function test_notification_endpoints_return_unread_and_mark_read(): void
    {
        $buyer = $this->buyer();
        $owner = User::where('email', 'owner@mail.com')->firstOrFail();
        $this->ensureCart($buyer);

        $this->actingAs($buyer)->post(route('product_transactions.store'), $this->checkoutPayload());
        $order = $buyer->productTransactions()->latest('id')->first();

        $this->actingAs($owner)->post(route('admin.orders.approve', $order))->assertRedirect();

        $response = $this->actingAs($buyer)->getJson(route('notifications.index'));
        $response->assertOk();
        $this->assertSame(1, $response->json('unread'));
        $this->assertCount(1, $response->json('data'));
        $this->assertFalse($response->json('data.0.read'));

        $this->actingAs($buyer)->postJson(route('notifications.readAll'))->assertOk();

        $reloaded = $this->actingAs($buyer)->getJson(route('notifications.index'));
        $this->assertSame(0, $reloaded->json('unread'));
    }
}