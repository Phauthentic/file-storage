<?php

/**
 * Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * @author    Florian Krämer
 * @link      https://github.com/Phauthentic
 * @license   https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace Phauthentic\Storage\Test\TestCase;

use ArrayIterator;
use League\Flysystem\Adapter\NullAdapter;
use Phauthentic\Infrastructure\Storage\AdapterCollection;
use Phauthentic\Test\TestCase\TestCase;

/**
 * AdapterCollectionTest
 */
class AdapterCollectionTest extends TestCase
{
    /**
     * @return false
     */
    public function testAdapterCollection(): void
    {
        $collection = new AdapterCollection();
        $adapter = new NullAdapter();

        $this->assertFalse($collection->has('doesnotexist'));

        $result = $collection->getIterator();
        $this->assertInstanceOf(ArrayIterator::class, $result);

        $collection->add('null', $adapter);
        $this->assertTrue($collection->has('null'));
        $collection->empty();
        $this->assertFalse($collection->has('null'));

        $result = $collection->getNameToClassmap();
        $this->assertEquals([], $result);
    }
}
