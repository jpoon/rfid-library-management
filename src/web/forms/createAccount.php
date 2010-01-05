<table class="forms">
    <tr>
        <td width="75">First Name</td>
        <td width="5">:</td>
        <td width="150"><input name="firstname" type="text" maxlength="30" value="<?php echo $session->form->getValue("firstname"); ?>"></td>
        <td width="300"><?php echo $session->form->getError("firstname"); ?></td>
    </tr>
    <tr>
        <td>Last Name</td>
        <td>:</td>
        <td><input name="lastname" type="text" maxlength="30" value="<?php echo $session->form->getValue("lastname"); ?>"></td>
        <td><?php echo $session->form->getError("lastname"); ?></td>
    </tr>
    <tr>
        <td>User Name</td>
        <td>:</td>
        <td><input name="username" type="text" maxlength="30" value="<?php echo $session->form->getValue("username"); ?>"></td>
        <td><?php echo $session->form->getError("username"); ?></td>
    </tr>
    <tr>
        <td>Password</td>
        <td>:</td>
        <td><input name="password1" type="password" maxlength="30"></td>
        <td><?php echo $session->form->getError("password"); ?></td>
    </tr>
    <tr>
        <td>Re-enter Password</td>
        <td>:</td>
        <td><input name="password2" type="password" maxlength="30"></td>
    </tr>
    <tr>
        <td>Email</td>
        <td>:</td>
        <td><input name="email" type="text" maxlength="30" value="<?php echo $session->form->getValue("email"); ?>"></td>
        <td><?php echo $session->form->getError("email"); ?></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td><input type="submit" name="submit" value="Register"></td>
    </tr>
</table>