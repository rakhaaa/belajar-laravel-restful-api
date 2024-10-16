<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Address;
use App\Models\Contact;
use Database\Seeders\UserSeeder;
use Database\Seeders\AddressSeeder;
use Database\Seeders\ContactSeeder;
use Database\Seeders\ListSeeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddressTest extends TestCase
{
    public function testCreateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();
        $this->post(
            "/api/contacts/" . $contact->id . "/addresses",
            [
                'street' => 'test',
                'city' => 'test',
                'province' => 'test',
                'country' => 'test',
                'postal_code' => '12345678',
            ],
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(201)
            ->assertJson([
                "data" => [
                    'street' => 'test',
                    'city' => 'test',
                    'province' => 'test',
                    'country' => 'test',
                    'postal_code' => '12345678',
                ]
            ]);
    }

    public function testCreateFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();

        $this->post(
            "/api/contacts/" . $contact->id . "/addresses",
            [
                'street' => 'test',
                'city' => 'test',
                'province' => 'test',
                'country' => '',
                'postal_code' => '123',
            ],
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'country' => [
                        "The country field is required."
                    ]
                ]
            ]);
    }

    public function testCreateNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();

        $this->post(
            '/api/contacts/' . ($contact->api + 1) . '/addresses',
            [
                'street' => 'test',
                'city' => 'test',
                'province' => 'test',
                'country' => 'test',
                'postal_code' => '12345'
            ],
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(404)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "Contact not found."
                    ]
                ]
            ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);

        $contact = Contact::query()->limit(1)->first();
        $address = Address::query()->limit(1)->first();

        $this->get(
            "/api/contacts/$contact->id/addresses/$address->id",
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    'street' => 'test',
                    'city' => 'test',
                    'province' => 'test',
                    'country' => 'test',
                    'postal_code' => '12345',
                ]
            ]);
    }

    public function testGetNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);

        $contact = Contact::query()->limit(1)->first();
        $address = Address::query()->limit(1)->first();

        $this->get(
            "/api/contacts/$contact->id/addresses/" . ($address->id + 1),
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(404)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "Contact not found."
                    ]
                ]
            ]);
    }

    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);

        $address = Address::query()->limit(1)->first();

        $this->put(
            "/api/contacts/$address->contact_id/addresses/$address->id",
            [
                'street' => 'street2',
                'city' => 'city2',
                'province' => 'province2',
                'country' => 'country2',
                'postal_code' => '12345678'
            ],
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    'street' => 'street2',
                    'city' => 'city2',
                    'province' => 'province2',
                    'country' => 'country2',
                    'postal_code' => '12345678'
                ]
            ]);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);

        $address = Address::query()->limit(1)->first();

        $this->put(
            "/api/contacts/$address->contact_id/addresses/$address->id",
            [
                'street' => 'street2',
                'city' => 'city2',
                'province' => 'province2',
                'country' => 'country2',
                'postal_code' => '123456789101112131415'
            ],
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'postal_code' => [
                        "The postal code field must not be greater than 10 characters."
                    ]
                ]
            ]);
    }

    public function testUpdateNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);

        $address = Address::query()->limit(1)->first();

        $this->put(
            "/api/contacts/$address->contact_id/addresses/" . ($address->id + 1),
            [
                'street' => 'street2',
                'city' => 'city2',
                'province' => 'province2',
                'country' => 'country2',
                'postal_code' => '12345678'
            ],
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(404)
            ->assertJson([
                "errors" => [
                    'message' => [
                        'Contact not found.'
                    ]
                ]
            ]);
    }

    public function testDeleteSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);

        $address = Address::query()->limit(1)->first();

        $this->delete(
            "/api/contacts/$address->contact_id/addresses/$address->id",
            [],
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);
    }

    public function testDeleteNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);

        $address = Address::query()->limit(1)->first();

        $this->delete(
            "/api/contacts/$address->contact_id/addresses/" . ($address->id + 1),
            [],
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(404)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "Contact not found."
                    ]
                ]
            ]);
    }

    public function testListSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, ListSeeder::class]);

        $contact = Contact::query()->limit(1)->first();

        $response = $this->get(
            "/api/contacts/$contact->id/addresses",
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));
        self::assertCount(20, $response['data']);
    }

    public function testListNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, ListSeeder::class]);

        $contact = Contact::query()->limit(1)->first();

        $response = $this->get(
            "/api/contacts/" . ($contact->id + 1) . "/addresses",
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(404)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "Contact not found."
                    ]
                ]
            ]);
    }
}
