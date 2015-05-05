<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2013 Purdue University. All rights reserved.
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
 * @author    Sam Wilson <samwilson@purdue.edu>
 * @copyright Copyright 2005-2013 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Hubzero\Console\Command;

use Hubzero\Utility\Date;
use Hubzero\Config\Registry;

/**
 * Database class
 **/
class Database extends Base implements CommandInterface
{
	/**
	 * Default (required) command - just executes run
	 *
	 * @return void
	 **/
	public function execute()
	{
		$this->output = $this->output->getHelpOutput();
		$this->help();
		$this->output->render();
	}

	/**
	 * Dump the database
	 *
	 * @return void
	 * @author
	 **/
	public function dump()
	{
		$tables   = App::get('db')->getTableList();
		$prefix   = App::get('db')->getPrefix();
		$excludes = [];
		$now      = new Date;
		$exclude  = '';
		$includes = (array)$this->arguments->getOpt('include-table', []);

		if (!$this->arguments->getOpt('all-tables'))
		{
			$this->output->addLine('Dumping database with all prefixed tables included');
			foreach ($tables as $table)
			{
				if (strpos($table, $prefix) !== 0 && !in_array(str_replace('#__', $prefix, $table), $includes))
				{
					$excludes[] = Config::get('db') . '.' . $table;
				}
				elseif (in_array(str_replace('#__', $prefix, $table), $includes))
				{
					$this->output->addLine('Also including `' . $table . '`');
				}
			}

			// Build exclude list string
			$exclude = '--ignore-table=' . implode(' --ignore-table=', $excludes);
		}
		else
		{
			$this->output->addLine('Dumping database with all tables included');
		}

		// Add save location option

		$home     = getenv('HOME');
		$hostname = gethostname();
		$filename = tempnam($home, "{$hostname}.mysql.dump." . $now->format('Y.m.d') . ".sql.");

		// Build command
		$cmd = "mysqldump -u " . Config::get('user') . " -p'" . Config::get('password') . "' " . Config::get('db') . " --routines {$exclude} > {$filename}";

		exec($cmd);

		// Print out location of file
		$this->output->addLine('File saved to: ' . $filename, 'success');
	}

	/**
	 * Load a database dump
	 *
	 * @return void
	 **/
	public function load()
	{
		if (!$infile = $this->arguments->getOpt(3))
		{
			$this->output->error('Please provide an input file');
		}
		else
		{
			if (!is_file($infile))
			{
				$this->output->error("'{$infile}' does not appear to be a valid file");
			}
		}

		// First, set some things aside that we need to reapply after the update
		$params                           = [];
		$params['com_system']             = \Component::params('com_system');
		$params['com_tools']              = \Component::params('com_tools');
		$params['com_usage']              = \Component::params('com_usage');
		$params['com_users']              = \Component::params('com_users');
		$params['plg_projects_databases'] = \Plugin::params('projects', 'databases');

		$tables = App::get('db')->getTableList();

		// See if we should drop all tables first
		if ($this->arguments->getOpt('drop-all-tables'))
		{
			$this->output->addLine('Dropping all tables...');
			foreach ($tables as $table)
			{
				App::get('db')->dropTable($table);
			}
		}

		// Craft the command to be executed
		$infile = escapeshellarg($infile);
		$cmd    = "mysql -u " . Config::get('user') . " -p'" . Config::get('password') . "' -D " . Config::get('db') . " < {$infile}";

		$this->output->addLine('Loading data from ' . $infile . '...');

		// Now push the big red button
		exec($cmd);

		// Now load some things back up
		foreach ($params as $k => $v)
		{
			if (!empty($v))
			{
				if (!method_exists($v, 'toArray'))
				{
					$v = new Registry($v);
				}

				// @FIXME: get rid of JStuff
				$db    = \JFactory::getDbo();
				$table = new \JTableExtension($db);
				$table->load(array('name'   => $k));
				$table->bind(array('params' => $v->toArray()));
				$table->store();
			}
		}

		$this->output->addLine('Load complete!', 'success');
	}

	/**
	 * Output help documentation
	 *
	 * @return void
	 **/
	public function help()
	{
		$this
			->output
			->addOverview(
				'Database utility functions.'
			)
			->addArgument(
				'--include-table: Include a specific table',
				'Specify a given table to be included in the dump. This primarily
				would be used to include a given table from the non-prefixed namespace.',
				'Example: --include-table=migration'
			)
			->addArgument(
				'--all-tables: Include all tables',
				'By default, the database dump does not include non-prefixed tables
				(example: host, display, etc...). This option can be used to include
				these tables. Use with caution when planning to evenutally load this
				data into another host (ex: dev) as it rarely makes sense to reload
				tool sessions into another environment.'
			)
			->addArgument(
				'--drop-all-tables: Drop all tables',
				'When loading in a database dump, this option will drop all tables
				prior to loading in the given dump. This is often helpful when the
				applied dump is divergent in schema from the current database being
				overwritten.'
			);
	}
}