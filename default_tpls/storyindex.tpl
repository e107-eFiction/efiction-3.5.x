<!-- INCLUDE BLOCK : header -->
<div id="pagetitle">{title} by {author}</div>
<div id="sort"> [{reviews} - {numreviews}] {score}{printicon}</div>
<div id="output">
<div class="jumpmenu">{jumpmenu}</div>
<div class="listbox">
<div class="content">{featuredstory}<span class="label">Summary: </span>{summary}<br />
<span class="label">Rated:</span> {rating}<br />
<span class="label">Categories:</span> {category} <span class="label">Characters: </span> {characters}<br />
{classifications}
<span class="label">Challenges:</span> {challengelinks}<br /> <span class="label">Series:</span> {serieslinks}<br />
<span class="label">Chapters: </span> {numchapters} <span class="label">Completed:</span> {completed} <br /> 
<span class="label">Word count:</span> {wordcount} <span class="label">Read:</span> {count}<br />
<span class="label"> Published: </span>{published} <span class="label">Updated:</span> {updated} </div>
{adminlinks}
</div>
<div style='text-align: center;'>{addtofaves}</div>
<!-- START BLOCK : storynotes -->
<div class='notes'><div class='title'><span class='label'>Story Notes:</span></div><div class='noteinfo'>{storynotes}</div></div>
<!-- END BLOCK : storynotes -->
<!-- START BLOCK : storyindexblock -->
      <p><b>{chapternumber}. {title} </b>by {author} [{reviews} - {numreviews}] {ratingpics} ({wordcount} words)<br />
	{chapternotes}{adminoptions}</p>
<!-- END BLOCK : storyindexblock -->
{storyend}{last_read}
<div id="pagelinks"><div class="jumpmenu">{jumpmenu2}</div></div>
<div class="respond">{roundrobin}</div>
{reviewform}
</div>
<!-- INCLUDE BLOCK : footer -->