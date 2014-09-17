<?php
include_once('import/constants.php');

$query = "SELECT delivery.id, messages.message, `username`, `access`, `align`, `sent`, `received` FROM " . $table_prefix . "messages AS messages, " . $table_prefix . "delivery AS delivery WHERE messages.id = delivery.message AND `session` = '$guest_login_id' AND `to` = '$operator_login_id' AND `status` >= '0' ORDER BY `datetime`";
$rows = $SQL->selectall($query);
if (is_array($rows)) {
        foreach ($rows as $key => $row) {
                if (is_array($row)) {

                        $id = $row['id'];
                        $username = addslashes($row['username']);
                        $message = addslashes($row['message']);
                        $access = $row['access'];
                        $align = $row['align'];
                        $sent = $row['sent'];
                        $received = $row['received'];

                        // Search and replace smilies with images if smilies are on
                        if ($guest_smilies == true) {
                                $message = htmlSmilies($message, './pictures/');
                        }

                        if ($align == '1') { $align = 'left'; } elseif ($align == '2') { $align = 'center'; } elseif ($align == '3') { $align = 'right'; }

                        // Outputs sent message
                        if ($access == '0'){
?>
<table width="100%" border="0" align="center">
        <tr>
                <td><div align="<?php echo($align); ?>"><?php if ($row['username'] != '') { ?><strong><?php echo($username); ?></strong>: <?php } ?><?php echo($message); ?><?php if ($row['hidden'] == '0') { ?><br></div></td>
        </tr>
</table>
<?php
                        }
                        // Outputs received message
                        if ($access == '1'){
?>
<table width="100%" border="0" align="center">
        <tr>
                <td><div align="<?php echo($align); ?>"><?php if ($row['username'] != '') { ?><strong><?php echo($username); ?></strong>: <?php } ?><?php if ($row['hidden'] == '0') { ?><br></div></td>
        </tr>
</table>
<?php
                        }
                }
        }
}

	}
}

?>
