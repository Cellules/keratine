<?php

namespace Keratine\Lucene\Mapping\Annotation;

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
final class DocumentField extends Annotation
{
    /** @var string */
    public $type = 'unStored';
}