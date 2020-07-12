<?php

namespace Bermuda;

/**
 * @param iterable $items
 * @return Enumerable
 */
function collect(iterable $items = []): Enumerable
{
    return new Enumerable($items);
}

/**
 * @param array $var
 * @return array
 */
function shuffle(array $var): array
{
    return usort($var, static function()
    {
        if (($x = random_int(0, 100)) == ($y = random_int(0, 100)))
        {
            return 0;
        }
        
        return ($x > $y) ? -1 : 1;
    });
}
