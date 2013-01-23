<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$item = $this->row->item();

if ($item->get('title')) { ?>
		<h4>
			<?php echo $this->escape(stripslashes($item->get('title'))); ?>
		</h4>
<?php } 

$path = DS . trim($this->params->get('filepath', '/site/collections'), DS) . DS . $item->get('id');
$base = 'index.php?option=' . $this->option;

$assets = $item->assets();

if ($assets->total() > 0)
{
	$images = array();
	$files = array();

	foreach ($assets as $asset)
	{
		if ($asset->image())
		{
			$images[] = $asset;
		}
		else
		{
			$files[] = $asset;
		}
	}
	if (count($images) > 0)
	{
		//$assets->rewind();
		$first = array_shift($images);

		list($originalWidth, $originalHeight) = getimagesize(JPATH_ROOT . $path . DS . ltrim($first->get('filename'), DS));
		$ratio = $originalWidth / $originalHeight;
		?>
			<div class="holder">
				<a rel="lightbox" href="<?php echo $path . DS . ltrim($first->get('filename'), DS); ?>" class="img-link">
					<img src="<?php echo $path . DS . ltrim($first->get('filename'), DS); ?>" alt="<?php echo ($first->get('description')) ? $this->escape(stripslashes($first->get('description'))) : ''; ?>" class="img" style="height: <?php echo round(300 / $ratio, 0, PHP_ROUND_HALF_UP); ?>px;" />
				</a>
			</div>
		<?php
		if (count($images) > 0)
		{
			?>
			<div class="gallery">
			<?php
			foreach ($images as $asset)
			{
				?>
				<a rel="lightbox" href="<?php echo $path . DS . ltrim($asset->get('filename'), DS); ?>" class="img-link">
					<img src="<?php echo $path . DS . ltrim($asset->get('filename'), DS); ?>" alt="<?php echo ($asset->get('description')) ? $this->escape(stripslashes($asset->get('description'))) : ''; ?>" class="img" />
				</a>
				<?php
			}
			?>
				<div class="clearfix"></div>
			</div>
		<?php
		}
	}
	//$assets->rewind();
	if (count($files) > 0)
	{
?>
		<ul class="file-list">
<?php
		foreach ($files as $asset)
		{
?>
				<li class="type-<?php echo $asset->get('type'); ?>">
					<a href="<?php echo ($asset->get('type') == 'link') ? $asset->get('filename') : $path . DS . ltrim($asset->get('filename'), DS); ?>">
						<?php echo $asset->get('filename'); ?>
					</a>
					<span class="file-meta">
						<span class="file-size">
				<?php if ($asset->get('type') != 'link') { ?>
							<?php echo Hubzero_View_Helper_Html::formatSize(filesize(JPATH_ROOT . $path . DS . ltrim($asset->get('filename'), DS))); ?>
				<?php } else { ?>
							<?php echo JText::_('link'); ?>
				<?php } ?>
						</span>
				<?php if ($asset->get('description')) { ?>
						<span class="file-description">
							<?php echo Hubzero_View_Helper_Html::formatSize(filesize(JPATH_ROOT . $path . DS . ltrim($asset->get('filename'), DS))); ?>
						</span>
				<?php } ?>
					</span>
				</li>
<?php
		}
?>
		</ul>
<?php
	}
}
?>
<?php if ($item->get('description') || $this->row->get('description')) { ?>
		<p class="description">
			<?php echo ($this->row->get('description')) ? $this->escape(stripslashes($this->row->get('description'))) : $this->escape(stripslashes($item->get('description'))); ?>
		</p>
<?php } ?>