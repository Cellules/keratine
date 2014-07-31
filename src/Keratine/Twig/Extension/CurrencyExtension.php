<?php
namespace Keratine\Twig\Extension;

use Silex\Application;

use Locale;
use NumberFormatter;

class CurrencyExtension extends \Twig_Extension
{
    private $app;
    private $options;

    public function __construct()
    {
        if (!class_exists('NumberFormatter')) {
            throw new \RuntimeException('The intl extension is needed to use intl-based filters.');
        }
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            'number' => new \Twig_Filter_Method($this, 'numberFilter'),
            'currency' => new \Twig_Filter_Method($this, 'currencyFilter')
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'currency';
    }


    public function numberFilter($number, $style = 'decimal', $format = 'default', $currency = null, $locale = null )
    {
        $formatter = $this->getNumberFormatter(
            $locale !== null ? $locale : Locale::getDefault(),
            $style
        );

        $formatValues = array(
            'default'   => NumberFormatter::TYPE_DEFAULT,
            'int32'     => NumberFormatter::TYPE_INT32,
            'int64'     => NumberFormatter::TYPE_INT64,
            'double'    => NumberFormatter::TYPE_DOUBLE,
            'currency'  => NumberFormatter::TYPE_CURRENCY,
        );

        return $formatter->format(
            $number,
            $formatValues[$format]);
    }

    public function currencyFilter($number, $currency = null, $locale = null)
    {
        $formatter = $this->getNumberFormatter(
            $locale !== null ? $locale : Locale::getDefault(),
            'currency'
        );

        return $formatter->formatCurrency($number, $currency);
    }

    public function getNumberFormatter($locale, $style)
    {
        $styleValues = array(
            'decimal'       => NumberFormatter::DECIMAL,
            'currency'      => NumberFormatter::CURRENCY,
            'percent'       => NumberFormatter::PERCENT,
            'scientific'    => NumberFormatter::SCIENTIFIC,
            'spellout'      => NumberFormatter::SPELLOUT,
            'ordinal'       => NumberFormatter::ORDINAL,
            'duration'      => NumberFormatter::DURATION,
        );

        return NumberFormatter::create(
            $locale !== null ? $locale : Locale::getDefault(),
            $styleValues[$style]
        );
    }
}