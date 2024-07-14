<?php
namespace RainCity;

/**
 * Helper class for passing data via a URL
 *
 * Converts data added to the object into a string of alphanumeric characters
 * which are suitibale for passing in a URL which are not the actual data and
 * can then convert the string back into the original data.
 */
class UrlDataObject
{
    /** Storage for the data */
    private \stdClass $data;

    /**
     * Constructs an instance of the class.
     *
     * If a string is provided, attempts to decode the string store the data.
     *
     * @param string $encodedData Optional string previously encoded with
     *      the encode() method.
     */
    public function __construct(?string $encodedData = null)
    {
        $this->data = new \stdClass();

        if (isset($encodedData)) {
            $this->decode($encodedData);
        }
    }

    /**
     * Stores a value, associating it with the specified key.
     *
     * @param string $key A key to use in later retrieving the value.
     * @param string $value The value to be stored.
     *
     * @return UrlDataObject The instance of the object allowing calls to
     *      set() to be chained.
     */
    public function set(string $key, string $value): UrlDataObject
    {
        $this->data->$key = $value;

        return $this;
    }

    /**
     * Retreive a value previously stored by it's key.
     *
     * @param string $key The key for the value to retrieve.
     *
     * @return string|NULL Returns the string associated with the key, or
     *      null if there is no value associated with the key.
     */
    public function get(string $key): ?string
    {
        return $this->data->$key ?? null;

    }

    /**
     * Converts the data stored in the object into a string which is not
     * recognizable as the original data but is suitable for including in a
     * URL.
     *
     * @return string|NULL Returns a string version of the data, or null if
     *      there is a problem generating the string.
     */
    public function encode(): ?string
    {
        $urlStr = null;

        $dataAsArray = (array)$this->data;

        if (!empty($dataAsArray)) {
            $str = json_encode($dataAsArray);

            if (false !== $str) {
                $encStr = gzdeflate($str);

                if (false !== $encStr) {
                    $b64Enc = base64_encode($encStr);
                    $urlStr = urlencode($b64Enc);
                }
            }
        }

        return $urlStr;
    }

    /**
     * Converts a string, previously created with the toString() method, back
     * into the data.
     *
     * @param string $urlString A string previously generated with the
     *      encode() method.
     *
     * @return bool Returns true on success, and false of there is a problem
     *      decoding the string. If the string cannot be decoded the data
     *      will remain empty.
     */
    public function decode(string $urlString): bool
    {
        $success = false;

        if (!empty($urlString)) {
            $urlDecodedStr = urldecode($urlString);

            if (preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $urlDecodedStr)) {
                $b64DecodedStr = base64_decode($urlDecodedStr);

                if (false !== $b64DecodedStr) {
                    $inflatedStr = gzinflate($b64DecodedStr);

                    if (false !== $inflatedStr) {
                        $jsonObj = json_decode($inflatedStr);

                        if (false !== $jsonObj && json_last_error() === JSON_ERROR_NONE) {
                            $this->data = $jsonObj;
                            $success = true;
                        }
                    }
                }
            }
        }

        return $success;
    }
}
