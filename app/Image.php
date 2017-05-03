<?php
	namespace App;
	use Illuminate\Database\Eloquent\Model;
	
	class Image extends Model{
		protected $table = 'image';  //指定表名
		protected $primaryKey = 'auto_id';  //指定主键
		public $timestamps = false;  //关闭自动添加时间
		protected $guarded = ['auto_id'];  //不可批量添加的字段（黑名单）
	}
?>