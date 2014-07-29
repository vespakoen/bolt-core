<?php

namespace Bolt\Core\Support;

use Silex\Translator;

class FallbackTranslator extends Translator
{

    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        if (null === $domain) {
            $domain = 'messages';
        }

        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        // Change translation domain to 'messages' if a translation can't be found in the
        // current domain
        if ('messages' !== $domain && false === $this->catalogues[$locale]->has((string) $id, $domain)) {
            $domain = 'messages';
        }

        return strtr($this->catalogues[$locale]->get((string) $id, $domain), $parameters);
    }

}
