<?php

namespace DigitalNode\FormMaker\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use WP_User;

class ApplicationPasswordAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Basic ')) {
            throw new \Exception('Basic auth header is missing');
        }

        $credentials = base64_decode(substr($header, 6));
        list($username, $password) = explode(':', $credentials);

        $user = get_user_by( 'login', $username );

        /* @var WP_User $user */
        $user = wp_authenticate_application_password(input_user: $user, username: $username, password: $password);

        if (! $user ) {
            throw new \Exception('Wrong credentials.');
        }

        $request->merge([
            'user' => collect($user->to_array())->except('user_pass')->all()
        ]);

        return $next($request);
    }
}
