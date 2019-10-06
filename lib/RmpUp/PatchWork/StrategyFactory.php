<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * StrategyFactory.php
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

use Composer\Script\Event;
use RmpUp\PatchWork\Patcher\PatcherInterface;
use RmpUp\PatchWork\Patcher\Patch;

/**
 * StrategyFactory
 *
 * @copyright  2019 Mike Pretzlaw (https://mike-pretzlaw.de)
 * @since      2019-10-05
 */
class StrategyFactory
{
    /**
     * Resolve class to exec
     *
     * For resolving a good strategy we define the order of strategies
     * and what executable is needed to fulfill them.
     *
     * @var array[]
     */
    private $classToExecutables = [
        Patch::class => [
            'patch',
            '/usr/bin/patch',
        ],
    ];

    /**
     * @param array $commands
     *
     * @return string|null First match for commands
     * or null when nothing matched.
     */
    private function resolveCommand(array $commands): ?string {
        // using builtin type command to check for "which"
        if (!$this->shellExec('type -t which')) {
            // "which" command is not there, wth?
            return null;
        }

        foreach ($commands as $command) {
            // Lookup
            if (!$this->shellExec('which '. escapeshellarg($command))) {
                return $command;
            }
        }

        return null;
    }

    public function create(Event $event, string $fullTargetPath): ?PatcherInterface
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');

        foreach ($this->classToExecutables as $class => $executables) {
            $executable = $this->resolveCommand($executables);

            if (null !== $executable) {
                return new $class($vendorDir, $fullTargetPath, $executable);
            }
        }

        $event->getIO()->writeError(
            sprintf(
                'PatchWork: Tools like patch or git are needed to apply '
            )
        );

        return null;
    }

    /**
     * @param $command
     *
     * @return string|null
     */
    private function shellExec($command)
    {
        return shell_exec($command);
    }
}
