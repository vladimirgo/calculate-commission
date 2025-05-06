<?php

namespace Tests\Input;

use App\Input\TransactionFileReader;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;

class TransactionFileReaderTest extends TestCase
{

    public function testReadTransactions()
    {
        $root = vfsStream::setup('root');
        $fileContent = <<<EOT
{"bin":"123","amount":"10.50","currency":"USD"}
{"bin":"456","amount":"200","currency":"EUR"}
{"bin":"789","amount":"5.99","currency":"GBP"}
EOT;
        $file = vfsStream::newFile('input.txt')->at($root)->setContent($fileContent);
        $filePath = $file->url();

        $reader = new TransactionFileReader();
        $transactions = iterator_to_array($reader->readTransactions($filePath));

        $this->assertCount(3, $transactions);
        $this->assertEquals('123', $transactions[0]->getBin());
        $this->assertEquals('10.50', $transactions[0]->getAmount());
        $this->assertEquals('USD', $transactions[0]->getCurrency());
    }

    public function testReadTransactionsHandlesEmptyAndInvalidLines()
    {
        $root = vfsStream::setup('root');
        $fileContent = <<<EOT
{"bin":"111","amount":"10","currency":"EUR"}

Invalid JSON
{"bin":"333","amount":"30","currency":"GBP"}
EOT;
        $file = vfsStream::newFile('input.txt')->at($root)->setContent($fileContent);
        $filePath = $file->url();

        $reader = new TransactionFileReader();
        $transactions = iterator_to_array($reader->readTransactions($filePath));

        $this->assertCount(2, $transactions);
        $this->assertEquals('111', $transactions[0]->getBin());
        $this->assertEquals('333', $transactions[1]->getBin());
    }

    public function testReadTransactionsThrowsExceptionForNonExistentFile()
    {
        $reader = new TransactionFileReader();
        $this->expectException(InvalidArgumentException::class);
        iterator_to_array($reader->readTransactions('non_existent_file.txt'));
    }
}
