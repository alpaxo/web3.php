<?php

namespace Test\Unit;

use Test\TestCase;
use phpseclib3\Math\BigInteger as BigNumber;
use Web3\Eth;

/**
 * @coversDefaultClass \Web3\Eth
 */
class EthBatchTest extends TestCase
{
    protected Eth $eth;

    public function setUp(): void
    {
        parent::setUp();

        $this->eth = $this->web3->eth;
    }

    /**
     * @covers ::batch
     */
    public function testBatch(): void
    {
        $eth = $this->eth;

        $eth->batch(true);
        $eth->protocolVersion();
        $eth->syncing();

        $eth->provider->execute(function ($err, $data) {
            if ($err !== null) {
                $this->fail('Got error!');
            }
            $this->assertTrue($data[0] instanceof BigNumber);
            $this->assertTrue($data[1] !== null);
        });
    }
}