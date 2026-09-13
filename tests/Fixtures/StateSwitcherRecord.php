<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class StateSwitcherRecord extends Model
{
    protected $table = 'state_switcher_records';

    protected $guarded = [];

    public $timestamps = false;
}
