<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Security;

use Nette;



/**
 * Represents the user of application.
 *
 * @author     David Grudl
 */
interface IIdentity
{

	/**
	 * Returns the name of user.
	 * @return string
	 */
	//function getName();

	/**
	 * Returns a list of roles that the user is a member of.
	 * @return array
	 */
	function getRoles();

}
