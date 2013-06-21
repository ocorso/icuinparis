<?php
/**
 * Validator is the base class for all validators.
 *
 * Child classes must implement the {@link validateAttribute} method.
 */
abstract class BFM_All_Model_Validators_Validator extends BFM_All_Objects_Component
{
    /**
     * @var array list of built-in validators (name=>class)
     */
    public static $builtInValidators = array(
        'email' => 'EmailValidator',
        'required' => 'RequiredValidator',
        'filter' => 'FilterValidator',
        'in' => 'RangeValidator',

        'boolean' => 'BooleanValidator',
        'default' => 'DefaultValueValidator',
        'url' => 'UrlValidator',
        'length' => 'StringValidator',
        'compare' => 'CompareValidator',
        'numerical' => 'NumberValidator',

        'phone' => 'PhoneValidator',

        'captcha' => 'CaptchaValidator', // @todo develop | tests
        'type' => 'TypeValidator', // @todo develop | tests
        'inline' => 'InlineValidator'

//        'unique' => 'UniqueValidator',  // @todo  develop | tests - сам сделаю, там сложно оказалось.
    );

    /**
     * @var array list of attributes to be validated.
     */
    public $attributes;
    /**
     * @var string the user-defined error message. Different validators may define various
     * placeholders in the message that are to be replaced with actual values. All validators
     * recognize "{attribute}" placeholder, which will be replaced with the label of the attribute.
     */
    public $message;
    /**
     * @var boolean whether this validation rule should be skipped if when there is already a validation
     * error for the current attribute. Defaults to false.
     */
    public $skipOnError = false;
    /**
     * @var array list of scenarios that the validator should be applied.
     * Each array value refers to a scenario name with the same name as its array key.
     */
    public $on;
    /**
     * @var boolean whether attributes listed with this validator should be considered safe for massive assignment.
     * Defaults to true.
     */
    public $safe = true;

    /**
     * Validates a single attribute.
     * This method should be overriden by child classes.
     * @param BFM_All_Model_Model $object the data object being validated
     * @param string $attribute the name of the attribute to be validated.
     */
    abstract protected function validateAttribute($object, $attribute);

    /**
     * Creates a validator object.
     * @param string $name the name or class of the validator
     * @param BFM_All_Model_Model $object the data object being validated that may contain the inline validation method
     * @param mixed $attributes list of attributes to be validated. This can be either an array of
     * the attribute names or a string of comma-separated attribute names.
     * @param array $params initial values to be applied to the validator properties
     * @return Validator the validator
     */
    public static function createValidator($name, $object, $attributes, $params = array())
    {
        if (is_string($attributes))
            $attributes = preg_split('/[\s,]+/', $attributes, -1, PREG_SPLIT_NO_EMPTY);

        if (isset($params['on'])) {
            if (is_array($params['on']))
                $on = $params['on'];
            else
                $on = preg_split('/[\s,]+/', $params['on'], -1, PREG_SPLIT_NO_EMPTY);
        }
        else
            $on = array();

        if (method_exists($object, $name)) {
            $validator = new BFM_All_Model_Validators_InlineValidator();
            $validator->attributes = $attributes;
            $validator->method = $name;
            $validator->params = $params;
            if (isset($params['skipOnError']))
                $validator->skipOnError = $params['skipOnError'];
        }
        else
        {
            $params['attributes'] = $attributes;
            if (isset(self::$builtInValidators[$name]))
                $className = 'BFM_All_Model_Validators_' . self::$builtInValidators[$name];
            else
                $className = $name;
            $validator = new $className();
            foreach ($params as $name => $value)
                $validator->$name = $value;
        }

        $validator->on = empty($on) ? array() : array_combine($on, $on);

        return $validator;
    }

    /**
     * Validates the specified object.
     * @param BFM_All_Model_Model $object the data object being validated
     * @param array $attributes the list of attributes to be validated. Defaults to null,
     * meaning every attribute listed in {@link attributes} will be validated.
     */
    public function validate($object, $attributes = null)
    {
        if (is_array($attributes))
            $attributes = array_intersect($this->attributes, $attributes);
        else
            $attributes = $this->attributes;
        foreach ($attributes as $attribute)
        {
            if (!$this->skipOnError || !$object->hasErrors($attribute))
                $this->validateAttribute($object, $attribute);
        }
    }

    /**
     * Returns a value indicating whether the validator applies to the specified scenario.
     * A validator applies to a scenario as long as any of the following conditions is met:
     * <ul>
     * <li>the validator's "on" property is empty</li>
     * <li>the validator's "on" property contains the specified scenario</li>
     * </ul>
     * @param string $scenario scenario name
     * @return boolean whether the validator applies to the specified scenario.
     */
    public function applyTo($scenario)
    {
        return empty($this->on) || isset($this->on[$scenario]);
    }

    /**
     * Adds an error about the specified attribute to the active record.
     * This is a helper method that performs message selection and internationalization.
     * @param BFM_All_Model_Model $object the data object being validated
     * @param string $attribute the attribute being validated
     * @param string $message the error message
     */
    protected function addError($object, $attribute, $message)
    {
        return $object->addError($attribute, $message);
    }

    /**
     * Checks if the given value is empty.
     * A value is considered empty if it is null, an empty array, or the trimmed result is an empty string.
     * Note that this method is different from PHP empty(). It will return false when the value is 0.
     * @param mixed $value the value to be checked
     * @param boolean $trim whether to perform trimming before checking if the string is empty. Defaults to false.
     * @return boolean whether the value is empty
     */
    protected function isEmpty($value, $trim = false)
    {
        return $value === null || $value === array() || $value === '' || $trim && is_scalar($value) && trim($value) === '';
    }
}

