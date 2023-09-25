<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testRegisterSuccess()
    {
        $this->post("/api/users", [
            "username" => "najib",
            "password" => "rahasia",
            "name" => "Najib Ismail"
        ])->assertStatus(201)
        ->assertJson([
            "data" => [
                "username" => "najib",
                "name" => "Najib Ismail"
            ]
        ]);
    }


    public function testRegisterFailed()
    {
        $this->post("/api/users", [
            "username" => "",
            "password" => "",
            "name" => ""
        ])->assertStatus(400)
        ->assertJson([
            "errors" => [
                "username" => [
                    "The username field is required."
                ],
                "password" => [
                    "The password field is required."
                ],
                "name" => [
                    "The name field is required."
                ],
            ]
        ]);
    }

    public function testRegisterUsernameAlreadyExists()
    {
        $this->testRegisterSuccess();
        $this->post("/api/users", [
            "username" => "najib",
            "password" => "rahasia",
            "name" => "Najib Ismail"
        ])->assertStatus(400)
        ->assertJson([
            "errors" => [
                "username" => [
                    "username already register"
                ]
            ]
        ]);
    }

    public function testLoginSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->post("/api/users/login", [
            "username" => "test",
            "password" => "test",
        ])->assertStatus(200)
        ->assertJson([
            "data" => [
                "username" => "test",
                "name" => "test",
            ]
        ]);


        $user = User::where("username", "test")->first();
        self::assertNotNull($user->token);
    }

    public function testLoginFailed()
    {
        $this->post("/api/users/login", [
            "username" => "test",
            "password" => "test",
        ])->assertStatus(401)
        ->assertJson([
            "errors" => [
                "message" => [
                    "username or password wrong"
                ]
            ]
        ]);

    }

    public function testLoginFailedPasswordWrong()
    {
        $this->seed([UserSeeder::class]);

        $this->post("/api/users/login", [
            "username" => "test",
            "password" => "salah",
        ])->assertStatus(401)
        ->assertJson([
            "errors" => [
                "message" => [
                    "username or password wrong"
                ]
            ]
        ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->get("/api/users/current", [
            "Authorization" => "test"
        ])->assertStatus(200)
          ->assertJson([
            "data" => [
                "username" => "test",
                "name" => "test"
            ]
          ]);
    }

    public function testGetUnauthorized()
    {
        $this->seed([UserSeeder::class]);

        $this->get("/api/users/current")->assertStatus(401)
          ->assertJson([
            "errors" => [
                "message"  => [
                    "unauthorized"
                ]
            ]
          ]);
    }

    public function getInvalidToken()
    {
        $this->seed([UserSeeder::class]);
        $this->get("/api/users/current", [
            "Authorization" => ""
        ])->assertStatus(401)
        ->assertJson([
          "errors" => [
              "message"  => [
                  "unauthorized"
              ]
          ]
        ]);
    }

    public function testUpdateNameSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where("username", "test")->first();

        $this->patch("/api/users/current",
        [
            "name" => "najib"
        ],
        [
            "Authorization" => "test"
        ])->assertStatus(200)
          ->assertJson([
            "data" => [
                "username" => "test",
                "name" => "najib"
            ]
          ]);
          $newUser = User::where("username", "test")->first();
          self::assertNotEquals($oldUser->name, $newUser->name);
    }

    public function testUpdatePasswordSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where("username", "test")->first();

        $this->patch("/api/users/current",
        [
            "password" => "baru"
        ],
        [
            "Authorization" => "test"
        ])->assertStatus(200)
          ->assertJson([
            "data" => [
                "username" => "test",
                "name" => "test"
            ]
          ]);
          $newUser = User::where("username", "test")->first();
          self::assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->patch("/api/users/current",
        [
            "name" => "najibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajibnajib"
        ],
        [
            "Authorization" => "test"
        ])->assertStatus(400)
          ->assertJson([
            "errors" => [
                "name" => [
                    "The name field must not be greater than 100 characters."
                ]
            ]
          ]);
    }

    public function testLogoutSucces()
    {
        $this->seed([UserSeeder::class]);

        $this->delete(uri : "/api/users/logout", headers : [
            "Authorization" => "test"
        ])->assertStatus(200)
        ->assertJson([
            "data" => true
        ]);

        $user = User::where("username", "test")->first();

        self::assertNull($user->token);
    }

    public function testLogoutFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->delete("/api/users/logout", [
            "Authorization" => "salah"
        ])->assertStatus(401)
        ->assertJson([
            "errors" => [
                "message" => [
                    "unauthorized"
                ]
            ]
        ]);
    }
}
