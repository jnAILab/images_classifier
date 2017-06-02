<?php
	namespace App;
	use Illuminate\Database\Eloquent\Model;
	
	class Category extends Model{
		protected $table = 'category';  //指定表名
		public $timestamps = false;  //关闭自动添加时间
		protected $guarded = ['auto_id'];  //不可批量添加的字段（黑名单）




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
    public function storegetCategories($ids,$names,$locations,$is_dels)
    {

        $i = 0;
        $flag = 0;
        foreach ($ids as $id) {
            $category = new Category();
            $category->category_id = $id;
            $category->category_name = $names[$i];
            $category->category_location = $locations[$i];
            $category->is_del = $is_dels[$i];
            //$category->save();
            if ($category->save()) {
                $flag++;
            }
            $i++;
        }
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
    public function updateCategoryNames($ids,$names)
    {
        $i = 0;
        $flag = 0;
        foreach ($ids as $id)
        {
            $num = Category::where('category_id','=',$id)->update(
                ['category_name'=>$names[$i]]
            );
            if($num>0){
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
            $num = Category::where('category_id','=',$id)->delete();
            //echo '第'.($i+1).'次删除了'.$num.'行！';
            if($num>0){
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