<?php declare(strict_types=1);
namespace RainCity;

/**
 * A set of miscellaneous helper methods.
 *
 */
class MiscHelper
{
    /**
     * Creates a temporary file in the system temp directory with the
     * specified extension.
     *
     * @deprecated Use FileSystem::createTempFile()
     *
     * @param string $extension The desired file extention.
     *
     * @return string The full path to the tempoary file. May return FALSE if
     *      the temporary file cannot be created.
     */
    public static function createTempFile(string $extension): string
    {
        return FileSystem::createTempFile($extension);
    }

    /**
     * Minifies a string, assumed to be HTML, by stripping extra whitespace
     * carriage-returns and newlines.
     *
     * @param string $inStr A string to be minified.
     *
     * @return string The minified string.
     */
    public static function minifyHtml(string $inStr): string
    {
        $outStr = '';

        $search = array(
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/([\t\r\n])+/s'    // strip tabs, carriage-returns and newlines
            //            '/<!--(.|\s)*?-->/' // Remove HTML comments
        );

        $replace = array(
            '>',
            '<',
            '\\1',
            ' '
//            ''
        );

        $outStr = preg_replace($search, $replace, $inStr);

        return trim($outStr);
    }

}
