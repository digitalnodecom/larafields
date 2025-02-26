<?php

namespace DigitalNode\Larafields\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use WP_Error;

class ApplicationPasswordAuth
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (empty($authHeader)) {
            return response()->json([
                'message' => 'Authentication required',
            ], 401);
        }

        $parts = explode(' ', $authHeader);
        if (count($parts) !== 2 || strtolower($parts[0]) !== 'basic') {
            return response()->json([
                'message' => 'Invalid authentication format',
            ], 401);
        }

        try {
            $credentials = base64_decode($parts[1]);
            if ($credentials === false) {
                return response()->json([
                    'message' => 'Invalid base64 encoding',
                ], 401);
            }

            [$username, $password] = explode(':', $credentials);

            if (empty($username) || empty($password)) {
                return response()->json([
                    'message' => 'Username or password is missing.',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid credentials format',
            ], 401);
        }

        $user = get_user_by('login', $username);

        if (! $user) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (! class_exists('\WP_Application_Passwords') || ! wp_is_application_passwords_available_for_user($user)) {
            return response()->json([
                'message' => 'Application passwords not available',
            ], 401);
        }

        $password = preg_replace('/[^a-z\d]/i', '', $password);
        $hashed_passwords = \WP_Application_Passwords::get_user_application_passwords($user->ID);

        foreach ($hashed_passwords as $key => $item) {
            if (wp_check_password($password, $item['password'], $user->ID)) {
                wp_set_current_user($user->ID, $user->user_login);
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }
}
