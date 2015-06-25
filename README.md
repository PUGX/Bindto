Bindto
=======

#### Simplest way to bind Request to DTO/Commands.

Bindto helps you to work with API and data validation using DTO and Commands.

Is really fast (it doesn't use Reflection) and binds the Request against your class.

It's the smartest way to stop using the slow and complex Symfony Form component for API.

## Install

`composer require pugx/bindto`

## Usage

Example, you have to create a Post/Patch/Put Api

### 1. Create a simple class that is the body of a request

##### protip: you can use with Symfony validation component annotations

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

use Bindto\Binder;

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

## PATCH support with partial modification

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

With a POST request all the assertions will be used,
with a PUT and PATCH only the `Type` and `Length` assertions.


## TODO

- recursive binder?
- collection?
- twig helper?
- tests decouple maptest from bindertest
- silex provider?
- sf bundle?

## Run tests

``` bash
composer dump-autoload
bin/phpunit
```