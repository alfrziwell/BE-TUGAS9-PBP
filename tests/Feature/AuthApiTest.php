<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase; // Automatically roll back DB changes after each test

    /**
     * Test user registration successfully.
     */
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'nama_lengkap' => 'Alif Alfarizi',
            'username' => 'alifalfarizi',
            'password' => 'secret123',
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'User successfully registered',
                 ]);

        $this->assertDatabaseHas('users', [
            'username' => 'alifalfarizi',
            'nama_lengkap' => 'Alif Alfarizi',
        ]);
    }

    /**
     * Test user registration validation rules.
     */
    public function test_user_registration_requires_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation error',
                 ]);
    }

    /**
     * Test user login and retrieving JWT token.
     */
    public function test_user_can_login_and_receive_jwt(): void
    {
        // Register the user first
        $user = User::create([
            'nama_lengkap' => 'Alif Alfarizi',
            'username' => 'alifalfarizi',
            'password' => 'secret123', // Model cast will hash it, but let's test authentication
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'alifalfarizi',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Login successful',
                     'token_type' => 'bearer',
                 ])
                 ->assertJsonStructure([
                     'access_token',
                     'expires_in',
                     'user',
                 ]);
    }

    /**
     * Test login failure with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'nonexistent',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Invalid username or password',
                 ]);
    }

    /**
     * Test retrieving the authenticated user's profile.
     */
    public function test_user_can_get_profile_with_jwt(): void
    {
        $user = User::create([
            'nama_lengkap' => 'Alif Alfarizi',
            'username' => 'alifalfarizi',
            'password' => 'secret123',
        ]);

        // Attempt login to get the token
        $loginResponse = $this->postJson('/api/login', [
            'username' => 'alifalfarizi',
            'password' => 'secret123',
        ]);

        $token = $loginResponse->json('access_token');

        // Access /api/me with Authorization header
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/me');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'username' => 'alifalfarizi',
                         'nama_lengkap' => 'Alif Alfarizi',
                     ],
                 ]);
    }

    /**
     * Test profile retrieval fails without token.
     */
    public function test_user_cannot_get_profile_without_token(): void
    {
        $response = $this->getJson('/api/me');

        // Should return 401 Unauthorized since route is protected by auth:api middleware
        $response->assertStatus(401);
    }

    /**
     * Test logging out.
     */
    public function test_user_can_logout(): void
    {
        $user = User::create([
            'nama_lengkap' => 'Alif Alfarizi',
            'username' => 'alifalfarizi',
            'password' => 'secret123',
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'username' => 'alifalfarizi',
            'password' => 'secret123',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Successfully logged out',
                 ]);

        // Trying to access profile again should fail now
        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/me');

        $profileResponse->assertStatus(401);
    }
}
