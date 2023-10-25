<?php

namespace Test\Unit;

use Test\TestCase;
use phpseclib3\Math\BigInteger as BigNumber;
use Web3\Net;

/**
 * @coversDefaultClass \Web3\Net
 */
class NetBatchTest extends TestCase
{
    protected Net $net;

    public function setUp(): void
    {
        parent::setUp();

        $this->net = $this->web3->net;
    }

    /**
     * @covers ::batch
     */
    public function testBatch(): void
    {
        $net = $this->net;

        $net->batch(true);
        $net->version();
        $net->listening();
        $net->peerCount();

        $net->provider->execute(function ($err, $data) {
            if ($err !== null) {
                return $this->fail('Got error!');
            }
            $this->assertTrue(is_string($data[0]));
            $this->assertTrue(is_bool($data[1]));
            $this->assertTrue($data[2] instanceof BigNumber);
        });
    }
}