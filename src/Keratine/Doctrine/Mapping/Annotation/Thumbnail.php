<?php

namespace Keratine\Doctrine\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Indexable annotation
 *
 * @author Quentin Aupetit <quentin.aupetit@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class Thumbnail extends Annotation
{
    /** @var string */
    public $path = '';

    /** @var array */
    public $sizes = array(array(100, 100));
}