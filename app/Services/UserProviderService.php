<?php

namespace App\Services;

use App\User;

class UserProviderService
{
    public function getUserProvider($provider)
    {

    }

    public function saveOrUpdate($userProvider, $provider)
    {
        try {
                
            if (!$this->hasUser($userProvider->email, $provider)) {

                $user = new User();
                $user->name     = $userProvider->name;
                $user->email    = $userProvider->email;
                $user->password = bcrypt($userProvider->token);
                $user->save();

                return $user;
            } 

            $user = User::where('email', $userProvider->email)->first();
            $user->name     = $userProvider->name;
            $user->email    = $userProvider->email;
            $user->password = bcrypt($userProvider->token);
            $user->save();

            return $user;


        } catch (\Exception $e) {
            
            return false;
        }       
    }

    public function hasUser($email, $provider)
    {
        return  (User::where('email', $email)->count() == 0) ? false : true; 
    }
}
