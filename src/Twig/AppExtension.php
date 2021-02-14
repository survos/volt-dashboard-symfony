<?php

namespace App\Twig;

use Gajus\Dindent\Indenter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('cleanup', [$this, 'cleanup']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('function_name', [$this, 'doSomething']),
        ];
    }

    public function cleanup($value)
    {
        $indenter = new Indenter();
        return $indenter->indent($value);
//        $indenter = new \Gajus\Dindent\Indenter();
        /**
         * @param string $element_name Element name, e.g. "b".
         * @param ELEMENT_TYPE_BLOCK|ELEMENT_TYPE_INLINE $type
         * @return null
         */
//        $indenter->setElementType('foo', \Gajus\Dindent\Indenter::ELEMENT_TYPE_BLOCK);
//        $indenter->setElementType('bar', \Gajus\Dindent\Indenter::ELEMENT_TYPE_INLINE);
        // ...
    }
}
