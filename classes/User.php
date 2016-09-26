<?php


class User extends BaseModel
{
    protected $fillable = ['username', 'name', 'email'];

    protected $rules = array(
        'fname'                 => 'required|alpha|min:2',
        'lname'                 => 'required|alpha|min:2',
        'username'              => 'required|unique:users',
        'email'                 => 'required|email',
        'password'              => 'required|alpha_num|between:6,12|confirmed',
        'password_confirmation' => 'required|alpha_num|between:6,12',
    );
}
