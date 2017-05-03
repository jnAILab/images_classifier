<?php
	namespace App;
	use Illuminate\Database\Eloquent\Model;
	
	class Category extends Model{
		protected $table = 'category';  //指定表名
		public $timestamps = false;  //关闭自动添加时间
		protected $guarded = ['auto_id'];  //不可批量添加的字段（黑名单）
			
		/**
     * @param $request
     * @param ids 从前台接收到的名称为ids的数组，数组中的数据对应表中的category_id
     * @param names 从前台接收到的名称为names的数组，数组中的数据对应表中的category_name
     * @param locations 从前台接收到的名称为locations的数组，数组中的数据对应表中的category_location
     * @param is_del 从前台接收到的名称为is_del的数组，数组中的数据对应表中的is_del
     * @return 返回更新了几条数据
     */
    public static function storegetCategories($request)
    {
        $ids = $request->input('ids');
        $names = $request->input('names');
        $locations = $request->input('locations');
        $is_dels = $request->input('is_dels');
        $i = 0;
        foreach ($ids as $id) {
            $category = new Category();
            $category->category_id = $id;
            $category->category_name = $names[$i];
            $category->category_location = $locations[$i];
            $category->is_del = $is_dels[$i];
            //$category->save();
            if ($category->save()) {
                echo '第' . ($i + 1) . '条数据插入成功！';
            }
            $i++;
        }
        return $i+1;
    }

    /**
     * @param $request
     * @return 返回成功更新了几个名称
     */
    public static function updateCategoryNames($request)
    {
        $befores = $request->input('befores');
        $afters = $request->input('afters');
        $i = 0;
        foreach ($befores as $before)
        {
            $num = Category::where('category_name','=',$before)->update(
                ['category_name'=>$afters[$i]]
            );
            $i++;
        }
        return $i+1;
    }

    /**
     * @param $request
     * @return 返回删除了几个名称
     */
    public static function deleteCategories($request)
    {
        $deletes = $request->input('deletes');
        //var_dump($data['id']);
        $i = 0;
        foreach ($deletes as $delete)
        {
            $num = Category::where('category_name','=',$delete)->delete();
            //echo '第'.($i+1).'次删除了'.$num.'行！';
            $i++;

        }
        return $i+1;
    }
	}
?>