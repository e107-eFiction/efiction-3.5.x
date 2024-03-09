		</div>
		<div class="gb-20 sidebar">
				<h3><span>{info_title}</span></h3>{info_content}<br /><br />{online_content}

				<!-- Just delete the < !-- and -- > before and after the shoutbox/poll to make it visible. Remember to add the blocks at your admin panel. -->
				<br />Your <b>shoutbox and/or poll</b> could be here. Just edit the footer.tpl<br />
				<!-- <h3>{shoutbox_title}</h3>{shoutbox_content} -->
				<!-- <h3>{poll_title}</h3>{poll_content} -->
				<br />
				<div align="center">{xml} {skinchange_content}</div>
		</div>

		<div class="gb-full">
				<div class="gb-33"><h3>{featured_title}</h3>{featured_content}</div>
				<div class="gb-33"><h3>{recent_title}</h3>{recent_content}</div>
				<div class="gb-33"><h3>{random_title}</h3>{random_content}</div>
		</div>
		
	<!-- START BLOCK : footer -->
    <div class="gb-full footer">
			<hr />
			{footer}
			<div class="copyright">Skin by <a href="http://artphilia.de">Artphilia Designs</a>. All rights reserved.</div>
    </div>

	</div> <!-- closing content grid -->   			
	<!-- END BLOCK : footer -->

	</body>
</html>