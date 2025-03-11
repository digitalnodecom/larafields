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
        return $this->serveAsset(
            $request,
            __DIR__.'/../../../resources/styles/public/larafields.css',
            'text/css'
        );
    }

    public function js(Request $request)
    {
        return $this->serveAsset(
            $request,
            __DIR__.'/../../../resources/js/public/larafields.js',
            'application/javascript'
        );
    }

    public function tomSelectCss(Request $request)
    {
        return $this->serveAsset(
            $request,
            __DIR__.'/../../../resources/js/public/css/tom-select.css',
            'text/css'
        );
    }

    /**
     * Serve an asset file with proper headers.
     *
     * @param Request $request
     * @param string $path
     * @param string $contentType
     * @return Response
     */
    protected function serveAsset(Request $request, string $path, string $contentType)
    {
        if (! File::exists($path)) {
            return new Response('Asset file not found', 404);
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
            ->header('Content-Type', $contentType)
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
