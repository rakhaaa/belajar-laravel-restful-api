<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Contact;
use Database\Seeders\UserSeeder;
use Database\Seeders\SearchSeeder;
use Database\Seeders\ContactSeeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactTest extends TestCase
{
    public function testCreateSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->post(
            '/api/contacts',
            [
                "first_name" => "kemal",
                "last_name" => "",
                "email" => "",
                "phone" => ""
            ],
            [
                'Authorization' => 'test'
            ],
        )
            ->assertStatus(201)
            ->assertJson([
                "data" => [
                    "first_name" => "kemal",
                    "last_name" => "",
                    "email" => "",
                    "phone" => ""
                ]
            ]);

        $this->post(
            '/api/contacts',
            [
                "first_name" => "john",
                "last_name" => "wick",
                "email" => "john@wick.com",
                "phone" => "0897654321"
            ],
            [
                'Authorization' => 'test'
            ],
        )
            ->assertStatus(201)
            ->assertJson([
                "data" => [
                    "first_name" => "john",
                    "last_name" => "wick",
                    "email" => "john@wick.com",
                    "phone" => "0897654321"
                ]
            ]);
    }

    public function testCreateFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->post(
            '/api/contacts',
            [
                "first_name" => "",
                "last_name" => "mayers",
                "email" => "john",
                "phone" => "08765637389"
            ],
            [
                "Authorization" => "test"
            ]
        )
            ->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "first_name" => [
                        "The first name field is required."
                    ],
                    "email" => [
                        "The email field must be a valid email address."
                    ]
                ]
            ]);
    }

    public function testCreateFailedAuthorization()
    {
        $this->seed([UserSeeder::class]);

        $this->post(
            '/api/contacts',
            [
                "first_name" => "john",
                "last_name" => "mayers",
                "email" => "john@mayers.com",
                "phone" => "08765637389"
            ],
            [
                "Authorization" => "wrong"
            ]
        )
            ->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "Unauthorized"
                    ]
                ]
            ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();
        $this->get(
            "/api/contacts/$contact->id",
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    "first_name" => "test",
                    "last_name" => "test",
                    "email" => "test@test.com",
                    "phone" => "12345",
                ]
            ]);
    }

    public function testGetNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();
        $this->get(
            "/api/contacts/" . $contact->id + 1,
            [
                "Authorization" => "test"
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

    public function testGetFailedAuthorization()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::where("first_name", "test")->first();
        $this->get(
            "/api/contacts/$contact->id",
            [
                "Authorization" => "wrong"
            ]
        )
            ->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "Unauthorized"
                    ]
                ]
            ]);
    }

    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();
        $this->put(
            "/api/contacts/$contact->id",
            [
                "first_name" => "new",
                "last_name" => "new",
                "email" => "new@new.com",
                "phone" => "54321",
            ],
            [
                "Authorization" => "test"
            ]
        )
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    "first_name" => "new",
                    "last_name" => "new",
                    "email" => "new@new.com",
                    "phone" => "54321",
                ]
            ]);
    }

    public function testUpdateValidationError()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();
        $this->put(
            "/api/contacts/$contact->id",
            [
                "first_name" => "",
                "last_name" => "new",
                "email" => "new@new.com",
                "phone" => "54321",
            ],
            [
                "Authorization" => "test"
            ]
        )
            ->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "first_name" => [
                        "The first name field is required."
                    ]
                ]
            ]);
    }

    public function testDeleteSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();
        $this->delete(
            "/api/contacts/$contact->id",
            [],
            [
                "Authorization" => "test"
            ]
        )
            ->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);
    }

    public function testDeleteValidationError()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();
        $this->delete(
            "/api/contacts/" . $contact->id + 1,
            [],
            [
                "Authorization" => "test"
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

    public function testSearchByFirstName()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get(
            "/api/contacts?name=first",
            [
                "Authorization" => "test"
            ]
        )
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertCount(10, $response['data']);
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByLastName()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get(
            "/api/contacts?name=last",
            [
                "Authorization" => "test"
            ]
        )
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertCount(10, $response['data']);
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByEmail() {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get(
            "/api/contacts?email=test",
            [
                "Authorization" => "test"
            ]
        )
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertCount(10, $response['data']);
        self::assertEquals(20, $response['meta']['total']);
    }
    
    public function testSearchByPhone() {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get(
            "/api/contacts?phone=1111",
            [
                "Authorization" => "test"
            ]
        )
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertCount(10, $response['data']);
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchNotFound() {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get(
            "/api/contacts?name=tidakada",
            [
                "Authorization" => "test"
            ]
        )
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertCount(0, $response['data']);
        self::assertEquals(0, $response['meta']['total']);
    }

    public function testSearchByPage() {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get(
            "/api/contacts?size=5&page=2",
            [
                "Authorization" => "test"
            ]
        )
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertCount(5, $response['data']);
        self::assertEquals(2, $response['meta']['current_page']);
        self::assertEquals(20, $response['meta']['total']);
    }
}
