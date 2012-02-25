<?php
/* $LastChangedRevision: $ */
$plugin['version'] = '0.3';
$plugin['author'] = 'Robert Wetzlmayr, Threshold State';
$plugin['author_uri'] = 'http://wetzlmayr.com/';
$plugin['description'] = 'Word count and article link for article edit pages';
$plugin['order'] = 5;
$plugin['type'] = '4';
$plugin['textpack'] = <<< EOT
#@admin
#@language de-de
words => Wörter
EOT;

if (!defined('txpinterface'))
	@include_once('zem_tpl.php');

if(0){
	?>
# --- BEGIN PLUGIN HELP ---

*wet_article_info* is a very simple plugin that displays some extra information on the *article > write* page.

When editing an existing article, you'll see something like this just below the Last Modified details:

bq. 705 words (680 + 25) | comments

The first item is a word count: the total words, followed by individual counts for the body and excerpt.

@comments@ will display a short summary of article comments, if any, with edit links for each.

The information is _not_ real time; it is updated when you save an article or open it for editing.

Based upon zem_article_info 0.2.

# --- END PLUGIN HELP ---
<?php
}
# --- BEGIN PLUGIN CODE ---

register_callback('wet_article_info', 'article_ui', 'author');

function wet_article_info($event, $step, $default, $rs) {
	global $app_mode;
	if ($rs) {
		$bcount = str_word_count(strip_tags($rs['Body_html']));
		$ecount = str_word_count(strip_tags($rs['Excerpt_html']));
		$wcount = $bcount + $ecount;

		// comments
		$comments = '';
		$comment_link = '';
		$rows = safe_rows('*', 'txp_discuss', "parentid='".doSlash($rs['ID'])."' and visible='".VISIBLE."' order by posted desc");
		if ($rows) {
			foreach ($rows as $c) {
				$m = preg_replace('@\s+@', ' ', strip_tags($c['message']));
				if (strlen($m) > 60)
					$m = substr($m, 0, 60).'&hellip;';
				$edit_link = eLink('discuss', 'discuss_edit', 'discussid', $c['discussid'], $m);
				$cout[] = $edit_link.br.$c['email'].' | '.safe_strftime('since', strtotime($c['posted']));
			}
			$comments = doWrap($cout, 'ul', 'li', '', '', ' id="wet_article_comments" style="display:none;"');
			$comment_link = ' | <a href="#" onclick="toggleDisplay(\'wet_article_comments\'); return false;">'.gTxt('comments').'</a>';
		}

		// main text
		$extra = ($bcount && $ecount) ? " ($bcount + $ecount)" : '';
		if ($app_mode == 'async') {
			send_script_response('$("#wet_article_info").remove();');
		}
		return $default.n.
			tag(
				graf("<small>$wcount ".gTxt('words')."$extra $comment_link</small>").n.
				$comments,
				'div', ' id="wet_article_info"'
			);
	}
}
# --- END PLUGIN CODE ---
?>
