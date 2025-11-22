<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sockeon\EventLoop\Coroutine\Coroutine;
use Sockeon\EventLoop\Loop\Loop;
use Sockeon\EventLoop\Promise\Promise;

echo "Real-World Example: Sequential API Requests\n";
echo "==========================================\n\n";

/**
 * Simulate an API request that takes some time
 */
function makeApiRequest(string $endpoint, float $delay = 0.1): Promise
{
    return new Promise(function (callable $resolve) use ($endpoint, $delay): void {
        // Simulate network delay
        Loop::getInstance()->delay($delay, function () use ($resolve, $endpoint): void {
            // Simulate API response
            $response = [
                'endpoint' => $endpoint,
                'data' => "Response from {$endpoint}",
                'timestamp' => microtime(true),
            ];
            $resolve($response);
        });
    });
}

$loop = Loop::getInstance();

// Example 1: Sequential API calls (like fetching user data, then their posts, then comments)
echo "Example 1: Sequential API Calls\n";
echo "--------------------------------\n";

$coroutine = Coroutine::create(function (): Generator {
    echo "  1. Fetching user profile...\n";
    $user = yield makeApiRequest('/api/user/123', 0.1);
    echo "     ✓ Got user: {$user['data']}\n";

    echo "  2. Fetching user's posts...\n";
    $posts = yield makeApiRequest('/api/user/123/posts', 0.15);
    echo "     ✓ Got posts: {$posts['data']}\n";

    echo "  3. Fetching comments for first post...\n";
    $comments = yield makeApiRequest('/api/posts/1/comments', 0.1);
    echo "     ✓ Got comments: {$comments['data']}\n";

    // Combine all data
    return [
        'user' => $user,
        'posts' => $posts,
        'comments' => $comments,
    ];
});

$coroutine->promise()->then(function ($data) use ($loop): void {
    echo "\n  Final result: All data fetched successfully!\n";
    echo "  - User: {$data['user']['data']}\n";
    echo "  - Posts: {$data['posts']['data']}\n";
    echo "  - Comments: {$data['comments']['data']}\n";
    $loop->stop();
});

$loop->run();
echo "\n";

// Example 2: Error handling in coroutines
echo "Example 2: Error Handling\n";
echo "-------------------------\n";

$coroutine2 = Coroutine::create(function (): Generator {
    try {
        echo "  1. Attempting risky operation...\n";
        $result = yield Promise::reject(new Exception('API endpoint not found'));
        echo "     This won't execute\n";
    } catch (Exception $e) {
        echo "     ✗ Caught error: {$e->getMessage()}\n";
        echo "  2. Trying fallback operation...\n";
        $fallback = yield makeApiRequest('/api/fallback', 0.1);
        echo "     ✓ Fallback successful: {$fallback['data']}\n";

        return $fallback;
    }
});

$coroutine2->promise()->then(function ($data) use ($loop): void {
    echo "\n  Fallback operation completed!\n";
    $loop->stop();
});

$loop->run();
echo "\n";

// Example 3: Conditional async operations
echo "Example 3: Conditional Async Operations\n";
echo "---------------------------------------\n";

$coroutine3 = Coroutine::create(function (): Generator {
    echo "  1. Checking if user is authenticated...\n";
    $authCheck = yield makeApiRequest('/api/auth/check', 0.1);
    echo "     ✓ Auth check: {$authCheck['data']}\n";

    if (strpos($authCheck['data'], 'authenticated') !== false) {
        echo "  2. User is authenticated, fetching profile...\n";
        $profile = yield makeApiRequest('/api/user/profile', 0.1);
        echo "     ✓ Profile: {$profile['data']}\n";

        return $profile;
    } else {
        echo "  2. User not authenticated, redirecting to login...\n";
        $login = yield makeApiRequest('/api/auth/login', 0.1);
        echo "     ✓ Login: {$login['data']}\n";

        return $login;
    }
});

$coroutine3->promise()->then(function ($data) use ($loop): void {
    echo "\n  Operation completed based on condition!\n";
    $loop->stop();
});

$loop->run();
echo "\n";

echo "All examples completed!\n";
