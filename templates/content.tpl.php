<?php
if (!function_exists('overlayContent'))
{
    function overlayContent($debuggerData, $debugger)
    {
    	?><html>
    	<head>
    		<title>Panthera Debugger</title>
    		<meta charset="utf-8">
    		<link rel="stylesheet" type="text/css" href="<?=$debugger->debuggerWebroot;?>/webroot/debugpopup.css">
    		<script type="text/javascript" src="<?=$debugger->debuggerWebroot;?>/webroot/jquery.js"></script>
    
    		<script type="text/javascript">
    		$(document).ready(function() {
    			i = 0;
    
    			htmlCode = "";
    
    			x = 0;  //horizontal coord
    			y = document.height; //vertical coord
    
    			if (!localStorage.getItem("debpopupTab") || localStorage.getItem("debpopupTab") == "debug")
    			{
    				window.scroll(x,y);
    			}
    
    			if (localStorage.getItem("debpopupTab"))
    			{
    				showTable(localStorage.getItem("debpopupTab"));
    			}
    		});
    
    		function showTable(tabName)
    		{
    			if ($("#tab_"+tabName).length)
    			{
    				$(".allTabs").hide();
    				$("#tab_"+tabName).show();
    				localStorage.setItem("debpopupTab", tabName);
    			}
    		}
    		</script>
    	</head>
    
    	<body>
    		<div id="logoBar">
    			<div class="centerWithContent pantheraLogo">
    				<span><a>Panthera Debugger</a></span>
    				
                    <div class="userBar">
                        <select id="sessionSelection">';
                        
                        <?php
                        foreach ($debuggerData['session'] as $sessionName)
                            print('<option value="' .$sessionName. '">' .$sessionName. '</option>');
                        ?>
                        </select>
                    </div>
    			</div>
    			
    			<!-- Menubar -->
    
    			<div id="menuBarVisibleLayer">
    				<div class="centerWithContent" id="menuBar">
                        <?php
    					foreach ($debuggerData['data'] as $key => $value)
    					{
    						if ($value['data'] or isset($value['template']))
    						{
    							?><span class="menuItem">
    								<a onclick="showTable(\'<?=$key;?>\');">
    									<img src="<?=$debugger->debuggerWebroot;?>. '/webroot/transparent.png" class="pantheraIcon menuIcon" alt="<?=$value['title'];?>">
    									<span class="menuText"><?=$value['title'];?></span>
    								</a>
    							</span>
    							<?php
    						}
    					}
                        ?>
    					</div>
    			</div>
    		</div>
    
    		<div id="ajax_content" class="centerWithContent popupContent">
    		</div>
        </body>
    </html>
    <?php
    }
}
