<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->js();
?>
<div id="recommendations">
	<h3><?php echo Lang::txt('PLG_RESOURCES_RECOMMENDATIONS_HEADER'); ?></h3>
	<div class="subject" id="recommendations-subject" data-base="<?php echo Request::base(true); ?>">
		<?php if ($this->results) { ?>
			<ul>
			<?php foreach ($this->results as $line) { ?>
				<li>
					<a href="<?php echo Route::url('index.php?option=' . $this->option . '&' . ($line->alias ? 'alias=' . $line->alias : 'id=' . $line->id) . '&rec_ref=' . $this->resource->id); ?>"><?php echo $this->escape(stripslashes($line->title)); ?></a>
				</li>
			<?php } ?>
			</ul>
		<?php } else { ?>
			<p><?php echo Lang::txt('PLG_RESOURCES_RECOMMENDATIONS_NO_RESULTS_FOUND'); ?></p>
		<?php } ?>

		<p id="credits">
			<a href="<?php echo Request::base(true); ?>/about/hubzero#recommendations"><?php echo Lang::txt('PLG_RESOURCES_RECOMMENDATIONS_POWERED_BY'); ?></a>
		</p>
	</div>
	<div class="aside">
		<p><?php echo Lang::txt('PLG_RESOURCES_RECOMMENDATIONS_EXPLANATION'); ?></p>
	</div>
</div>
