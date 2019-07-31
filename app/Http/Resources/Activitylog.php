<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Activitylog extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $changelog="";                
        foreach($this['attributes'] as $k => $v){        
            $changelog.='<b>'.$k .'</b> was <u class="text-red">'.$this['old'][$k]. '</u> now <i class="text-green">'.$v."</i><br>";
        }
        return [
            $changelog
        ];
    }
}
