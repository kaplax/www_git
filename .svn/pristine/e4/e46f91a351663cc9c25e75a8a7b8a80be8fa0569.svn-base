<?php
class Page {
	/**
	 * 返回分页数组
	 * @author kk
	 * @since 2014-04-12
	 * @param unknown $params
	 */
	public static function createPage( $params )
	{
		$pager				=	'<ul class="pagination">';
		$maxButtonCount		=	5;	//显示的按钮个数，
		do{
			try {
				$url		=	isset( $params['url'] ) ? $params['url'] : '';
				$size		=	isset( $params['size'] ) ? $params['size'] : '';
				$page		=	isset( $params['page'] ) ? $params['page'] : 1;
				$pageSize	=	isset( $params['pageSize'] ) ? $params['pageSize'] : PAGESIZE;
				$pageTotal	=	ceil( $size / $pageSize); 
				if( !strpos( $url ,  '?' ) ){
					$url	.=	'?c=2';
				}
				$url		.=	'&pageSize='. $pageSize ;
				$pager		.=	'<li ><a href="'.$url.'&page=1">首页</a></li>';
				if( 0==$size ){
					break;
				}
				if( $maxButtonCount >= $pageTotal  ){
					for( $i=1;$i<=$pageTotal;$i++ ){
						if( $page==$i ){
							$pager	.=	'<li class="active"><a href="'.$url.'&page='.$i.'">'.$i.'</a></li>';
						}else{
							$pager	.=	'<li><a href="'.$url.'&page='.$i.'">'.$i.'</a></li>';
						}
					}
				}else{
					if( 1 != $page ){
						$pager	.=	'<li ><a href="'.$url.'&page='. ( $page-1 ) .'">上一页</a></li>';
					}
					if( $page + $maxButtonCount > $pageTotal ){
						$frist	=	$pageTotal - $maxButtonCount +1;
						for ( $i=$frist ;$i<=$pageTotal;$i++ )
						{
							if( $page==$i ){
								$pager	.=	'<li class="active"><a href="'.$url.'&page='.$i.'">'.$i.'</a></li>';
							}else{
								$pager	.=	'<li><a href="'.$url.'&page='.$i.'">'.$i.'</a></li>';
							}
						}
					}else{
						for( $i=$page ; $i<$page+$maxButtonCount ; $i++ ){
							if( $page==$i ){
								$pager	.=	'<li class="active"><a href="'.$url.'&page='.$i.'">'.$i.'</a></li>';
							}else{
								$pager	.=	'<li><a href="'.$url.'&page='.$i.'">'.$i.'</a></li>';
							}
						}
					}
					if( $pageTotal != $page ){
						$pager	.=	'<li ><a href="'.$url.'&page='. ($page+1) .'">下一页</a></li>';
					}
				}
				$pager	.=	'<li ><a href="'.$url.'&page='.$pageTotal.'">末页</a></li>';
			} catch (Exception $e) {
			}
		}while(0);
    	$pager	.=	'</ul>';
    	return $pager;
	}
}

?>