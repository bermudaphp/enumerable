<?php

namespace Bermuda\Enumerable;

/**
 * @param iterable $items
 * @return Enumerable
 */
function collect(iterable $items = []): Enumerable
{
    return new Enumerable($items)
}
