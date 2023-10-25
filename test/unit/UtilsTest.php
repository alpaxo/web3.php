<?php

namespace Test\Unit;

use InvalidArgumentException;
use Test\TestCase;
use phpseclib3\Math\BigInteger as BigNumber;
use Web3\Utils;

/**
 * @coversDefaultClass \Web3\Utils
 */
class UtilsTest extends TestCase
{
    /**
     * 'hello world'
     * you can check by call pack('H*', $hex)
     */
    protected string $testHex = '68656c6c6f20776f726c64';

    /**
     * from GameToken approve function
     */
    protected string $testJsonMethodString = '{
      "constant": false,
      "inputs": [
        {
          "name": "_spender",
          "type": "address"
        },
        {
          "name": "_value",
          "type": "uint256"
        }
      ],
      "name": "approve",
      "outputs": [
        {
          "name": "success",
          "type": "bool"
        }
      ],
      "payable": false,
      "stateMutability": "nonpayable",
      "type": "function",
      "test": {
        "name": "testObject"
      }
    }';

    /**
     * see: https://github.com/sc0Vu/web3.php/issues/112
     */
    protected string $testIssue112Json = '[
        {
          "constant": true,
          "inputs": [],
          "name": "name",
          "outputs": [
            {
              "name": "",
              "type": "string"
            }
          ],
          "payable": false,
          "stateMutability": "view",
          "type": "function"
        },
        {
          "constant": true,
          "inputs": [],
          "name": "decimals",
          "outputs": [
            {
              "name": "",
              "type": "uint256"
            }
          ],
          "payable": false,
          "stateMutability": "view",
          "type": "function"
        },
        {
          "constant": true,
          "inputs": [
            {
              "name": "tokenOwner",
              "type": "address"
            }
          ],
          "name": "balanceOf",
          "outputs": [
            {
              "name": "balance",
              "type": "uint256"
            }
          ],
          "payable": false,
          "stateMutability": "view",
          "type": "function"
        },
        {
          "constant": false,
          "inputs": [
            {
              "name": "to",
              "type": "address"
            },
            {
              "name": "tokens",
              "type": "uint256"
            }
          ],
          "name": "transfer",
          "outputs": [
            {
              "name": "success",
              "type": "bool"
            }
          ],
          "payable": false,
          "stateMutability": "nonpayable",
          "type": "function"
        }
    ]';

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @covers ::toHex
     */
    public function testToHex(): void
    {
        $this->assertEquals($this->testHex, Utils::toHex('hello world'));
        $this->assertEquals('0x' . $this->testHex, Utils::toHex('hello world', true));

        $this->assertEquals('0x927c0', Utils::toHex(0x0927c0, true));
        $this->assertEquals('0x927c0', Utils::toHex('600000', true));
        $this->assertEquals('0x927c0', Utils::toHex(600000, true));
        $this->assertEquals('0x927c0', Utils::toHex(new BigNumber(600000), true));
        
        $this->assertEquals('0xea60', Utils::toHex(0x0ea60, true));
        $this->assertEquals('0xea60', Utils::toHex('60000', true));
        $this->assertEquals('0xea60', Utils::toHex(60000, true));
        $this->assertEquals('0xea60', Utils::toHex(new BigNumber(60000), true));

        $this->assertEquals('0x', Utils::toHex(0x00, true));
        $this->assertEquals('0x', Utils::toHex('0', true));
        $this->assertEquals('0x', Utils::toHex(0, true));
        $this->assertEquals('0x', Utils::toHex(new BigNumber(0), true));

        $this->assertEquals('0x30', Utils::toHex(48, true));
        $this->assertEquals('0x30', Utils::toHex('48', true));
        $this->assertEquals('30', Utils::toHex(48));
        $this->assertEquals('30', Utils::toHex('48'));

        $this->assertEquals('0x30', Utils::toHex(new BigNumber(48), true));
        $this->assertEquals('0x30', Utils::toHex(new BigNumber('48'), true));
        $this->assertEquals('30', Utils::toHex(new BigNumber(48)));
        $this->assertEquals('30', Utils::toHex(new BigNumber('48')));
    }

    /**
     * @covers ::hexToBin
     */
    public function testHexToBin(): void
    {
        $str = Utils::hexToBin($this->testHex);

        $this->assertEquals('hello world', $str);

        $str = Utils::hexToBin('0x' . $this->testHex);

        $this->assertEquals('hello world', $str);

        $str = Utils::hexToBin('0xe4b883e5bda9e7a59ee4bb99e9b1bc');

        $this->assertEquals('七彩神仙鱼', $str);

    }

    /**
     * @covers ::isZeroPrefixed
     */
    public function testIsZeroPrefixed(): void
    {
        $isPrefixed = Utils::isZeroPrefixed($this->testHex);

        $this->assertFalse($isPrefixed);

        $isPrefixed = Utils::isZeroPrefixed('0x' . $this->testHex);

        $this->assertTrue($isPrefixed);
    }

    /**
     * @covers ::isAddress
     */
    public function testIsAddress(): void
    {
        $isAddress = Utils::isAddress('ca35b7d915458ef540ade6068dfe2f44e8fa733c');
        $this->assertTrue($isAddress);

        $isAddress = Utils::isAddress('0xca35b7d915458ef540ade6068dfe2f44e8fa733c');
        $this->assertTrue($isAddress);

        $isAddress = Utils::isAddress('0Xca35b7d915458ef540ade6068dfe2f44e8fa733c');
        $this->assertTrue($isAddress);

        $isAddress = Utils::isAddress('0XCA35B7D915458EF540ADE6068DFE2F44E8FA733C');
        $this->assertTrue($isAddress);

        $isAddress = Utils::isAddress('0xCA35B7D915458EF540ADE6068DFE2F44E8FA733C');
        $this->assertTrue($isAddress);

        $isAddress = Utils::isAddress('0xCA35B7D915458EF540ADE6068DFE2F44E8FA73cc');
        $this->assertFalse($isAddress);
    }

    /**
     * @covers ::isAddressChecksum
     */
    public function testIsAddressChecksum(): void
    {
        $isAddressChecksum = Utils::isAddressChecksum('0x52908400098527886E0F7030069857D2E4169EE7');
        $this->assertTrue($isAddressChecksum);

        $isAddressChecksum = Utils::isAddressChecksum('0x8617E340B3D01FA5F11F306F4090FD50E238070D');
        $this->assertTrue($isAddressChecksum);

        $isAddressChecksum = Utils::isAddressChecksum('0xde709f2102306220921060314715629080e2fb77');
        $this->assertTrue($isAddressChecksum);

        $isAddressChecksum = Utils::isAddressChecksum('0x27b1fdb04752bbc536007a920d24acb045561c26');
        $this->assertTrue($isAddressChecksum);

        $isAddressChecksum = Utils::isAddressChecksum('0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAed');
        $this->assertTrue($isAddressChecksum);

        $isAddressChecksum = Utils::isAddressChecksum('0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAed');
        $this->assertTrue($isAddressChecksum);

        $isAddressChecksum = Utils::isAddressChecksum('0xfB6916095ca1df60bB79Ce92cE3Ea74c37c5d359');
        $this->assertTrue($isAddressChecksum);

        $isAddressChecksum = Utils::isAddressChecksum('0xdbF03B407c01E7cD3CBea99509d93f8DDDC8C6FB');
        $this->assertTrue($isAddressChecksum);

        $isAddressChecksum = Utils::isAddressChecksum('0xD1220A0cf47c7B9Be7A2E6BA89F429762e7b9aDb');
        $this->assertTrue($isAddressChecksum);

        $isAddressChecksum = Utils::isAddressChecksum('0XD1220A0CF47C7B9BE7A2E6BA89F429762E7B9ADB');
        $this->assertFalse($isAddressChecksum);

        $isAddressChecksum = Utils::isAddressChecksum('0xd1220a0cf47c7b9be7a2e6ba89f429762e7b9adb');
        $this->assertFalse($isAddressChecksum);
    }

    /**
     * @covers ::toChecksumAddress
     */
    public function testToChecksumAddress(): void
    {
        $checksumAddressTest = [
            // All caps
            '0x52908400098527886E0F7030069857D2E4169EE7',
            '0x8617E340B3D01FA5F11F306F4090FD50E238070D',
            // All Lower
            '0xde709f2102306220921060314715629080e2fb77',
            '0x27b1fdb04752bbc536007a920d24acb045561c26',
            // Normal
            '0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAed',
            '0xfB6916095ca1df60bB79Ce92cE3Ea74c37c5d359',
            '0xdbF03B407c01E7cD3CBea99509d93f8DDDC8C6FB',
            '0xD1220A0cf47c7B9Be7A2E6BA89F429762e7b9aDb'
        ];

        for ($i=0; $i<count($checksumAddressTest); $i++) {
            $checksumAddress = Utils::toChecksumAddress(strtolower($checksumAddressTest[$i]));
            $this->assertEquals($checksumAddressTest[$i], $checksumAddress);
        }
    }

    /**
     * @covers ::stripZero
     */
    public function testStripZero(): void
    {
        $str = Utils::stripZero($this->testHex);

        $this->assertEquals($str, $this->testHex);

        $str = Utils::stripZero('0x' . $this->testHex);

        $this->assertEquals($str, $this->testHex);
    }

    /**
     * @covers ::sha3
     * @throws \Exception
     */
    public function testSha3(): void
    {
        $str = Utils::sha3('');
        $this->assertNull($str);

        $str = Utils::sha3('baz(uint32,bool)');
        $this->assertEquals('0xcdcd77c0', mb_substr($str, 0, 10));
    }

    /**
     * @covers ::toWei
     */
    public function testToWei(): void
    {
        $bn = Utils::toWei('0x1', 'wei');
        $this->assertEquals('1', $bn->toString());

        $bn = Utils::toWei('18', 'wei');
        $this->assertEquals('18', $bn->toString());

        $bn = Utils::toWei('1', 'ether');
        $this->assertEquals('1000000000000000000', $bn->toString());

        $bn = Utils::toWei('0x5218', 'wei');
        $this->assertEquals('21016', $bn->toString());

        $bn = Utils::toWei('0.000012', 'ether');
        $this->assertEquals('12000000000000', $bn->toString());

        $bn = Utils::toWei('0.1', 'ether');
        $this->assertEquals('100000000000000000', $bn->toString());

        $bn = Utils::toWei('1.69', 'ether');
        $this->assertEquals('1690000000000000000', $bn->toString());

        $bn = Utils::toWei('0.01', 'ether');
        $this->assertEquals('10000000000000000', $bn->toString());

        $bn = Utils::toWei('0.002', 'ether');
        $this->assertEquals('2000000000000000', $bn->toString());

        $bn = Utils::toWei('-0.1', 'ether');
        $this->assertEquals('-100000000000000000', $bn->toString());

        $bn = Utils::toWei('-1.69', 'ether');
        $this->assertEquals('-1690000000000000000', $bn->toString());

        $bn = Utils::toWei('', 'ether');
        $this->assertEquals('0', $bn->toString());
    }

    /**
     * @covers ::toEther
     */
    public function testToEther():void
    {
        list($bnq, $bnr) = Utils::toEther('0x1', 'wei');

        $this->assertEquals('0', $bnq->toString());
        $this->assertEquals('1', $bnr->toString());

        list($bnq, $bnr) = Utils::toEther('18', 'wei');

        $this->assertEquals('0', $bnq->toString());
        $this->assertEquals('18', $bnr->toString());

        list($bnq, $bnr) = Utils::toEther('1', 'kether');

        $this->assertEquals('1000', $bnq->toString());
        $this->assertEquals('0', $bnr->toString());

        list($bnq, $bnr) = Utils::toEther('0x5218', 'wei');

        $this->assertEquals('0', $bnq->toString());
        $this->assertEquals('21016', $bnr->toString());

        list($bnq, $bnr) = Utils::toEther('0x5218', 'ether');

        $this->assertEquals('21016', $bnq->toString());
        $this->assertEquals('0', $bnr->toString());
    }

    /**
     * @covers ::fromWei
     */
    public function testFromWei(): void
    {
        list($bnq, $bnr) = Utils::fromWei('1000000000000000000', 'ether');

        $this->assertEquals('1', $bnq->toString());
        $this->assertEquals('0', $bnr->toString());

        list($bnq, $bnr) = Utils::fromWei('18', 'wei');

        $this->assertEquals('18', $bnq->toString());
        $this->assertEquals('0', $bnr->toString());

        list($bnq, $bnr) = Utils::fromWei(1, 'femtoether');

        $this->assertEquals('0', $bnq->toString());
        $this->assertEquals('1', $bnr->toString());

        list($bnq, $bnr) = Utils::fromWei(0x11, 'nano');

        $this->assertEquals('0', $bnq->toString());
        $this->assertEquals('17', $bnr->toString());

        list($bnq, $bnr) = Utils::fromWei('0x5218', 'kwei');

        $this->assertEquals('21', $bnq->toString());
        $this->assertEquals('16', $bnr->toString());

        try {
            list($bnq, $bnr) = Utils::fromWei('0x5218', 'test');
        } catch (InvalidArgumentException $e) {
            $this->assertNotNull($e);
        }
    }

    /**
     * @covers ::jsonMethodToString
     */
    public function testJsonMethodToString(): void
    {
        $json = json_decode($this->testJsonMethodString);
        $methodString = Utils::jsonMethodToString($json);

        $this->assertEquals('approve(address,uint256)', $methodString);

        $json = json_decode($this->testJsonMethodString, true);
        $methodString = Utils::jsonMethodToString($json);

        $this->assertEquals('approve(address,uint256)', $methodString);

        $methodString = Utils::jsonMethodToString([
            'name' => 'approve(address,uint256)'
        ]);

        $this->assertEquals('approve(address,uint256)', $methodString);
    }

    /**
     * @covers ::jsonToArray
     */
    public function testJsonToArray(): void
    {
        $decodedJson = json_decode($this->testJsonMethodString);
        $jsonArray = Utils::jsonToArray($decodedJson);

        $jsonAssoc = json_decode($this->testJsonMethodString, true);
        $jsonArray2 = Utils::jsonToArray($jsonAssoc);

        $this->assertEquals($jsonAssoc, $jsonArray);
        $this->assertEquals($jsonAssoc, $jsonArray2);

        $jsonAssoc = json_decode($this->testIssue112Json, true);
        $jsonArray = Utils::jsonToArray($jsonAssoc);

        $this->assertEquals($jsonAssoc, $jsonArray);
    }

    /**
     * @covers ::isHex
     */
    public function testIsHex(): void
    {
        $isHex = Utils::isHex($this->testHex);

        $this->assertTrue($isHex);

        $isHex = Utils::isHex('0x' . $this->testHex);

        $this->assertTrue($isHex);

        $isHex = Utils::isHex('hello world');

        $this->assertFalse($isHex);
    }

    /**
     * @covers ::isNegative
     */
    public function testIsNegative(): void
    {
        $isNegative = Utils::isNegative('-1');

        $this->assertTrue($isNegative);

        $isNegative = Utils::isNegative('1');

        $this->assertFalse($isNegative);
    }

    /**
     * @covers ::toBn
     */
    public function testToBn():void
    {
        $bn = Utils::toBn('');
        $this->assertEquals('0', $bn->toString());

        $bn = Utils::toBn(11);
        $this->assertEquals('11', $bn->toString());

        $bn = Utils::toBn('0x12');
        $this->assertEquals('18', $bn->toString());

        $bn = Utils::toBn('-0x12');
        $this->assertEquals('-18', $bn->toString());

        $bn = Utils::toBn(0x12);
        $this->assertEquals('18', $bn->toString());

        $bn = Utils::toBn('ae');
        $this->assertEquals('174', $bn->toString());

        $bn = Utils::toBn('-ae');
        $this->assertEquals('-174', $bn->toString());

        $bn = Utils::toBn('-1');
        $this->assertEquals('-1', $bn->toString());

        $bn = Utils::toBn('-0.1');
        $this->assertEquals(4, count($bn));
        $this->assertEquals('0', $bn[0]->toString());
        $this->assertEquals('1', $bn[1]->toString());
        $this->assertEquals(1, $bn[2]);
        $this->assertEquals('-1', $bn[3]->toString());

        $bn = Utils::toBn(-0.1);

        $this->assertEquals(4, count($bn));
        $this->assertEquals('0', $bn[0]->toString());
        $this->assertEquals('1', $bn[1]->toString());
        $this->assertEquals(1, $bn[2]);
        $this->assertEquals('-1', $bn[3]->toString());

        $bn = Utils::toBn('0.1');
        $this->assertEquals(4, count($bn));
        $this->assertEquals('0', $bn[0]->toString());
        $this->assertEquals('1', $bn[1]->toString());
        $this->assertEquals(1, $bn[2]);
        $this->assertFalse($bn[3]);

        $bn = Utils::toBn('-1.69');
        $this->assertEquals(4, count($bn));
        $this->assertEquals('1', $bn[0]->toString());
        $this->assertEquals('69', $bn[1]->toString());
        $this->assertEquals(2, $bn[2]);
        $this->assertEquals('-1', $bn[3]->toString());

        $bn = Utils::toBn(-1.69);
        $this->assertEquals('1', $bn[0]->toString());
        $this->assertEquals('69', $bn[1]->toString());
        $this->assertEquals(2, $bn[2]);
        $this->assertEquals('-1', $bn[3]->toString());

        $bn = Utils::toBn('1.69');
        $this->assertEquals(4, count($bn));
        $this->assertEquals('1', $bn[0]->toString());
        $this->assertEquals('69', $bn[1]->toString());
        $this->assertEquals(2, $bn[2]);
        $this->assertFalse($bn[3]);

        $bn = Utils::toBn(new BigNumber(1));
        $this->assertEquals('1', $bn->toString());
    }
}