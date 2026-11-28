<?php

use App\Enums\UserRole;
use App\Http\Concerns\ApiResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('user can login and receive a sanctum bearer token', function () {
    $user = User::factory()->withRole(UserRole::Admin)->create([
        'email' => 'admin@teras.test',
        'password' => 'secret123',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'admin@teras.test',
        'password' => 'secret123',
        'device_name' => 'pest-client',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Login berhasil')
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.email', 'admin@teras.test')
        ->assertJsonPath('data.user.role', 'admin');

    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();
    $this->assertDatabaseCount('personal_access_tokens', 1);
});

test('login fails with 401 when credentials are invalid', function () {
    User::factory()->create([
        'email' => 'staff@teras.test',
        'password' => 'correct-password',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'staff@teras.test',
        'password' => 'wrong-password',
    ]);

    $response
        ->assertUnauthorized()
        ->assertExactJson([
            'message' => 'Email atau password salah.',
        ]);
});

test('validation errors on api routes use standard validation failed format', function () {
    $response = $this->postJson('/api/login', []);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Validation failed')
        ->assertJsonStructure([
            'message',
            'errors' => ['email', 'password'],
        ]);
});

test('authenticated user can fetch profile and logout revokes token', function () {
    $user = User::factory()->withRole(UserRole::Guru)->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $meResponse = $this->withToken($token)->getJson('/api/me');

    $meResponse
        ->assertOk()
        ->assertJsonPath('message', 'Data pengguna berhasil diambil')
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.role', 'guru');

    $logoutResponse = $this->withToken($token)->postJson('/api/logout');

    $logoutResponse
        ->assertOk()
        ->assertExactJson([
            'message' => 'Logout berhasil',
            'data' => null,
        ]);

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('unauthenticated requests to protected api endpoints return 401', function () {
    $this->getJson('/api/students')
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);

    $this->postJson('/api/employees', [])
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
});

test('guru role can read students and manage daily notes but cannot write students or promotions', function () {
    $guru = User::factory()->withRole(UserRole::Guru)->create();
    Sanctum::actingAs($guru);

    $this->getJson('/api/students')->assertOk();
    $this->postJson('/api/daily-notes', [])->assertCreated();

    $this->postJson('/api/students', [])->assertForbidden();
    $this->deleteJson('/api/students/1')->assertForbidden();
    $this->postJson('/api/promotions/preview')->assertForbidden();
});

test('staff role can write students but cannot delete students or run promotions', function () {
    $staff = User::factory()->withRole(UserRole::Staff)->create();
    Sanctum::actingAs($staff);

    $this->postJson('/api/students', [])->assertCreated();
    $this->deleteJson('/api/students/1')->assertForbidden();
    $this->postJson('/api/promotions/preview')->assertForbidden();
});

test('admin and kepala sekolah can access high privilege endpoints', function (UserRole $role) {
    $user = User::factory()->withRole($role)->create();
    Sanctum::actingAs($user);

    $this->deleteJson('/api/students/1')->assertOk();
    $this->postJson('/api/promotions/preview')->assertOk();
})->with([
    'admin' => [UserRole::Admin],
    'kepala_sekolah' => [UserRole::KepalaSekolah],
]);

test('unknown api route returns 404 json response', function () {
    $this->getJson('/api/non-existent-endpoint')
        ->assertNotFound()
        ->assertExactJson([
            'message' => 'Resource not found.',
        ]);
});

test('api response trait formats success, paginated, and error payloads consistently', function () {
    $responder = new class
    {
        use ApiResponse;

        public function ok(): JsonResponse
        {
            return $this->successResponse(['id' => 1], 'Data berhasil disimpan', 201);
        }

        public function list(): JsonResponse
        {
            $paginator = new LengthAwarePaginator(
                items: [['id' => 1], ['id' => 2]],
                total: 5,
                perPage: 2,
                currentPage: 1,
            );

            return $this->paginatedResponse($paginator);
        }

        public function fail(): JsonResponse
        {
            return $this->errorResponse('Bad request', 400, ['field' => ['Invalid']]);
        }
    };

    expect($responder->ok()->getStatusCode())->toBe(201)
        ->and($responder->ok()->getData(true))->toBe([
            'message' => 'Data berhasil disimpan',
            'data' => ['id' => 1],
        ]);

    expect($responder->list()->getStatusCode())->toBe(200)
        ->and($responder->list()->getData(true))->toBe([
            'message' => 'Data berhasil diambil',
            'data' => [['id' => 1], ['id' => 2]],
            'meta' => [
                'current_page' => 1,
                'last_page' => 3,
                'per_page' => 2,
                'total' => 5,
            ],
        ]);

    expect($responder->fail()->getStatusCode())->toBe(400)
        ->and($responder->fail()->getData(true))->toBe([
            'message' => 'Bad request',
            'errors' => ['field' => ['Invalid']],
        ]);
});
