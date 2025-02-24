<?php

namespace DigitalNode\Larafields\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use WP_Error;
use WP_User;

class ApplicationPasswordAuth
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (empty($authHeader)) {
            return response('Authentication required', 401);
        }

        $parts = explode(' ', $authHeader);
        if (count($parts) !== 2 || strtolower($parts[0]) !== 'basic') {
            return response('Invalid authentication format', 401);
        }

        try {
            $credentials = base64_decode($parts[1]);
            if ($credentials === false) {
                throw new \Exception('Invalid base64 encoding');
            }

            list($username, $password) = explode(':', $credentials);

            if (empty($username) || empty($password)) {
                throw new \Exception('Invalid credentials format');
            }
        } catch (\Exception $e) {
            return response('Invalid credentials format', 401);
        }

        $user = get_user_by('login', $username);

        if (! $user) {
            return response('User not found', 401);
        }

        $user = wp_authenticate_application_password($user, $username, $password);

        if ($user instanceof WP_Error) {
            return response('Invalid credentials', 401);
        }

        wp_set_current_user($user->ID, $user->user_login);

        return $next($request);
    }
}
