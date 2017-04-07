<?php

namespace Saritasa\Laravel\Controllers\Responses;

use App\Models\Preference;
use Saritasa\Transformers\DtoModel;

class AddressDTO extends DtoModel
{
    protected $name;
    protected $address;
    protected $city;
    protected $state;
    protected $zip;

    function __construct(Preference $preferences)
    {
        $this->name = $preferences->shipping_name;
        $this->address = $preferences->shipping_street_address;
        $this->city = $preferences->shipping_city;
        $this->state = $preferences->shipping_state;
        $this->zip = $preferences->shipping_zip_code;
    }
}