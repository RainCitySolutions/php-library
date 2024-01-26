<?php declare(strict_types=1);
namespace RainCity;

trait SerializeAsArrayTrait
{
    public function __serialize(): array
    {
        return get_object_vars($this);
    }

    public function __unserialize(array $data): void
    {
        foreach ($data as $var => $value) {
            /**
             * Only set values for properties of the object.
             *
             * Generally this will be the case but this accounts for the
             * possiblity that a field may be removed from the class in the
             * future.
             */
            if (property_exists($this, $var)) {
                $this->$var = $value;
            }
        }
    }

    /**
     * Implementation of the \Serializable::serialize() method
     *
     * @return string|NULL A string representation of the object or null if
     *      it cannot be serialized.
     */
    public function serialize(): ?string
    {
        return serialize($this->__serialize());
    }

    /**
     * Implementation of the \Serializable::unserialize method
     *
     * @param string $serialized A string representation of the object
     */
    public function unserialize($serialized): void
    {
        $this->__unserialize(unserialize($serialized));
    }
}
