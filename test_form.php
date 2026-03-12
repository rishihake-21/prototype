<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Create a simple test user if none exists
if (\App\Models\User::count() == 0) {
    $user = \App\Models\User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => 'creator'
    ]);
    echo "Created test user: " . $user->email . "\n";
} else {
    $user = \App\Models\User::first();
    echo "Using existing user: " . $user->email . "\n";
}

// Log in the user programmatically
app()['auth']->setUser($user);

// Test the create route
$response = $app['router']->dispatch(
    \Illuminate\Http\Request::create('/syllabi/create', 'GET')
);

echo "Response status: " . $response->getStatusCode() . "\n";
echo "Response content length: " . strlen($response->getContent()) . "\n";

// Check if we can access the syllabus form
if (strpos($response->getContent(), 'syllabusForm') !== false) {
    echo "SUCCESS: syllabusForm function found in response\n";
} else {
    echo "ERROR: syllabusForm function NOT found in response\n";
}

if (strpos($response->getContent(), 'x-data="syllabusForm()"') !== false) {
    echo "SUCCESS: Alpine.js initialization found\n";
} else {
    echo "ERROR: Alpine.js initialization NOT found\n";
}