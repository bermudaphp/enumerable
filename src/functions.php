<?php

namespace Lobster\Arrayzy;

/**
 * @param array $data
 * @return Enumerable
 */
function collect(iterable $data = []) : Enumerable {
    return Collect::get($data);
}
