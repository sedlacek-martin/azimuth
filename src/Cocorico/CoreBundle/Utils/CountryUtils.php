<?php

namespace Cocorico\CoreBundle\Utils;

use Symfony\Component\Intl\Intl;

class CountryUtils
{

    /**
     * @param string $iso
     * @return string
     */
    public static function getCountryName(string $iso): string
    {
        $countries = Intl::getRegionBundle()->getCountryNames();
        return $countries[$iso] ?? '';
    }


}