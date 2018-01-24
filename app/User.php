<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'email', 'password', 'flag', 'password', 'info_id', 'referral_id', 'eth_wallet'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public $rules = [
            'id'  => 'required',
            'email' => 'required',
            'name' => 'required',
            'eth_wallet' => 'required',
            'flag' => 'required',
            'info_id' => 'required',
            'referral_id' => 'required',
        ];

    public function referral() {
        return $this->hasOne('App\User');
    }

    public function info() {
        return $this->hasOne('App\Info', 'id', 'info_id');
    }

    public function transactions() {
        return $this->hasMany('App\Transaction')->orderBy('created_at', 'desc');
    }

    public function wallets() {
        return $this->hasMany('App\Wallet');
    }
}
