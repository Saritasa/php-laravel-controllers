<?php

namespace App\Api\V1\Responses;

use App\Models\NotificationType;

class NotificationSettingDTO extends DtoModel
{
    protected $id;
    protected $name;
    protected $is_on;

    protected static $collectionKey = 'settings';

    function __construct($userSetting, NotificationType $notificationType)
    {
        $this->id = $notificationType->id;
        $this->name = $notificationType->name;
        $this->is_on = $userSetting ? $userSetting->is_on : $notificationType->default_on;
    }
}