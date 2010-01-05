<h2>Confirm</h2>
<p>Please confirm your details before the book details are submitted.</p>

<table class="bookDetail">
	<tr>
        <td width="105" class="highLightRowCol">Title</td>
        <td width="250"><b><?php echo $session->form->getValue("title"); ?></b></td>
    </tr>
    <tr>
        <td class="highLightRowCol">Author</td>
        <td><b><?php echo $session->form->getValue("author"); ?></b></td>
    </tr>
    <tr>
        <td class="highLightRowCol">Publisher</td>
        <td><b><?php echo $session->form->getValue("publisher"); ?></b></td>
    </tr>
    <tr>
        <td class="highLightRowCol">Call Number</td>
        <td><b><?php echo $session->form->getValue("callNo"); ?></b></td>
    </tr>
    <tr>
        <td class="highLightRowCol">Book State</td>
        <td><b><?php echo $session->form->getValue("state"); ?></b></td>
    </tr>
    <tr>
        <td class="highLightRowCol">ISBN Number</td>
        <td><b><?php echo $session->form->getValue("isbn"); ?></b></td>
    </tr>
    <tr>
        <td class="highLightRowCol">Shelf</td>
        <td><b><?php echo $session->form->getValue("shelf"); ?></b></td>
    </tr>
</table>
