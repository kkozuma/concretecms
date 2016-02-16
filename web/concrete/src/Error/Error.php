<?php
namespace Concrete\Core\Error;

use Exception;
use Loader;
use stdClass;

class Error implements \ArrayAccess, \JsonSerializable
{
    protected $error = array();
    public $helperAlwaysCreateNewInstance = true;

    /**
     * Whether a offset exists.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     *
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     *
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->error[$offset];
    }

    /**
     * Offset to retrieve.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     *
     * @return mixed Can return all value types.
     *
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return array_get($this->error, $offset);
    }

    /**
     * Offset to set.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     *
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->add($value);
        } else {
            $this->error[$offset] = $value;
        }
    }

    /**
     * Offset to unset.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     *
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->error[$offset]);
    }

    /**
     * Adds an error object or exception to the internal error array.
     *
     * @param Exception | string $e
     */
    public function add($e)
    {
        if ($e instanceof self) {
            foreach ($e->getList() as $errorString) {
                $this->add($errorString);
            }
        } elseif (is_object($e) && ($e instanceof Exception)) {
            $this->error[] = $e->getMessage();
        } else {
            $this->error[] = $e;
        }
    }

    /**
     * Returns a list of errors in the error helper.
     *
     * @return array
     */
    public function getList()
    {
        return $this->error;
    }

    /**
     * Returns whether or not this error helper has more than one error registered within it.
     *
     * @return bool
     */
    public function has()
    {
        return count($this->error) > 0;
    }

    /**
     * Outputs the HTML of an error list, with the correct style attributes/classes. This is a convenience method.
     */
    public function output()
    {
        if ($this->has()) {
            echo '<ul class="ccm-error">';
            foreach ($this->getList() as $error) {
                echo '<li>' . $error . '</li>';
            }
            echo '</ul>';
        }
    }

    /**
     * Outputs the the error as a JSON object.
     */
    public function outputJSON()
    {
        if ($this->has()) {
            echo json_encode($this);
        }
    }

    public function jsonSerialize()
    {
        if ($this->has()) {
            $o = array();
            $o['error'] = true;
            $o['errors'] = array();
            foreach ($this->getList() as $error) {
                $o['errors'][] = $error;
            }
            return $o;
        }
    }
}
