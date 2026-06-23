<?php

declare(strict_types=1);

// Known-good [gregorian "Y-m-d", hijri "Y-m-d"] pairs spread across the range.
dataset('conversion_pairs', [
    'mid-range'      => ['1982-12-02', '1403-02-17'],
    'gregorian min'  => ['1924-08-01', '1343-01-01'],
    'ramadan 1445'   => ['2024-03-11', '1445-09-01'],
    '15th century'   => ['1979-11-20', '1400-01-01'],
    'muharram 1445'  => ['2023-07-19', '1445-01-01'],
]);
