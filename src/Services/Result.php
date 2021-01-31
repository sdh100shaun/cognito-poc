<?php
declare(strict_types=1);

namespace App\Services;
use Aws\Result as AwsResult;
use Aws\ResultInterface;

class Result extends AwsResult implements ResultInterface
{
    public function hasError():bool{
        return !empty($this->get('error'));
    }

    private function getChallengeName():string {
        return  parent::get('ChallengeName');
    }

    public function requiresNewPassword(): bool
    {
        return  $this->getChallengeName() === 'NEW_PASSWORD_REQUIRED';
    }
}