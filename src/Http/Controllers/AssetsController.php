<?php

namespace DigitalNode\Larafields\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class AssetsController extends Controller
{
    public function css(Request $request)
    {
        $path = __DIR__.'/../../../resources/styles/public/larafields.css';

        if (! File::exists($path)) {
            return new Response('CSS file not found', 404);
        }

        $content = File::get($path);

        $etag = md5($content);
        $lastModified = File::lastModified($path);

        if (
            $request->header('If-None-Match') === $etag ||
            $request->header('If-Modified-Since') === gmdate('D, d M Y H:i:s', $lastModified).' GMT'
        ) {
            return new Response(null, 304);
        }

        return (new Response($content, 200))
            ->header('Content-Type', 'text/css')
            ->header('ETag', $etag)
            ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified).' GMT')
            ->header('Cache-Control', config('larafields.assets.cache_control', 'public, max-age=31536000'));
    }

    public function js(Request $request)
    {
        $path = __DIR__.'/../../../resources/js/public/larafields.min.js';
        
        // Use non-minified version in development
        if (config('app.env') === 'local' && !File::exists($path)) {
            $path = __DIR__.'/../../../resources/js/public/larafields.js';
        }

        if (! File::exists($path)) {
            return new Response('JavaScript file not found', 404);
        }

        $content = File::get($path);

        $etag = md5($content);
        $lastModified = File::lastModified($path);

        if (
            $request->header('If-None-Match') === $etag ||
            $request->header('If-Modified-Since') === gmdate('D, d M Y H:i:s', $lastModified).' GMT'
        ) {
            return new Response(null, 304);
        }

        return (new Response($content, 200))
            ->header('Content-Type', 'application/javascript')
            ->header('ETag', $etag)
            ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified).' GMT')
            ->header('Cache-Control', config('larafields.assets.cache_control', 'public, max-age=31536000'));
    }

    /**
     * Get the version string for cache busting.
     *
     * @return string
     */
    protected function getVersionString()
    {
        $packageJsonPath = __DIR__.'/../../../package.json';

        if (File::exists($packageJsonPath)) {
            $packageJson = json_decode(File::get($packageJsonPath), true);

            return $packageJson['version'] ?? '1.0.0';
        }

        return '1.0.0';
    }
}
