<?php

namespace Saritasa\Laravel\Controllers\Responses;

use App\Models\NotificationSetting;
use App\Models\NotificationType;
use Saritasa\Transformers\DtoModel;

class NotificationSettingDTO extends DtoModel
{
    protected $id;
    protected $name;
    protected $is_on;

    protected static $collectionKey = 'settings';

    function __construct(NotificationSetting $userSetting, NotificationType $notificationType)
    {
        $this->id = $notificationType->id;
        $this->name = $notificationType->name;
        $this->is_on = $userSetting ? $userSetting->is_on : $notificationType->default_on;
    }
}