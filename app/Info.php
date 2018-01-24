<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Info extends Model
{

    protected $table = 'info';

    // Solution for using only created_at
    //public $timestamps = ['created_at'];
    const UPDATED_AT = null;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'country', 'passport_image_path', 'driving_license_path', 'bank_statement_path', 'mandatory_request_path'
    ];

    public $rules = [
            'id'  => 'required',
            'country' => 'required',
            'passport_image_path' => 'required',
            'driving_license_path' => 'required',
            'bank_statement_path' => 'required',
            'mandatory_request_path' => 'required',
           // 'person_image_path' => 'required',
        ];
    
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'country', 'passport_image_path', 'driving_license_path', 'bank_statement_path', 'mandatory_request_path', 'created_at',
    ];

    public function user(){
        return $this->hasOne('App\User');
    }

}
