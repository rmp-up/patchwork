<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Finder.php
 *
 * LICENSE: This source file is created by the company around Mike Pretzlaw
 * located in Germany also known as rmp-up. All its contents are proprietary
 * and under german copyright law. Consider this file as closed source and/or
 * without the permission to reuse or modify its contents.
 * This license is available through the world-wide-web at the following URI:
 * https://rmp-up.de/license-generic.txt . If you did not receive a copy
 * of the license and are unable to obtain it through the web, please send a
 * note to mail@rmp-up.de so we can mail you a copy.
 *
 * @package    PatchWork
 * @copyright  2019 Mike Pretzlaw
 * @license    https://rmp-up.de/license-generic.txt
 * @link       https://rmp-up.de/patchwork
 * @since      2019-10-05
 */

declare(strict_types=1);

namespace RmpUp\PatchWork;

use Composer\Package\Link;
use Generator;

/**
 * Finder
 *
 * @copyright  2019 Mike Pretzlaw (https://mike-pretzlaw.de)
 * @since      2019-10-05
 */
class Finder
{
    /**
     * @var array|Link[]
     */
    private $packageLinks;

    /**
     * Finder constructor.
     *
     * @param Link[] $packageLinks
     */
    public function __construct(array $packageLinks)
    {
        $this->packageLinks = $packageLinks;
    }

    /**
     * @param string $pattern
     *
     * @return Link[]|Generator
     */
    public function find(string $pattern): Generator
    {
        if (array_key_exists($pattern, $this->packageLinks)) {
            // Found directly
            yield $pattern => $this->packageLinks[$pattern];
            return;
        }

        if ('*' === $pattern || '*/*' === $pattern) {
            // All links needed
            yield from $this->packageLinks;
            return;
        }

        $regExp = '@^'
            . str_replace('\\*', '.*', preg_quote($pattern, '@'))
            . '$@';

        foreach ($this->packageLinks as $packageName => $link) {
            if (preg_match($regExp, $packageName)) {
                yield $packageName => $link;
            }
        }
    }
}
