<?php

declare(strict_types=1);

namespace SDOSA\Exceptions;

/** Thrown when a Hijri (year, month, day) triple is not a real calendar date. */
final class InvalidHijriDate extends HijriException
{
}
