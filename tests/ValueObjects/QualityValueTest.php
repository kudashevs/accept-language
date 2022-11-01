<?php

namespace Kudashevs\AcceptLanguage\Tests\ValueObjects;

use Kudashevs\AcceptLanguage\Exceptions\InvalidQualityValueArgumentException;
use Kudashevs\AcceptLanguage\ValueObjects\QualityValue;
use PHPUnit\Framework\TestCase;

class QualityValueTest extends TestCase
{
    /** @test */
    public function it_can_be_instanciated()
    {
        $quality = new QualityValue(1);

        $this->assertNotEmpty($quality->getQuality());
        $this->assertTrue($quality->isValid());
    }

    /**
     * @test
     * @dataProvider provideDifferentInvalidQualityValues
     */
    public function it_can_throw_exception_when_a_quality_is_wrong($quality)
    {
        $this->expectException(InvalidQualityValueArgumentException::class);
        $this->expectExceptionMessage('The quality value "' . $quality . '" is invalid.');

        new QualityValue($quality);
    }

    public function provideDifferentInvalidQualityValues(): array
    {
        return [
            'a random string results in zero' => [
                'wrong',
                0,
            ],
            'a numerical string with invalid int quality results in int' => [
                '2',
                2,
            ],
            'a numerical string with invalid float quality results in float' => [
                '2.0',
                2.0,
            ],
            'an invalid negative quality results in no change' => [
                -1,
                -1,
            ],
            'an invalid positive quality results in no change' => [
                2,
                2,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentValidQualityValues
     */
    public function it_can_normalize_a_valid_quality($input, $expected)
    {
        $quality = new QualityValue($input);

        $this->assertSame($expected, $quality->getQuality());
        $this->assertTrue($quality->isValid());
    }

    public function provideDifferentValidQualityValues(): array
    {
        return [
            'an empty quality results in the not acceptable' => [
                '',
                0,
            ],
            'a null quality results in the not acceptable' => [
                null,
                0,
            ],
            'a numerical string with valid quality results in the quality' => [
                '1',
                1,
            ],
            'a valid quality 0 results in the the not acceptable' => [
                0,
                0,
            ],
            'a valid quality 0.0 results in the not acceptable' => [
                0.0,
                0,
            ],
            'a valid quality 0.3 results in the quality' => [
                0.3,
                0.3,
            ],
            'a valid quality 1 results in the quality' => [
                1,
                1,
            ],
            'a valid quality 1.0 results in the quality' => [
                1.0,
                1,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider providedDifferentQualityBoundaryValues
     */
    public function it_can_normalize_at_boundaries($input, $expected, bool $valid)
    {
        $quality = new QualityValue($input);

        $this->assertSame($expected, $quality->getQuality());
        $this->assertSame($valid, $quality->isValid());
    }

    public function providedDifferentQualityBoundaryValues(): array
    {
        return [
            'a zero value results in the not acceptable' => [
                0,
                0,
                true,
            ],
            'a positive within bounds 0.001 results in the quality' => [
                0.001,
                0.001,
                true,
            ],
            'a positive within bounds 0.999 results in the quality' => [
                0.999,
                0.999,
                true,
            ],
            'a positive withing bounds 1 results in the quality' => [
                1,
                1,
                true,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentQualityValuesWithDifferentFallbacks
     */
    public function it_can_normalize_an_ivalid_quality_with_fallback(array $options, $input, $expected)
    {
        $quality = new QualityValue($input, $options);

        $this->assertSame($expected, $quality->getQuality());
        $this->assertTrue($quality->isValid());
    }

    public function provideDifferentQualityValuesWithDifferentFallbacks(): array
    {
        return [
            'an empty quality with a fallback results in the fallback' => [
                [
                    'fallback_value' => 0.5,
                ],
                '',
                0.5,
            ],
            'a null quality with a fallback results in the fallback' => [
                [
                    'fallback_value' => 1,
                ],
                null,
                1,
            ],
        ];
    }
}
