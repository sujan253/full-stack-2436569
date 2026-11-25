<?php
// TwigHelper.php
// Run "composer install" on MI-Linux to install Twig before this works
// Sujan - 5CS045

require_once __DIR__ . '/vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TwigHelper {

    private static $twig = null;

    // MI-Linux serves this app from a /~2436569/games-site/ subfolder, but a
    // local dev server runs it at the domain root - detect which one we're on.
    private static function basePath() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (strpos($host, 'mi-linux.wlv.ac.uk') !== false) {
            return '/~2436569/games-site/';
        }
        return '/';
    }

    public static function getTwig() {
        if (self::$twig === null) {
            $loader = new FilesystemLoader(__DIR__ . '/templates/twig');

            // cache off during development, set a path for production
            self::$twig = new Environment($loader, [
                'cache' => false,
                'autoescape' => 'html', // auto XSS escaping - brilliant feature of Twig
                'debug' => true,
            ]);

            // add useful custom functions to twig
            self::$twig->addFunction(new TwigFunction('asset', function($path) {
                return self::basePath() . ltrim($path, '/');
            }));

            self::$twig->addFunction(new TwigFunction('url', function($page, $params = []) {
                $q = $params ? '?' . http_build_query($params) : '';
                return self::basePath() . $page . $q;
            }));

            // pass logged in status to every template
            self::$twig->addGlobal('is_logged_in', !empty($_SESSION['user_id']));
            self::$twig->addGlobal('current_page', basename($_SERVER['PHP_SELF']));
        }
        return self::$twig;
    }

    // render a template and output it
    public static function render($template, $data = []) {
        // real HTTP security headers - a <meta http-equiv> tag is not honoured by
        // browsers for these, so they must be sent as actual response headers
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
        }

        // merge in flash message if there is one
        if (!empty($_SESSION['flash'])) {
            $data['flash'] = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }
        echo self::getTwig()->render($template, $data);
    }
}
?>
