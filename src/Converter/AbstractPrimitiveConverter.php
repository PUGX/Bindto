<?php
namespace Bindto\Converter;

use Bindto\Exception\ConversionException;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractPrimitiveConverter extends AbstractConverter
{
    /**
     * {@inheritdoc}
     */
    public function apply($value, $propertyName, array $options, $from)
    {
        $options = $this->resolveOptions($options);
        $isStrict = $options['strict'];

        // don't try converting if the value is already of the target type
        if (false === $this->needsConverting($value)) {
            return $value;
        }

        // if value is an object and we're not being asked to cast to an array then leave it as an object. this is
        // desirable in most situations as it exposes bugs in your validation workflow.
        if ((true === is_object($value)) && (!$this instanceof ArrayConverter)) {
            return $value;
        }

        $newValue = $this->onApply($value, $propertyName, $options, $from);

        // in strict mode we throw an exception rather than setting to null if we couldn't cast
        if (true === $isStrict) {
            if ((null === $newValue) && (null !== $value)) {
                throw ConversionException::fromDomain($propertyName, $value, 'Not a valid type', 'conversion_exception.primitive.not_a_valid_type');
            }
        }

        return $newValue;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'strict' => true,
        ]);
        $resolver->addAllowedTypes('strict', ['boolean']);
    }

    /**
     * Applies the primitive conversion rules.
     *
     * @param mixed  $value
     * @param string $propertyName
     * @param array  $options
     * @param mixed  $from
     * @return mixed
     */
    protected abstract function onApply($value, $propertyName, array $options, $from);

    /**
     * Checks if the value needs converting.
     *
     * @param mixed $value
     * @return bool
     */
    protected abstract function needsConverting($value);
}
