<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PackagesParser.php
 *
 * LICENSE: This source file is created by the company around Mike Pretzlaw
 * located in Germany also known as rmp-up. All its contents are proprietary
 * and under german copyright law. Consider this file as closed source and/or
 * without the permission to reuse or modify its contents.
 * This license is available through the world-wide-web at the following URI:
 * https://mike-pretzlaw.de/license-generic.txt . If you did not receive a copy
 * of the license and are unable to obtain it through the web, please send a
 * note to mail@mike-pretzlaw.de so we can mail you a copy.
 *
 * @package    work
 * @copyright  2019 Mike Pretzlaw
 * @license    https://mike-pretzlaw.de/license-generic.txt
 * @link       https://project.mike-pretzlaw.de/work
 * @since      2019-10-06
 */

declare(strict_types=1);

namespace RmpUp\PatchWork;

use Composer\Package\CompletePackage;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Repository\RepositoryInterface;
use Composer\Script\Event;
use Generator;

/**
 * PackagesParser
 *
 * @copyright  2019 Mike Pretzlaw (https://mike-pretzlaw.de)
 * @since      2019-10-06
 */
class PackagesParser
{
    const EXTRA_FIELD = 'patchwork';
    /**
     * @var string
     */
    private $vendorDir;

    public function __construct(string $vendorDir)
    {
        $this->vendorDir = $vendorDir;
    }

    /**
     * @param Link[] $packageLinks
     * @param Event  $event
     *
     * @return Generator|array[]
     */
    public function parse($packageLinks, RepositoryInterface $repo): Generator
    {
        foreach ($packageLinks as $require) {
            $pack = $repo->findPackage($require->getTarget(), $require->getConstraint());

            if (false === $pack instanceof CompletePackage || self::EXTRA_FIELD !== $pack->getType()) {
                continue;
            }

            $extra = $pack->getExtra();

            if (!array_key_exists(static::EXTRA_FIELD, $extra)) {
                continue;
            }

            yield $pack->getName() => $this->globPatchFiles(
                (array) $extra[static::EXTRA_FIELD],
                $this->vendorDir,
                $pack
            );
        }
    }

    /**
     * @param array            $patches
     * @param string           $vendorDir
     * @param PackageInterface $pack
     *
     * @return array
     */
    private function globPatchFiles($patches, $vendorDir, PackageInterface $pack): array
    {
        $patchFiles = [];
        foreach ($patches as $patch) {
            $patchFiles[] = glob($vendorDir . '/' . $pack->getName() . '/' . $patch, GLOB_NOSORT);
        }

        return array_merge(...$patchFiles);
    }
}
