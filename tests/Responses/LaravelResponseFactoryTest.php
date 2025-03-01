<?php

namespace League\Glide\Responses;

use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaravelResponseFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance()
    {
        $this->assertInstanceOf(
            'League\Glide\Responses\LaravelResponseFactory',
            new LaravelResponseFactory()
        );
    }

    public function testCreate()
    {
        $this->cache = Mockery::mock('League\Flysystem\FilesystemOperator', function ($mock) {
            $mock->shouldReceive('mimeType')->andReturn('image/jpeg')->once();
            $mock->shouldReceive('fileSize')->andReturn(0)->once();
            $mock->shouldReceive('readStream')->andReturn(fopen('php://memory', 'r'))->once();
        });

        $factory = new LaravelResponseFactory();
        $response = $factory->create($this->cache, '');

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));
        $this->assertEquals('0', $response->headers->get('Content-Length'));
        $this->assertStringContainsString(gmdate('D, d M Y H:i', strtotime('+1 year')), $response->headers->get('Expires'));
        $this->assertEquals('max-age=31536000, public', $response->headers->get('Cache-Control'));
    }
}
