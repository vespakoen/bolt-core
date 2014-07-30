<?php

namespace Bolt\Core\Provider\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;

use Bolt\Core\Support\FallbackTranslator;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class TranslationServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $path = $app['paths']['app'].'/translations';

        $directory = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory);

        $domains = array();
        foreach ($iterator as $file) {
            if ( ! in_array($file->getFilename(), array('.', '..'))) {
                $namespace = trim(str_replace($path, '', $file->getPath()), '/');
                $namespace = $namespace ? $namespace : '__messages__';
                $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
                $language = basename($file->getFilename(), '.' . $extension);

                $domains[$namespace][$language] = Yaml::parse(file_get_contents($file->getPathname()));
            }
        }

        $app['translator.domains'] = $domains;

        $app['translator'] = $app->share(function ($app) {
            $translator = new FallbackTranslator($app, $app['translator.message_selector']);

            // Handle deprecated 'locale_fallback'
            if (isset($app['locale_fallback'])) {
                $app['locale_fallbacks'] = (array) $app['locale_fallback'];
            }

            $translator->setFallbackLocales($app['locale_fallbacks']);

            $translator->addLoader('array', new ArrayLoader());
            $translator->addLoader('xliff', new XliffFileLoader());

            foreach ($app['translator.domains'] as $domain => $data) {
                foreach ($data as $locale => $messages) {
                    $translator->addResource('array', $messages, $locale, $domain);
                }
            }

            return $translator;
        });

    }

    public function boot(Application $app)
    {
    }

}
