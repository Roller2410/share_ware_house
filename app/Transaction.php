<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    protected $table = 'transactions';

    // Solution for using only created_at
    //public $timestamps = ['created_at'];
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'hash', 'wallet', 'target_amount', 'source_amount', 'currency_id', 'created_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //'email'
    ];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function currency(){
        return $this->belongsTo('App\Currency');
    }

}
