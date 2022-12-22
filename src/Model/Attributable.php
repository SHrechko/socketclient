<?php

namespace App\Model;

/**
 * Class Attributable
 * @package Officio\Common
 */
abstract class Attributable implements \JsonSerializable {

    public const ATTR_TYPE_STRING = 'string';
    public const ATTR_TYPE_INTEGER = 'integer';
    public const ATTR_TYPE_DOUBLE = 'double';
    public const ATTR_TYPE_FLOAT = 'float';

    /**
     * Returns a list of safe attribute names which will be used for massive assignment.
     * @return string[]
     */
    public function safeAttributes() {
        return [];
    }

    /**
     * Returns an associative array where key is attribute name, and value is a type to cast
     * the value when exporting the object to array
     * @return array<string, string>
     */
    public function attributeTypes() {
        return [];
    }

    /**
     * Get list of defined attribute names
     * @return string[]
     */
    public function getAttributeNames() {
        return array_keys(get_class_vars(get_called_class()));
    }

    /**
     * Bulk assignment of attribute values. If safe attributes are declared, assignment will
     * be limited to them. If attribute isn't declared as a property, error will be thrown.
     * @param array<string, mixed> $attrs
     * @return $this
     * @throws \Exception
     */
    public function setAttributes(array $attrs) {
        $safeAttributes = $this->safeAttributes();
        if (!empty($safeAttributes)) {
            $attrs = array_intersect_key($attrs, array_flip($safeAttributes));
        }

        foreach ($attrs as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * Attribute value setter. Checks for safe attributes.
     * @param string $name
     * @param mixed $value
     * @return $this
     * @throws \Exception
     */
    public function __set($name, $value) {
        $safeAttributes = $this->safeAttributes();
        if (empty($safeAttributes) || in_array($name, $safeAttributes)) {
            $this->setAttribute($name, $value);
        }
        return $this;
    }

    /**
     * Attribute value assignment. Doesn't check for safe attributes.
     * @param string $name
     * @param mixed $value
     * @throws \Exception
     */
    public function setAttribute($name, $value) {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
        else {
            throw new \Exception(sprintf('Attribute %s doesn\'t exist in %s', $name, get_called_class()));
        }
    }

    /**
     * Getter for the attribute
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name) {
        return $this->getAttribute($name);
    }

    /**
     * Returns attribute value
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function getAttribute($name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        else {
            throw new \Exception(sprintf('Attribute %s doesn\'t exist in %s', $name, get_called_class()));
        }
    }

    /**
     * Constructor.
     * @param array<string, mixed> $attrs
     * @throws \Exception
     */
    public function __construct(array $attrs = []) {
        $this->setAttributes($attrs);
    }

    /**
     * Converts attribute to array
     * @param string $name
     * @param mixed $attr
     * @return mixed
     * @throws \Exception
     */
    public function convertAttributeToArray($name, $attr) {
        $attributeTypes = $this->attributeTypes();
        if (is_array($attr)) {
            $result = [];
            foreach ($attr as $key => $value) {
                $result[$key] = $this->convertAttributeToArray($key, $value);
            }
            return $result;
        }
        elseif (is_object($attr)) {
            if ($attr instanceof Attributable) {
                return $attr->toArray();
            }
            else{
                $result = (method_exists($attr, 'toArray')) ? $attr->toArray() : (array) $attr;
                return $this->convertAttributeToArray($name, $result);
            }
        }

        $type = $attributeTypes[$name] ?? false;
        if ($type === self::ATTR_TYPE_INTEGER) {
            return (int) $attr;
        }
        elseif ($type === self::ATTR_TYPE_FLOAT) {
            return (float) $attr;
        }
        elseif ($type === self::ATTR_TYPE_DOUBLE) {
            return (double) $attr;
        }
        elseif ($type === self::ATTR_TYPE_STRING) {
            return (string) $attr;
        }

        return $attr;
    }

    /**
     * Convert object into an array
     * @param string[] $attrs
     * @return array
     * @throws \Exception
     */
    public function toArray($attrs = false) {
        $result = array();
        if (!$attrs) $attrs = $this->getAttributeNames();
        foreach ($attrs as $attr) {
            $result[$attr] = $this->convertAttributeToArray($attr, $this->getAttribute($attr));
        }
        return $result;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

}

