<?php declare(strict_types=1);
namespace RainCity;

use PHPUnit\Framework\TestCase;

/**
 * @covers \RainCity\MiscHelper
 *
 */
class MiscHelperTest extends TestCase
{
    public function testMinifyHtml_noChange () {
        $input = '<button id="99">Button Text</button>';

        self::assertEquals($input, MiscHelper::minifyHtml($input));
    }

    public function testMinifyHtml_whitespaceAfterTag () {
        $input = "<div> \t  <span>  Text</span>    </div>   ";
        $expected = '<div> <span> Text</span> </div>';

        self::assertEquals($expected, MiscHelper::minifyHtml($input));
    }

    public function testMinifyHtml_whitespaceBeforeTag () {
        $input = "\t   <div>  \t\t  <span>Text    </span>   </div>";
        $expected = '<div> <span>Text </span> </div>';

        self::assertEquals($expected, MiscHelper::minifyHtml($input));
    }

    public function testMinifyHtml_singleTagMultipleLines () {
        ob_start();
        ?>
        <button
            id="redcapConsent-99"
            class="btn btn-secondary btn-sm"
            type="button"
            data-redcap-url="http://test"
            data-redcap-code="1234567890ABCDEF"
            >
            Review/Revoke Consent
        </button>
        <?php
        $input = ob_get_contents();
        ob_end_clean();

        $expected =
            '<button id="redcapConsent-99" class="btn btn-secondary btn-sm" ' .
            'type="button" data-redcap-url="http://test" data-redcap-code="1234567890ABCDEF" > ' .
            'Review/Revoke Consent </button>';

        self::assertEquals($expected, MiscHelper::minifyHtml($input));
    }

}
