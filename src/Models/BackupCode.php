<?php

namespace Firesphere\BootstrapMFA;


use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

/**
 * Class BackupCode
 *
 * @property string $Code
 * @property string $Salt
 * @property bool $Used
 * @property int $MemberID
 * @method Member Member()
 */
class BackupCode extends DataObject
{

    private static $table_name = 'BackupCode';

    private static $db = [
        'Code' => 'Varchar(255)',
        'Used' => 'Boolean(false)'
    ];

    private static $has_one = [
        'Member' => Member::class
    ];

    private static $indexes = [
        'Code' => [
            'type'    => 'unique',
            'columns' => ["MemberID","Code"],
        ],
    ];

    protected function generateToken()
    {
        $config = Config::inst()->get(CodeGenerator::class);
        $generator = Injector::inst()->get(CodeGenerator::class)
            ->setLength($config['length']);
        switch ($config['type']) {
            case 'mixed':
                $generator->alphanumeric();
                break;
            case 'numeric':
                $generator->numbersonly();
                break;
            case 'characters':
                $generator->charactersonly();
                break;
            default:
                $generator->numbersonly();
        }
        switch ($config['case']) {
            case 'upper':
                $generator->uppercase();
                break;
            case 'lower':
                $generator->lowercase();
                break;
            case 'mixed':
                $generator->mixedcase();
                break;
            default:
                $generator->mixedcase();
        }

        return $generator->generate();
    }

    public function populateDefaults()
    {
        $this->Code = $this->generateToken();

        return parent::populateDefaults();
    }

    /**
     * @param Member $member
     * @return DataList|static[]
     */
    public static function getValidTokensForMember($member)
    {
        return static::get()->filter(
            [
                'Used'     => false,
                'MemberID' => $member->ID
            ]
        );
    }

    public static function generateTokensForMember($member)
    {
        $message = '<p>Here are your tokens, please store them securily. ' .
            'They are stored encrypted and can not be recovered, only reset.</p><p>';
        $session = Controller::curr()->getRequest()->getSession();
        $limit = static::config()->get('token_limit');
        for ($i = 0; $i < $limit; ++$i) {
            $code = static::create();
            $code->MemberID = $member->ID;
            $token = $code->Code;
            $code->Code = $member->encryptWithUserSettings($code->Code);
            $code->write();
            $code->destroy();
            $message .= sprintf('%s<br />', $token);
        }
        $message .= '</p>';
        $session->set('tokens', $message);
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        // Encrypt a new temporary key before writing to the database
        if (!$this->Used) {
            $member = $this->Member();
            $this->Code = $member->encryptWithUserSettings($this->Code);
        }
    }

    public function expire()
    {
        $this->Used = true;
        $this->write();

        return $this;
    }

    public function canEdit($member = null)
    {
        return false;
    }

}
