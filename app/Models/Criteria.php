<?php

namespace App\Models;

use App\Services\Common;
use Illuminate\Http\Request;

class Criteria
{
    public $request;
    public $relationships;
    public $details;
    public $pagination = -1;
    public $httpParams =  [];
    public $optional;
    public $updatedColumns = [];
    public $customColumns = [];

    public function __construct(protected $httpRequest = null)
    {
        $this->request = $httpRequest;
        if ($httpRequest) {
            $this->httpParams = $httpRequest->query();
            $this->relationships = Common::splitToArray($httpRequest['relationships']);
            $this->details = $httpRequest->validated();
        }
    }
}
