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

namespace Components\Support\Admin\Controllers;

use Hubzero\Component\AdminController;
use Request;
use Date;
use Lang;

include_once dirname(dirname(__DIR__)) . '/models/status.php';

/**
 * Support controller class for ticket stats
 */
class Stats extends AdminController
{
	/**
	 * Displays some overview stats of tickets
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Instantiate a new view
		$filters = array(
			'type'  => Request::getVar('type', 'submitted'),
			'group' => Request::getVar('group', ''),
			'sort'  => Request::getVar('sort', 'name'),
			'year'  => Request::getInt('year', Date::of('now')->format('Y')),
			'month' => Date::of('now')->format('m')
		);

		$filters['type'] = ($filters['type'] == 'automatic') ? 1 : 0;

		$this->view->opened = array();
		$this->view->closed = array();

		$sql = "SELECT DISTINCT(g.`cn`), g.description
				FROM `#__support_tickets` AS s
				LEFT JOIN `#__xgroups` AS g ON g.gidNumber=s.`group_id`
				WHERE s.`group_id` > 0
				AND s.type=" . $this->database->quote($filters['type']) . "
				ORDER BY g.description ASC";
		$this->database->setQuery($sql);
		$this->view->groups = $this->database->loadObjectList();

		// Users
		$this->view->users = null;

		if ($filters['group'] || $filters['group'] == '_none_')
		{
			$query = "SELECT a.username, a.name, a.id
				FROM `#__users` AS a, `#__xgroups` AS g, `#__xgroups_members` AS gm
				WHERE g.cn=" . $this->database->quote($filters['group']) . " AND g.gidNumber=gm.gidNumber AND gm.uidNumber=a.id
				ORDER BY a.name";
		}
		else
		{
			$query = "SELECT DISTINCT a.username, a.name, a.id
				FROM `#__users` AS a
				INNER JOIN `#__support_tickets` AS s ON s.owner = a.id
				WHERE a.block = '0' AND s.type=" . $this->database->quote($filters['type']) . "
				ORDER BY a.name";
		}

		$this->database->setQuery($query);
		$users = $this->database->loadObjectList();

		// First ticket
		$sql = "SELECT YEAR(created)
				FROM `#__support_tickets`
				WHERE report!=''
				AND type=" . $this->database->quote($filters['type']) . " ORDER BY created ASC LIMIT 1";
		$this->database->setQuery($sql);
		$first = intval($this->database->loadResult());

		// Opened tickets
		$sql = "SELECT id, created, YEAR(created) AS `year`, MONTH(created) AS `month`, status, owner
				FROM `#__support_tickets`
				WHERE report!=''
				AND type=" . $this->database->quote($filters['type']);
		if (!$filters['group'] || $filters['group'] == '_none_')
		{
			$sql .= " AND `group_id`=0";
		}
		else
		{
			$gidNumber = 0;
			if ($group = \Hubzero\User\Group::getInstance($filters['group']))
			{
				$gidNumber = $group->get('gidNumber');
			}
			$sql .= " AND `group_id`=" . $this->database->quote($gidNumber);
		}
		$sql .= " ORDER BY created ASC";
		$this->database->setQuery($sql);
		$openTickets = $this->database->loadObjectList();

		$owners = array();

		$open = array();
		$this->view->opened['open'] = 0;
		$this->view->opened['new'] = 0;
		$this->view->opened['unassigned'] = 0;
		foreach ($openTickets as $o)
		{
			if (!isset($open[$o->year]))
			{
				$open[$o->year] = array();
			}
			if (!isset($open[$o->year][$o->month]))
			{
				$open[$o->year][$o->month] = 0;
			}
			$open[$o->year][$o->month]++;

			$this->view->opened['open']++;

			if (!$o->status)
			{
				$this->view->opened['new']++;
			}
			if (!$o->owner)
			{
				$this->view->opened['unassigned']++;
			}
			else
			{
				if (!isset($owners[$o->owner]))
				{
					$owners[$o->owner] = 0;
				}
				$owners[$o->owner]++;
			}
		}

		// Closed tickets
		$sql = "SELECT t.id AS ticket, t.owner AS created_by, t.closed AS created, YEAR(t.closed) AS `year`, MONTH(t.closed) AS `month`, UNIX_TIMESTAMP(t.created) AS opened, UNIX_TIMESTAMP(t.closed) AS closed
				FROM `#__support_tickets` AS t
				WHERE t.report!=''
				AND t.type=" . $this->database->quote($filters['type']) . " AND t.open=0";
		if (!$filters['group'] || $filters['group'] == '_none_')
		{
			$sql .= " AND t.`group_id`=0";
		}
		else if ($filters['group'])
		{
			$gidNumber = 0;
			if ($group = \Hubzero\User\Group::getInstance($filters['group']))
			{
				$gidNumber = $group->get('gidNumber');
			}
			$sql .= " AND t.`group_id`=" . $this->database->quote($gidNumber);
		}
		$sql .= " ORDER BY t.closed ASC";

		$this->database->setQuery($sql);
		$clsd = $this->database->loadObjectList();

		$this->view->opened['closed'] = 0;
		$closedTickets = array();
		foreach ($clsd as $closed)
		{
			if (!isset($closedTickets[$closed->ticket]))
			{
				$closedTickets[$closed->ticket] = $closed;
			}
			else
			{
				if ($closedTickets[$closed->ticket]->created < $closed->created)
				{
					$closedTickets[$closed->ticket] = $closed;
				}
			}
		}
		$this->view->closedTickets = $closedTickets;
		$closed = array();
		foreach ($closedTickets as $o)
		{
			if (!isset($closed[$o->year]))
			{
				$closed[$o->year] = array();
			}
			if (!isset($closed[$o->year][$o->month]))
			{
				$closed[$o->year][$o->month] = 0;
			}
			$closed[$o->year][$o->month]++;
			$this->view->opened['closed']++;
		}

		// Group data by year and gather some info for each user
		$y = date("Y");
		$y++;
		$this->view->closedmonths = array();
		$this->view->openedmonths = array();
		for ($k=$first, $n=$y; $k < $n; $k++)
		{
			$this->view->closedmonths[$k] = array();
			$this->view->openedmonths[$k] = array();

			for ($i = 1; $i <= 12; $i++)
			{
				if ($k == $filters['year'] && $i > $filters['month'])
				{
					break;
					//$this->view->closedmonths[$k][$i] = 'null';
					//$this->view->openedmonths[$k][$i] = 'null';
				}
				else
				{
					$this->view->closedmonths[$k][$i] = (isset($closed[$k]) && isset($closed[$k][$i])) ? $closed[$k][$i] : 0;
					$this->view->openedmonths[$k][$i] = (isset($open[$k]) && isset($open[$k][$i]))     ? $open[$k][$i]   : 0;
				}

				foreach ($users as $j => $user)
				{
					if (!isset($user->total))
					{
						$user->total = 0;
					}
					if (!isset($user->tickets))
					{
						$user->tickets = array();
					}
					if (!isset($user->closed))
					{
						$user->closed = array();
					}
					if (!isset($user->closed[$k]))
					{
						$user->closed[$k] = array();
					}

					if ($i <= "9"&preg_match("#(^[1-9]{1})#", $i))
					{
						$filters['month'] = "0$i";
					}
					if ($k == $filters['year'] && $i > $filters['month'])
					{
						$user->closed[$k][$i] = 'null';
					}
					else
					{
						$user->closed[$k][$i] = 0;
						foreach ($clsd as $c)
						{
							if (intval($c->year) == intval($k) && intval($c->month) == intval($i))
							{
								if ($c->created_by == $user->id)
								{
									$user->closed[$k][$i]++;
									$user->total++;
									$user->tickets[] = $c;
								}
							}
						}
					}

					$users[$j] = $user;
				}
			}
		}

		// Sort users by number of tickets closed
		$u = array();
		foreach ($users as $k => $user)
		{
			$user->assigned = 0;
			if (isset($owners[$user->id]))
			{
				$user->assigned = $owners[$user->id];
			}

			$key = (string) $user->total;
			if (isset($u[$key]))
			{
				$key .= '.' . $k;
			}
			$u[$key] = $user;
		}
		krsort($u);

		// Output view
		$this->view
			->set('filters', $filters)
			->set('title', Lang::txt(strtoupper($this->_option)))
			->set('config', $this->config)
			->set('users', $u)
			->set('first', $first)
			->display();
	}
}
