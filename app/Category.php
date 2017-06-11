<?php
	namespace App;
	use Illuminate\Database\Eloquent\Model;
	
	class Category extends Model{
		protected $table = 'category';  //指定表名

		protected $guarded = ['auto_id'];  //不可批量添加的字段（黑名单）



    public function getCategoryName($category_id)
    {
        $result = $this->select('category_name')->where('category_id',$category_id)->get();
        return $result;
    }

    public function getCategoryList()
    {
        $categorys = Category::get();
        //var_dump($categorys);
        //echo $categorys->auto_id;
        return $categorys;
    }
		/**
     * @param $request
     * @param ids 从前台接收到的名称为ids的数组，数组中的数据对应表中的category_id
     * @param names 从前台接收到的名称为names的数组，数组中的数据对应表中的category_name
     * @param locations 从前台接收到的名称为locations的数组，数组中的数据对应表中的category_location
     * @param is_del 从前台接收到的名称为is_del的数组，数组中的数据对应表中的is_del
     * @return 返回更新了几条数据
     */
    public function storegetCategories($names)
    {
        $flag = 0;
        $i = 0;
        foreach ($names as $name) {
            $i++;
            $ids = Category::select('auto_id')->get()->toArray();
            $id = max($ids)['auto_id']+1;
            $category_id = 'C_'.$id;
            $location = 'public/image/'.$name.'/';
            $bool = Category::create([
                'category_id'=>$category_id,
                'category_name'=>$name,
                'category_location'=>$location
            ]);
            $dir = iconv("UTF-8", "GBK", "Image/$name");

            if($bool) {
                $flag++;
                if (!file_exists($dir)){
                    mkdir ($dir,0777,true);
                    //echo '创建文件夹成功';
                } else {
                    //echo '需创建的文件夹已经存在';
                }
            }
            };
      if($i == $flag)
       {
           $resultCode = 1;
        }else{
           $resultCode = 0;
       }
        return $resultCode;
    }
    /**
     * @param $request
     * @return 返回成功更新了几个名称
     */
    public function updateCategoryNames($category_ids,$category_names)
    {
        $i = 0;
        $flag1 = 0;
        $flag2 = 0;
        foreach ($category_names as $category_name) {
            if(Category::where('category_name', $category_name)->first()) {
                $oldCategory = Category::where('category_id', $category_ids[$i])->select('category_name')->get()->toArray();
                //$newCategory = Category::where('category_name', $category_name)->select('auto_id')->get()->toArray();
                $oldName = $oldCategory[0]['category_name'];
                $newName = $category_name;
                /*echo $oldName;
                echo $newName;
                echo 'Image/'.$oldName;
                echo 'move Image\\'.$oldName.'\* Image\\'.$newName;*/
                //说明：在windows下  用2  将1注释掉  在linux下相反！！！！！！！！！
               // $mvLocation = iconv("UTF-8", "GBK", 'mv Image/'.$oldName.'/* Image/'.$newName);//1
                $mvLocation = iconv("UTF-8", "GBK", 'move Image\\'.$oldName.'\* Image\\'.$newName);//2
                system($mvLocation);
               // $remove = iconv("UTF-8", "GBK", 'rm -rf Image/'.$oldName);//1
                $remove = iconv("UTF-8", "GBK", 'rd Image\\'.$oldName);//2
                system($remove);
                Category::where('category_id',$category_ids[$i])->delete();
                $flag1++;
            }else{
                $oldCategory = Category::where('category_id', $category_ids[$i])->select('category_name')->get()->toArray();
                //$newCategory = Category::where('category_name', $category_name)->select('auto_id')->get()->toArray();
                $oldName = $oldCategory[0]['category_name'];
                $newName = $category_name;
                $oldDic = iconv("UTF-8", "GBK", "Image/$oldName");
                $newDic = iconv("UTF-8", "GBK", "Image/$newName");
                $bool = rename($oldDic,$newDic);
                echo $bool;
                $location = 'public/image/'.$category_name.'/';
                Category::where('category_id',$category_ids[$i])->update(
                    [
                        'category_name'=>$category_name,
                        'category_location'=>$location
                    ]
                );
                $flag2++;
            }
            $i++;
        }
        if($flag1+$flag2 == $i){
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * @param $request
     * @return 返回删除了几个名称
     */
    public function deleteCategories($ids)
    {

        //var_dump($data['id']);
        $i = 0;
        $flag = 0;
        foreach ($ids as $id)
        {
            $num = Category::where('category_id',$id)->update(
                ['is_del'=>1]
            );
            //echo '第'.($i+1).'次删除了'.$num.'行！';
            if($num){
                $flag++;
            }
            $i++;
        }
        if($i == $flag){
            $resultCode = 1;
        }else{
            $resultCode = 0;
        }
        return $resultCode;
    }
	}
?>