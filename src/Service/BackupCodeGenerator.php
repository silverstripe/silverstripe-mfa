<?php declare(strict_types=1);

namespace SilverStripe\MFA\Service;

use Security;
use SilverStripe\MFA\State\BackupCode;
use SS_Object;

class BackupCodeGenerator extends SS_Object implements BackupCodeGeneratorInterface
{
    /**
     * The number of back-up codes that should be generated for a user. Note that changing this value will not
     * regenerate or generate new codes to meet the new number. The user will have to manually regenerate codes to
     * receive the new number of codes.
     *
     * @config
     * @var int
     */
    private static $backup_code_count = 15;

    /**
     * The length of each individual backup code.
     *
     * @config
     * @var int
     */
    private static $backup_code_length = 9;

    /**
     * Generates a list of backup codes
     *
     * {@inheritDoc}
     */
    public function generate(): array
    {
        $codeCount = (int) $this->config()->get('backup_code_count');
        $codeLength = (int) $this->config()->get('backup_code_length');
        $charset = $this->getCharacterSet();

        $codes = [];
        while (count($codes) < $codeCount) {
            $code = $this->generateCode($charset, $codeLength);

            if (!in_array($code, $codes)) {
                $hashData = Security::encrypt_password($code);
                $codes[] = BackupCode::create($code, $hashData['password'], $hashData['algorithm'], $hashData['salt']);
            }
        }

        return $codes;
    }

    public function getCharacterSet(): array
    {
        $characterSet = array_merge(
            range('a', 'z'),
            range('A', 'Z'),
            range(0, 9)
        );

        $this->extend('updateCharacterSet', $characterSet);

        return $characterSet;
    }

    /**
     * Generates a backup code at the specified string length, using a mixture of digits and mixed case letters
     *
     * @param array $charset
     * @param int $codeLength
     * @return string
     */
    protected function generateCode(array $charset, int $codeLength = 9): string
    {
        $characters = [];
        $numberOfOptions = count($charset);
        while (count($characters) < $codeLength) {
            $key = random_int(0, $numberOfOptions - 1); // zero based array
            $characters[] = $charset[$key];
        }
        return implode($characters);
    }
}
