<!-- INCLUDE BLOCK : header -->
<div class="gb-full"> 
	<h1>{title} von {author}</h1>
	<div align="right">{printicon} {printepub}</div>
	<div id="output">
	<div class="jumpmenu">{jumpmenu}</div>
	<div class="listbox">
	<b>Zusammenfassung:</b> {summary}<br>
	<b>Eingestuft:</b> {rating} [{reviews} - {numreviews}] {score} {featuredstory}<br>
	<b>Kategorie:</b> {category}<br>
	<b>Charaktere:</b> {characters}<br>
	{classifications}
	<b>Herausforderung:</b> {challengelinks}<br> <b>Serie:</b> {serieslinks}<br>
	<b>Kapitel: </b> {numchapters} <b>Abgeschlossen:</b> {completed} <br> 
	<b>Wörter:</b> {wordcount} <b>Gelesen:</b> {count}<br>
	<b>Veröffentlicht:</b> {published} <b>Aktualisiert:</b> {updated}
	</div>
	{adminlinks}<br>
	<div align="center">{addtofaves}</div>
	</div>
	
	<br><br>
	
	<!-- START BLOCK : storynotes -->
	<blockquote><i><b>Story Anmerkungen:</b></i> {storynotes}</blockquote>
	<!-- END BLOCK : storynotes -->
	<br><br>
	
	<!-- START BLOCK : storyindexblock -->
	<b>{chapternumber}.</b> {title} by {author} [{reviews} - {numreviews}] {ratingpics} ({wordcount} words)
	<br><i>{chapternotes}</i> {adminoptions}<br>
	<!-- END BLOCK : storyindexblock -->
	
	{storyend} {last_read}
	<div id="pagelinks"><div class="jumpmenu">{jumpmenu2}</div></div>
	<div class="respond">{roundrobin}</div>
	{reviewform}
</div>
<!-- INCLUDE BLOCK : footer -->