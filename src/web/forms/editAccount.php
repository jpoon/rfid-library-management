<table class="forms">
    <tr>
        <td colspan="3"><strong>View/Edit Account</strong></td>
        <td>
            <?php
                if($session->form->num_errors > 0){
                    echo "<div class=\"formError\">".$session->form->num_errors." error(s) found</div>";
                } elseif ( isset($_SESSION['editSuccess']) ) {
                    /* Editing was successful */
                    unset($_SESSION['editSuccess']);
                    echo "<div class=\"formSuccess\">Changes successfully saved</div>";
                } 
            ?>
        </td>
    </tr>
    <tr>
        <td width="75">First Name</td>
        <td width="5">:</td>
        <td width="150"><input name="firstname" type="text" maxlength="30" value="<?php echo $session->firstname; ?>"></td>
        <td width="300"><?php echo $session->form->getError("firstname"); ?></td>
    </tr>
    <tr>
        <td>Last Name</td>
        <td>:</td>
        <td><input name="lastname" type="text" maxlength="30" value="<?php echo $session->lastname; ?>"></td>
        <td><?php echo $session->form->getError("lastname"); ?></td>
    </tr>
    <tr>
        <td>User Name</td>
        <td>:</td>
        <td><?php echo $session->username; ?></td>
    </tr>
    <tr>
        <td>Password</td>
        <td>:</td>
        <td><input name="password" type="password" maxlength="30"></td>
        <td><?php echo $session->form->getError("password"); ?></td>
    </tr>
    <tr>
        <td>Email</td>
        <td>:</td>
        <td><input name="email" type="text" maxlength="30" value="<?php echo $session->email; ?>"></td>
        <td><?php echo $session->form->getError("email"); ?></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td><input type="submit" name="submit" value="Save"></td>
    </tr>
</table>