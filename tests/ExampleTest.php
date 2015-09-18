<?php

namespace Bindto;

use Symfony\Component\HttpFoundation\Request;
use Bindto\Fixtures\DTOCreatePage;

class ExampleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function example()
    {
        $binder = Binder::createSimpleProductionBinder();

        $request = $this->createRequest();

        $bindResult = $binder->bind($request, DTOCreatePage::class);

        $this->assertTrue($bindResult->isValid(), "bind result should be valid");
        $this->assertEmpty($bindResult->getViolations(), "violations should be empty");
        $this->assertInstanceOf('Bindto\Fixtures\DTOCreatePage', $bindResult->getData(), "we should get back a DTOCreatePage object");
        $obj = $bindResult->getData();
        $this->assertEquals('my-slug', $obj->title, "request values should be successfully mapped in resulting object");
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
            'seoDescription' => 'Lorem ipsum dolor sit amet, consectetuer adipi',
            'respect' => 'robustness principle'     //this element should be ignored as it is not part of the class structure to be mapped
        ];

        $request = Request::create('http://test.com/foo', 'POST', $vars, [], [], []);

        return $request;
    }
}
