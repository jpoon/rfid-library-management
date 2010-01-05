	<?php echo "<form name=\"changeBookDetails\" method=\"post\" action=\"bookProcess.php?changeBookDetails&type=".$queryType."\">"; ?>
	<table class="forms">
	<tr>
        <td width="105">Title</td>
        <td width="5">:</td>
        <td width="150"><input name="title" type="text" style='width: 150px;' value="<?php echo $session->form->getValue("title"); ?>"></td>
        <td><?php echo $session->form->getError("title"); ?></td>
    </tr>
    <tr>
        <td>Author</td>
        <td>:</td>
        <td><input name="author" type="text" style='width: 150px;' maxlength="<?php MAX_LEN?>" value="<?php echo $session->form->getValue("author"); ?>"></td>
        <td><?php echo $session->form->getError("author"); ?></td>
    </tr>
    <tr>
        <td>Publisher</td>
        <td>:</td>
        <td><input name="publisher" type="text" style='width: 150px;' maxlength="<?php MAX_LEN?>" value="<?php echo $session->form->getValue("publisher"); ?>"></td>
        <td><?php echo $session->form->getError("publisher"); ?></td>
    </tr>
    <tr>
        <td>Call Number</td>
        <td>:</td>
        <td>
        <?php 
        if( $queryType == "add" ){
        	echo "<input name=\"callNo\" type=\"text\" style='width: 150px;' maxlength=".MAX_LEN." value=\"".$session->form->getValue("callNo")."\">";
        }
        else{
        	echo "<input type=\"hidden\" name=\"callNo\" value=\"".$session->form->getValue("callNo")."\" />";
        	echo "<b>".$session->form->getValue("callNo")."</b>";
        }
        ?>
        </td>
        <td><?php echo $session->form->getError("callNo"); ?></td>
    </tr>
    <tr>
        <td>Book State</td>
        <td>:</td>
        <td>
        <?php if($session->form->getValue("state") == "On Loan") echo "<b>On Loan</b>";
        	  else{
        ?>
        <select name="state" style='width: 150px;' >
			<option value="In Stacks" <?php if($session->form->getValue("state") == "In Stacks") echo "selected"?>>In Stacks</option>
			<option value="In Reserve" <?php if($session->form->getValue("state") == "In Reserve") echo "selected"?>>In Reserve</option>
			<option value="Returned" <?php if($session->form->getValue("state") == "Returned") echo "selected"?>>Returned</option>
			<option value="Missing" <?php if($session->form->getValue("state") == "Missing") echo "selected"?>>Missing</option>
			<option value="Damaged" <?php if($session->form->getValue("state") == "Damaged") echo "selected"?>>Damaged</option>
		</select><?php } ?></td>
    </tr>
    <tr>
        <td>ISBN Number</td>
        <td>:</td>
        <td><input name="isbn" type="text" style='width: 150px;' maxlength="<?php MAX_LEN?>" value="<?php echo $session->form->getValue("isbn"); ?>"></td>
        <td><?php echo $session->form->getError("isbn"); ?></td>
    </tr>
    <tr>
        <td>Shelf</td>
        <td>:</td>
        <td><select name="shelf" style='width: 150px;' >
        <?php
        while( $desc = mysql_fetch_array($shelves) ){
        	$myDesc = $desc['description'];
        	echo "<option value=\"$myDesc\"";
        	if( $session->form->getValue("shelf") == $myDesc ) echo "selected"; 
        	echo ">$myDesc</option>";
        }
        ?>
        </select></td>
        <td><?php echo $session->form->getError("shelf"); ?></td>
    </tr>
    <input type="hidden" name="rfid" value="<?php echo $session->form->getValue("rfid"); ?>" />
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>
            <input type="submit" name="submit" style='width: 150px;' value="Submit" />
        </td>
    </tr>
    </table>
	</form>
