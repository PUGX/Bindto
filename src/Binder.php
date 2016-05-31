<?php

namespace Bindto;

use Bindto\Mapper\MapperStrategy;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\FilesystemCache;

class Binder
{
    /** @var ValidatorInterface */
    private $validator;
    /** @var MapperInterface */
    private $mapper;
    /** @var array */
    private $defaultGroups;

    public function __construct(ValidatorInterface $validator, MapperInterface $mapper, $addDefaultGroups = ['Default'])
    {
        $this->validator = $validator;
        $this->mapper = $mapper;
        if (!is_array($addDefaultGroups)) {
            $addDefaultGroups = [$addDefaultGroups];
        }
        $this->defaultGroups = $addDefaultGroups;
    }

    public static function createDefaultBinder()
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
        $mapper = MapperStrategy::createDefaultMapperStrategy();

        return new self($validator, $mapper);
    }

    public static function createSimpleProductionBinder()
    {
        if (!extension_loaded('apc') || false === @apc_cache_info()) {
            $readerCache = new CachedReader(new AnnotationReader(), new FilesystemCache(sys_get_temp_dir().'/Bindto'));
        } else {
            $readerCache = new CachedReader(new AnnotationReader(), new ApcCache());
        }

        $builder = Validation::createValidatorBuilder();
        $builder->setTranslationDomain('validators');
        $builder->addObjectInitializers([]);
        $builder->enableAnnotationMapping($readerCache);
        $validator = $builder->getValidator();

        $mapper = MapperStrategy::createDefaultMapperStrategy();

        return new self($validator, $mapper);
    }

    /**
     * @param mixed $request
     * @param mixed $object
     * @param string[] $validationGroups Groups to apply validation to
     *
     * @return BindResult
     *
     * @throws \Exception
     */
    public function bind($request, $object, array $validationGroups = [])
    {
        if (!is_object($object)) {
            $object = new $object();
        }

        $newObject = $this->mapper->map($request, $object);

        $groups = $this->defaultGroups;

        if (method_exists($request, 'getMethod')) {
            $groups[] = $request->getMethod();
        }

        $groups = array_merge($groups, $validationGroups);

        $issues = $this->validator->validate($object, null, $groups);

        return $this->createBindResultFromFilledObject($issues, $newObject);
    }

    private function createBindResultFromFilledObject($issues, $object)
    {
        return new BindResult($object, $issues);
    }
}
