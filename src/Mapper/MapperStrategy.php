<?php

namespace OB\Mapper;

use OB\Exception\MapperNotFoundException;
use OB\MapperInterface;

class MapperStrategy implements MapperInterface
{
    /** @var MapperInterface[] */
    private $strategies;
    /** @var MapperInterface */
    private $fallaback;

    public function __construct($fallback = null)
    {
        $this->strategies = [];
        $this->fallaback = $fallback;
    }

    public static function createDefaultMapperStrategy()
    {
        $mapper = new self(new StandardObjectMapper());
        $mapper->addStrategy(
            '\Psr\Http\Message\ServerRequestInterface',
            new ServerRequestPSR7Mapper()
        );
        $mapper->addStrategy(
            '\Symfony\Component\HttpFoundation\Request',
            new SymfonyRequestMapper()
        );

        return $mapper;
    }

    public function addStrategy($class, MapperInterface $mapper)
    {
        $this->strategies[$class] = $mapper;
    }

    public function addFallback(MapperInterface $mapper)
    {
        $this->fallaback = $mapper;
    }

    /**
     * {@inherit}
     *
     * @throws \Exception
     */
    public function map($from, $to)
    {
        foreach ($this->strategies as $class=>$strategy) {
            if ($from instanceof $class) {
                return $strategy->map($from, $to);
            }
        }

        if ($this->fallaback) {
           return $this->fallaback->map($from, $to);
        }

        throw new MapperNotFoundException($from);
    }
}