<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Patch.php
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
 * Patch
 *
 * @copyright  2019 Mike Pretzlaw (https://mike-pretzlaw.de)
 * @since      2019-10-05
 */
class Patch extends AbstractPatcher
{
    /**
     * @param array $packageToPatches
     *
     * @throws PatchException
     */
    public function applyPatches($packageToPatches): void
    {
        foreach ($packageToPatches as $patches) {
            foreach ($patches as $patch) {
                $rejectFile = tempnam(sys_get_temp_dir(), 'patchwork');

                $command = escapeshellcmd($this->executable)
                    . ' -d ' . escapeshellarg($this->fullTargetPath)
                    . ' -p1 ' // makes git patch files yummy
                    . ' -f ' // do not ask for deletions
                    . ' -s ' // hush
                    . ' --merge=diff3 ' // detects if already applied
                    . ' -r ' . escapeshellarg($rejectFile) // no reject files to keep things clean
                    . ' < ' . escapeshellarg($patch)
                ;

                $hasFailure = 0;
                $output = '';
                exec($command, $output, $hasFailure);

                if ($hasFailure) {
                    throw new PatchException($output, $patch);
                }
            }
        }
    }

}
