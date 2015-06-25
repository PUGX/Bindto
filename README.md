OBinder
=======

Obinder helps you to work with API and data validation using DTO and Commands.

Is really fast it doesn't use Reflection and binds the Request against a Class.

It is the smartest way to stop using the slow and complex Symfony Form component for API.

## Usage

Example, you have to create a Post/Patch/Put Api

### 1. Create a simple class that is the body of a request with Symfony validation component

``` php

use Symfony\Component\Validator\Constraints as Assert;

Class CreateFeedback {

    /**
     * @Assert\NotBlank(groups={"POST"})
     * @Assert\Type(type="string")
     */
    public $subject;

    /**
     * @Assert\NotNull(groups={"POST"})
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 10,
     *      max = 500)
     */
    public $body;
}
```

### 2. In your controller

if you use Silex you may want to enforce the input validation,
improving the example at http://silex.sensiolabs.org/doc/usage.html#example-post-route:

``` php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->post('/feedback', function (Request $request) {

    $binder = Binder::createSimpleProductionBinder();
    $result = $binder->bind($request, CreateFeedback::class);
    if (!$result->isValid()) {
        throw new \Exception($result->getViolations());
    }
    $createFeedBack = $result->getData();
    mail($createFeedBack->subject, '[YourSite] Feedback', $createFeedBack->body);

    return new Response('Thank you for your feedback!', 201);
});

$app->run();

```





## Note Partially patch

Use the validation groups if you want to PATCH partially:

``` php
/**
 * @Assert\NotNull(groups={"POST"})
 * @Assert\Type(type="string")
 * @Assert\Length(
 *      min = 10,
 *      max = 500)
 */
public $body;
```

With the POST request all the assertions will be used,
with the PUT and PATCH only the `Type` and `Length`,
you can now update DTO partially.

## POST-ing

``` php
use Symfony\Component\HttpFoundation\Request;

public function postPageAction(Request $request)
{
    $binder = Binder::createSimpleProductionBinder();
    $result = $binder->bind($request, DTO/CreatePage::class)
    if (!$result->isValid()) {
        //do somethings with $result->getViolations()
    }
    // $result->getData() has a new filled instance of /DTO/Page
    // do something wih $result->getData();
    // $page = Model\Page::fromCreatePage($result->getData());
    // $this->orm->persist($page);
    // ...
}

```

## PATCH-ing

The problem is that you can have partial object of the DTO/CreatePage


For patch you need to have a valid object


## TODO

- collection?
- twig helper
- tests decouple maptest from bindertest
- silex provider
- sf bundle

## Run tests

``` bash
composer dump-autoload
bin/phpunit
```