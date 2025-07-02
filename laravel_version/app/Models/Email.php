<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $appends = ['timestamp'];

   public function getDestinationAttribute($value)
    {
        return json_decode($value, true);
    }
   public function getTimestampAttribute($value)
    {
        $date = Carbon::parse($this->created_at)->toDateTimeString();
        return $date; 
    }
}
