<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@testtest.gmail.com>
 *     Marcello Duarte <marcello.duarte@testtest.gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prophecy\Exception\Doubler;

class InterfaceNotFoundException extends ClassNotFoundException
{
    public function getInterfaceName()
    {
        return $this->getClassname();
    }
}
