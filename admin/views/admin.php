<?php
/**
 * Represents the view for the administration dashboard.
 */
?>

<div class="wrap">
	<h1>User Access</h1>
	<?php
	if ( self::getUserMessage() != '' ) {
		echo '<div class="updated"><p>'.self::getUserMessage().'</p></div>';
	}
	?>
	<div class="section">
		<p>Please select a user from the dropdown below. Assign which admin menu items they are able to access.</p>
		<p><?php echo self::getMySelect(); ?></p>
	</div>
	<br />
	<?php
	if ( isset($_GET['id']) ) {
		?>
		<input type="button" class="button-secondary right deselectAll" value="Deselect All">
		<input type="button" class="button-secondary right selectAll" value="Select All">
		<div class="clear"></div>
		<div class="section">
			<p>Now select the menu items available to this user.</p>
			<p>
				<form name="myMenu" method="post">
					<input type="hidden" name="userId" value="<?php echo $userId; ?>">
					<?php echo self::getMyMenuCheck(); ?>
					<div class="clear"></div>
					<br />
					<p>Specify the message you would like to show the user when they try to access something not available to them.</p>
					<p><input type="text" name="user_message" id="user_message" class="textMe" placeholder="Sorry, you do not have access to this resource." value="<?php echo self::getMessage(); ?>"></p>
					<br />
					<input type="submit" class="user-submit button-primary" name="go" value="Save">
				</form>
			</p>
		</div>
		<?php
	}
	?>
</div>