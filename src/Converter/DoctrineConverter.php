<?php
namespace Bindto\Converter;

use Bindto\Exception\ConversionException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\ORMException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DoctrineConverter extends AbstractConverter
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($value, $propertyName, array $options, $from)
    {
        $options = $this->resolveOptions($options);

        if ($value instanceof $options['entity']) {
            return $value;
        }

        $className = $options['entity'];
        $repo = $this->entityManager->getRepository($className);
        $arguments = $options['arguments'];
        $entity = null;

        // replace $value with the actual value
        array_walk($arguments, function(&$item) use ($value) {
            if ($item === '$value') {
                $item = $value;
            }
        });

        try {
            if (strlen($value) > 0) {
                $entity = call_user_func_array([$repo, $options['method']], $arguments);

                if ($entity === null) {
                    throw ConversionException::fromNotFound($propertyName, $value);
                }
            }
        } catch (\Exception $ex) {
            if ($ex instanceof ORMException) {
                throw ConversionException::fromSystem($propertyName, $value, $ex->getMessage(), 'conversion_exception.doctrine.exception',$ex);
            } else if ($ex instanceof \DomainException) {
                throw ConversionException::fromDomain($propertyName, $value, $ex->getMessage(), 'conversion_exception.doctrine.exception', $ex);
            }

            throw $ex;
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entity' => null,
            'method' => 'find',
            'arguments' => ['$value'],
        ]);
        $resolver->addAllowedTypes('entity', ['string']);
        $resolver->addAllowedTypes('method', ['string']);
        $resolver->addAllowedTypes('arguments', ['array']);
        $resolver->setRequired(['entity', 'method']);
    }
}
