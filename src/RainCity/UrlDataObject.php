<?php
namespace RainCity;

/**
 * Helper class for passing data via a URL
 *
 * Converts an array into a string of alphanumeric characters which are
 * suitibale for passing in a URL which are not the actual data and can then
 * convert the string back into the original data array.
 */
class UrlDataObject
{
    private const DELIMITER = '::';

    /**
     * Private constructor as only the static methods should be used.
     */
    private function __construct()
    {
        //NOSONAR - don't allow creating instances
    }

    /**
     * Converts an array of data into a string which is not recognizable as
     * the original data but is suitable for including in a URL.
     *
     * @param array $inputData The data to be stringified.
     *
     * @return string|NULL Returns a string version of the data, or null if
     *      there is a problem generating the string.
     */
    public static function toString(array $inputData): ?string
    {
        $urlStr = null;

        if (!empty($inputData)) {
            $data = array_merge([count($inputData)], $inputData);
            $str = implode(self::DELIMITER, $data);
            $encStr = gzdeflate($str);

            if (false !== $encStr) {
                $b64Enc = base64_encode($encStr);
                $urlStr = urlencode($b64Enc);
            }
        }

        return $urlStr;
    }

    /**
     * Converts a string, previously created with the toString() method, back
     * into the array of data.
     *
     * @param string $urlString A string previously generated with the
     *      toString() method.
     *
     * @return array The array of data, which may be empty if there is a
     *      problem deconstructing the string.
     */
    public static function fromString(string $urlString): array
    {
        $data = [];

        if (!empty($urlString)) {
            $urlDecodedStr = urldecode($urlString);

            if (preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $urlDecodedStr)) {
                $b64DecodedStr = base64_decode($urlDecodedStr);

                if (false !== $b64DecodedStr) {
                    $inflatedStr = gzinflate($b64DecodedStr);

                    if (false !== $inflatedStr && str_contains($inflatedStr, self::DELIMITER)) {
                        $dataArray = explode(self::DELIMITER, $inflatedStr);

                        $cnt = intval(array_shift($dataArray));

                        if (0 !== $cnt && count($dataArray) == $cnt) {
                            $data = $dataArray;
                        }
                    }
                }
            }
        }

        return $data;
    }
}
