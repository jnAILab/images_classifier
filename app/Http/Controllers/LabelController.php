<?php
/**
 * Created by PhpStorm.
 * User: ZZM
 * Date: 2017/4/27
 * Time: 15:17
 */

namespace App\Http\Controllers;

use App\Label;
use Illuminate\Http\Request;
use App\LabelModel;

class LabelController extends Controller
{
    //储存用户标记的标签
    public static function storeLabelContent(Request $request)
    {
        Label::storeLabelContent($request);

    }

    //获取指定用户图片的标签内容
    public static function getLabelContent(Request $request)
    {
        Label::getLabelContent($request);
        return $request;

    }

    //更新标签的内容
    public static function updateLabelContent(Request $request)
    {
        Label::updateLabelContent($request);

    }

    //删除标签
    public static function deleteLabel(Request $request)
    {
        $result=Label::deleteLabel($request);
        return $result;
    }

}