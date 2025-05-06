<?php
namespace App\Input;

use App\Transaction;
use InvalidArgumentException;

class TransactionFileReader {
    public function readTransactions(string $filePath) {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("Input file not found: " . $filePath);
        }

        if (!is_readable($filePath)) {
            throw new InvalidArgumentException("Input file is not readable: " . $filePath);
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new InvalidArgumentException("Could not open input file: " . $filePath);
        }

        try {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                $transaction = $this->parseTransaction($line);
                if ($transaction !== null) {
                    yield $transaction;
                }
            }
        } finally {
            fclose($handle);
        }
    }

    private function parseTransaction(string $line): ?Transaction {
        try {
            $data = json_decode($line, true);

            if (!isset($data['bin'], $data['amount'], $data['currency'])) {
                return null;
            }

            return new Transaction($data['bin'], $data['amount'], $data['currency']);
        } catch (\Exception $e) {
            return null;
        }
    }
}
