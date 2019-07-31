<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Contact extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'user_id'=>$this->id,
            'user_name'=>$this->fname.' '.$this->lname,
            'avatar'=>($this->avatar) ? $this->avatar : 'default_avatar_male.jpg'
        ];
    }
}
