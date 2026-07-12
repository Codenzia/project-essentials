<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Tests\Fixtures;

use Codenzia\ProjectEssentials\Traits\CanLogsActivity;
use Illuminate\Database\Eloquent\Model;

class LoggableWidget extends Model
{
    use CanLogsActivity;

    protected $table = 'loggable_widgets';

    protected $fillable = [
        'name',
        'password',
        'api_token',
        'internal_note',
    ];

    protected $hidden = [
        'password',
    ];

    protected array $activityLogExcept = [
        'internal_note',
    ];
}
