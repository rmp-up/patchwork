<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PatchException.php
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
 * @since      2019-10-06
 */

declare(strict_types=1);

namespace patchwork\lib\RmpUp\PatchWork\Patcher;

use Exception;
use Throwable;

/**
 * PatchException
 *
 * @copyright  2019 Mike Pretzlaw (https://mike-pretzlaw.de)
 * @since      2019-10-06
 */
class PatchException extends Exception
{
    public function __construct(string $message = '', string $patchFile = '')
    {
        parent::__construct(
            sprintf(
                'Patch "%s" could not be applied: %s',
                $patchFile,
                $message
            )
        );
    }
}
