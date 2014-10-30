<?php

namespace Vf\Bundle\GocardlessEnterpriseBundle\Entity;

use GoCardless\Enterprise\Client as BaseClient;

class Client extends BaseClient
{

    protected $primaryCreditorId;

    public function __construct(\Guzzle\Http\Client $client, array $config)
    {
        parent::__construct($client, $config);

        $this->primaryCreditorId = $config["primaryCreditorId"];

    }

    /**
     * @return mixed
     */
    public function getPrimaryCreditorId()
    {
        return $this->primaryCreditorId;
    }


}

