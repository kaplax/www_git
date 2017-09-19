<!DOCTYPE html>
<html lang="zh-cn">
    <head>
    <meta charset="utf-8">
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="x-rim-auto-match" content="none">
    </head>
    <body>
     <div class="container" style='min-width: 480px;'>
	          <!-- content==================start================================ -->
	          <div class="row show_demo">
	          		<div class="col-xs-12 col-sm-12 col-md-12" style='padding:0px;margin:0px;'>
	          			<?php echo $content; ?>
	          		</div>
	          	</div>
	       	  <!-- content==================end================================ -->
	       	  <div class='row company_logo' style='text-align:center;font-size:10px;width:100%;bottom:5px;margin:0 auto;'>
	          	<table style='width:100%;text-align:center;margin-top:2px;margin-bottom:2px;'>
	          		<tr>
	          			<td >
	          				Powered by&nbsp;<img alt="" src="<?php echo Yii::app()->request->baseUrl; ?>/images/logo.png" class='' style='height:10px;vertical-align:text-bottom;margin-bottom:2px;'>
		          		</td>
		          	</tr>
		         </table>
	          </div>
	    </div>
	</body>
</html>
