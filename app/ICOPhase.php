<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ICOPhase extends Model
{

    protected $table = 'ico_phases';

    public $timestamps = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'title', 'start_time', 'end_time', 'tokens_amount', 'token_price', 'tokens_sold'
    ];

    public $rules = [
            'id'  => 'required',
            'title' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'tokens_amount' => 'required',
            'token_price' => 'required',
            'tokens_sold' => 'required',
        ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

}
