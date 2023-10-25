<?php

namespace Test\Unit;

use Test\TestCase;
use phpseclib3\Math\BigInteger as BigNumber;
use Web3\Formatters\BigNumberFormatter;

/**
 * @coversDefaultClass \Web3\Formatters\BigNumberFormatter
 */
class BigNumberFormatterTest extends TestCase
{
    protected BigNumberFormatter $formatter;

    public function setUp(): void
    {
        parent::setUp();

        $this->formatter = new BigNumberFormatter;
    }

    /**
     * @covers ::format
     */
    public function testFormat(): void
    {
        $formatter = $this->formatter;

        $bigNumber = $formatter->format(1);

        $this->assertEquals('1', $bigNumber->toString());

        $this->assertTrue($bigNumber instanceof BigNumber);
    }
}