<?php

namespace Test;

use \PHPUnit\Framework\TestCase as BaseTestCase;
use Web3\Web3;

class TestCase extends BaseTestCase
{
    protected Web3 $web3;

    protected string $testRinkebyHost = 'https://rinkeby.infura.io/vuethexplore';

    protected string $testHost = 'http://localhost:8545';

    /**
     * coinbase
     * 
     * @var string
     */
    protected $coinbase;

    public function setUp(): void
    {
        $web3 = new Web3($this->testHost);

        $this->web3 = $web3;

        $web3->eth->coinbase(function ($err, $coinbase) {
            if ($err !== null) {
                $this->fail($err->getMessage());
            }

            $this->coinbase = $coinbase;
        });
    }

    public function tearDown(): void {}
}