<table class="forms">
    <tr>
        <td width="70">Username</td>
        <td width="5">:</td>
        <td width="152"><input name="username" type="text" maxlength="<?php MAX_LEN?>" value="<?php echo $session->form->getValue("username"); ?>"></td>
        <td width="300"><?php echo $session->form->getError("login"); ?></td>
    </tr>
    <tr>
        <td>Password</td>
        <td>:</td>
        <td><input name="password" type="password" maxlength="<?php MAX_LEN?>"></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>
            <input type="submit" name="submit" value="Login" />
            &nbsp;
            <input type="button" value="New User" onclick="location.href='register.php'" />
        </td>
    </tr>
</table>
