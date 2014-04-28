<?php
namespace Keratine\Doctrine\Util;

use Gedmo\Sluggable\Util\Urlizer as GedmoUrlizer;

class Urlizer extends GedmoUrlizer
{
    /**
     * Uses transliteration tables to convert any kind of utf8 character
     *
     * @param string $text
     * @param string $separator
     * @return string $text
     */
    public static function transliterate($text, $separator = '-')
    {
        $text = strip_tags($text);
        $text =  GedmoUrlizer::transliterate($text, $separator);
        return $text;
    }
}