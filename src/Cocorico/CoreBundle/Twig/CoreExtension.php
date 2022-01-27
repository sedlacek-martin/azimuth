<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Twig;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\ListingImage;
use Cocorico\CoreBundle\Utils\PHP;
use Cocorico\UserBundle\Entity\UserImage;
use Lexik\Bundle\CurrencyBundle\Twig\Extension\CurrencyExtension;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class CoreExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    protected $currencyExtension;

    protected $translator;

    protected $session;

    protected $locales;

    protected $timeUnit;

    protected $timeUnitIsDay;

    protected $timeZone;

    protected $daysDisplayMode;

    protected $timesDisplayMode;

    protected $timeUnitFlexibility;

    protected $timeUnitAllDay;

    protected $timeHoursAvailable;

    protected $allowSingleDay;

    protected $endDayIncluded;

    protected $listingDefaultStatus;

    protected $listingPricePrecision;

    protected $currencies;

    protected $defaultCurrency;

    protected $currentCurrency;

    protected $priceMin;

    protected $priceMax;

    protected $feeAsOfferer;

    protected $feeAsAsker;

    protected $displayMarker;

    protected $bookingExpirationDelay;

    protected $bookingAcceptationDelay;

    protected $bookingValidationMoment;

    protected $bookingValidationDelay;

    protected $bookingPriceMin;

    protected $vatRate;

    protected $includeVat;

    protected $displayVat;

    protected $listingSearchMinResult;

    protected $listingDuplication;

    protected $minStartTimeDelay;

    protected $addressDelivery;

    /**
     * @param CurrencyExtension   $currencyExtension
     * @param TranslatorInterface $translator
     * @param Session             $session
     * @param array               $parameters
     */
    public function __construct(
        $currencyExtension,
        TranslatorInterface $translator,
        Session $session,
        array $parameters
    ) {
        //Services
        $this->currencyExtension = $currencyExtension;
        $this->translator = $translator;
        $this->session = $session;

        $parameters = $parameters['parameters'];

        $this->locales = $parameters['cocorico_locales'];

        //Time unit
        $this->timeUnit = $parameters['cocorico_time_unit'];
        $this->timeUnitIsDay = ($this->timeUnit % 1440 == 0) ? true : false;
        $this->timeZone = $parameters['cocorico_time_zone'];
        $this->timeUnitAllDay = $parameters['cocorico_time_unit_allday'];
        $this->timeUnitFlexibility = $parameters['cocorico_time_unit_flexibility'];
        $this->daysDisplayMode = $parameters['cocorico_days_display_mode'];
        $this->timesDisplayMode = $parameters['cocorico_times_display_mode'];
        $this->timeHoursAvailable = $parameters['cocorico_time_hours_available'];

        //Currencies
        $this->currencies = $parameters['cocorico_currencies'];
        $this->defaultCurrency = $parameters['cocorico_currency'];
        $this->currentCurrency = $session->get('currency', $this->defaultCurrency);

        //Fees
        $this->feeAsOfferer = $parameters['cocorico_fee_as_offerer'];
        $this->feeAsAsker = $parameters['cocorico_fee_as_asker'];

        //Status
        $this->listingDefaultStatus = $parameters['cocorico_listing_availability_status'];

        //Prices
        $this->listingPricePrecision = $parameters['cocorico_listing_price_precision'];
        $this->priceMin = $parameters['cocorico_listing_price_min'];
        $this->priceMax = $parameters['cocorico_listing_price_max'];
        $this->bookingPriceMin = $parameters['cocorico_booking_price_min'];

        //Map
        $this->displayMarker = $parameters['cocorico_listing_map_display_marker'];

        $this->listingSearchMinResult = $parameters['cocorico_listing_search_min_result'];
        $this->listingDuplication = $parameters['cocorico_listing_duplication'];

        $this->allowSingleDay = $parameters['cocorico_booking_allow_single_day'];
        $this->endDayIncluded = $parameters['cocorico_booking_end_day_included'];

        //Delay
        $this->bookingExpirationDelay = $parameters['cocorico_booking_expiration_delay'];
        $this->bookingAcceptationDelay = $parameters['cocorico_booking_acceptation_delay'];
        $this->bookingValidationMoment = $parameters['cocorico_booking_validated_moment'];
        $this->bookingValidationDelay = $parameters['cocorico_booking_validated_delay'];
        $this->minStartTimeDelay = $parameters['cocorico_booking_min_start_time_delay'];

        //VAT
        $this->vatRate = $parameters['cocorico_vat'];
        $this->includeVat = $parameters['cocorico_include_vat'];
        $this->displayVat = $parameters['cocorico_display_vat'];

        $this->addressDelivery = $parameters['cocorico_user_address_delivery'];
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('repeat', [$this, 'stringRepeatFilter']),
            new Twig_SimpleFilter('ucwords', 'ucwords'),
            new Twig_SimpleFilter('format_price', [$this, 'formatPriceFilter']),
            new Twig_SimpleFilter('strip_private_info', [$this, 'stripPrivateInfoFilter']),
        ];
    }

    /**
     * @param $input
     * @param $multiplier
     * @return string
     */
    public function stringRepeatFilter($input, $multiplier)
    {
        return str_repeat($input, $multiplier);
    }

    /**
     * @param int    $price
     * @param string $locale
     * @param int    $precision
     * @param bool   $convert
     * @return string
     */
    public function formatPriceFilter($price, $locale, $precision = null, $convert = true)
    {
        if (is_null($precision)) {
            $precision = $this->listingPricePrecision;
        }

        $targetCurrency = $this->currentCurrency;
        if (!$convert) {
            $targetCurrency = $this->defaultCurrency;
        }

        $this->currencyExtension->getFormatter()->setLocale($locale);
        if ($price > 0) {
            $price = $this->currencyExtension->convert($price, $targetCurrency, !$precision);
        } else {
            $price = 0;
        }

        $price = $this->currencyExtension->format($price, $targetCurrency, $precision);

        return $price;
    }

    /**
     * @param string $text
     * @param array  $typeInfo
     * @param string $replaceBy Text replacement translated
     *
     * @return string
     */
    public function stripPrivateInfoFilter(
        $text,
        $typeInfo = ['phone', 'email', 'domain'],
        $replaceBy = 'default'
    ) {
        if ($replaceBy == 'default') {
            $replaceBy = $this->translator->trans(
                'private_info_replacement',
                [],
                'cocorico'
            );
        }

        return PHP::strip_texts($text, $typeInfo, $replaceBy);
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'session_upload_progress_name', function () {
                    return ini_get('session.upload_progress.name');
                }
            ),
            new Twig_SimpleFunction('currencySymbol', [$this, 'currencySymbolFunction']),
            new Twig_SimpleFunction('staticProperty', [$this, 'staticProperty']),
        ];
    }

    /**
     * Get currency symbol of currency arg
     *
     * @param $currency
     * @return null|string
     */
    public function currencySymbolFunction($currency)
    {
        return Intl::getCurrencyBundle()->getCurrencySymbol($currency);
    }

    /**
     * Get static properties values
     *
     * @param string $class
     * @param string $property
     * @return mixed
     */
    public function staticProperty($class, $property)
    {
        if (property_exists($class, $property)) {
            return $class::$$property;
        }

        return null;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getGlobals()
    {
        $listing = new ReflectionClass(Listing::class);
        $listingConstants = $listing->getConstants();

        $listingImage = new ReflectionClass(ListingImage::class);
        $listingImageConstants = $listingImage->getConstants();

        $userImage = new ReflectionClass(UserImage::class);
        $userImageConstants = $userImage->getConstants();

        return [
            'locales' => $this->locales,
            'ListingConstants' => $listingConstants,
            'ListingImageConstants' => $listingImageConstants,
            'UserImageConstants' => $userImageConstants,
            'timeUnit' => $this->timeUnit,
            'timeUnitIsDay' => $this->timeUnitIsDay,
            'timeZone' => $this->timeZone,
            'timeUnitAllDay' => $this->timeUnitAllDay,
            'timeHoursAvailable' => $this->timeHoursAvailable,
            'daysDisplayMode' => $this->daysDisplayMode,
            'timesDisplayMode' => $this->timesDisplayMode,
            'timeUnitFlexibility' => $this->timeUnitFlexibility,
            'allowSingleDay' => $this->allowSingleDay,
            'endDayIncluded' => $this->endDayIncluded,
            'listingDefaultStatus' => $this->listingDefaultStatus,
            'listingPricePrecision' => $this->listingPricePrecision,
            'currencies' => $this->currencies,
            'defaultCurrency' => $this->defaultCurrency,
            'currentCurrency' => $this->currentCurrency,
            'priceMin' => $this->priceMin,
            'priceMax' => $this->priceMax,
            'feeAsOfferer' => $this->feeAsOfferer,
            'feeAsAsker' => $this->feeAsAsker,
            'displayMarker' => $this->displayMarker,
            'bookingExpirationDelay' => $this->bookingExpirationDelay,
            'bookingAcceptationDelay' => $this->bookingAcceptationDelay,
            'bookingValidationMoment' => $this->bookingValidationMoment,
            'bookingValidationDelay' => $this->bookingValidationDelay,
            'bookingPriceMin' => $this->bookingPriceMin,
            'vatRate' => $this->vatRate,
            'includeVat' => $this->includeVat,
            'displayVat' => $this->displayVat,
            'listingSearchMinResult' => $this->listingSearchMinResult,
            'listingDuplication' => $this->listingDuplication,
            'minStartTimeDelay' => $this->minStartTimeDelay,
            'addressDelivery' => $this->addressDelivery,
        ];
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getName()
    {
        return 'core_extension';
    }
}
