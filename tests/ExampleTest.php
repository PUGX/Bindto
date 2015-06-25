<?php

namespace OB;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\FilesystemCache;
use OB\Mapper\MapperStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

class ExampleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function example()
    {
        $binder = Binder::createSimpleProductionBinder();

        $request = $this->createRequest();

        $bindResult = $binder->bind($request, \OB\Fixtures\DTOCreatePage::class);

        $this->assertTrue($bindResult->isValid(), $bindResult->getViolations());
    }

    /**
     * @return Request
     */
    private function createRequest()
    {
        $vars = [
            'title' => 'my-slug',
            'body' => 'Lorem ipsum dolor sit amet, consectetuer adipi',
            'seoTitle' => 'slug of lorem ',
            'seoDescription' => 'Lorem ipsum dolor sit amet, consectetuer adipi'
        ];

        $request = Request::create('http://test.com/foo', 'POST', $vars, [], [], []);
        return $request;
    }
}
