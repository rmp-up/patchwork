<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * StrategyInterface.php
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

namespace RmpUp\PatchWork\Patcher;

use Composer\Package\Link;
use patchwork\lib\RmpUp\PatchWork\Patcher\PatchException;

/**
 * StrategyInterface
 *
 * @copyright  2019 Mike Pretzlaw (https://mike-pretzlaw.de)
 * @since      2019-10-05
 */
interface PatcherInterface
{
    /**
     * PatcherInterface constructor.
     *
     * @param string $vendorDir
     * @param string $fullTargetPath Target path where patch will be applied.
     * @param string $executable     Path to executable.
     */
    public function __construct(string $vendorDir, string $fullTargetPath, string $executable);

    /**
     * @param array $packagesToPatches
     *
     * @return void
     *
     * @throws PatchException
     */
    public function applyPatches($packagesToPatches): void;
}
