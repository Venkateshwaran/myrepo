<?php 
	include "init.inc.php";
	checkLogin();
	checkStudent();
	extract($_GET);
	$difflevelArray=array(1=>"Easy", 2=>"Medium", 3=>"Difficult");
	// Log entry into quizlog
	
	$res = mysqli_query($con, "Insert into quizlog (quizId, userId) values($quizId,".$_SESSION['userId'].")");
	if(!$res) //query failed -> meaning he had started the quiz earlier
	{
		//get the start time
		$res = mysqli_query($con,"select time_to_sec(timediff(NOW(),startTime)) as timeElapsed,complete from quizlog 
									where userId = ".$_SESSION['userId']." and quizId=$quizId");
		$row = mysqli_fetch_assoc($res);
		
		$timeElapsedInMinutes = ceil($row['timeElapsed']/60);
		$timeElapsedInSeconds = $row['timeElapsed']%60;
		//echo $timeElapsedInMinutes;
		if($timeElapsedInMinutes > 30 || $row['complete'] == 1)
		{
			//disqualify or send him back
			header("Location:dashboard.php?error=1");
		}
		
		
	}
	
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Online Quiz</title>
<link href="style.css" rel="stylesheet" type="text/css" />

<script language="javascript">

var mins
var secs;

function cd() {
 	mins = 1 * m("<?php if(isset($timeElapsedInMinutes)) echo (3 - $timeElapsedInMinutes );
 	else echo "3";?>"); // change minutes here
 	secs = 0 + s(":<?php if(isset($timeElapsedInMinutes)) echo (60 - $timeElapsedInSeconds - 1);
 	else echo "01";?>"); // change seconds here (always add an additional second to your total)
 	redo();
}

function m(obj) {
 	for(var i = 0; i < obj.length; i++) {
  		if(obj.substring(i, i + 1) == ":")
  		break;
 	}
 	return(obj.substring(0, i));
}

function s(obj) {
 	for(var i = 0; i < obj.length; i++) {
  		if(obj.substring(i, i + 1) == ":")
  		break;
 	}
 	return(obj.substring(i + 1, obj.length));
}

function dis(mins,secs) {
 	var disp;
 	if(mins <= 9) {
  		disp = " 0";
 	} else {
  		disp = " ";
 	}
 	disp += mins + ":";
 	if(secs <= 9) {
  		disp += "0" + secs;
 	} else {
  		disp += secs;
 	}
 	return(disp);
}

function redo() {
 	secs--;
 	if(secs == -1) {
  		secs = 59;
  		mins--;
 	}
 	document.cd.disp.value = dis(mins,secs); 
 	if((mins ==1) && (secs == 0)) 
  		window.alert("Only 1 minute remains...\nSpeed Up"); 
  	
 	if((mins == 0) && (secs == 0)) {
  		window.alert("Time is up. Press OK to continue."); 
  		answers.submit();
  		console.log("submitted");
 	} else {
 		cd = setTimeout("redo()",1000);
 	}
}

function init() {
  cd();
}
window.onload = init;
var currentQuestion = 0;
</script>

</head>

<body>
	<div id="main">
    	<?php disp_header();?>
		<div id="content">
			<div id="details">
				<div class="title">
					
					<?php echo singleValQuery("select title from quiz where quizId=$quizId");?>
					<div id="timer">
					<form name="cd">
					<input id="txt" readonly="true" type="text" value="15:00" border="0" name="disp">
					</form>
				</div>
				</div>
				<form method="post" name="answers" id="answers" action="results.php?quizId=<?php echo $quizId;?>">
				<div id="qContainer">
					<?php 
						$q=array();
						$i=0;
						$res=mysqli_query($con,
						
						"(
							SELECT questions.* FROM questions 
							JOIN questionsforquiz 
							ON questions.qid=questionsforquiz.qid
							WHERE questionsforquiz.quizId=$quizId
							ORDER BY RAND()
							)
						") 
							or die("error fetching questions");
						
						
						
						/*"(Select * from questions where quizId=$quizId and difflevel=1 order by rand() limit 3)
						union
						(Select * from questions where quizId=$quizId and difflevel=2 order by rand() limit 4)
						union
						(Select * from questions where quizId=$quizId and difflevel=3 order by rand() limit 3)") 
							or die("error fetching questions");
						print_r($res);*/
						echo "<input type='hidden' value='$quizId' name='quizId'>";
						while($row=mysqli_fetch_assoc($res))
						{
							extract($row);
							
							$i++;
							echo "<div class='qPanel'>
								<input type='hidden'>
								<div class='qText'>
								<input type='hidden' value='$qId' name='qid[$i]'>
								<b>Q$i.</b> $question <br>
								";
								if(file_exists("pictures/$qId.jpg"))
									echo '<img src="pictures/'.$qId.'.jpg" />';
								echo "
								</div>
								
								<div class='options'>
									<input type='radio' name='op[$i]' value='0' id='op-$i-0' class='hidden' checked>
									<input type='radio' name='op[$i]' value='1' id='op-$i-1'><label for='op-$i-1'>$op1";
									if(file_exists("pictures/$qId"."_0.jpg"))
										echo '<img src="pictures/'.$qId.'_0.jpg" />';
									echo "</label><br>
									<input type='radio' name='op[$i]' value='2' id='op-$i-2'><label for='op-$i-2'>$op2";
									if(file_exists("pictures/$qId"."_0.jpg"))
										echo '<img src="pictures/'.$qId.'_1.jpg" />';
									echo "</label><br>
									<input type='radio' name='op[$i]' value='3' id='op-$i-3'><label for='op-$i-3'>$op3";
									if(file_exists("pictures/$qId"."_0.jpg"))
										echo '<img src="pictures/'.$qId.'_2.jpg" />';
									echo "</label><br>
									<input type='radio' name='op[$i]' value='4' id='op-$i-4'><label for='op-$i-4'>$op4";
									if(file_exists("pictures/$qId"."_0.jpg"))
										echo '<img src="pictures/'.$qId.'_3.jpg" />';
									echo "</label><br>
								</div>
								<div><b>Level:</b> $difflevelArray[$difflevel]</div>
							</div>";
						}
						
					
					?>
					
				</div>
				<?php
				echo "<div id='nav'>";
						echo "<a class='navbutton' onclick='nav(-1)'>&laquo; Previous Question</a> ";
						echo "<a class='navbutton' onclick='nav(1)'>Next Question &raquo;</a><br /><br /><br /><br />";
						for($i=0;$i<10;$i++)
						{
							
							echo "<a class='qButton' onclick='show($i)'>".($i+1)."</a>";
						}
						echo "</div>";
				?>
				<script type="text/javascript">
						document.getElementsByClassName("qPanel")[0].className+=" active";
						document.getElementsByClassName("qButton")[0].className+=" current";
						function show(i)
						{
							document.getElementsByClassName("current")[0].className="qButton";
							document.getElementsByClassName("active")[0].className="qPanel";
							
							document.getElementsByClassName("qPanel")[i].className+=" active";
							document.getElementsByClassName("qButton")[i].className+=" current";
							currentQuestion=i;
							
						}
						function nav(offset)
						{
							var targetQuestion = currentQuestion + offset;
							if ( targetQuestion>=0 && targetQuestion< document.getElementsByClassName("qButton").length)
								show(targetQuestion);
							
						}
					</script>
                    <center><input class="button" type="submit" value="Submit Answers" name="submit1"/></center>
				</form>
			</div>
        </div>        
        <?php disp_footer();?>
    </div>
</body>
</html>
