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
            $domain = '__messages__';
        }

        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        // Change translation domain to '__messages__' if a translation can't be found in the
        // current domain
        if ('__messages__' !== $domain && false === $this->catalogues[$locale]->has((string) $id, $domain)) {
            $domain = '__messages__';
        }

        return strtr($this->catalogues[$locale]->get((string) $id, $domain), $parameters);
    }

}
