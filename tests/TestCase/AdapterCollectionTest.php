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
use RuntimeException;

/**
 * AdapterCollectionTest
 */
class AdapterCollectionTest extends TestCase
{
    /**
     * @return void
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

    /**
     * @return void
     */
    public function testAddDuplicateAdapterThrowsException(): void
    {
        $collection = new AdapterCollection();
        $adapter1 = new NullAdapter();
        $adapter2 = new NullAdapter();

        $collection->add('test', $adapter1);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('An adapter with the name `test` already exists in the collection');

        $collection->add('test', $adapter2);
    }

    /**
     * @return void
     */
    public function testRemove(): void
    {
        $collection = new AdapterCollection();
        $adapter = new NullAdapter();

        $collection->add('test', $adapter);
        $this->assertTrue($collection->has('test'));

        $collection->remove('test');
        $this->assertFalse($collection->has('test'));
    }

    /**
     * @return void
     */
    public function testGet(): void
    {
        $collection = new AdapterCollection();
        $adapter = new NullAdapter();

        $collection->add('test', $adapter);
        $result = $collection->get('test');

        $this->assertSame($adapter, $result);
    }

    /**
     * @return void
     */
    public function testGetNonExistentAdapterThrowsException(): void
    {
        $collection = new AdapterCollection();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A factory registered with the name `nonexistent` is not part of the collection.');

        $collection->get('nonexistent');
    }

    /**
     * @return void
     */
    public function testGetNameToClassmapWithAdapters(): void
    {
        $collection = new AdapterCollection();
        $adapter1 = new NullAdapter();
        $adapter2 = new NullAdapter();

        $collection->add('adapter1', $adapter1);
        $collection->add('adapter2', $adapter2);

        $result = $collection->getNameToClassmap();

        $this->assertEquals([
            'adapter1' => 'League\\Flysystem\\Adapter\\NullAdapter',
            'adapter2' => 'League\\Flysystem\\Adapter\\NullAdapter'
        ], $result);
    }
}
