
		<div id="sort">
			<a href="./blog.php">
				<span>All</span>
			</a>
			<span id="topicchoice">
				<span>Topics</span>
				<div class="dropdown dropdown-left">
					<?php $topicCount=count($topics); for($i=0; $i<$topicCount; $i++) {echo '<a href="./blog.php?topic='.$topics[$i].'">'.$topics[$i].'</a>';} ?>
				</div>
			</span>
			<span id="tagchoice">
				<span>Tags</span>
				<div class="dropdown dropdown-left">
					<input id="tagSearch" placeholder="Enter Tag">
					<button id="tagSearchButton">Search</button>
				</div>
			</span>
		</div>