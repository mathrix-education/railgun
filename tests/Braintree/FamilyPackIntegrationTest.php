<?php
namespace Tests\Braintree;

use App\Models\User;
use App\Notifications\TransactionSuccessNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\Passport;

/**
 * Class FamilyPackIntegrationTest
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package   Tests\Braintree
 * @since     4.1.1
 * @copyright Mathrix Education SA
 */
class FamilyPackIntegrationTest extends BraintreeTestCase
{
    /**
     * POST /braintree/subscriptions.
     *
     * Test subscription to `v4_annually` as non-customer with 3 children
     *
     * @throws \App\Exceptions\Http400InvalidRequestException
     */
    public function testSubscribeAnnuallyAsNonCustomer()
    {
        Notification::fake();

        $customer = User::where("type", "=", "registered")
            ->whereNull("braintree_id")
            ->inRandomOrder()
            ->firstOrFail();
        Passport::actingAs($customer, ["registered"]);

        $this->json("post", "/subscriptions", [
            "planId" => "v4_annually",
            "paymentMethodNonce" => "fake-valid-visa-nonce",
            "family_pack_children_count" => 3
        ]);

        $this->assertHttp200Success();
        $this->assertNotInDatabase("users", [
            "id" => $customer->id,
            "braintree_id" => null,
        ]);
        $this->assertTrue($customer->hasBraintreeSubscription("v4_annually"));
        $this->assertNotificationSentToVia(TransactionSuccessNotification::class, $customer, "mail");

        return $customer;
    }

    /**
     * @depends testSubscribeAnnuallyAsNonCustomer
     *
     * POST /family-packs/{familyPackId}/children
     * Test add a child to an existing family pack
     *
     * @param User $customer
     *
     * @return User
     */
    public function testAddChild(User $customer)
    {
        Passport::actingAs($customer, ["registered"]);

        $child = User::inRandomOrder()->whereNull("family_pack_id")->firstOrFail();
        $familyPack = $customer->familyPackAsParent;

        $this->json("post", "/family-packs/{$familyPack->id}/children", [
            "email" => $child->email
        ]);

        $child = User::findOrFail($child->id);

        $this->assertHttp200Success();
        $this->assertTrue($child->premium);
        $this->assertEquals(1, $familyPack->children()->count());
        $this->assertInDatabase("users", ["id" => $child->id, "family_pack_id" => $familyPack->id]);

        return $customer;
    }

    /**
     * @depends testAddChild
     *
     * DELETE /family-packs/{familyPackId}/children
     * Test remove a child from an existing family pack
     *
     * @param User $customer
     */
    public function testRemoveChild(User $customer)
    {
        Passport::actingAs($customer, ["registered"]);

        $familyPack = $customer->familyPackAsParent;
        $child = $familyPack->children[0];

        $this->json("delete", "/family-packs/{$familyPack->id}/children", [
            "email" => $child->email
        ]);

        $child = User::findOrFail($child->id);

        $this->assertHttp200Success();
        $this->assertFalse($child->premium);
        $this->assertEquals(0, $familyPack->children()->count());
        $this->assertInDatabase("users", ["id" => $child->id, "family_pack_id" => null]);

    }
}
