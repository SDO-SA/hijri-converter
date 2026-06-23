<?php

declare(strict_types=1);

namespace SDOSA\Support;

/**
 * Small numeric helpers used by the conversion algorithm.
 *
 * @internal
 */
final class Math
{
    /**
     * Locate the right-most insertion point for $x in the sorted array $a,
     * keeping it sorted (equivalent to Python's bisect.bisect_right).
     *
     * @param int[] $a Sorted (ascending) array of integers.
     */
    public static function bisectRight(array $a, int $x): int
    {
        $lo = 0;
        $hi = count($a);

        while ($lo < $hi) {
            $mid = intdiv($lo + $hi, 2);
            if ($x < $a[$mid]) {
                $hi = $mid;
            } else {
                $lo = $mid + 1;
            }
        }

        return $lo;
    }

    /**
     * Integer division returning [quotient, remainder] with Python-style
     * floor semantics (remainder has the sign of the divisor).
     *
     * @return array{0:int,1:int}
     */
    public static function divmod(int $a, int $b): array
    {
        if ($b === 0) {
            throw new \InvalidArgumentException('Division by zero.');
        }

        $q = (int) floor($a / $b);
        $r = $a - $q * $b;

        return [$q, $r];
    }
}
