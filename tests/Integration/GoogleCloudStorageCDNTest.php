<?php

namespace Tests\Integration\GoogleCloudStorage;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use Tests\BaseTestCase;

/**
 * Class GoogleCloudStorageCDNTest.
 * Test write/read/delete access for the Google Cloud Storage CDN bucket in this order.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class GoogleCloudStorageCDNTest extends BaseTestCase
{
    /** @var FilesystemAdapter $bucket */
    private $bucket;
    private const TEXT = "Lorem ipsum dolor sit amet consectur init.";


    public function setUp()
    {
        parent::setUp();
        $this->bucket = Storage::disk("cdn");
    }


    /**
     * Test write access to the GCS CDN bucket.
     *
     * @return string A random UUID which wil be used as common test file.
     *
     * @throws FileExistsException
     */
    public function testWrite(): string
    {
        $uuid = $this->faker->uuid;
        $result = $this->bucket->write("$uuid.txt", self::TEXT);
        $this->assertTrue($result);

        return $uuid;
    }


    /**
     * @depends testWrite
     *
     * Test read access to the GCS CDN bucket.
     *
     * @param string $uuid The text file UUID.
     *
     * @return string The text file UUID.
     * @throws FileNotFoundException
     */
    public function testRead(string $uuid): string
    {
        $result = $this->bucket->read("$uuid.txt");
        $this->assertEquals($result, self::TEXT);

        return $uuid;
    }


    /**
     * @depends testRead
     *
     * Test delete access to the GCS CDN bucket.
     *
     * @param string $uuid The text file UUID.
     */
    public function testDelete(string $uuid): void
    {
        $result = $this->bucket->delete("$uuid.txt");
        $this->assertTrue($result);
    }
}
