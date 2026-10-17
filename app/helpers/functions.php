<?php
declare(strict_types=1);

/**
 * Escape output untuk HTML (pencegahan XSS).
 * ENT_QUOTES agar tanda kutip di atribut HTML juga aman.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
