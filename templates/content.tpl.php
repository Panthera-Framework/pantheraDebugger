<?php
if (!function_exists('overlayContent'))
{
    function overlayContent($debuggerData, $debugger, $fileName)
    {
    	?><html>
    	<head>
    		<title>Panthera Debugger</title>
    		<meta charset="utf-8">
    		<link rel="stylesheet" type="text/css" href="<?=$debugger->debuggerWebroot;?>/webroot/debugpopup.css">
    		<script type="text/javascript" src="<?=$debugger->debuggerWebroot;?>/webroot/jquery.js"></script>
    		<script type="text/javascript" src="<?=$debugger->debuggerWebroot;?>/webroot/panthera.js"></script>
    		<link rel="stylesheet" type="text/css" href="<?=$debugger->debuggerWebroot;?>/webroot/jsoneditor/jsoneditor.min.css">
            <script type="text/javascript" src="<?=$debugger->debuggerWebroot;?>/webroot/jsoneditor/jsoneditor.min.js"></script>
    
    		<script type="text/javascript">
            var fileName = '<?php print($fileName);?>';
    
    		function showTable(tabName)
    		{
    		    $('#ajaxResponseContent').html('');
    		    
    			panthera.jsonPOST({
    			    'url': '?jsonRequest=getTab&fileName='+fileName+'&tabName='+tabName,
    			    'success': function (response) {
    			        if (response.status == 'success')
    			        {
    			            $('#ajaxResponseContent').html(response.data);
    			        }
    			    }
    			});
    		}
    		
    		$(document).ready(function () {
    		    showTable('request');
    		});
    		</script>
    	</head>
    
    	<body>
    		<div id="logoBar">
    			<div class="centerWithContent pantheraLogo">
    				<span><a>Panthera Debugger</a></span>
    				
                    <div class="userBar">
						<?php
						if ($debugger -> db -> sessions )
						{
						?>
							<select id="sessionSelection" onchange="window.location.href = '?sessionKey='+this.value;">';

							<?php
							foreach ($debugger -> db -> sessions as $sessionKey => $sessionName)
								print('<option value="' .$sessionKey. '" ' .($fileName == $sessionKey ? 'selected' : ''). '>' .$sessionName. '</option>');
							?>
							</select>
						<?php
						}
						?>
                    </div>
    			</div>
    			
    			<!-- Menubar -->

				<?php
				if ($debugger -> db -> sessions and $debuggerData['data'])
				{
				?>
    
    			<div id="menuBarVisibleLayer">
    				<div class="centerWithContent" id="menuBar">
                        <?php
							foreach ($debuggerData['data'] as $key => $value)
							{
								if ($value['data'] or isset($value['template']))
								{
									?><span class="menuItem">
										<a onclick="showTable('<?=$key;?>');">
											<img src="<?=$debugger->debuggerWebroot;?>/webroot/transparent.png" class="pantheraIcon menuIcon" alt="<?=$value['title'];?>">
											<span class="menuText"><?=$value['title'];?></span>
										</a>
									</span>
									<?php
								}
							}
						?>
    				</div>
    			</div>
				<?php
				} else {
				?>
				<div style="text-align: center;">No dump selected, and/or no dumps avaliable. Please connect debugger with your application to generate dumps to analyze.</div>
				<?php
				}
				?>
    		</div>

    		<div id="ajaxResponseContent" class="centerWithContent popupContent">
    		</div>
        </body>
    </html>
    <?php
    }
}
