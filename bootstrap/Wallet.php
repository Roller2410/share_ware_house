<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{

    protected $table = 'wallets';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'currency_id', 'user_id', 'address', 'private_key', 'balance',
    ];

    public $rules = [
            'id'  => 'required',
            'currency_id' => 'required',
            'address' => 'required',
            'private_key' => 'required',
            'balance' => 'required',
            'user_id' => 'required',
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
