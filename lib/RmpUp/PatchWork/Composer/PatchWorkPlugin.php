<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PatchWorkPlugin.php
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
 * @since      2019-10-01
 */

declare(strict_types=1);

namespace RmpUp\PatchWork\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use patchwork\lib\RmpUp\PatchWork\Patcher\PatchException;
use RmpUp\PatchWork\Finder;
use RmpUp\PatchWork\PackagesParser;
use RmpUp\PatchWork\StrategyFactory;

/**
 * PatchWorkPlugin
 *
 * @copyright  2019 Mike Pretzlaw (https://mike-pretzlaw.de)
 * @since      2019-10-01
 * @internal
 */
class PatchWorkPlugin implements PluginInterface, EventSubscriberInterface
{
    protected const PACKAGE_TYPE = 'patchwork';

    /**
     * @var Composer
     */
    private $composer;
    
    /**
     * @var IOInterface
     */
    private $io;
    /**
     * @var StrategyFactory
     */
    private $strategyFactory;

    /**
     * Apply plugin modifications to Composer
     *
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     * * The method name to call (priority defaults to 0)
     * * An array composed of the method name to call and the priority
     * * An array of arrays composed of the method names to call and respective
     *   priorities, or 0 if unset
     *
     * For instance:
     *
     * * array('eventName' => 'methodName')
     * * array('eventName' => array('methodName', $priority))
     * * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Right after "post-install-cmd" where more package installer reside
            'pre-autoload-dump' => ['patch', 0]
        ];
    }

    /**
     * @param Event $event
     */
    public function patch($event): void
    {
        $baseDir = dirname(realpath(Factory::getComposerFile())) . DIRECTORY_SEPARATOR;

        $finder = $this->patchFinder(
            array_merge(
                $event->getComposer()->getPackage()->getRequires(),
                $event->getComposer()->getPackage()->getDevRequires()
            )
        );

        $localRepository = $event->getComposer()->getRepositoryManager()->getLocalRepository();

        // Iterate config
        foreach ($this->getConfig() as $directory => $patches) {
            $fullPath = $baseDir . ltrim($directory, DIRECTORY_SEPARATOR);

            if ($this->io->isDebug()) {
                // Escaped to prevent special chars in output.
                $this->io->write('Resolving patchwork strategy for ' . escapeshellarg($fullPath));
            }

            if (!is_dir($fullPath)) {
                $this->io->write(
                    sprintf(
                        '<warning>PatchWork: Skipping missing directory "%s"</warning>',
                        escapeshellarg($directory) // Escaped to prevent special chars in output.
                    )
                );
            }

            $patches = (array) $patches; // In case it's not an array (but a string e.g.)

            if ($this->io->isDebug()) {
                $this->io->write(sprintf('Applying %d patch(es) ...', count($patches)));
            }

            $patcher = $this->strategy($event, $fullPath);

            if (null === $patcher) {
                $this->io->writeError('PatchWork: No patcher available');
            }

            foreach ($patches as $patchPattern) {
                try {
                    $patcher->applyPatches(
                        $this->parser()->parse(
                            $finder->find($patchPattern),
                            $localRepository
                        )
                    );
                } catch (PatchException $e) {
                    $this->io->writeError('PatchWork: ' . $e->getMessage());
                }
            }
        }
    }

    private function parser(): PackagesParser
    {
        static $parser;

        if (null === $parser) {
            $parser = new PackagesParser($this->composer->getConfig()->get('vendor-dir'));
        }

        return $parser;
    }

    private function strategy($event, $dir)
    {
        if (null === $this->strategyFactory) {
            $this->strategyFactory = new StrategyFactory();
        }

        return $this->strategyFactory->create($event, $dir);
    }

    private function getConfig(?string $section = null)
    {
        $extra = $this->composer->getPackage()->getExtra();

        if (null === $section) {
            return $extra[static::PACKAGE_TYPE] ?? [];
        }

        return $extra[static::PACKAGE_TYPE][$section] ?? null;
    }

    private function patchFinder(array $packageLinks)
    {
        return new Finder($packageLinks);
    }
}
