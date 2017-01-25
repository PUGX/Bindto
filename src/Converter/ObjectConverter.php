<?php
namespace Bindto\Converter;

use Bindto\Binder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectConverter extends AbstractConverter
{

    /**
     * @var Binder
     */
    private $binder;

    /**
     * @param Binder $binder
     */
    public function __construct(Binder $binder)
    {
        $this->binder = $binder;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($value, $propertyName, array $options, $from)
    {
        $options = $this->resolveOptions($options);
        $result = $this->binder->bind($value, $options['class']);

        return $result->getData();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => null,
        ]);
        $resolver->addAllowedTypes('class', ['string']);
    }
}
