<?php

use App\Models\User;

$successCollection = ['message' => 'Success', 'data' => []];
$successResource = ['message' => 'Success', 'data' => null];

$resourceEndpoints = static function (string $resource) use ($successCollection, $successResource): array {
    $collectionUri = "/api/{$resource}";
    $resourceUri = "{$collectionUri}/1";

    return [
        "{$resource} index" => ['GET', $collectionUri, 200, $successCollection],
        "{$resource} create" => ['POST', $collectionUri, 201, $successResource],
        "{$resource} show" => ['GET', $resourceUri, 200, $successResource],
        "{$resource} update with PUT" => ['PUT', $resourceUri, 200, $successResource],
        "{$resource} update with PATCH" => ['PATCH', $resourceUri, 200, $successResource],
        "{$resource} delete" => ['DELETE', $resourceUri, 200, $successResource],
    ];
};

test('API resource endpoints return their expected status and JSON response', function (
    string $method,
    string $uri,
    int $status,
    array $expectedResponse,
) {
    $response = $this->json($method, $uri);

    match ($status) {
        200 => $response->assertOk(),
        201 => $response->assertCreated(),
    };

    $response->assertExactJson($expectedResponse);
})->with([
    ...$resourceEndpoints('students'),
    ...$resourceEndpoints('academic-years'),
    ...$resourceEndpoints('sections'),
    ...$resourceEndpoints('student-sections'),
    ...$resourceEndpoints('employees'),
    ...$resourceEndpoints('school-bells'),
    ...$resourceEndpoints('daily-notes'),
    ...$resourceEndpoints('promotions'),
    'student import' => ['POST', '/api/students/import', 200, $successResource],
    'download student import template' => ['GET', '/api/students/import/template', 200, $successResource],
    'list sections for student' => ['GET', '/api/students/1/sections', 200, $successCollection],
    'attach section to student' => ['POST', '/api/students/1/sections', 201, $successResource],
    'activate academic year' => ['POST', '/api/academic-years/1/activate', 200, $successResource],
    'list students in section' => ['GET', '/api/sections/1/students', 200, $successCollection],
    'attach students to section' => ['POST', '/api/sections/1/students', 201, $successResource],
    'detach student from section' => ['DELETE', '/api/sections/1/students/1', 200, $successResource],
    'update student section status' => ['PATCH', '/api/student-sections/1/status', 200, $successResource],
    'list employees available for homeroom' => ['GET', '/api/employees/available-for-homeroom', 200, $successCollection],
    'test school bell' => ['POST', '/api/school-bells/1/test', 200, $successResource],
    'preview promotions' => ['POST', '/api/promotions/preview', 200, $successCollection],
    'undo promotion' => ['POST', '/api/promotions/1/undo', 200, $successResource],
]);

test('the user endpoint returns 401 when no authenticated user is provided', function () {
    $response = $this->getJson('/api/user');

    $response
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
});

test('the user endpoint returns the authenticated user with status 200', function () {
    $user = User::factory()->make([
        'name' => 'API Test User',
        'email' => 'api-test@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->getJson('/api/user');

    $response
        ->assertOk()
        ->assertJsonPath('name', 'API Test User')
        ->assertJsonPath('email', 'api-test@example.com');
});
