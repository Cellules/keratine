<?php
namespace Keratine\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;


class TimeToIntegerTransformer implements DataTransformerInterface
{
    /**
     * Transforms an integer to a string (time at format hh:ii:ss).
     *
     * @param  integer $time
     * @return string|null
     */
    public function transform($time)
    {
        if (null === $time) {
            return '';
        }

        $hours = floor($time / 3600);
        $minutes = floor(($time % 3600) / 60);
        $seconds = $time % 3600 % 60;

        return sprintf('%s:%s:%s',sprintf('%02s', $hours), sprintf('%02s', $minutes), sprintf('%02s', $seconds));
    }

    /**
     * Transforms a string (time at format hh:ii:ss) to an integer.
     *
     * @param  string|null $time
     * @return integer
     */
    public function reverseTransform($time)
    {
        if (!$time) {
            return 0;
        }

        $timeSplit = explode(':', $time);

        $timeSplit = array_reverse($timeSplit);

        $time = 0;

        for ($i=0; $i < count($timeSplit); $i++) {
            $time += $timeSplit[$i] * pow(60, $i);
        }

        return $time;
    }
}