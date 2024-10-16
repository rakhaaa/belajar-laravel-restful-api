<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $this->post('/api/users', [
            'username' => 'Bohr',
            'password' => 'rahasia',
            'name' => 'James Bohr'
        ])
            ->assertStatus(201)
            ->assertJson([
                "data" => [
                    'username' => 'Bohr',
                    'name' => 'James Bohr'
                ]
            ]);
    }

    public function testRegisterFailed()
    {
        $this->post('/api/users', [
            'username' => '',
            'password' => '',
            'name' => ''
        ])
            ->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'username' => [
                        'The username field is required.'
                    ],
                    'password' => [
                        'The password field is required.'
                    ],
                    'name' => [
                        'The name field is required.'
                    ]
                ]
            ]);
    }

    public function testRegisterUsernameAlreadyExists()
    {
        $this->testRegisterSuccess();

        $this->post('/api/users', [
            'username' => 'Bohr',
            'password' => 'rahasia',
            'name' => 'James Bohr'
        ])
            ->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "username" => [
                        "Username already registered."
                    ],
                ]
            ]);
    }

    public function testUserLoginSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test'
        ])
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);

        $user = User::where('username', 'test')->first();
        self::assertNotNull($user->token);
    }

    public function testLoginFailedUsernameNotFound()
    {
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test'
        ])
            ->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "username or password is wrong"
                    ]
                ]
            ]);
    }

    public function testLoginFailedPasswordWrong()
    {
        $this->seed(UserSeeder::class);

        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'wrong'
        ])
            ->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "username or password is wrong"
                    ]
                ]
            ]);
    }

    public function testGetUserSuccess()
    {
        $this->seed(UserSeeder::class);

        $this->get('/api/users/current', [
            'Authorization' => 'test'
        ])
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);
    }

    public function testGetUserFailed()
    {
        $this->seed(UserSeeder::class);

        $this->get('/api/users/current')
            ->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "Unauthorized"
                    ]
                ]
            ]);
    }

    public function testGetUserUnvalidToken()
    {
        $this->seed(UserSeeder::class);

        $this->get('/api/users/current', [
            'Authorization' => 'wrong'
        ])
            ->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "Unauthorized"
                    ]
                ]
            ]);
    }

    public function testUpdateName()
    {
        $this->seed(UserSeeder::class);
        $oldUser = User::where('username', 'test')->first();

        $this->patch(
            '/api/users/current',
            [
                'name' => 'baru'
            ],
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    "username" => 'test',
                    'name' => 'baru'
                ]
            ]);

        $newUser = User::where('username', 'test')->first();

        self::assertNotEquals($oldUser->name, $newUser->name);
    }

    public function testUpdateUserPassword()
    {
        $this->seed(UserSeeder::class);

        $oldUser = User::where("username", "test")->first();
        $this->patch(
            '/api/users/current',
            [
                'password' => 'baru'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
            ->assertJson([
                "data" => [
                    "username" => 'test',
                    "name" => 'test'
                ]
            ]);
        $newUser = User::where("username", "test")->first();

        self::assertNotEquals($oldUser, $newUser);
    }

    public function testUpdateUserFailed()
    {
        $this->seed(UserSeeder::class);

        $this->patch(
            '/api/users/current',
            [
                'name' => 'barubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubarubaru'
            ],
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "name" => [
                        "The name field must not be greater than 100 characters."
                    ]
                ]
            ]);
    }

    public function testLogoutSuccess()
    {
        $this->seed(UserSeeder::class);

        $this->delete(uri: '/api/users/logout', headers: [
            'Authorization' => 'test'
        ])
            ->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);
        
        $user = User::where('username', 'test')->first();
        self::assertNull($user->token);
    }

    public function testLogoutFailed()
    {
        $this->seed(UserSeeder::class);

        $this->delete(uri: '/api/users/logout', headers: [
            'Authorization' => 'wrong'
        ])
            ->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "Unauthorized"
                    ]
                ]
            ]);
        
        $user = User::where('username', 'test')->first();
        self::assertNotNull($user->token);
    }
}
