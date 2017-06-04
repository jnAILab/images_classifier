<?php
/**
 * Created by PhpStorm.
 * User: ZZM
 * Date: 2017/6/3
 * Time: 19:09
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image_Label extends Model
{
    protected $table = 'image_label';

    protected $primaryKey = 'auto_id';

    protected $guarded = ['auto_id'];

    //向image_label中储存image_id和label_id
    public function addId($image_id,$label_id,$is_del)
    {

        $remind = Image_Label::create(
            [
                'image_id'=>$image_id,
                'label_id'=>$label_id,
                'is_del'=>$is_del
            ]
        );

        if($remind)
        {
            return 1;
        }else{
            return 0;
        }
    }
}

