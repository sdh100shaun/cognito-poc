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

}